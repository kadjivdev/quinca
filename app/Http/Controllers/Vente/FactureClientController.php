<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\Client;
use App\Models\Catalogue\{Tarification, Article};
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\Depot;
use App\Models\Parametre\PointDeVente;
use App\Models\Vente\{FactureClient, LigneFacture, PointVente, SessionCaisse, ReglementClient};
use App\Models\Parametre\Societe;
use App\Models\Parametre\UniteMesure;
use App\Models\Stock\StockDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ServiceStockEntree;


class FactureClientController extends Controller
{
    /**
     * Affiche la liste des factures
     */

    private $serviceStockEntree;

    public function __construct(ServiceStockEntree $serviceStockEntree)
    {
        $this->serviceStockEntree = $serviceStockEntree;
    }
    public function index(Request $request)
    {
        try {
            $pointsVentes = PointVente::all();

            Log::info('Début du chargement de la liste des factures');
            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
            $configuration = Societe::first();
            $tauxTva = $configuration ? $configuration->taux_tva : 18;

            $query = FactureClient::with(['client'])
                ->select([
                    'id',
                    'numero',
                    'date_facture',
                    'date_echeance',
                    'client_id',
                    'statut',
                    'type_facture',
                    'date_validation',
                    'montant_ht',
                    'montant_ttc',
                    'montant_regle',
                    'session_caisse_id',
                    'created_by',
                    'validated_by',
                    'encaissed_at'
                ])
                ->orderBy('date_facture', 'desc');

            // Chargement des factures avec les relations nécessaires
            if ($request->pointVente) {
                $factures = $query->get()
                    ->filter(function ($facture) use ($request) {
                        return $facture->createdBy->pointDeVente->id == $request->pointVente;
                    });
            } else {
                $factures = $query->get();
            }

            // Ajouter des attributs calculés pour chaque facture
            $factures->transform(function ($facture) {
                // Calcul du reste à payer
                $facture->reste_a_payer = $facture->montant_ttc - $facture->montant_regle;

                // Détermination du vrai statut basé sur le paiement
                if ($facture->statut === 'brouillon') {
                    $facture->statut_reel = 'brouillon';
                } elseif ($facture->statut === 'validee') {
                    if ($facture->montant_regle == 0) {
                        $facture->statut_reel = 'validee';
                    } elseif ($facture->montant_regle < $facture->montant_ttc) {
                        $facture->statut_reel = 'partiellement_payee';
                    } elseif ($facture->montant_regle >= $facture->montant_ttc) {
                        $facture->statut_reel = 'payee';
                    }
                }

                // Vérifier si la facture est en retard
                $facture->is_overdue = $facture->statut !== 'payee'
                    && Carbon::now()->startOfDay()->gt($facture->date_echeance);

                return $facture;
            });

            $facturesResteAPayer = $factures->filter(function ($facture) {
                return $facture->reste_a_payer > 0;
            });
            $montantResteAPyer = $facturesResteAPayer->sum('montant_ttc');

            // Calculer le montant total des factures du mois en cours
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $facturesDuMois = $factures->filter(function ($facture) use ($currentMonth, $currentYear) {
                return Carbon::parse($facture->date_facture)->month == $currentMonth &&
                    Carbon::parse($facture->date_facture)->year == $currentYear;
            });

            $montantFactureMois = $facturesDuMois->sum('montant_ttc');

            // Calculer le total encaissé et le nombre de factures encaissées
            $facturesEncaissees = $facturesDuMois->filter(function ($facture) {
                return !is_null($facture->encaissed_at);
            });

            $totalEncaisse = $facturesEncaissees->sum('montant_ttc');
            $nombreEncaisse = $facturesEncaissees->count();

            $statsFactures = [
                'total_mois' => $montantFactureMois,
                'total_encaisse' => $totalEncaisse,
                'nombre_encaisse' => $nombreEncaisse,
                'montant_en_attente' => $montantResteAPyer,
                'factures_en_attente' => $facturesResteAPayer,
            ];

            Log::info('Liste des factures chargée avec succès', [
                'nombre_factures' => count($factures)
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'factures' => $factures
                    ]
                ]);
            }

            // Charger la liste des clients pour le filtre
            $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->orderBy('raison_sociale')->get(['id', 'raison_sociale', 'taux_aib']);

            return view('pages.ventes.facture.index', compact('factures', 'clients', 'date', 'tauxTva', 'statsFactures', 'pointsVentes'));
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement de la liste des factures', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Une erreur est survenue lors du chargement des factures'
                ], 500);
            }

            return back()->with('error', 'Une erreur est survenue lors du chargement des factures');
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Début création facture', ['request' => $request->all()]);

            // Vérifications initiales
            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session de caisse requise.'
                ], 422);
            }

            $client = Client::findOrFail($request->client_id);
            $configuration = Societe::firstOrFail();

            // Validation
            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',
                'moyen_reglement' => 'required|string',

                'lignes' => 'required|array|min:1',
                'lignes*article_id' => 'required|exists,articles',
                'lignes*depot_id' => 'required|exits,depots',
                'lignes*quantite' => 'required',
                'lignes*tarification_id' => 'required',

                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string',
                // 'depot' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userPv = auth()->user()->pointDeVente;
            $userPv_depotIds = $userPv->depot->pluck("id")->toArray(); //les depots du users

            // on verifie si les articles selectionnés sont tous dans son depôts
            foreach ($request->lignes as $ligne) {
                $depot = Depot::find($ligne["depot_id"]);
                $article = Article::find($ligne['article_id']);

                if (!in_array($ligne["depot_id"], $userPv_depotIds)) {
                    return response()->json([
                        'status' => false,
                        'message' => "Le dépôt ($depot->libelle_depot) ne vous appartient pas! Vous ne pouvez pas y passer une ecriture "
                    ], 500);
                }
            }

            // On verifie si les quantités saisies au niveau des articles ne depasse pas le reste de quantité sur l'article
            foreach ($request->lignes as $ligne) {
                $depot = Depot::find($ligne["depot_id"]);
                // 
                $stock = StockDepot::where('depot_id', $ligne["depot_id"])
                    ->where('article_id', $ligne['article_id'])
                    ->first();

                /**
                 * Recherche de la conversion
                 */
                $venteUnite = UniteMesure::findOrFail($ligne['unite_vente_id']);
                $stockUnite = UniteMesure::findOrFail($stock->unite_mesure_id);
                $article = Article::findOrFail($ligne['article_id']);

                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $ligne['unite_vente_id'],
                        $stock->unite_mesure_id,
                        $stock->article_id
                    );
                if (!$conversion) {
                    return response()->json([
                        'status' => false,
                        'message' => "Il n'y a pas de conversion de l'unité ($venteUnite->libelle_unite) vers ($stockUnite->libelle_unite) pour l'article ($article->code_article), ni l'inverse! Veuillez créer cette conversion afin de continuer l'opération"
                    ], 500);
                }

                /**
                 * Obtention de la quantité convertie
                 */

                $QteConvertie = $this->serviceStockEntree
                    ->convertirQuantite($ligne['quantite'], $conversion, $ligne['unite_vente_id']);

                $QteStockConvertie = $this->serviceStockEntree
                    ->convertirQuantite($stock->quantite_reelle, $conversion, $ligne['unite_vente_id']);

                // on verifie la quantité restante de l'article dans le depot est suffisante

                if ($stock->quantite_reelle < $QteConvertie) {
                    return response()->json([
                        'status' => false,
                        'message' => "Le reste du stock de l'article ($article->designation) est de $QteStockConvertie $venteUnite->libelle_unite dans le depôt ({$stock->depot->libelle_depot})! Stock insuiffisant par rapport à la quantité saisie"
                    ], 500);
                }
            }

            DB::beginTransaction();

            try {
                // Création de la facture
                $facture = new FactureClient();
                $facture->fill([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'session_caisse_id' => $sessionCaisse->id,
                    'created_by' => auth()->id(),
                    'observations' => $request->observations,
                    'statut' => 'brouillon',
                    'type_facture' => $request->type_facture === "normaliser" ? "NORMALISE" : "SIMPLE",
                    'montant_ht' => 0,
                    'montant_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                    'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
                ]);

                $facture->save();

                $totalHT = 0;
                $totalRemise = 0;
                $totalTVA = 0;
                $totalAIB = 0;

                // Création des lignes
                foreach ($request->lignes as $ligne) {
                    $ligneFacture = new LigneFacture([
                        'article_id' => $ligne['article_id'],
                        'unite_vente_id' => $ligne['unite_vente_id'],
                        'quantite' => $ligne['quantite'],
                        'prix_unitaire_ht' => $ligne['tarification_id'],
                        'taux_remise' => $ligne['taux_remise'] ?? 0,
                        'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                        'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
                        'depot' => $ligne["depot_id"],
                    ]);

                    $facture->lignes()->save($ligneFacture);

                    $totalHT += $ligneFacture->montant_ht;
                    $totalRemise += $ligneFacture->montant_remise;
                    if ($request->type_facture === 'normaliser') {
                        $totalTVA += $ligneFacture->montant_tva;
                        $totalAIB += $ligneFacture->montant_aib;
                    }
                }

                $montantHTApresRemise = $totalHT - $totalRemise;
                $montantTTC = $montantHTApresRemise;
                if ($request->type_facture === 'normaliser') {
                    $montantTTC += $totalTVA + $totalAIB;
                }

                // Mise à jour des totaux
                $facture->update([
                    'montant_ht' => $totalHT,
                    'montant_remise' => $totalRemise,
                    'montant_ht_apres_remise' => $montantHTApresRemise,
                    'montant_tva' => $totalTVA,
                    'montant_aib' => $totalAIB,
                    'montant_ttc' => $montantTTC,
                    'montant_regle' => $request->montant_regle,
                ]);

                // Création du règlement si nécessaire
                if ($request->montant_regle > 0) {
                    $reglement = new ReglementClient([
                        'facture_client_id' => $facture->id,
                        'date_reglement' => Carbon::parse($request->date_facture),
                        'type_reglement' => $request->moyen_reglement,
                        'montant' => $request->montant_regle,
                        'statut' => 'brouillon',
                        'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                $sessionCaisse->mettreAJourTotaux();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture créée avec succès',
                    'data' => ['facture' => $facture->load([
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
                        'sessionCaisse',
                        'createdBy',
                        'reglements'
                    ])]
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur création facture', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur création facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Début mise à jour facture', ['request' => $request->all(), 'facture_id' => $id]);

            // Vérifications initiales
            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Session de caisse requise.'
                ], 422);
            }

            $facture = FactureClient::findOrFail($id);
            $client = Client::findOrFail($request->client_id);
            $configuration = Societe::firstOrFail();

            // Validation
            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',
                'moyen_reglement' => 'required|string',
                'lignes' => 'required|array|min:1',
                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Mise à jour de la facture
                $facture->update([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'session_caisse_id' => $sessionCaisse->id,
                    'updated_by' => auth()->id(),
                    'observations' => $request->observations,
                    'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                    'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
                    'montant_regle' => $request->montant_regle
                ]);

                // Suppression des anciens règlements
                $facture->reglements()->delete();

                // Création du règlement si nécessaire
                if ($request->montant_regle > 0) {
                    $reglement = new ReglementClient([
                        'facture_client_id' => $facture->id,
                        'date_reglement' => Carbon::parse($request->date_facture),
                        'type_reglement' => $request->moyen_reglement,
                        'montant' => $request->montant_regle,
                        'statut' => 'brouillon',
                        'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                // Réinitialisation des totaux et suppression des anciennes lignes
                $facture->lignes()->delete();

                $totalHT = 0;
                $totalRemise = 0;
                $totalTVA = 0;
                $totalAIB = 0;

                // Mise à jour des lignes
                foreach ($request->lignes as $ligne) {
                    $ligneFacture = new LigneFacture([
                        'article_id' => $ligne['article_id'],
                        'unite_vente_id' => $ligne['unite_vente_id'],
                        'quantite' => $ligne['quantite'],
                        'prix_unitaire_ht' => $ligne['tarification_id'],
                        'taux_remise' => $ligne['taux_remise'] ?? 0,
                        'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                        'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib
                    ]);

                    $facture->lignes()->save($ligneFacture);

                    $totalHT += $ligneFacture->montant_ht;
                    $totalRemise += $ligneFacture->montant_remise;
                    if ($request->type_facture === 'normaliser') {
                        $totalTVA += $ligneFacture->montant_tva;
                        $totalAIB += $ligneFacture->montant_aib;
                    }
                }

                $montantHTApresRemise = $totalHT - $totalRemise;
                $montantTTC = $montantHTApresRemise;
                if ($request->type_facture === 'normaliser') {
                    $montantTTC += $totalTVA + $totalAIB;
                }

                // Mise à jour des totaux de la facture
                $facture->update([
                    'montant_ht' => $totalHT,
                    'montant_remise' => $totalRemise,
                    'montant_ht_apres_remise' => $montantHTApresRemise,
                    'montant_tva' => $totalTVA,
                    'montant_aib' => $totalAIB,
                    'montant_ttc' => $montantTTC,
                ]);

                $sessionCaisse->mettreAJourTotaux();
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture mise à jour avec succès',
                    'data' => ['facture' => $facture->load([
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
                        'sessionCaisse',
                        'reglements'
                    ])]
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur mise à jour facture', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur mise à jour facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchArticles(Request $request)
    {
        $search = $request->get('q');

        $stocks = StockDepot::with('article')
            ->get()
            ->filter(function ($stock) use ($search) {
                return $stock->article->where('code_article', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });

        return response()->json([
            'results' => $stocks->map(function ($stock) {
                /**
                 * @param $resteStock Reste du stock dans le depot
                 */

                $resteStock = $stock->article
                    ->reste($stock->depot_id);

                return [
                    'id' => $stock->article->id,
                    'text' => $stock->article->designation,
                    'code_article' => $stock->article->code_article,
                    'depot' => $stock->depot,
                    'unite_mesure_labele' => $stock->uniteMesure->libelle_unite,
                    'stock' => $resteStock ? number_format($resteStock, 0, " ", " ") : 00,
                ];
            })
        ]);
    }

    public function getTarifs(Request $request, $articleId)
    {
        try {
            $article = Article::with(['tarifications.typeTarif'])
                ->findOrFail($articleId);

            $tarifs = $article->tarifications
                ->where('statut', true)
                ->map(function ($tarif) {
                    return [
                        'id' => $tarif->id,
                        'text' => sprintf(
                            '%s FCFA - %s',
                            number_format($tarif->prix, 2),
                            $tarif->typeTarif->libelle_type_tarif ?? ''
                        ),
                        'prix' => $tarif->prix
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'tarifs' => $tarifs
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des tarifs'
            ], 500);
        }
    }

    public function getUnites(Request $request, $articleId)
    {
        try {
            Log::info('Début récupération des unités', ['article_id' => $articleId]);

            // Récupérer l'article avec son unité de mesure
            $article = Article::with('uniteMesure')->findOrFail($articleId);

            $unites = collect();

            // 1. Ajouter l'unité de base de l'article si elle existe
            if ($article->uniteMesure) {
                $unites->push([
                    'id' => $article->uniteMesure->id,
                    'text' => $article->uniteMesure->libelle_unite
                ]);
            }

            // 2. Obtenir toutes les unités ayant des conversions pour cet article
            $unitesConversion = ConversionUnite::where('article_id', $articleId)
                ->where('statut', true)
                ->with(['uniteSource', 'uniteDest'])
                ->get();

            // Ajouter les unités source actives
            $unitesConversion->pluck('uniteSource')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites) {
                    if (!$unites->contains('id', $unite->id)) {
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite
                        ]);
                    }
                });

            // Ajouter les unités destination actives
            $unitesConversion->pluck('uniteDest')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites) {
                    if (!$unites->contains('id', $unite->id)) {
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite
                        ]);
                    }
                });

            Log::info('Unités récupérées avec succès', [
                'article_id' => $articleId,
                'nombre_unites' => $unites->count(),
                'unites' => $unites->toArray()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'unites' => $unites->values()->all()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des unités', [
                'article_id' => $articleId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des unités: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            Log::info('Début du chargement des détails de la facture', ['facture_id' => $id]);

            $facture = FactureClient::with([
                'client',
                'lignes.article',
                'lignes.facturedepot',
                'lignes.uniteVente',
                'lignes.tarification.typeTarif',
                'sessionCaisse',
                'createdBy'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'facture' => $facture,
                    'dateFacture' => $facture->date_facture->format('d/m/Y'),
                    'dateEcheance' => $facture->date_echeance->format('d/m/Y'),
                    'montantHT' => number_format($facture->montant_ht, 0, ',', ' '),
                    'montantTVA' => number_format($facture->montant_tva, 0, ',', ' '),
                    'montantTTC' => number_format($facture->montant_ttc, 0, ',', ' '),
                    'montantRegle' => number_format($facture->montant_regle, 0, ',', ' '),
                    'montantRestant' => number_format($facture->montant_ttc - $facture->montant_regle, 0, ',', ' '),
                    'tauxTVA' => $facture->taux_tva,
                    'tauxAIB' => $facture->taux_aib
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement des détails de la facture', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors du chargement des détails de la facture'
            ], 500);
        }
    }

    public function validateFacture($id)
    {
        try {
            DB::beginTransaction();

            $facture = FactureClient::with(['client', 'lignes.article', 'reglements'])
                ->findOrFail($id);

            if ($facture->statut === 'validee') {
                throw new Exception('Facture déjà validée');
            }

            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                ->first();

            if (!$sessionCaisse) {
                throw new Exception('Session de caisse requise');
            }

            $updateData = [
                'date_validation' => now(),
                'validated_by' => auth()->id(),
                'statut' => 'validee'
            ];


            $facture->update($updateData);

            if ($reglement = $facture->reglements->first()) {
                $reglement->update([
                    'date_validation' => now(),
                    'validated_by' => auth()->id(),
                    'statut' => 'validee'
                ]);
            }

            $sessionCaisse->mettreAJourTotaux();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Facture validée',
                'data' => ['facture' => $facture->fresh(['client', 'createdBy'])]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation facture', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $facture = FactureClient::findOrFail($id);

            // Vérifier le statut
            if ($facture->statut === 'validee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer une facture validée'
                ], 422);
            }

            // Supprimer les règlements de manière forcée
            $facture->reglements()->forceDelete();

            // Supprimer les lignes
            $facture->lignes()->delete();

            // Supprimer la facture
            $facture->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Facture et règlements supprimés avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression facture', [
                'facture_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function details(FactureClient $facture)
    {
        return response()->json([
            'id' => $facture->id,
            'numero' => $facture->numero,
            'client' => [
                'id' => $facture->client->id,
                'raison_sociale' => $facture->client->raison_sociale
            ],
            'montant_ttc' => $facture->montant_ttc,
            'montant_ttc' => $facture->montant_ttc,
            'montant_regle' => $facture->montant_regle,
            'reste_a_payer' => $facture->reste_a_payer,
            'date_facture' => $facture->date_facture->format('Y-m-d'),
            'statut' => $facture->statut
        ]);
    }

    public function print(FactureClient $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);


        $pdf = PDF::loadView('pages.ventes.facture.partials.print-facture', compact('facture'));
        $pdf->setPaper('a4');

        return $pdf->stream("facture_{$facture->numero}.pdf");
    }

    /**
     * Obtenir les détails d'une facture
     *
     * @param FactureClient $facture
     * @return JsonResponse
     */

    public function getDetailsFacture(FactureClient $facture): JsonResponse
    {
        // Changement dans le chargement des relations
        $facture->load([
            'client',
            'sessionCaisse', // On charge d'abord la session
            'lignes.article'
        ]);

        return response()->json(
            [
                'numero' => $facture->numero,
                'date_facture' => $facture->date_facture->format('d/m/Y'),
                'client' => [
                    'raison_sociale' => $facture->client->raison_sociale
                ],
                'point_vente' => $facture->sessionCaisse ? [
                    'libelle' => $facture->sessionCaisse->point_de_vente_id ?
                        PointDeVente::find($facture->sessionCaisse->point_de_vente_id)->nom_pv : '-'
                ] : null,
                'montant_ht' => number_format($facture->montant_ht, 0, ',', ' '),
                'montant_tva' => number_format($facture->montant_tva, 0, ',', ' '),
                'montant_ttc' => number_format($facture->montant_ttc, 0, ',', ' '),
                'lignes' => $facture->lignes->map(function ($ligne) {
                    return [
                        'article' => [
                            'designation' => $ligne->article->designation
                        ],
                        'quantite' => number_format($ligne->quantite, 0, ',', ' '),
                        'prix_unitaire' => number_format($ligne->prix_unitaire_ht, 0, ',', ' '),
                        'montant_total' => number_format($ligne->montant_ttc, 0, ',', ' ')
                    ];
                })
            ]
        );
    }
}
