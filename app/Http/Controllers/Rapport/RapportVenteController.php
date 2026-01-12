<?php

namespace App\Http\Controllers\Rapport;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\{Article, FamilleArticle};
use App\Models\Parametre\Agent;
use App\Models\Vente\{AcompteClient, FactureClient, SessionCaisse, ReglementClient, ReglementRevendeur};
use App\Models\Vente\Client;
use App\Models\Parametre\PointDeVente;
use App\Models\Revendeur\FactureRevendeur;
use App\Services\ServiceStockEntree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RapportVenteController extends Controller
{
    private $serviceStockEntree;

    function __construct(ServiceStockEntree $serviceStockEntree)
    {
        $this->serviceStockEntree = $serviceStockEntree;
    }

    public function ventesParArticle(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $articleId = $request->get('article_id');

        $query = Article::with(["stocks", "famille"])->orderBy('designation');

        if ($articleId) {
            $query->where('id', $articleId);
        }

        $articles = $query->whereHas("stocks") //articles existant au moins dans un stocks
            ->get()->filter(function ($article) {
                // Check if any stock for this article has qteVendu > 0
                return $article->stocks->contains(function ($stock) use ($article) {
                    return $article->qteVendu($stock->depot_id) > 0;
                });
            })->values(); // Use values() to re-index the collection after filtering

        $articles->map(function ($article) {
            $article->qteTotalVendu = 0;
            $article->qantiteBase = 0;
            $article->stocks->map(function ($stock) use ($article) {
                $article->qteTotalVendu += $article->qteVendu($stock->depot_id);
                $stock->qteTotalVenduStock = $article->qteVendu($stock->depot_id);
                $stock->montantTotalVendu = $article->montantTotalsVendu($stock->depot_id);

                /**STOCK DE BASE */
                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $stock->unite_mesure_id,
                        $stock->article->unite_mesure_id,
                        $stock->article_id
                    );

                $article->qantiteBase += $conversion ? $this->serviceStockEntree
                    ->convertirQuantite(
                        $stock->quantite_reelle,
                        $conversion,
                        $stock->unite_mesure_id
                    ) : 00;
            });
        });

        return view('pages.rapports.ventes.vente-par-article', compact(
            'articles',
            'dateDebut',
            'dateFin',
            'articleId'
        ));
    }

    public function ventesParFamille(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $familleId = $request->get('famille_id');

        $query = Article::with(["stocks", "famille"])->orderBy('designation');

        if ($familleId) {
            $query->where('id', $familleId);
        }

        $articles = $query->whereHas("stocks") //articles existant au moins dans un stocks
            ->get()->filter(function ($article) {
                // Check if any stock for this article has qteVendu > 0
                return $article->stocks->contains(function ($stock) use ($article) {
                    return $article->qteVendu($stock->depot_id) > 0;
                });
            })->values(); // Use values() to re-index the collection after filtering

        $articles->map(function ($article) {
            $article->qteTotalVendu = 0;
            $article->qantiteBase = 0;
            $article->stocks->map(function ($stock) use ($article) {
                $article->qteTotalVendu += $article->qteVendu($stock->depot_id);
                $stock->qteTotalVenduStock = $article->qteVendu($stock->depot_id);
                $stock->montantTotalVendu = $article->montantTotalsVendu($stock->depot_id);

                /**STOCK DE BASE */
                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $stock->unite_mesure_id,
                        $stock->article->unite_mesure_id,
                        $stock->article_id
                    );

                $article->qantiteBase += $conversion ? $this->serviceStockEntree
                    ->convertirQuantite(
                        $stock->quantite_reelle,
                        $conversion,
                        $stock->unite_mesure_id
                    ) : 00;
            });
        });

        $familles = FamilleArticle::where('statut', true)
            ->orderBy('libelle_famille')
            ->get(['id', 'code_famille', 'libelle_famille']);

        return view('pages.rapports.ventes.vente-par-famille', compact(
            'articles',
            'familles',
            'dateDebut',
            'dateFin',
            'familleId'
        ));
    }

    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Ventes du jour
        $ventesJour = DB::table('facture_clients')
            ->where('statut', 'validee')
            ->whereDate('date_facture', $today)
            ->select([
                DB::raw('COUNT(*) as nombre_factures'),
                DB::raw('SUM(montant_ttc) as ca_total'),
                DB::raw('SUM(montant_regle) as montant_encaisse')
            ])->first();

        // Ventes mensuelles par jour
        $ventesParJour = DB::table('facture_clients')
            ->where('statut', 'validee')
            ->whereBetween('date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                DB::raw('DATE(date_facture) as date'),
                DB::raw('COUNT(*) as nombre_factures'),
                DB::raw('SUM(montant_ttc) as ca_total')
            ])
            ->groupBy('date')
            ->get();

        // Top 5 clients
        $topClients = DB::table('facture_clients as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                'c.raison_sociale',
                DB::raw('COUNT(f.id) as nombre_factures'),
                DB::raw('SUM(f.montant_ttc) as ca_total')
            ])
            ->groupBy('c.id', 'c.raison_sociale')
            ->orderBy('ca_total', 'desc')
            ->limit(5)
            ->get();

        // Top 5 articles
        $topArticles = DB::table('ligne_facture_clients as lf')
            ->join('facture_clients as f', 'f.id', '=', 'lf.facture_client_id')
            ->join('articles as a', 'a.id', '=', 'lf.article_id')
            ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                'a.designation',
                DB::raw('SUM(lf.quantite) as quantite_vendue'),
                DB::raw('SUM(lf.montant_ttc) as ca_total')
            ])
            ->groupBy('a.id', 'a.designation')
            ->orderBy('ca_total', 'desc')
            ->limit(5)
            ->get();

        // Ventes par famille
        $ventesParFamille = DB::table('ligne_facture_clients as lf')
            ->join('facture_clients as f', 'f.id', '=', 'lf.facture_client_id')
            ->join('articles as a', 'a.id', '=', 'lf.article_id')
            ->join('famille_articles as fa', 'fa.id', '=', 'a.famille_id')
            // ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                'fa.libelle_famille',
                DB::raw('SUM(lf.montant_ttc) as ca_total')
            ])
            ->groupBy('fa.id', 'fa.libelle_famille')
            ->get();

        return view('pages.rapports.ventes.dashboard-vente', compact(
            'ventesJour',
            'ventesParJour',
            'topClients',
            'topArticles',
            'ventesParFamille'
        ));
    }

    public function ventesParClient(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $clientId = $request->get('client_id');

        $query = Article::with(["stocks", "famille"])->orderBy('designation');

        $articles = $query->whereHas("stocks") //articles existant au moins dans un stocks
            ->get()->filter(function ($article) use ($clientId) {
                // Check if any stock for this article has qteVendu > 0
                return $article->stocks->contains(function ($stock) use ($article, $clientId) {
                    /** Les clients concernés */
                    if ($clientId) {
                        $clientFactureIds = $article->ventes->map(function ($ligneFacture) {
                            //seuls les factures validées
                            if ($ligneFacture->factureClient->validated_by) {
                                return $ligneFacture->factureClient
                                    ->client_id; //on recupere les client_id
                            }
                        });

                        $clientRevendeurIds = $article->venteRevendeurs->map(function ($ligneFactureRevendeur) {
                            //seuls les factures validées
                            if ($ligneFactureRevendeur->factureRevendeur->validated_by) {
                                return $ligneFactureRevendeur->factureRevendeur
                                    ->client_id; //on recupere les client_id
                            }
                        });

                        $clientIds = $clientFactureIds
                            ->concat($clientRevendeurIds)
                            ->toArray();

                        /** Si le client se trouve dans les ventes & qteVendu>0 */
                        return in_array($clientId, $clientIds) &&
                            $article->qteVendu($stock->depot_id) > 0;
                    }

                    /** Sans un client */
                    return $article->qteVendu($stock->depot_id) > 0;
                });
            })->values(); // Use values() to re-index the collection after filtering

        $articles->map(function ($article) use ($clientId) {
            $article->qteTotalVendu = 0;
            $article->qantiteBase = 0;
            $article->client = Client::find($clientId);
            $article->stocks->map(function ($stock) use ($article) {
                $article->qteTotalVendu += $article->qteVendu($stock->depot_id);
                $stock->qteTotalVenduStock = $article->qteVendu($stock->depot_id);
                $stock->montantTotalVendu = $article->montantTotalsVendu($stock->depot_id);

                /**STOCK DE BASE */
                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $stock->unite_mesure_id,
                        $stock->article->unite_mesure_id,
                        $stock->article_id
                    );

                $article->qantiteBase += $conversion ? $this->serviceStockEntree
                    ->convertirQuantite(
                        $stock->quantite_reelle,
                        $conversion,
                        $stock->unite_mesure_id
                    ) : 00;
            });
        });

        $clients = Client::get(["id", "raison_sociale", "code_client"]);

        return view('pages.rapports.ventes.vente-par-client', compact(
            'articles',
            'clients',
            'dateDebut',
            'dateFin',
            'clientId'
        ));
    }

    public function suivieVente(Request $request)
    {
        $dateDebut = $request->get('date_debut') ? Carbon::parse($request->get('date_debut')) : Carbon::now()->startOfMonth();
        $dateFin = $request->get('date_fin') ? Carbon::parse($request->get('date_fin')) : Carbon::now();
        $clientId = $request->get('client_id', null);
        $articleId = $request->get('article_id', null);
        // Base query
        $baseQuery = FactureClient::query()
            ->where('statut', 'validee')
            ->whereBetween('date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
            ->when($clientId, fn($q) => $q->where('client_id', $clientId));

        // Stats
        $stats = [
            'total_factures' => $baseQuery->count(),
            'montant_ht' => $baseQuery->sum('montant_ht'),
            'montant_ttc' => $baseQuery->sum('montant_ttc'),
            'montant_regle' => $baseQuery->sum('montant_regle')
        ];

        // Rapport des ventes par article
        $rapportVentes = DB::table('ligne_facture_clients as l')
            ->join('facture_clients as f', 'l.facture_client_id', '=', 'f.id')
            ->join('articles as a', 'l.article_id', '=', 'a.id')
            ->select([
                'a.code_article',
                'a.designation',
                DB::raw('SUM(l.quantite) as quantite_vendue'),
                DB::raw('SUM(l.montant_ht) as montant_ht'),
                DB::raw('SUM(l.montant_tva) as montant_tva'),
                DB::raw('SUM(l.montant_aib) as montant_aib'),
                DB::raw('SUM(l.montant_ttc) as montant_ttc')
            ])
            ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
            ->when($clientId, fn($q) => $q->where('f.client_id', $clientId))
            ->groupBy('a.id', 'a.code_article', 'a.designation')
            ->get();

        // Ventes par mois
        $ventesParMois = DB::table('facture_clients')
            ->select([
                DB::raw('DATE_FORMAT(date_facture, "%Y-%m") as mois'),
                DB::raw('COUNT(*) as nombre_factures'),
                DB::raw('SUM(montant_ht) as total_ht'),
                DB::raw('SUM(montant_ttc) as total_ttc'),
                DB::raw('SUM(montant_regle) as total_regle')
            ])
            ->where('statut', 'validee')
            ->whereBetween('date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->groupBy(DB::raw('DATE_FORMAT(date_facture, "%Y-%m")'))
            ->orderBy('mois')
            ->get();

        // Liste des articles
        $articles = Article::where('statut', Article::STATUT_ACTIF)
            ->orderBy('designation')
            ->get(['id', 'code_article', 'designation']);

        // Liste des clients
        $clients = Client::where('statut', true)
            ->orderBy('raison_sociale')
            ->get(['id', 'raison_sociale', 'code_client']);

        return view('pages.rapports.ventes.etat-vente', compact(
            'rapportVentes',
            'ventesParMois',
            'articles',
            'clients',
            'stats',
            'dateDebut',
            'dateFin',
            'clientId',
            'articleId'
        ));
    }

    // ventes journalières
    public function ventesJournalieres(Request $request)
    {
        try {
            $date = Carbon::parse($request->date ?? now());

            try {
                $factureClients = FactureClient::with([
                    'client',
                    'createdBy',
                    'lignes.article', // Ajout des lignes et de l'article
                    'reglements' => function ($query) {
                        $query->where('statut', ReglementClient::STATUT_VALIDE);
                    }
                ])
                    ->whereDate('date_facture', $date)
                    // ->where('statut', 'validee')
                    ->get();

                $factureRevendeurs = FactureRevendeur::with([
                    'client',
                    'createdBy',
                    'lignes.article', // Ajout des lignes et de l'article
                    'reglements' => function ($query) {
                        $query->where('statut', ReglementRevendeur::STATUT_VALIDE);
                    }
                ])->whereDate('date_facture', $date)
                    // ->where('statut', 'validee')
                    ->get();

                $factureRevendeurs->map(function ($vente) {
                    $vente->revendeur = true;
                    return $vente;
                });

                $ventes = $factureClients->concat($factureRevendeurs);

                if ($ventes->isEmpty()) {
                    return view('pages.rapports.ventes.vente-journaliere')
                        ->with('warning', 'Aucune vente trouvée pour cette date')
                        ->with('ventes', collect([]))
                        ->with('totaux', [
                            'total_global' => 0,
                            'total_comptant' => 0,
                            'total_credit' => 0,
                        ])
                        ->with('date', $date);
                }
                // Mapping des données
                $ventesFormatted = $ventes->map(function ($facture) {
                    try {
                        $type_vente = 'Crédit';
                        /**
                         * On rajoute une marge de 5F 
                         * au montant réglé
                         * avant de le comparer au montant de la vente à solder
                         */

                        if ($facture->montant_regle + 5 >= $facture->montant_ttc) {
                            $type_vente = 'Comptant';
                        }

                        // Préparation des lignes de détail
                        $lignes = $facture->lignes->map(function ($ligne) {
                            return [
                                'produit' => $ligne->article->designation ?? 'N/A',
                                'quantite' => $ligne->quantite ?? 0,
                                'prix_unitaire' => $ligne->prix_unitaire_ht ?? 0,
                                'total' => $ligne->montant_ttc ?? 0
                            ];
                        });

                        return [
                            'id' => $facture->id,
                            'numero' => $facture->numero ?? 'N/A',
                            'date_ecriture' => $facture->created_at->format('d/m/Y H:i'),
                            'date_vente' => $facture->date_facture->format('d/m/Y'),
                            'reference' => $facture->numero ?? 'N/A',
                            'type_vente' => $type_vente,
                            'moyen_reglement' => $facture->moyen_reglement,
                            'revendeur' => $facture->revendeur,
                            'createdBy' => $facture->createdBy,
                            'categorie_vente' => $facture->client->categorie ?? 'N/A',
                            'statut' => $facture->statut,
                            'client' => $facture->client->raison_sociale ?? 'Client inconnu',
                            'montant_ttc' => $facture->montant_ttc ?? 0,
                            'montant_regle' => $facture->montant_regle ?? 0,
                            'reste_a_payer' => $facture->montant_ttc - ($facture->montant_regle ?? 0),
                            'lignes' => $lignes, // Ajout des lignes de détail
                        ];
                    } catch (\Exception $e) {
                        Log::error('Erreur lors du mapping de la facture #' . $facture->id . ': ' . $e->getMessage());
                        return null;
                    }
                })->filter();


                // Calcul des totaux
                $totaux = [
                    'total_global' => $ventesFormatted->sum('montant_ttc'),
                    'total_comptant' => $ventesFormatted->where('type_vente', 'Comptant')->sum('montant_ttc'),
                    'total_credit' => $ventesFormatted->where('type_vente', 'Crédit')->sum('montant_ttc'),
                ];

                return view('pages.rapports.ventes.vente-journaliere', [
                    'ventes' => $ventesFormatted,
                    'totaux' => $totaux,
                    'date' => $date
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la récupération des ventes: ' . $e->getMessage());
                return back()
                    ->with('error', 'Une erreur est survenue lors de la récupération des données: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Erreur générale: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur inattendue est survenue: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ventes journalières
    public function ventesAgents(Request $request)
    {
        try {
            $agent = $request->agent_id ? Agent::findOrFail($request->agent_id) : Agent::findOrFail(1); //agent par défaut
            $agents = Agent::all();

            try {
                $factureClients = FactureClient::whereNotNull("validated_by")
                    ->with([
                        'client.agent',
                        'createdBy',
                        'lignes.article', // Ajout des lignes et de l'article
                        'reglements' => function ($query) {
                            $query->where('statut', ReglementClient::STATUT_VALIDE);
                        }
                    ])
                    ->whereHas("client", function ($q) use ($agent) {
                        $q->where('agent_id', $agentId ?? $agent->id);
                    })
                    ->orderByDesc('created_at')
                    ->get();

                $factureRevendeurs = FactureRevendeur::whereNotNull("validated_by")
                    ->with([
                        'client',
                        'createdBy',
                        'lignes.article', // Ajout des lignes et de l'article
                        'reglements' => function ($query) {
                            $query->where('statut', ReglementRevendeur::STATUT_VALIDE);
                        }
                    ])
                    ->whereHas("client", function ($q) use ($agent) {
                        $q->where('agent_id', $agentId ?? $agent->id);
                    })
                    ->orderByDesc('created_at')
                    ->get();

                $factureRevendeurs->map(function ($vente) {
                    $vente->revendeur = true;
                    return $vente;
                });

                $ventes = $factureClients->concat($factureRevendeurs);

                if ($ventes->isEmpty()) {
                    return view('pages.rapports.ventes.vente-agent')
                        ->with('warning', 'Aucune vente trouvée pour cette date')
                        ->with('ventes', collect([]))
                        ->with('totaux', [
                            'total_global' => 0,
                            'total_comptant' => 0,
                            'total_credit' => 0,
                        ])
                        ->with('agent', $agent)
                        ->with('agents', $agents);
                }
                // Mapping des données
                $ventesFormatted = $ventes->map(function ($facture) {
                    try {
                        $type_vente = 'Crédit';
                        /**
                         * On rajoute une marge de 5F 
                         * au montant réglé
                         * avant de le comparer au montant de la vente à solder
                         */

                        if ($facture->montant_regle + 5 >= $facture->montant_ttc) {
                            $type_vente = 'Comptant';
                        }

                        // Préparation des lignes de détail
                        $lignes = $facture->lignes->map(function ($ligne) {
                            return [
                                'produit' => $ligne->article->designation ?? 'N/A',
                                'quantite' => $ligne->quantite ?? 0,
                                'prix_unitaire' => $ligne->prix_unitaire_ht ?? 0,
                                'total' => $ligne->montant_ttc ?? 0
                            ];
                        });

                        return [
                            'id' => $facture->id,
                            'numero' => $facture->numero ?? 'N/A',
                            'date_ecriture' => $facture->created_at->format('d/m/Y H:i'),
                            'date_vente' => $facture->date_facture->format('d/m/Y'),
                            'reference' => $facture->numero ?? 'N/A',
                            'type_vente' => $type_vente,
                            'moyen_reglement' => $facture->moyen_reglement,
                            'revendeur' => $facture->revendeur,
                            'createdBy' => $facture->createdBy,
                            'categorie_vente' => $facture->client->categorie ?? 'N/A',
                            'statut' => $facture->statut,
                            'client' => $facture->client?->raison_sociale ?? '---',
                            'agent' => $facture->client?->agent?->nom ?? '---',
                            'montant_ttc' => $facture->montant_ttc ?? 0,
                            'montant_regle' => $facture->montant_regle ?? 0,
                            'reste_a_payer' => $facture->montant_ttc - ($facture->montant_regle ?? 0),
                            'lignes' => $lignes, // Ajout des lignes de détail
                        ];
                    } catch (\Exception $e) {
                        Log::error('Erreur lors du mapping de la facture #' . $facture->id . ': ' . $e->getMessage());
                        return null;
                    }
                })->filter();

                // Calcul des totaux
                $totaux = [
                    'total_global' => $ventesFormatted->sum('montant_ttc'),
                    'total_regle' => $ventesFormatted->sum('montant_regle'),
                    'total_comptant' => $ventesFormatted->where('type_vente', 'Comptant')->sum('montant_ttc'),
                    'total_credit' => $ventesFormatted->where('type_vente', 'Crédit')->sum('montant_ttc'),
                ];

                // return $totaux;
                return view('pages.rapports.ventes.vente-agent', [
                    'ventes' => $ventesFormatted,
                    'totaux' => $totaux,
                    'agent' => $agent,
                    'agents' => $agents
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur lors de la récupération des ventes: ' . $e->getMessage());
                return back()
                    ->with('error', 'Une erreur est survenue lors de la récupération des données: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Erreur générale: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur inattendue est survenue: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function enregistrementsAll(Request $request)
    {
        try {
            // $date = Carbon::parse($request->date);

            try {
                $factureClientsQuery = FactureClient::with([
                    'client',
                    'createdBy',
                    'lignes.article', // Ajout des lignes et de l'article
                    'reglements' => function ($query) {
                        $query->where('statut', ReglementClient::STATUT_VALIDE);
                    }
                ])
                    // ->whereDate('date_facture', $date)
                    ->where('statut', 'validee');
                // ->get();

                $factureRevendeursQuery = FactureRevendeur::with([
                    'client',
                    'createdBy',
                    'lignes.article', // Ajout des lignes et de l'article
                    'reglements' => function ($query) {
                        $query->where('statut', ReglementRevendeur::STATUT_VALIDE);
                    }
                ])
                    // ->whereDate('date_facture', $date)
                    ->where('statut', 'validee');
                // ->get();

                if ($request->date) {
                    $date = Carbon::parse($request->date);
                    $factureClients = $factureClientsQuery
                        ->whereDate('date_facture', $date)
                        ->get();
                    $factureRevendeurs =
                        $factureRevendeursQuery
                        ->whereDate('date_facture', $date)
                        ->get();
                } else {
                    $factureClients = $factureClientsQuery
                        ->get();
                    $factureRevendeurs =
                        $factureRevendeursQuery
                        ->get();
                }

                $factureRevendeurs->map(function ($vente) {
                    $vente->revendeur = true;
                    return $vente;
                });

                $ventes = $factureClients->concat($factureRevendeurs);

                if ($ventes->isEmpty()) {
                    return view('pages.rapports.ventes.enregistrements-all')
                        ->with('warning', 'Aucune vente trouvée pour cette date')
                        ->with('ventes', collect([]))
                        ->with('totaux', [
                            'total_global' => 0,
                            'total_comptant' => 0,
                            'total_credit' => 0,
                        ]);
                }

                // Mapping des données
                $ventesFormatted = $ventes->map(function ($facture) {
                    try {
                        $type_vente = 'Crédit';
                        if ($facture->montant_ttc <= $facture->montant_regle) {
                            $type_vente = 'Comptant';
                        }

                        // Préparation des lignes de détail
                        $lignes = $facture->lignes->map(function ($ligne) {
                            return [
                                'produit' => $ligne->article->designation ?? 'N/A',
                                'quantite' => $ligne->quantite ?? 0,
                                'prix_unitaire' => $ligne->prix_unitaire_ht ?? 0,
                                'total' => $ligne->montant_ttc ?? 0
                            ];
                        });

                        return [
                            'id' => $facture->id,
                            'numero' => $facture->numero ?? 'N/A',
                            'date_ecriture' => $facture->created_at->format('m/d/Y H:i'),
                            'date_vente' => $facture->date_facture->format('m/d/Y'),
                            'reference' => $facture->numero ?? 'N/A',
                            'type_vente' => $type_vente,
                            'revendeur' => $facture->revendeur,
                            'createdBy' => $facture->createdBy,
                            'categorie_vente' => $facture->client->categorie ?? 'N/A',
                            'client' => $facture->client->raison_sociale ?? 'Client inconnu',
                            'montant_ttc' => $facture->montant_ttc ?? 0,
                            'montant_regle' => $facture->montant_regle ?? 0,
                            'reste_a_payer' => $facture->montant_ttc - ($facture->montant_regle ?? 0),
                            'lignes' => $lignes, // Ajout des lignes de détail
                            'statut' => $facture->statut // Ajout des lignes de détail
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Erreur lors du mapping de la facture #' . $facture->id . ': ' . $e->getMessage());
                        return null;
                    }
                })->filter();

                // Calcul des totaux
                $totaux = [
                    'total_global' => $ventesFormatted->sum('montant_ttc'),
                    'total_comptant' => $ventesFormatted->where('type_vente', 'Comptant')->sum('montant_ttc'),
                    'total_credit' => $ventesFormatted->where('type_vente', 'Crédit')->sum('montant_ttc'),
                ];

                return view('pages.rapports.ventes.enregistrements-all', [
                    'ventes' => $ventesFormatted,
                    'totaux' => $totaux,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la récupération des ventes: ' . $e->getMessage());
                return back()
                    ->with('error', 'Une erreur est survenue lors de la récupération des données: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Erreur générale: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur inattendue est survenue: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Détermine le type de vente en fonction du paiement
     */
    private function determinerTypeVente(FactureClient $facture): string
    {
        try {
            if ($facture->est_solde) {
                return 'Comptant';
            } elseif ($facture->montant_regle > 0) {
                return 'Partiellement payé';
            } else {
                return 'Crédit';
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la détermination du type de vente pour la facture #' . $facture->id . ': ' . $e->getMessage());
            return 'Indéterminé';
        }
    }

    public function sessionVente(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $dateDebut = $request->get('date_debut') ? Carbon::parse($request->get('date_debut')) : Carbon::now()->startOfMonth();
            $dateFin = $request->get('date_fin') ? Carbon::parse($request->get('date_fin')) : Carbon::now();

            // Get session
            $session = $sessionId ?
                SessionCaisse::findOrFail($sessionId) :
                SessionCaisse::orderByDesc("id")->first(); //on prends la dernière session

            // Load relationships with filters
            $session->load([
                'factures' => function ($q) use ($dateDebut, $dateFin) {
                    if (
                        auth()->user()->hasRole("Super Administrateur")
                        || auth()->user()->hasRole("CONTROLE INTERNE")
                        || auth()->user()->hasRole("CONTROLE EXTERNE ET CELLULE DE REQUETE")
                    ) {
                        $q->whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);
                    } else {
                        //un unser simple ne vera que ses ventes
                        $q
                            // ->where("created_by", auth()->user()->id)
                            ->whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);
                    }
                },
            ]);

            // Get sessions list for dropdown
            $sessions = SessionCaisse::orderBy('date_ouverture', 'desc')
                ->get();

            $montantSolde = $session->factures
                ->where("moyen_reglement", '!=', "MoMo") // exception des momos
                ->flatMap(function ($facture) { //on recupère tous les reglements en un seul tableau
                    return $facture->reglements;
                })
                // ->whereNotNull("validated_by")
                ->sum("montant");

            return view('pages.rapports.ventes.session-vente', compact(
                'session',
                'sessions',
                'dateDebut',
                'dateFin',
                'montantSolde'
            ));
        } catch (\Exception $e) {
            \Log::info("Erreure lors du chargement", ["error" => $e->getMessage()]);
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function sessionReglement(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $dateDebut = $request->get('date_debut') ? Carbon::parse($request->get('date_debut')) : Carbon::now()->startOfMonth();
            $dateFin = $request->get('date_fin') ? Carbon::parse($request->get('date_fin')) : Carbon::now();

            // Get session
            $session = $sessionId ?
                SessionCaisse::findOrFail($sessionId) :
                SessionCaisse::where('statut', 'ouverte')
                ->latest()->firstOrFail();

            // Load relationships with filters
            $session->load([
                'reglements' => function ($q) use ($dateDebut, $dateFin) {
                    if (
                        auth()->user()->hasRole("Super Administrateur")
                        || auth()->user()->hasRole("CONTROLE INTERNE")
                        || auth()->user()->hasRole("CONTROLE EXTERNE ET CELLULE DE REQUETE")
                    ) {
                        $q->whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);
                    } else {
                        //un ser simple ne vera que ses reglements
                        $q->where("created_by", auth()->user()->id)
                            ->whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);
                    }
                }
            ]);

            // Get sessions list for dropdown
            $sessions = SessionCaisse::orderBy('date_ouverture', 'desc')
                ->get();

            return view('pages.rapports.ventes.session-reglements', compact(
                'session',
                'sessions',
                'dateDebut',
                'dateFin'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function sessionAccompte(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $dateDebut = $request->get('date_debut') ? Carbon::parse($request->get('date_debut')) : Carbon::now()->startOfMonth();
            $dateFin = $request->get('date_fin') ? Carbon::parse($request->get('date_fin')) : Carbon::now();

            // Get session
            $session = $sessionId ?
                SessionCaisse::findOrFail($sessionId) :
                SessionCaisse::where('statut', 'ouverte')->latest()->firstOrFail();

            // Load relationships with filters
            $session->load([
                'accompteClients' => function ($q) use ($dateDebut, $dateFin) {
                    $q->whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);

                    if (
                        auth()->user()->hasRole("Super Administrateur")
                        || auth()->user()->hasRole("CONTROLE INTERNE")
                        || auth()->user()->hasRole("CONTROLE EXTERNE ET CELLULE DE REQUETE")
                    ) {
                        $q->whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);
                    } else {
                        //un user simple ne vera que ses accomptes
                        $q->where("created_by", auth()->user()->id)
                            ->whereBetween('created_at', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);
                    }
                }
            ]);

            // return response()->json($session);
            // Get sessions list for dropdown
            $sessions = SessionCaisse::orderBy('date_ouverture', 'desc')
                ->get();

            return view('pages.rapports.ventes.session-accomptes', compact(
                'session',
                'sessions',
                'dateDebut',
                'dateFin'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function rapportCompteClient(Request $request)
    {
        // Construction de la requête principale
        $query = Client::query()
            ->withSum(['facturesClient as total_factures' => function ($q) {
                $q->whereNotNull('facture_clients.date_validation')
                    ->whereNull('facture_clients.deleted_at');
            }], 'montant_ttc')
            ->withSum(['facturesClient as total_reglements' => function ($q) {
                $q->whereNotNull('facture_clients.date_validation')
                    ->whereNull('facture_clients.deleted_at')
                    ->whereHas('reglements', function ($q) {
                        $q->whereNotNull('reglement_clients.validated_at')
                            ->whereNull('reglement_clients.deleted_at');
                    })
                    ->join('reglement_clients', 'facture_clients.id', '=', 'reglement_clients.facture_client_id')
                    ->select(\DB::raw('COALESCE(SUM(reglement_clients.montant), 0) as total_reglements'));
            }], 'montant_ttc')
            ->withSum(['acomptes as total_acomptes' => function ($q) {
                $q->where('statut', AcompteClient::STATUT_VALIDE)
                    ->whereNull('deleted_at');
            }], 'montant')
            ->with(['soldeInitial' => function ($q) {
                $q->latest('date_solde');
            }]);

        // Filtre par client si spécifié
        if ($request->client_id) {
            $query->where('id', $request->client_id);
        }

        // Filtre par point de vente si spécifié
        if ($request->point_de_vente_id) {
            $query->where('point_de_vente_id', $request->point_de_vente_id);
        }

        // Récupération et calcul des soldes
        $clients = $query->get()
            ->map(function ($client) {
                // Calcul du solde : Factures - (Règlements + Acomptes)
                $soldeInitial = $client->soldeInitial;
                $montantInitial = 0;

                if ($soldeInitial) {
                    $montantInitial = $soldeInitial->type === 'CREDITEUR' ? -$soldeInitial->montant : $soldeInitial->montant;
                }

                $client->solde = $montantInitial + ($client->total_factures ?? 0)
                    - (($client->total_reglements ?? 0) + ($client->total_acomptes ?? 0));
                return $client;
            });

        // Statistiques par mode de règlement et acomptes pour un client spécifique
        $statsParMode = [];
        if ($request->client_id) {
            $modes = [
                ReglementClient::TYPE_ESPECE,
                ReglementClient::TYPE_CHEQUE,
                ReglementClient::TYPE_VIREMENT,
                ReglementClient::TYPE_CARTE_BANCAIRE,
                ReglementClient::TYPE_MOMO,
                ReglementClient::TYPE_FLOOZ,
                ReglementClient::TYPE_CELTIS,
                ReglementClient::TYPE_EFFET,
                ReglementClient::TYPE_AVOIR
            ];

            foreach ($modes as $mode) {
                $statsParMode[$mode] = ReglementClient::whereHas('facture', function ($q) use ($request) {
                    $q->where('client_id', $request->client_id);
                })
                    ->whereNotNull('validated_at')
                    ->where('type_reglement', $mode)
                    ->sum('montant');
            }

            // Ajout des statistiques pour les acomptes
            $statsParMode['acomptes'] = [
                'espece' => AcompteClient::where('client_id', $request->client_id)
                    ->where('statut', AcompteClient::STATUT_VALIDE)
                    ->where('type_paiement', AcompteClient::TYPE_ESPECE)
                    ->sum('montant'),
                'cheque' => AcompteClient::where('client_id', $request->client_id)
                    ->where('statut', AcompteClient::STATUT_VALIDE)
                    ->where('type_paiement', AcompteClient::TYPE_CHEQUE)
                    ->sum('montant'),
                'virement' => AcompteClient::where('client_id', $request->client_id)
                    ->where('statut', AcompteClient::STATUT_VALIDE)
                    ->where('type_paiement', AcompteClient::TYPE_VIREMENT)
                    ->sum('montant'),
            ];
        }

        // Détail des mouvements
        $mouvements = collect();
        if ($request->client_id) {
            $client = Client::with('soldeInitial')->find($request->client_id);
            $soldeInitial = $client->soldeInitial;

            // Ajout du solde initial aux mouvements uniquement s'il existe
            if ($soldeInitial) {
                $mouvements->push([
                    'id' => $soldeInitial->id,
                    'date' => $soldeInitial->date_solde,
                    'type' => 'SOLDE_INITIAL',
                    'reference' => 'SI-' . $soldeInitial->id,
                    'debit' => $soldeInitial->type === 'DEBITEUR' ? $soldeInitial->montant : 0,
                    'credit' => $soldeInitial->type === 'CREDITEUR' ? $soldeInitial->montant : 0,
                    'commentaire' => $soldeInitial->commentaire
                ]);
            }

            // Récupération des factures
            $factures = FactureClient::where('client_id', $request->client_id)
                ->whereNotNull('date_validation')
                ->get()
                ->map(function ($facture) {
                    return [
                        'id' => $facture->id,
                        'date' => $facture->date_facture,
                        'type' => 'FACTURE',
                        'reference' => $facture->numero,
                        'debit' => $facture->montant_ttc,
                        'credit' => 0,
                        'statut_paiement' => $facture->est_solde ? 'SOLDEE' : 'NON_SOLDEE'
                    ];
                });

            // Récupération des règlements
            $reglements = ReglementClient::whereHas('facture', function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            })
                ->whereNotNull('validated_at')
                ->get()
                ->map(function ($reglement) {
                    return [
                        'id' => $reglement->id,
                        'date' => $reglement->date_reglement,
                        'type' => 'REGLEMENT',
                        'reference' => $reglement->numero,
                        'mode' => $reglement->type_reglement,
                        'reference_paiement' => $reglement->reference_preuve,
                        'debit' => 0,
                        'credit' => $reglement->montant
                    ];
                });

            // Récupération des acomptes
            $acomptes = AcompteClient::where('client_id', $request->client_id)
                ->where('statut', AcompteClient::STATUT_VALIDE)
                ->get()
                ->map(function ($acompte) {
                    return [
                        'id' => $acompte->id,
                        'date' => $acompte->date,
                        'type' => 'ACOMPTE',
                        'reference' => $acompte->reference,
                        'mode' => $acompte->type_paiement,
                        'debit' => 0,
                        'credit' => $acompte->montant,
                        'observation' => $acompte->observation
                    ];
                });

            // Préparation des mouvements en assurant que le solde initial soit en premier
            if ($soldeInitial) {
                $soldeInitialDate = $soldeInitial->date_solde;
            } else {
                $soldeInitialDate = null;
            }

            // Fusion des factures, règlements et acomptes
            $operationsTemp = $factures->concat($reglements)->concat($acomptes);

            // Filtrage par date si solde initial existe
            if ($soldeInitialDate) {
                $operationsTemp = $operationsTemp->filter(function ($operation) use ($soldeInitialDate) {
                    return Carbon::parse($operation['date'])->greaterThanOrEqual(Carbon::parse($soldeInitialDate));
                });
            }

            // Tri chronologique de toutes les opérations
            $mouvements = $mouvements->concat($operationsTemp)->sortBy([
                ['date', 'asc'],
                ['type', 'desc']
            ]);
        }

        // Retour de la vue avec toutes les données
        return view('pages.rapports.ventes.compte-client', [
            'clients' => $clients,
            'mouvements' => $mouvements,
            'solde_initial' => $request->client_id ? $soldeInitial : null,
            'statistiques' => [
                'total_clients' => $clients->count(),
                'total_factures' => $clients->sum('total_factures'),
                'total_reglements' => $clients->sum('total_reglements'),
                'total_acomptes' => $clients->sum('total_acomptes'),
                'solde_global' => $clients->sum('solde'),
                'clients_debiteurs' => $clients->where('solde', '>', 0)->count(),
                'clients_crediteurs' => $clients->where('solde', '<', 0)->count(),
                'montant_debiteur' => $clients->where('solde', '>', 0)->sum('solde'),
                'montant_crediteur' => abs($clients->where('solde', '<', 0)->sum('solde')),
                'par_mode' => $statsParMode
            ],
            'filtres' => [
                'clients' => Client::select('id', 'raison_sociale as nom')
                    ->orderBy('raison_sociale')
                    ->get(),
                'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')
                    ->orderBy('nom_pv')
                    ->get()
            ],
            'params' => [
                'client_id' => $request->client_id,
                'point_de_vente_id' => $request->point_de_vente_id
            ]
        ]);
    }
}
