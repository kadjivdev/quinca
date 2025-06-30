<?php

namespace App\Http\Controllers\Revendeur;

use App\Http\Controllers\Controller;
use App\Models\Vente\Client;
use App\Models\Catalogue\{Tarification, Article};
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\Depot;
use App\Models\Vente\{FactureClient, SessionCaisse, ReglementClient};
use App\Models\Parametre\Societe;
use App\Models\Parametre\UniteMesure;
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
use App\Services\ServiceStockEntree;


class FactureRevendeurController extends Controller
{
    private $serviceStockSortie;
    private $serviceStockEntree;

    public function __construct(
        ServiceStockSortie $serviceStockSortie,
        ServiceStockEntree $serviceStockEntree
    ) {
        $this->serviceStockSortie = $serviceStockSortie;
        $this->serviceStockEntree = $serviceStockEntree;
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
            $query = FactureRevendeur::with(['client'])
                ->where('type_vente', 'normale')
                ->orderBy('date_facture', 'desc');

            if (
                auth()->user()->hasRole("Super Administrateur")
                || auth()->user()->hasRole("CONTROLE INTERNE")
                || auth()->user()->hasRole("CONTROLE EXTERNE ET CELLULE DE REQUETE")
            ) {
                $factures = $query->get();
            } else {
                $factures = $query->where('point_de_vente_id', Auth()->user()->point_de_vente_id)
                    ->get();
            }

            // return response()->json($factures);
            // Ajouter des attributs calculés pour chaque facture
            $factures->transform(function ($facture) {
                // Calcul du reste à payer
                $facture->reste_a_payer = ($facture->montant_ttc - $facture->montant_remise) - $facture->montant_regle;

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
                'nombre_factures' => $factures->count()
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
            $clients =  Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)
                ->orderBy('raison_sociale')->get(['id', 'raison_sociale', 'taux_aib']);

            return view('pages.revendeur.facture.index', compact('factures', 'clients', 'date', 'tauxTva', 'depots'));
        } catch (Exception $e) {
            // dd($e->getMessage());

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
                // 'montant_regle' => 'required|numeric|min:0',
                'moyen_reglement' => 'required|string',

                'lignes' => 'required|array|min:1',
                'lignes' => 'required|array|min:1',
                'lignes*article_id' => 'required|exists,articles',
                'lignes*depot_id' => 'required|exits,depots',
                'lignes*quantite' => 'required',
                'lignes*tarification_id' => 'required',

                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $request->montant_regle = 0;

            /**
             * on verifie si les articles selectionnés
             * sont tous dans son depôts pour les non admins
             */
            if (!auth()->user()->hasRole("Super Administrateur")) {
                $userPv = auth()->user()->pointDeVente;
                $userPv_depotIds = $userPv->depot->pluck("id")->toArray(); //les depots du users

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
                    ->convertirQuantite($stock->article->reste($stock->depot_id), $conversion, $ligne['unite_vente_id']);

                // on verifie la quantité restante de l'article dans le depot est suffisante
                if ($QteStockConvertie < $QteConvertie) {
                    return response()->json([
                        'status' => false,
                        'message' => "Le reste du stock de l'article ($article->designation) est de $QteStockConvertie $venteUnite->libelle_unite dans le depôt ({$stock->depot->libelle_depot})! Stock insuiffisant par rapport à la quantité saisie"
                    ], 500);
                }
            }

            DB::beginTransaction();

            try {
                // Création de la facture
                $facture = new FactureRevendeur();

                $facture->fill([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    'point_de_vente_id' => Auth()->user()->point_de_vente_id,
                    'created_by' => auth()->id(),
                    'observations' => $request->observations,
                    'statut' => 'brouillon',
                    'type_facture' => $request->type_facture === "normaliser" ? "NORMALISE" : "SIMPLE",
                    'montant_ht' => 0,
                    'montant_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'taux_tva' => 0.18,
                    'taux_aib' => 0.01,
                    'taux_remise' => 0,
                    'montant_ht_apres_remise' => 0,
                    'montant_tva',
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'montant_regle' => 0,
                ]);

                $facture->save();

                // Création des lignes
                foreach ($request->lignes as $index => $ligne) {
                    $ligneMontantTTC = $ligne['quantite'] * $ligne['tarification_id'];
                    $ligneMontantHt = $ligneMontantTTC / 1.19;
                    $ligneMontantTVA = $ligneMontantHt * 0.18;
                    $ligneMontantAIB = $ligneMontantHt * 0.01;
                    $ligneMontantRemise = $ligneMontantTTC * $ligne['taux_remise'] / 100;

                    Log::info("La ligne $index", [
                        "montantHt" => $ligneMontantHt,
                        "montantTva" => $ligneMontantTVA,
                        "montantAib" => $ligneMontantAIB,
                        "montantRemise" => $ligneMontantRemise,
                    ]);

                    $ligneFacture = new LigneFactureRevendeur([
                        'article_id' => $ligne['article_id'],
                        'unite_vente_id' => $ligne['unite_vente_id'],
                        'quantite' => $ligne['quantite'],
                        'depot' => $ligne['depot_id'],
                        'prix_unitaire_ht' => $ligne['tarification_id'],
                        'taux_remise' => $ligne['taux_remise'] ?? 0,
                        'taux_tva' => $request->type_facture === 'simple' ? 0 : 18 / 100,
                        'taux_aib' => $request->type_facture === 'simple' ? 0 : 1 / 100,
                        'montant_ht' => $ligneMontantHt,
                        'montant_tva' => $ligneMontantTVA,
                        'montant_aib' => $ligneMontantAIB,
                        'montant_ttc' => $ligneMontantTTC,
                        'montant_remise' => $ligneMontantRemise,
                        'montant_ht_apres_remise' => $ligneMontantHt - $ligneMontantRemise,
                        'quantite_livree' => 0,
                    ]);

                    $facture->lignes()->save($ligneFacture);
                }

                // Mise à jour des totaux
                $facture->update([
                    'montant_ht' => $facture->lignes->sum("montant_ht"),
                    'montant_remise' => $facture->lignes->sum("montant_remise"),
                    'montant_ht_apres_remise' => $facture->lignes->sum("montant_ht_apres_remise"),
                    'montant_tva' => $facture->lignes->sum("montant_tva"),
                    'montant_aib' => $facture->lignes->sum("montant_aib"),
                    'montant_ttc' => $facture->lignes->sum("montant_ttc"),
                    'montant_regle' => $request->montant_regle,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture créée avec succès',
                    'data' => ['facture' => $facture->load([
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
                        'createdBy',
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
        Log::info("Terme de recherche:", ["terme" => $search]);
        $user = auth()->user();

        $stocks = StockDepot::with('article')
            ->get()
            ->filter(function ($stock) use ($search, $user) {
                /** POUR UN ADMIN OU UN CHARGE DES STOCKS, ON NE FAIT PAS DE FILTRE */
                if ($user->hasRole('Super Administrateur') || $user->hasRole('CHARGE DES STOCKS ET SUIVI DES ACHATS')) {
                    return (
                        str_contains(strtolower($stock->article->designation), strtolower($search)) ||
                        str_contains(strtolower($stock->article->code_article), strtolower($search))
                    );
                }

                /** ON FILTRE LES STOCKS SELON LES POINT DE VENTE DU USER */
                $userPv = auth()->user()->pointDeVente;
                $userPv_depotIds = $userPv->depot->pluck("id")->toArray(); //les depots du users

                if (in_array($stock->depot_id, $userPv_depotIds)) {

                    return (str_contains(strtolower($stock->article->designation), strtolower($search)) ||
                        str_contains(strtolower($stock->article->code_article), strtolower($search))
                    );
                }
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
                    'unite_mesure' => $stock->uniteMesure, //->libelle_unite,
                    'stock' => $resteStock ?? 00,
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
            Log::info('Début du chargement des détails de la facture revendeur ...', ['facture_id' => $id]);

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
                'message' => 'Une erreur est survenue lors du chargement des détails de la facture ' . $e->getMessage()
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
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
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

            $facture = FactureRevendeur::findOrFail($id);

            if ($facture->statut === 'validee') {
                throw new Exception('Facture déjà validée');
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

    /**
     * 
     */
    public function print(Request $request, FactureRevendeur $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);

        $logo = $request->get("logo");

        $pdf = PDF::loadView('pages.ventes.facture.partials.print-facture', compact('facture', "logo"));
        $pdf->setPaper('a4');

        return $pdf->stream("facture_{$facture->numero}.pdf");
    }

    /**
     * Bon à livrer
     */
    public function bonALivrer(Request $request, FactureRevendeur $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);

        $entete = $request->get("entete");

        $pdf = PDF::loadView('pages.ventes.facture.partials.bon-a-livrer', compact('facture', 'entete'));
        $pdf->setPaper('a4');

        return $pdf->stream("bon_a_livrer_{$facture->numero}.pdf");
    }

    /**
     * Bordereau de livraison
     */
    public function bordereauLivraison(Request $request, FactureRevendeur $facture)
    {
        // Chargement des relations nécessaires
        $facture->load([
            'client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);

        $entete = $request->get("entete");

        $pdf = PDF::loadView('pages.ventes.facture.partials.bordereau-livraison', compact('facture', 'entete'));
        $pdf->setPaper('a4');

        return $pdf->stream("bordereau_{$facture->numero}.pdf");
    }


    public function MakevalidationDaily(Request $request)
    {
        $request->validate([
            'date_debut' => 'nullable|date',
            'client_id' => 'required|exists:clients,id',
            'moyen_paiement' => 'nullable|string|max:255',
        ]);

        try {
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

            if (count($facturesNonReglees) == 0) {
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
