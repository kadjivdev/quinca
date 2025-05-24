<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\{Client, LivraisonClient, FactureClient, LigneFacture, LigneLivraisonClient};
use App\Models\Catalogue\Article;
use App\Models\Parametre\{Depot, ConversionUnite};
use App\Models\Stock\StockDepot;
use App\Services\ServiceStockSortie;
use App\Services\ServiceStockEntree;
use Codedge\Fpdf\Fpdf\ChiffreEnLettre;
use Codedge\Fpdf\Fpdf\PDF_MC_Table;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log};
use Exception;
use Illuminate\Validation\ValidationException;

class LivraisonClientController extends Controller
{
    private $serviceStockSortie, $serviceStockEntree;

    public function __construct(ServiceStockSortie $serviceStockSortie, ServiceStockEntree $serviceStockEntree)
    {
        $this->serviceStockSortie = $serviceStockSortie;
        $this->serviceStockEntree = $serviceStockEntree;
    }

    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec pagination
        $livraisons = LivraisonClient::with([
            'facture.client',
            'depot',
            'lignes.article',
            'createdBy',
            'validatedBy'
        ])->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $livraisons->whereHas('facture', function ($query) use ($request) {
                $query->where('client_id', $request->client_id);
            });
        }

        if ($request->filled('depot_id')) {
            $livraisons->where('depot_id', $request->depot_id);
        }

        if ($request->filled('statut')) {
            $livraisons->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $livraisons->whereDate('date_livraison', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $livraisons->whereDate('date_livraison', '<=', $request->date_fin);
        }

        // $livraisons = $livraisons->paginate(10);
        $livraisons = $livraisons->get();

        // Données pour les filtres et le modal de création
        $clients =  Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)->orderBy('raison_sociale')->get();
        $depots = Depot::actif()->orderBy('libelle_depot')->get();

        // Statistiques pour le header
        $totalArticlesLivres = DB::table('ligne_livraison_clients')
            ->join('livraison_clients', 'livraison_clients.id', '=', 'ligne_livraison_clients.livraison_client_id')
            ->where('livraison_clients.statut', 'valide')
            ->whereMonth('livraison_clients.date_livraison', now()->month)
            ->sum('ligne_livraison_clients.quantite');

        $stats = [
            'total_livraisons' => LivraisonClient::count(),
            'livraisons_validees' => LivraisonClient::where('statut', 'valide')->count(),
            'livraisons_en_attente' => LivraisonClient::where('statut', 'brouillon')->count(),
            'total_articles_livres' => $totalArticlesLivres
        ];

        // Recherche des factures valides pour le modal de création
        $factures = FactureClient::where('statut', 'validee')
            ->whereHas('lignes', function ($query) {
                $query->whereRaw('quantite_base > IFNULL((
                            SELECT SUM(llc.quantite_base)
                            FROM ligne_livraison_clients llc
                            JOIN livraison_clients lc ON llc.livraison_client_id = lc.id
                            WHERE llc.ligne_facture_id = ligne_facture_clients.id
                            AND lc.statut = "valide"
                        ), 0)');
            })
            ->with(['client', 'lignes' => function ($query) {
                $query->whereRaw('quantite_base > IFNULL((
                            SELECT SUM(llc.quantite_base)
                            FROM ligne_livraison_clients llc
                            JOIN livraison_clients lc ON llc.livraison_client_id = lc.id
                            WHERE llc.ligne_facture_id = ligne_facture_clients.id
                            AND lc.statut = "valide"
                        ), 0)')
                    ->with(['article', 'uniteVente']);
            }])
            ->latest()
            ->get();


        return view('pages.ventes.livraison.index', compact(
            'livraisons',
            'clients',
            'depots',
            'factures',
            'stats',
            'date'
        ));
    }

    public function store(Request $request)
    {
        try {

            $data = $request->all();

            // Remplacer les virgules par des points dans `lignes.*.quantite`
            if (isset($data['lignes'])) {
                $data['lignes'] = array_map(function ($ligne) {
                    if (isset($ligne['quantite'])) {
                        $ligne['quantite'] = str_replace(',', '.', $ligne['quantite']);
                    }
                    return $ligne;
                }, $data['lignes']);
            };

            // Validation des données
            $validated = $request->validate([
                'facture_id' => 'required|exists:facture_clients,id',
                'depot_id' => 'required|exists:depots,id',
                'depot_dest_id' => 'nullable|exists:depots,id',
                'lignes' => 'required|array',
                'lignes.*.ligne_facture_id' => 'required|exists:ligne_facture_clients,id',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.unite_vente_id' => 'required|exists:unite_mesures,id',
                'lignes.*.quantite' => 'required|numeric|min:0',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            $facture = FactureClient::findOrFail($validated['facture_id']);

            if (!$facture->peutEtreLivree()) {
                return response()->json([
                    'success' => false,
                    'message' => $facture->statut !== 'validee'
                        ? 'Cette facture n\'est pas dans un état permettant la livraison'
                        : 'Cette facture est déjà totalement livrée'
                ], 422);
            }

            DB::beginTransaction();

            // Création de la livraison
            $livraison = new LivraisonClient();
            $livraison->facture_client_id = $validated['facture_id'];
            $livraison->depot_id = $validated['depot_id'];
            $livraison->depot_dest_id = $validated['depot_dest_id'] ?? null;
            $livraison->numero = LivraisonClient::generateNumero();
            $livraison->date_livraison = now();
            $livraison->statut = 'brouillon';
            $livraison->notes = $validated['notes'];
            $livraison->created_by = auth()->id();
            $livraison->save();

            // Création des lignes
            foreach ($validated['lignes'] as $data) {
                if ($data['quantite'] > 0) {
                    $ligneFacture = LigneFacture::find($data['ligne_facture_id']);

                    /**Update du ligne facture */
                    $ligneFacture->update(["quantite_livree_simple"=>$data['quantite']]);

                    // Vérifier les quantités par rapport à la facture
                    $quantiteLivree = $ligneFacture->lignesLivraison()
                        ->whereHas('livraison', function ($query) {
                            $query->where('statut', 'valide');
                        })
                        ->sum('quantite');

                    if ($data['quantite'] > ($ligneFacture->quantite - $quantiteLivree)) {
                        throw new Exception(
                            "La quantité saisie dépasse le reste à livrer pour l'article " .
                                $ligneFacture->article->designation
                        );
                    }

                    // Conversion en unité de base si nécessaire
                    $article = Article::findOrFail($data['article_id']);
                    $quantiteBase = $data['quantite'];

                    if ($data['unite_vente_id'] != $article->unite_mesure_id) {
                        $conversion = ConversionUnite::where([
                            'unite_source_id' => $data['unite_vente_id'],
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

                    $ligneLivraison = new LigneLivraisonClient();
                    $ligneLivraison->livraison_client_id = $livraison->id;
                    $ligneLivraison->ligne_facture_id = $data['ligne_facture_id'];
                    $ligneLivraison->article_id = $data['article_id'];
                    $ligneLivraison->unite_vente_id = $data['unite_vente_id'];
                    $ligneLivraison->quantite = $data['quantite'];
                    $ligneLivraison->quantite_base = $quantiteBase;
                    $ligneLivraison->prix_unitaire = $data['prix_unitaire'];
                    $ligneLivraison->montant_total = $data['quantite'] * $data['prix_unitaire'];
                    $ligneLivraison->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison créé avec succès',
                'data' => [
                    'livraison' => $livraison->load([
                        'facture.client',
                        'lignes.article',
                        'lignes.uniteVente',
                        'lignes.ligneFacture.tarification',
                        'createdBy'
                    ])
                ]
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la livraison:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Valide une livraison
     */
    public function validateLivraison(Request $request, LivraisonClient $livraisonClient)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Vérifier si la livraison est déjà validée
            if ($livraisonClient->statut !== 'brouillon') {
                throw new Exception('Cette livraison a déjà été validée ou annulée');
            }

            // Charger les relations nécessaires
            $livraisonClient->load(['lignes.article', 'lignes.uniteVente']);

            // Traiter chaque ligne
            foreach ($livraisonClient->lignes as $ligne) {
                // Vérifier le stock disponible
                $stock = StockDepot::where([
                    'article_id' => $ligne->article_id,
                    'depot_id' => $livraisonClient->depot_id
                ])->first();

                if (!$stock || $stock->quantite_reelle < $ligne->quantite_base) {
                    throw new Exception(
                        "Stock insuffisant pour l'article {$ligne->article->designation} " .
                            "(Demandé: {$ligne->quantite_base}, Disponible: " .
                            ($stock ? $stock->quantite_reelle : 0) . ")"
                    );
                }

                // Créer le mouvement de sortie
                $mouvementSortie = $this->serviceStockSortie->traiterSortieStock([
                    'date_mouvement' => $livraisonClient->date_livraison,
                    'depot_id' => $livraisonClient->depot_id,
                    'article_id' => $ligne->article_id,
                    'unite_mesure_id' => $ligne->article->unite_mesure_id,
                    'quantite' => $ligne->quantite_base,
                    'reference_mouvement' => $livraisonClient->numero,
                    'document_type' => 'LIVRAISON_CLIENT',
                    'document_id' => $livraisonClient->id,
                    'user_id' => auth()->id(),
                    'notes' => "Livraison client #{$livraisonClient->numero}"
                ]);

                if (!$mouvementSortie['succes']) {
                    throw new Exception($mouvementSortie['message']);
                }

                // Associer le mouvement à la ligne
                $ligne->mouvement_stock_id = $mouvementSortie['donnees']['mouvement_id'];
                $ligne->save();

                if ($livraisonClient->depot_dest_id !== null) {
                    $entrees[] = [
                        'depot_id' => $livraisonClient->depot_dest_id,
                        'article_id' => $ligne->article_id,
                        'unite_mesure_id' => $ligne->article->unite_mesure_id,
                        'quantite' => $ligne->quantite_base,
                        'prix_unitaire' => $ligne->prix_unitaire,
                        'date_mouvement' => $livraisonClient->date_livraison,
                        'reference_mouvement' => $livraisonClient->numero,
                        'document_type' => 'BON_LIVRAISON_FOURNISSEUR',
                        'document_id' => $livraisonClient->id,
                        'notes' => "Livraison client #{$livraisonClient->numero}",
                        'user_id' => auth()->id()
                    ];
                }
            }

            if ($livraisonClient->depot_dest_id !== null) {
                // Traiter les entrées en stock
                $resultatStock = $this->serviceStockEntree->traiterEntreesMultiples($entrees);

                \Log::debug('Résultat traitement stock:', $resultatStock);

                if (!$resultatStock['succes']) {
                    throw new Exception("Erreur lors de la mise à jour du stock : " . $resultatStock['message']);
                }
            }

            // Valider la livraison
            $livraisonClient->update([
                'statut' => 'valide',
                'date_validation' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livraison validée avec succès',
                'data' => [
                    'livraison' => $livraisonClient->fresh([
                        'lignes.mouvementStock',
                        'validatedBy'
                    ])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de la livraison:', [
                'livraison_id' => $livraisonClient->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Récupère les lignes de facture disponibles pour livraison
     */
    public function getLignesFactureDisponibles(Request $request, FactureClient $factureClient)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        // Charger les lignes avec leurs quantités déjà livrées
        $lignes = $factureClient->lignes()
            ->with(['article', 'uniteVente'])
            ->whereRaw('quantite_base > IFNULL((
                SELECT SUM(llc.quantite_base)
                FROM ligne_livraison_clients llc
                JOIN livraison_clients lc ON llc.livraison_client_id = lc.id
                WHERE llc.ligne_facture_id = ligne_facture_clients.id
                AND lc.statut = "valide"
            ), 0)')
            ->get()
            ->map(function ($ligne) use ($request) {
                // Calculer la quantité déjà livrée
                $quantiteLivree = DB::table('ligne_livraison_clients')
                    ->join('livraison_clients', 'livraison_clients.id', '=', 'ligne_livraison_clients.livraison_client_id')
                    ->where('ligne_livraison_clients.ligne_facture_id', $ligne->id)
                    ->where('livraison_clients.statut', 'valide')
                    ->sum('ligne_livraison_clients.quantite');

                // Récupérer le stock disponible
                $stockDisponible = 0;
                if ($request->filled('depot_id')) {
                    $stock = StockDepot::where([
                        'article_id' => $ligne->article_id,
                        'depot_id' => $request->depot_id
                    ])->first();
                    $stockDisponible = $stock ? $stock->quantite_reelle : 0;
                }

                return [
                    'id' => $ligne->id,
                    'article' => [
                        'id' => $ligne->article->id,
                        'designation' => $ligne->article->designation,
                        'reference' => $ligne->article->code_article
                    ],
                    'unite_vente' => [
                        'id' => $ligne->uniteVente->id,
                        'libelle' => $ligne->uniteVente->libelle_unite
                    ],
                    'quantite_facturee' => $ligne->quantite,
                    'quantite_base' => $ligne->quantite_base,
                    'quantite_livree' => $quantiteLivree,
                    'reste_a_livrer' => $ligne->quantite - $quantiteLivree,
                    'stock_disponible' => $stockDisponible,
                    'prix_unitaire' => $ligne->prix_unitaire_ht
                ];
            });

        return response()->json([
            'lignes' => $lignes,
            'facture' => [
                'numero' => $factureClient->numero,
                'client' => [
                    'id' => $factureClient->client->id,
                    'raison_sociale' => $factureClient->client->raison_sociale
                ],
                'date_facture' => $factureClient->date_facture->format('d/m/Y')
            ]
        ]);
    }

    /**
     * Supprime une livraison
     */
    public function destroy(Request $request, LivraisonClient $livraisonClient)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Vérifier si la livraison peut être supprimée
            if ($livraisonClient->statut !== 'brouillon') {
                throw new Exception('Seules les livraisons en brouillon peuvent être supprimées');
            }

            // Supprimer les lignes
            $livraisonClient->lignes()->delete();

            // Supprimer la livraison
            $livraisonClient->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison supprimé avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la livraison:', [
                'livraison_id' => $livraisonClient->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function verifierStock(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            $request->validate([
                'article_id' => 'required|exists:articles,id',
                'depot_id' => 'required|exists:depots,id'
            ]);

            // Vérifier le stock disponible et prix moyen via StockDepot
            $stock = StockDepot::where([
                'article_id' => $request->article_id,
                'depot_id' => $request->depot_id
            ])->first();

            $article = Article::find($request->article_id);

            if ($stock) {
                return response()->json([
                    'success' => true,
                    'quantite' => number_format($stock->quantite_reelle, 3, '.', ''),
                    'prix_moyen' => number_format($stock->prix_moyen, 2, '.', ''),
                    'message' => 'Stock vérifié avec succès',
                    'unite' => $article->uniteMesure->libelle_unite
                ]);
            }

            // Si aucun stock n'existe pour cet article dans ce magasin
            return response()->json([
                'success' => true,
                'quantite' => '0.000',
                'prix_moyen' => '0.00',
                'message' => 'Aucun stock existant',
                'unite' => $article->uniteMesure->libelle_unite
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du stock:', [
                'article_id' => $request->article_id,
                'depot_id' => $request->depot_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du stock'
            ], 500);
        }
    }

    public function edit(Request $request, LivraisonClient $livraisonClient)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            // Vérifier si la livraison est modifiable
            if ($livraisonClient->statut !== 'brouillon') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette livraison ne peut plus être modifiée'
                ], 422);
            }

            // Charger les relations nécessaires
            $livraisonClient->load([
                'facture.client',
                'depot',
                'lignes.article.uniteMesure',
                'lignes'
            ]);

            // Préparer les données des lignes
            $lignes = $livraisonClient->lignes->map(function ($ligne) use ($livraisonClient) {
                // Calculer la quantité déjà livrée pour cette ligne de facture
                $quantiteLivree = DB::table('ligne_livraison_clients')
                    ->join('livraison_clients', 'livraison_clients.id', '=', 'ligne_livraison_clients.livraison_client_id')
                    ->where('ligne_livraison_clients.ligne_facture_id', $ligne->ligne_facture_id)
                    ->where('livraison_clients.statut', 'valide')
                    ->where('ligne_livraison_clients.id', '!=', $ligne->id) // Exclure la ligne courante
                    ->sum('ligne_livraison_clients.quantite_base');

                // Récupérer le stock disponible depuis StockDepot
                $stockDepot = StockDepot::where('article_id', $ligne->article_id)
                    ->where('depot_id', $livraisonClient->depot_id)
                    ->first();

                $stockDisponible = $stockDepot ? $stockDepot->getQuantiteDisponibleAttribute() : 0;
                $prixUnitaire = $stockDepot ? $stockDepot->prix_moyen : 0;

                return [
                    'id' => $ligne->id,
                    'ligne_facture_id' => $ligne->ligne_facture_id,
                    'article' => [
                        'id' => $ligne->article->id,
                        'designation' => $ligne->article->designation,
                        'reference' => $ligne->article->code_article
                    ],
                    'unite_mesure' => [
                        'id' => $ligne->article->uniteMesure->id,
                        'libelle' => $ligne->article->uniteMesure->libelle_unite
                    ],
                    'quantite' => $ligne->quantite,
                    'quantite_facturee' => $ligne->ligneFacture->quantite,
                    'quantite_livree' => $quantiteLivree,
                    'reste_a_livrer' => $ligne->ligneFacture->quantite - $quantiteLivree,
                    'prix_unitaire' => $prixUnitaire,
                    'montant_total' => $ligne->montant_total,
                    'stock_disponible' => $stockDisponible
                ];
            });

            // Récupérer la liste des dépôts pour le select
            $depots = Depot::actif()->orderBy('libelle_depot')->get();

            return response()->json([
                'success' => true,
                'livraison' => [
                    'id' => $livraisonClient->id,
                    'numero' => $livraisonClient->numero,
                    'date_livraison' => $livraisonClient->date_livraison->format('d/m/Y'),
                    'depot_id' => $livraisonClient->depot_id,
                    'notes' => $livraisonClient->notes,
                    'facture' => [
                        'id' => $livraisonClient->facture->id,
                        'numero' => $livraisonClient->facture->numero,
                        'date_facture' => $livraisonClient->facture->date_facture->format('d/m/Y'),
                        'client' => [
                            'id' => $livraisonClient->facture->client->id,
                            'raison_sociale' => $livraisonClient->facture->client->raison_sociale
                        ]
                    ]
                ],
                'lignes' => $lignes,
                'depots' => $depots
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des données de la livraison:', [
                'livraison_id' => $livraisonClient->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données de la livraison'
            ], 500);
        }
    }

    public function update(Request $request, LivraisonClient $livraisonClient)
    {
        try {
            if ($livraisonClient->statut !== 'brouillon') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette livraison ne peut plus être modifiée'
                ], 422);
            }

            $validated = $request->validate([
                'depot_id' => 'required|exists:depots,id',
                'lignes' => 'required|array',
                'lignes.*.ligne_facture_id' => 'required|exists:ligne_facture_clients,id',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.quantite' => 'required|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Mettre à jour la livraison
            $livraisonClient->update([
                'depot_id' => $validated['depot_id'],
                'notes' => $validated['notes']
            ]);

            // Supprimer les anciennes lignes
            $livraisonClient->lignes()->delete();

            // Créer les nouvelles lignes
            foreach ($validated['lignes'] as $data) {
                if ($data['quantite'] > 0) {
                    $ligneFacture = LigneFacture::find($data['ligne_facture_id']);

                    // Vérifier les quantités par rapport à la facture
                    $quantiteLivree = $ligneFacture->lignesLivraison()
                        ->whereHas('livraison', function ($query) use ($livraisonClient) {
                            $query->where('statut', 'valide')
                                ->where('id', '!=', $livraisonClient->id);
                        })
                        ->sum('quantite');

                    if ($data['quantite'] > ($ligneFacture->quantite - $quantiteLivree)) {
                        throw new Exception(
                            "La quantité saisie dépasse le reste à livrer pour l'article " .
                                $ligneFacture->article->designation
                        );
                    }

                    // Récupérer l'article avec son unité de mesure
                    $article = Article::with('uniteMesure')->findOrFail($data['article_id']);

                    if (!$article->unite_mesure_id) {
                        throw new Exception("L'article {$article->designation} n'a pas d'unité de mesure définie");
                    }

                    // Vérifier le stock disponible
                    $stockDepot = StockDepot::where([
                        'depot_id' => $validated['depot_id'],
                        'article_id' => $article->id
                    ])->first();

                    if (!$stockDepot || $stockDepot->getQuantiteDisponibleAttribute() < $data['quantite']) {
                        throw new \Exception("Stock insuffisant pour l'article {$article->designation}");
                    }

                    // Créer la ligne de livraison
                    $ligneLivraison = new LigneLivraisonClient();
                    $ligneLivraison->livraison_client_id = $livraisonClient->id;
                    $ligneLivraison->ligne_facture_id = $data['ligne_facture_id'];
                    $ligneLivraison->article_id = $data['article_id'];
                    $ligneLivraison->unite_vente_id = $article->unite_mesure_id; // Utiliser l'unité de l'article
                    $ligneLivraison->quantite = $data['quantite'];
                    $ligneLivraison->quantite_base = $data['quantite']; // Car unité liée à l'article
                    $ligneLivraison->prix_unitaire = $stockDepot->prix_moyen;
                    $ligneLivraison->montant_total = $data['quantite'] * $stockDepot->prix_moyen;
                    $ligneLivraison->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livraison modifiée avec succès',
                'data' => [
                    'livraison' => $livraisonClient->fresh([
                        'facture.client',
                        'lignes.article.uniteMesure',
                        'lignes.ligneFacture',
                        'lignes.mouvementStock',
                        'depot'
                    ])
                ]
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
                'type' => 'warning'
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la modification de la livraison:', [
                'livraison_id' => $livraisonClient->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function show(Request $request, LivraisonClient $livraisonClient)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        // Charger les relations nécessaires
        $livraisonClient->load([
            'facture.client',
            'depot',
            'lignes.article',
            'lignes.uniteVente',
            'lignes.ligneFacture',
            'createdBy',
            'validatedBy'
        ]);

        // Préparer les données pour la réponse
        $data = [
            'livraison' => [
                'id' => $livraisonClient->id,
                'numero' => $livraisonClient->numero,
                'date_livraison' => $livraisonClient->date_livraison->format('d/m/Y'),
                'date_validation' => $livraisonClient->date_validation ? $livraisonClient->date_validation->format('d/m/Y H:i') : null,
                'statut' => $livraisonClient->statut,
                'notes' => $livraisonClient->notes,
                'facture' => [
                    'numero' => $livraisonClient->facture->numero,
                    'date' => $livraisonClient->facture->date_facture->format('d/m/Y'),
                    'client' => [
                        'raison_sociale' => $livraisonClient->facture->client->raison_sociale,
                        'telephone' => $livraisonClient->facture->client->telephone,
                        'adresse' => $livraisonClient->facture->client->adresse
                    ]
                ],
                'depot' => [
                    'libelle' => $livraisonClient->depot->libelle_depot,
                    'adresse' => $livraisonClient->depot->adresse_depot
                ],
                'created_by' => $livraisonClient->createdBy ? $livraisonClient->createdBy->name : null,
                'validated_by' => $livraisonClient->validatedBy ? $livraisonClient->validatedBy->name : null
            ],
            'lignes' => $livraisonClient->lignes->map(function ($ligne) {
                return [
                    'article' => [
                        'reference' => $ligne->article->reference,
                        'designation' => $ligne->article->designation
                    ],
                    'quantite' => $ligne->quantite,
                    'unite' => $ligne->uniteVente->libelle_unite,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'montant_total' => $ligne->montant_total
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function generateBonA4(FactureClient $facture)
    {

        // dd($facture);

        $pdf = new PDF_MC_Table();
        $pdf->AliasNbPages();  // To use the total number of pages
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // $pdf->Image("assets/img/logo.jpeg", 150, 10, 50, 30);
        // $pdf->Image("assets/img/head_facture.jpg", 10, 10, 70, 30);

        // $pdf->SetFont('', 'B', 10);
        // $pdf->Text(150, 42, 'Cotonou, le '. date("d m Y"));

        $pdf->SetFont('', 'BU', 12);
        $pdf->Text(75, 25, 'BORDEREAU DE LIVRAISON');
        $pdf->SetFont('', 'B', 12);
        $pdf->Text(85, 32, '_ _ _ _ _ _ _ _ _ _ _ ');

        // $pdf->SetFont('', 'B', 12);
        $pdf->Text(10, 45, 'Client :');
        $pdf->SetFont('', '', 12);
        $pdf->Text(30, 45, $facture->client->raison_sociale);

        $pdf->SetXY(10, 55);
        $pdf->SetFont('', 'B', 12);
        $pdf->SetWidths(array(120, 30, 30, 40));
        $pdf->SetAligns(array('L', 'C', 'C'));
        $pdf->Row(array(utf8_decode('Désignation'), utf8_decode('Tonnage'), utf8_decode('Détails')));

        $pdf->SetFont('', '', 12);
        $tot_ht = 0;
        foreach ($facture->lignes as $ligne) {
            $pdf->Row([
                utf8_decode($ligne->article->designation),
                number_format($ligne->quantite, 2, ',', ' '),
                utf8_decode($ligne->uuniteVente->libelle_unite),
            ]);
            $tot_ht += $ligne->quantite * $ligne->prix_unitaire_ht;
        }

        $pdf->SetXY(0, $pdf->GetY());
        $pdf->CheckPageBreak(20);
        $pdf->SetFont('', 'BU', 10);
        $pdf->Text($pdf->GetX() + 10, $pdf->GetY() + 10, utf8_decode('LIVREUR'));
        $pdf->Text($pdf->GetX() + 80, $pdf->GetY() + 10, utf8_decode('CHAUFFEUR'));
        $pdf->Text($pdf->GetX() + 160, $pdf->GetY() + 10, utf8_decode('RECEPTIONNAIRE'));

        $pdf->SetFont('', 'B', 8);
        $pdf->Text($pdf->GetX() + 160, $pdf->GetY() + 35, utf8_decode('Cotonou le ' . date('d/m/Y')));

        // Générer le nom de fichier unique pour le PDF
        $fileName = uniqid('proforma_', true) . '.pdf';

        // Stocker le PDF dans le système de fichiers temporaire
        // $tempFilePath = storage_path('app/temp/' . $fileName);
        // Capture la sortie du PDF en mémoire
        return response()->stream(
            function () use ($pdf) {
                $pdf->Output('I', 'bordereau_livraison.pdf');
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="bordereau_livraison.pdf"',
            ]
        );
    }
    public function generateBonA5(FactureClient $facture)
    {
        $pdf = new PDF_MC_Table('L', 'mm', 'A5');
        $pdf->AliasNbPages();  // To use the total number of pages
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        $pdf->SetFont('', 'BU', 10);
        $pdf->Text(90, 15, 'BORDEREAU DE LIVRAISON');
        $pdf->SetFont('', 'B', 10);
        $pdf->Text(95, 20, '_ _ _ _ _ _ _ _ _ _ _ ');

        $pdf->SetFont('', 'B', 10);
        $pdf->Text(10, 30, 'Client :');
        $pdf->SetFont('', '', 10);
        $pdf->Text(25, 30, $facture->client->raison_sociale);

        $pdf->SetXY(10, 40);
        $pdf->SetFont('', 'B', 10);
        $pdf->SetWidths(array(100, 40, 40));
        $pdf->SetAligns(array('L', 'C', 'C'));
        $pdf->Row(array(utf8_decode('Désignation'), utf8_decode('Tonnage'), utf8_decode('Détails')));

        $pdf->SetFont('', '', 10);
        foreach ($facture->lignes as $ligne) {
            $pdf->Row([
                utf8_decode($ligne->article->designation),
                number_format($ligne->quantite, 2, ',', ' '),
                utf8_decode($ligne->uniteVente->libelle_unite),
            ]);
        }

        $pdf->SetXY(0, $pdf->GetY());
        $pdf->CheckPageBreak(40);
        $pdf->SetFont('', 'BU', 8);
        $pdf->Text($pdf->GetX() + 10, $pdf->GetY() + 10, utf8_decode('LIVREUR'));
        $pdf->Text($pdf->GetX() + 90, $pdf->GetY() + 10, utf8_decode('CHAUFFEUR'));
        $pdf->Text($pdf->GetX() + 165, $pdf->GetY() + 10, utf8_decode('RECEPTIONNAIRE'));

        $pdf->SetFont('', 'B', 8);
        $pdf->Text($pdf->GetX() + 160, $pdf->GetY() + 35, utf8_decode('Cotonou le ' . date('d/m/Y')));

        return response()->stream(
            function () use ($pdf) {
                $pdf->Output('I', 'bordereau_livraison_A5.pdf');
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="bordereau_livraison_A5.pdf"',
            ]
        );
    }
}
