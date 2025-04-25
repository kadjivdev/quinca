<?php

namespace App\Http\Controllers\Revendeur;

use App\Http\Controllers\Controller;
use App\Models\Vente\Client;
use App\Models\Catalogue\{Tarification, Article};
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\Depot;
use App\Models\Parametre\PointDeVente;
use App\Models\Vente\{FactureClient, LigneFacture, SessionCaisse, ReglementClient};
use App\Models\Parametre\Societe;
use App\Models\Revendeur\FactureRevendeur;
use App\Models\Revendeur\LigneFactureRevendeur;
use App\Models\Stock\StockDepot;
use App\Services\ServiceStockSortie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Illuminate\Support\Facades\Auth;

class SpecialController extends Controller
{

    private $serviceStockSortie;

    public function __construct(ServiceStockSortie $serviceStockSortie)
    {
        $this->serviceStockSortie = $serviceStockSortie;
    }

    /**
     * Affiche la liste des factures
     */

    public function index()
    {
        try {
            Log::info('Début du chargement de la liste des factures');
            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
            $configuration = Societe::first();
            $tauxTva = $configuration ? $configuration->taux_tva : 18;

            // Chargement des factures avec les relations nécessaires
            $factures = FactureRevendeur::with(['client'])
                ->select([
                    'id',
                    'numero',
                    'date_facture',
                    'date_echeance',
                    'client_id',
                    'statut',
                    'montant_ht',
                    'montant_ttc',
                    'montant_regle',
                    'created_by',
                    'validated_by',
                ])
                ->where('type_vente', 'speciale')
                ->orderBy('date_facture', 'desc')
                ->paginate(10);

            // Ajouter des attributs calculés pour chaque facture
            $factures->getCollection()->transform(function ($facture) {
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

            Log::info('Liste des factures chargée avec succès', [
                'nombre_factures' => $factures->total()
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
            $clients =  Client::orderBy('raison_sociale')->get(['id', 'raison_sociale', 'taux_aib']);

            return view('pages.revendeur.special.index', compact('factures', 'clients', 'date', 'tauxTva'));
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
            Log::info('Début création facture Revendeur', ['request' => $request->all()]);

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
                // Création de la facture
                $facture = new FactureRevendeur();
                $facture->fill([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'created_by' => auth()->id(),
                    'observations' => $request->observations,
                    'statut' => 'brouillon',
                    'montant_ht' => 0,
                    'montant_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'point_de_vente_id' => Auth()->user()->point_de_vente_id,
                    'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                    'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
                    'type_vente' => 'speciale'
                ]);
                $facture->save();

                $totalHT = 0;
                $totalRemise = 0;
                $totalTVA = 0;
                $totalAIB = 0;

                // Création des lignes
                foreach ($request->lignes as $ligne) {
                    $ligneFacture = new LigneFactureRevendeur([
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

                // Mise à jour des totaux
                $facture->update([
                    'montant_ht' => $totalHT,
                    'montant_remise' => $totalRemise,
                    'montant_ht_apres_remise' => $montantHTApresRemise,
                    'montant_tva' => $totalTVA,
                    'montant_aib' => $totalAIB,
                    'montant_ttc' => $montantTTC,
                    'montant_regle' => $request->montant_regle
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture créée avec succès',
                    'data' => ['facture' => $facture->load([
                        'client', 'lignes.article', 'lignes.uniteVente', 'createdBy',
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

    public function searchArticles(Request $request)
    {
        $search = $request->get('q');

        $articles = Article::query()
            ->where(function ($query) use ($search) {
                $query->where('code_article', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            })
            ->where('statut', 'actif')
            ->select(['id', 'code_article', 'designation', 'stock_actuel'])
            ->limit(10)
            ->get();  // Ceci retourne maintenant une Collection

        return response()->json([
            'results' => $articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'text' => $article->designation,
                    'code_article' => $article->code_article,
                    'stock' => $article->stock_actuel
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

            $facture = FactureRevendeur::with([
                'client',
                'lignes.article',
                'lignes.uniteVente',
                'lignes.tarification.typeTarif',
                // 'sessionCaisse',
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

            $facture = FactureRevendeur::with(['client', 'lignes.article'])
                ->findOrFail($id);

            if ($facture->statut === 'validee') {
                throw new Exception('Facture déjà validée');
            }

            // if (!$facture->peutEtreLivree()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => $facture->statut !== 'validee'
            //             ? 'Cette facture n\'est pas dans un état permettant la livraison'
            //             : 'Cette facture est déjà totalement livrée'
            //     ], 422);
            // }

            foreach ($facture->lignes as $data) {
                if ($data['quantite'] > 0) {

                    // Conversion en unité de base si nécessaire
                    $article = Article::findOrFail($data->article_id);
                    $quantiteBase = $data['quantite'];

                    if ($data['unite_vente_id'] != $article->unite_mesure_id) {
                        $conversion = ConversionUnite::where([
                            'unite_source_id' => $data->unite_vente_id,
                            'unite_dest_id' => $article->unite_mesure_id,
                            'article_id' => $article->id,
                            'statut' => true
                        ])->first();

                        if (!$conversion) {
                            throw new Exception(
                                "Pas de conversion trouvée pour l'article " . $article->designation
                            );
                        }

                        $quantiteBase = $conversion->convertir($data['quantite']);
                    }

                    $pointVenteConnected = PointDeVente::with('depot')->findOrFail(auth()->user()->point_de_vente_id);

                    // Vérifier le stock disponible
                    $stock = StockDepot::where([
                        'article_id' => $data->article_id,
                        'depot_id' => $pointVenteConnected->depot_id
                    ])->first();

                    if (!$stock || $stock->quantite_reelle < $data->quantite_base) {
                        throw new Exception(
                            "Stock insuffisant pour l'article {$data->article->designation} " .
                            "(Demandé: {$data->quantite_base}, Disponible: " .
                            ($stock ? $stock->quantite_reelle : 0) . ")"
                        );
                    }

                    // Créer le mouvement de sortie
                    $mouvementSortie = $this->serviceStockSortie->traiterSortieStock([
                        'date_mouvement' => $facture->date_facture,
                        'depot_id' => $pointVenteConnected->depot_id,
                        'article_id' => $data->article_id,
                        'unite_mesure_id' => $article->unite_mesure_id,
                        'quantite' => $quantiteBase,
                        'reference_mouvement' => ' ',
                        'document_type' => 'LIVRAISON_CLIENT',
                        'document_id' => $facture->id,
                        'user_id' => auth()->id(),
                        'notes' => "Facture client #{$facture->numero}"
                    ]);

                    if (!$mouvementSortie['succes']) {
                        throw new Exception($mouvementSortie['message']);
                    }
    
                    // Associer le mouvement à la ligne
                    $data->mouvement_stock_id = $mouvementSortie['donnees']['mouvement_id'];
                    $data->save();
                }
            }

            $updateData = [
                'date_validation' => now(),
                'validated_by' => auth()->id(),
                'statut' => 'validee'
            ];

            $facture->update($updateData);

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

            $facture = FactureRevendeur::findOrFail($id);

            // Vérifier le statut
            if ($facture->statut === 'validee') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Impossible de supprimer une facture validée'
                ], 422);
            }

            // Supprimer les règlements de manière forcée
            // $facture->reglements()->forceDelete();

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
    public function details(FactureRevendeur $facture)
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

    public function print(FactureRevendeur $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);


        $pdf = PDF::loadView('pages.revendeur.special.partials.print-facture', compact('facture'));
        $pdf->setPaper('a4');

        return $pdf->stream("facture_{$facture->numero}.pdf");
    }    

    public function specialeRapport(Request $request) {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));

        $ventes = FactureRevendeur::whereBetween('date_facture', [$dateDebut, $dateDebut])
                                    ->where('type_vente', 'speciale')
                                    ->where('encaisse', 'non')
                                    ->where('statut', 'brouillon')
                                    ->with('client')
                                    ->with('lignes')
                                    ->get();

        $totalLignes = $ventes->sum(function ($vente) {
            return $vente->lignes->count();
        });

        $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->get();

        return view('pages.revendeur.validation.special.index', compact(            
            'dateDebut',
            'ventes',
            'clients',
            'totalLignes'
        ));
    }

    public function MakeSellvalidation(Request $request, FactureRevendeur $facture) {
        $request->validate([
            'articles' => 'required|array',
            'articles.*.quantite' => 'required|numeric|min:0',
            'articles.*.prix_unitaire' => 'required|numeric|min:0',
            'client_id' => 'required|exists:clients,id',
            // 'reference_preuve' => 'nullable|string|max:255',
            // 'banque' => 'nullable|string|max:255',
            'moyen_reglement' => 'required|string',
        ]);

        try{
            DB::beginTransaction();

            $sessionCaisse = SessionCaisse::ouverte()
            ->where('utilisateur_id', auth()->id())
            ->first();

            // Début écriture de facture pour le compte du client auquel on a vendu. Remarqu'ons que le client est pris depuis factureRevendeur

            $configuration = Societe::firstOrFail();
            $client = Client::findOrFail($facture->client_id);

            $factureClient = new FactureClient();
            $factureClient->fill([
                'date_facture' => now(),
                'client_id' => $facture->client_id,
                'date_echeance' => now(),
                'session_caisse_id' => $sessionCaisse->id,
                'created_by' => auth()->id(),
                'observations' => $facture->notes,
                'statut' => 'validee',
                'montant_ht' => 0,
                'montant_remise' => 0,
                'montant_tva' => 0,
                'montant_aib' => 0,
                'montant_ttc' => 0,
                'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
            ]);
            $factureClient->save();

            $totalHT = 0;
            $totalRemise = 0;
            $totalTVA = 0;
            $totalAIB = 0;

            $lignes = LigneFactureRevendeur::where('facture_revendeur_id', $facture->id)->get();

            // Création des lignes
            foreach ($lignes as $key => $ligne) {
                $ligneFacture = new LigneFacture([
                    'article_id' => $ligne['article_id'],
                    'unite_vente_id' => $ligne['unite_vente_id'],
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire_ht' => $request->articles[$key]['prix_unitaire'],
                    'taux_remise' => $ligne['taux_remise'] ?? 0,
                    'taux_tva' => $ligne->taux_tva,
                    'taux_aib' => $ligne->taux_aib
                ]);

                // dd($factureClient);

                $factureClient->lignes()->save($ligneFacture);

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
            $factureClient->update([
                'montant_ht' => $totalHT,
                'montant_remise' => $totalRemise,
                'montant_ht_apres_remise' => $montantHTApresRemise,
                'montant_tva' => $totalTVA,
                'montant_aib' => $totalAIB,
                'montant_ttc' => $montantTTC,
                // 'montant_regle' => $montantTTC
            ]);

            // Fin écriture dans le compte du client 

            // Règlement dans le compte du client
            $reglementClient = new ReglementClient();
            $reglementClient->facture_client_id = $factureClient->id;
            $reglementClient->facture()->associate($factureClient); // Important: associer la facture
            $reglementClient->date_reglement = now();
            $reglementClient->type_reglement = $request->moyen_reglement;
            $reglementClient->montant = $factureClient->montant_ttc;
            $reglementClient->created_by = auth()->id();
            $reglementClient->statut = ReglementClient::STATUT_BROUILLON;
            $reglementClient->save();

            // Valider le règlement
            if (!$reglementClient->valider(auth()->id())) {
                throw new Exception("Erreur lors de la validation du règlement");
            }

            // Mettre à jour la session caisse
            if (method_exists($sessionCaisse, 'mettreAJourTotaux')) {
                $sessionCaisse->mettreAJourTotaux();
            }
            // Fin règlement dans le compte du client

            // Début règlement dans le compte du Nord
                                    
            // Calcul de la somme des montants_ttc
            $sommeMontantTTCC = $facture->montant_ttc;

            $facturesNonReglees = FactureClient::where('statut', 'validee')
                                    ->whereColumn('montant_regle', '<', 'montant_ttc') // montant réglé < montant total
                                    ->where('client_id', $request->client_id)
                                    ->orderBy('date_facture', 'asc') // Trier par date
                                    ->get();

            if(count($facturesNonReglees) == 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune facture en attente de règlement',
                ]);
            }

            foreach ($facturesNonReglees as $factureTemp) {
                // Calculer le montant restant à régler pour cette facture
                $montantRestant = $factureTemp->montant_ttc - $factureTemp->montant_regle;

                if ($sommeMontantTTCC <= 0) {
                    break; // On arrête si la somme totale à régler est épuisée
                }

                // Le montant à régler pour cette facture est soit le montant restant, soit ce qui reste de la somme totale
                $montantARegler = min($montantRestant, $sommeMontantTTCC);

                $reglementNord = new ReglementClient();
                $reglementNord->facture_client_id = $factureTemp->id;
                $reglementNord->facture()->associate($factureTemp); // Important: associer la facture
                $reglementNord->date_reglement = now();
                $reglementNord->type_reglement = $request->moyen_reglement;
                $reglementNord->montant = $montantARegler;
                $reglementNord->created_by = auth()->id();
                $reglementNord->statut = ReglementClient::STATUT_BROUILLON;
                $reglementNord->save();

                // Valider le règlement
                if (!$reglementNord->valider(auth()->id())) {
                    throw new Exception("Erreur lors de la validation du règlement");
                }

                // Mettre à jour la session caisse
                if (method_exists($sessionCaisse, 'mettreAJourTotaux')) {
                    $sessionCaisse->mettreAJourTotaux();
                }                

                // Réduire la somme totale à régler
                $sommeMontantTTCC -= $montantARegler;
            }

            $facture->encaisse = 'oui';
            $facture->encaissed_at = now();
            $facture->save();
            // Fin règlement compte du Nord

            // Déstockage compte Nord
            foreach ($lignes as $data) {
                if ($data['quantite'] > 0) {
                    // Conversion en unité de base si nécessaire
                    $article = Article::findOrFail($data->article_id);
                    $quantiteBase = $data['quantite'];

                    if ($data['unite_vente_id'] != $article->unite_mesure_id) {
                        $conversion = ConversionUnite::where([
                            'unite_source_id' => $data->unite_vente_id,
                            'unite_dest_id' => $article->unite_mesure_id,
                            'article_id' => $article->id,
                            'statut' => true
                        ])->first();

                        if (!$conversion) {
                            throw new Exception(
                                "Pas de conversion trouvée pour l'article " . $article->designation
                            );
                        }

                        $quantiteBase = $conversion->convertir($data['quantite']);
                    }

                    // $pointVenteConnected = PointDeVente::with('depot')->findOrFail(auth()->user()->point_de_vente_id);
                    $depot = Depot::where('point_de_vente_id', auth()->user()->point_de_vente_id)->first();

                    // Vérifier le stock disponible
                    $stock = StockDepot::where([
                        'article_id' => $data->article_id,
                        'depot_id' => $depot->id
                    ])->first();

                    if (!$stock || $stock->quantite_reelle < $data->quantite_base) {
                        throw new Exception(
                            "Stock insuffisant pour l'article {$data->article->designation} " .
                            "(Demandé: {$data->quantite_base}, Disponible: " .
                            ($stock ? $stock->quantite_reelle : 0) . ")"
                        );
                    }

                    // Créer le mouvement de sortie
                    $mouvementSortie = $this->serviceStockSortie->traiterSortieStock([
                        'date_mouvement' => $facture->date_facture,
                        'depot_id' => $depot->id,
                        'article_id' => $data->article_id,
                        'unite_mesure_id' => $article->unite_mesure_id,
                        'quantite' => $quantiteBase,
                        'reference_mouvement' => ' ',
                        'document_type' => 'LIVRAISON_CLIENT',
                        'document_id' => $facture->id,
                        'user_id' => auth()->id(),
                        'notes' => "Facture client #{$facture->numero}"
                    ]);

                    if (!$mouvementSortie['succes']) {
                        throw new Exception($mouvementSortie['message']);
                    }
    
                    // Associer le mouvement à la ligne
                    $data->mouvement_stock_id = $mouvementSortie['donnees']['mouvement_id'];
                    $data->save();
                }
            }
            // Fin déstokage compte du Nord

            DB::commit();

            // Log de l'action
            Log::info('Règlement journalier revendeur avec succès', [
                'date' => $request->date_debut,
                'utilisateur_id' => auth()->id(),
                // 'session_caisse_id' => $sessionCaisse->id,
                'montant' => $sommeMontantTTCC
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Validation effectuée avec succès',
            ]);

            
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la validation du règlement journalier revendeur', [
                'date' => $request->date_debut,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
