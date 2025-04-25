<?php

namespace App\Http\Controllers\Revendeur;

use App\Http\Controllers\Controller;
use App\Models\Vente\Client;
use App\Models\Catalogue\{Tarification, Article};
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\Depot;
use App\Models\Parametre\PointDeVente;
use App\Models\Vente\{FactureClient, SessionCaisse, ReglementClient};
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

class FactureRevendeurController extends Controller
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
                ->where('type_vente', 'normale')
                ->where('point_de_vente_id', Auth()->user()->point_de_vente_id)
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

            $depots = Depot::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->get();

            // Charger la liste des clients pour le filtre
            $clients =  Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->orderBy('raison_sociale')->get(['id', 'raison_sociale', 'taux_aib']);

            return view('pages.revendeur.facture.index', compact('factures', 'clients', 'date', 'tauxTva', 'depots'));
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
                    'type_vente' => 'normale'
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
                // 'sessionCaisse',
                'createdBy',
                'pointDeVente'
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
                'message' => 'Une erreur est survenue lors du chargement des détails de la facture '.$e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Début mise à jour facture', ['request' => $request->all(), 'facture_id' => $id]);

            // Vérifications initiales
            // $sessionCaisse = SessionCaisse::ouverte()
            //     ->where('utilisateur_id', auth()->id())
            //     ->first();

            // if (!$sessionCaisse) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Session de caisse requise.'
            //     ], 422);
            // }

            $facture = FactureRevendeur::findOrFail($id);
            $client = Client::findOrFail($request->client_id);
            $configuration = Societe::firstOrFail();

            // Validation
            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                // 'montant_regle' => 'required|numeric|min:0',
                // 'moyen_reglement' => 'required|string',
                'lignes' => 'required|array|min:1',
                // 'type_facture' => 'required|in:simple,normaliser',
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
                    'updated_by' => auth()->id(),
                    'observations' => $request->observations,
                    'taux_tva' => $request->type_facture === 'simple' ? 0 : $configuration->taux_tva,
                    'taux_aib' => $request->type_facture === 'simple' ? 0 : $client->taux_aib,
                ]);

                // Réinitialisation des totaux et suppression des anciennes lignes
                $facture->lignes()->delete();

                $totalHT = 0;
                $totalRemise = 0;
                $totalTVA = 0;
                $totalAIB = 0;

                // Mise à jour des lignes
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

                // Mise à jour des totaux de la facture
                $facture->update([
                    'montant_ht' => $totalHT,
                    'montant_remise' => $totalRemise,
                    'montant_ht_apres_remise' => $montantHTApresRemise,
                    'montant_tva' => $totalTVA,
                    'montant_aib' => $totalAIB,
                    'montant_ttc' => $montantTTC,
                ]);
                
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture mise à jour avec succès',
                    'data' => ['facture' => $facture->load([
                       'client', 'lignes.article', 'lignes.uniteVente',
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


        $pdf = PDF::loadView('pages.revendeur.facture.partials.print-facture', compact('facture'));
        $pdf->setPaper('a4');

        return $pdf->stream("facture_{$facture->numero}.pdf");
    }

    public function dailyRapport(Request $request) {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));

        $ventes = FactureRevendeur::whereBetween('date_facture', [$dateDebut, $dateDebut])
                                    ->where('type_vente', 'normale')
                                    ->where('encaisse', 'non')
                                    ->where('statut', 'validee')
                                    ->with('client')
                                    ->with('lignes')
                                    ->get();

        $totalLignes = $ventes->sum(function ($vente) {
            return $vente->lignes->count();
        });

        $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->get();

        return view('pages.revendeur.validation.vente-normale', compact(            
            'dateDebut',
            'ventes',
            'clients',
            'totalLignes'
        ));
    }


    public function MakevalidationDaily(Request $request) {
        $request->validate([
            'date_debut' => 'nullable|date',
            'client_id' => 'required|exists:clients,id',
            'moyen_paiement' => 'nullable|string|max:255',
        ]);

        try{
            DB::beginTransaction();
            $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));

            $factures = FactureRevendeur::whereBetween('date_facture', [$dateDebut, $dateDebut])
                                        ->where('type_vente', 'normale')
                                        ->where('encaisse', 'non')
                                        ->where('statut', 'validee')
                                        ->with('client')
                                        ->get();
                                    
            // Calcul de la somme des montants_ttc
            $sommeMontantTTCC = $factures->sum('montant_ttc');

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

            foreach ($facturesNonReglees as $facture) {
                // Calculer le montant restant à régler pour cette facture
                $montantRestant = $facture->montant_ttc - $facture->montant_regle;

                if ($sommeMontantTTCC <= 0) {
                    break; // On arrête si la somme totale à régler est épuisée
                }

                // Le montant à régler pour cette facture est soit le montant restant, soit ce qui reste de la somme totale
                $montantARegler = min($montantRestant, $sommeMontantTTCC);

                $reglement = new ReglementClient();
                $reglement->facture_client_id = $facture->id;
                $reglement->facture()->associate($facture); // Important: associer la facture
                $reglement->date_reglement = now();
                $reglement->type_reglement = $request->type_reglement;
                $reglement->montant = $montantARegler;
                $reglement->created_by = auth()->id();
                $reglement->statut = ReglementClient::STATUT_BROUILLON;
                $reglement->save();
                
                // Vérifier si on a une session de caisse ouverte
                $sessionCaisse = SessionCaisse::where('utilisateur_id', auth()->id())
                    ->where('statut', 'ouverte')
                    ->first();

                if (!$sessionCaisse) {
                    throw new Exception('Vous devez avoir une session de caisse ouverte pour valider un règlement');
                }

                // Valider le règlement
                if (!$reglement->valider(auth()->id())) {
                    throw new Exception("Erreur lors de la validation du règlement");
                }

                // Mettre à jour la session caisse
                if (method_exists($sessionCaisse, 'mettreAJourTotaux')) {
                    $sessionCaisse->mettreAJourTotaux();
                }

                // Mettre à jour le montant réglé de la facture
                $facture->montant_regle += $montantARegler;
                $facture->save();

                // Réduire la somme totale à régler
                $sommeMontantTTCC -= $montantARegler;
            }

            foreach ($factures as $facture) {
                $facture->encaisse = 'oui';
                $facture->encaissed_at = now();
                $facture->save();
            }

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
