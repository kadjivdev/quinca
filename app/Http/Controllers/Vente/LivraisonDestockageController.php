<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\{Client, LigneLivraisonDestockage, LivraisonDestockage};
use App\Models\Catalogue\Article;
use App\Models\Parametre\{Depot, UniteMesure};
use App\Models\Revendeur\FactureRevendeur;
use App\Models\Revendeur\LigneFactureRevendeur;
use App\Models\Stock\StockDepot;
use App\Services\ServiceStockSortie;
use App\Services\ServiceStockEntree;
use Barryvdh\DomPDF\Facade\Pdf;
use Codedge\Fpdf\Fpdf\PDF_MC_Table;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log};
use Exception;
use Illuminate\Validation\ValidationException;

class LivraisonDestockageController extends Controller
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
        $livraisons = LivraisonDestockage::with([
            "facture.destockage",
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
            $livraisons->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $livraisons->whereDate('created_at', '<=', $request->date_fin);
        }

        $livraisons = $livraisons->get();

        // Données pour les filtres et le modal de création
        $clients =  Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)
            ->orderBy('raison_sociale')->get();
        $depots = Depot::actif()->orderBy('libelle_depot')->get();

        // Statistiques pour le header
        // $totalArticlesLivres = DB::table('ligne_livraison_destockages')
        //     ->join('livraison_destockages', 'livraison_destockages.id', '=', 'livraison_destockages.livraison_destockage_id')
        //     ->where('livraison_destockages.statut', 'valide')
        //     ->whereMonth('livraison_destockages.date_livraison', now()->month)
        //     ->sum('ligne_livraison_destockages.quantite');

        $stats = [
            'total_livraisons' => LivraisonDestockage::count(),
            'livraisons_validees' => LivraisonDestockage::where('statut', 'valide')->count(),
            'livraisons_en_attente' => LivraisonDestockage::where('statut', 'brouillon')->count(),
            'total_articles_livres' => 0
        ];

        return view('pages.ventes.livraison-destockage.index', compact(
            'livraisons',
            'clients',
            'depots',
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
                'facture_id' => 'required|exists:facture_revendeurs,id',
                'depot_id' => 'required|exists:depots,id',
                'depot_dest_id' => 'nullable|exists:depots,id',
                'lignes' => 'required|array',
                'lignes.*.ligne_facture_id' => 'required|exists:ligne_facture_revendeurs,id',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.unite_vente_id' => 'required|exists:unite_mesures,id',
                'lignes.*.quantite' => 'required|numeric|min:0',
                'lignes.*.quantite_supplementaire' => 'required|numeric|min:0',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'document'             => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max en Ko (5 Mo)
            ]);

            $facture = FactureRevendeur::findOrFail($validated['facture_id']);

            if (!$facture->peutEtreLivree()) {
                return response()->json([
                    'success' => false,
                    'message' => $facture->statut !== 'validee'
                        ? 'Cette facture n\'est pas dans un état permettant la livraison'
                        : 'Cette facture est déjà totalement livrée'
                ], 422);
            }

            DB::beginTransaction();

            // traitement du document
            $documentUrl = null;
            if ($request->hasFile("document")) {
                $document = $request->file("document");
                $name = time() . "_" . $document->getClientOriginalName();
                $document->move("livraison_docs", $name);
                $documentUrl = asset("/livraison_docs/" . $name);
            }

            // Création de la livraison
            $livraison = new LivraisonDestockage();
            $livraison->facture_revendeur_id = $validated['facture_id'];
            $livraison->depot_id = $validated['depot_id'];
            $livraison->depot_dest_id = $validated['depot_dest_id'] ?? null;
            $livraison->numero = LivraisonDestockage::generateNumero();
            $livraison->date_livraison = now();
            $livraison->statut = 'brouillon';
            $livraison->notes = $validated['notes'];
            $livraison->created_by = auth()->id();
            $livraison->document = $documentUrl;
            $livraison->save();

            Log::info("Data des lignes", ["data" => $validated['lignes']]);

            // Création des lignes
            foreach ($validated['lignes'] as $data) {
                $QTeTotal = $data['quantite'] + $data['quantite_supplementaire'];
                Log::info("Qte après formatage:", ["qteTotal" => $QTeTotal]);

                if ($QTeTotal > 0) {
                    $ligneFacture = LigneFactureRevendeur::findOrFail($data['ligne_facture_id']);

                    $article = Article::findOrFail($data["article_id"]);
                    $ligneUniteMesure = UniteMesure::findOrFail($data["unite_vente_id"]);

                    // qte de base
                    $conversion = $this->serviceStockEntree->rechercherConversion(
                        $article->unite_mesure_id,
                        $data["unite_vente_id"],
                        $article->id
                    );

                    if (!$conversion) {
                        throw new Exception(sprintf(
                            "Aucune conversion trouvée de l'unité (%s) vers (%s) pour l'article %s ni l'inverse! Veuillez créer la conversion avant de continuer",
                            $ligneUniteMesure?->libelle_unite ?? '---',
                            $article->uniteMesure?->libelle_unite ?? '---',
                            $article->code_article ?? '---'
                        ));
                    }

                    /**Update du ligne facture */
                    // $ligneFacture
                    //     ->update([
                    //         "quantite_livree_simple" => $ligneFacture->quantite_livree_simple + $QTeTotal
                    //     ]);

                    $ligneFacture->quantite_livree_simple += $QTeTotal;
                    $ligneFacture->save();

                    // Vérifier les quantités par rapport à la facture
                    $quantiteLivree = $ligneFacture->lignesLivraison()
                        ->whereHas('livraison', function ($query) {
                            $query->where('statut', 'valide');
                        })
                        ->sum('quantite');


                    // conversion en unite de base
                    $qteSaisieConvertie = $this->serviceStockEntree->convertirQuantite(
                        $data["quantite"],
                        $conversion,
                        $data['unite_vente_id']
                    );

                    // qte de base
                    $quantite_base = $this->serviceStockEntree->convertirQuantite(
                        $data['quantite'],
                        $conversion,
                        $data['unite_vente_id']
                    );

                    // qte livree
                    $qteLivreeConvertie = $this->serviceStockEntree->convertirQuantite(
                        $quantiteLivree,
                        $conversion,
                        $data['unite_vente_id']
                    );

                    $QTeTotalConvertie = $this->serviceStockEntree->convertirQuantite(
                        $QTeTotal,
                        $conversion,
                        $data['unite_vente_id']
                    );

                    $reste = $qteSaisieConvertie - $qteLivreeConvertie;

                    if ($QTeTotalConvertie > $reste) {
                        throw new Exception(
                            "La quantité saisie dépasse le reste à livrer pour l'article " .
                                $ligneFacture->article->designation
                        );
                    }

                    $ligneLivraison = new LigneLivraisonDestockage();
                    $ligneLivraison->livraison_destockage_id = $livraison->id;
                    $ligneLivraison->ligne_facture_id = $data['ligne_facture_id'];
                    $ligneLivraison->article_id = $data['article_id'];
                    $ligneLivraison->unite_vente_id = $data['unite_vente_id'];
                    $ligneLivraison->quantite = $data['quantite'];
                    $ligneLivraison->quantite_base = $quantite_base;
                    $ligneLivraison->quantite_supplementaire = $data["quantite_supplementaire"];
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

    public function validateLivraison(Request $request, LivraisonDestockage $livraisonDestockage)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Vérifier si la livraison est déjà validée
            if ($livraisonDestockage->statut !== 'brouillon') {
                throw new Exception('Cette livraison a déjà été validée ou annulée');
            }

            // Charger les relations nécessaires
            $livraisonDestockage->load(['lignes.article', 'lignes.uniteVente']);

            $entrees = [];

            /** */
            foreach ($livraisonDestockage->lignes as $livraisonLigne) {
                /**Ligne Facture destockage associée */
                $ligneFactureRevendeur = $livraisonLigne
                    ->ligneFacture;

                /**MAJ de la ligne facture client */
                $ligneFactureRevendeur
                    ->update([
                        "quantite_livree" => $ligneFactureRevendeur
                            ->quantite_livree + $livraisonLigne->quantite
                    ]);

                // Créer le mouvement de sortie
                $mouvementSortie = $this->serviceStockSortie->traiterSortieStock([
                    'depot_id' => $livraisonDestockage->depot_id,

                    'article_id' => $livraisonLigne->article_id,
                    'unite_mesure_id' => $livraisonLigne->unite_vente_id,
                    'quantite' => $livraisonLigne->quantite,
                    'prix_unitaire' => $livraisonLigne->prix_unitaire,
                    'date_mouvement' => $livraisonDestockage->date_livraison,

                    'reference_mouvement' => $livraisonDestockage->numero,
                    'document_type' => 'LIVRAISON_CLIENT_DESTOCKAGE',
                    'document_id' => $livraisonDestockage->id,
                    'user_id' => auth()->id(),
                    'notes' => "Livraison client #{$livraisonDestockage->numero}"
                ]);

                if (!$mouvementSortie['succes']) {
                    throw new Exception($mouvementSortie['message']);
                }

                // Associer le mouvement à la ligne
                $livraisonLigne->mouvement_stock_id = $mouvementSortie['donnees']['mouvement_id'];
                $livraisonLigne->save();

                /** DATA POUR APPROVISIONNEMENT */
                if ($livraisonDestockage->depot_dest_id) {
                    //cette opération se fait seulement quand un dépôt de destination est choisi
                    $entrees[] = [
                        'depot_id' => $livraisonDestockage->depot_dest_id,
                        'article_id' => $livraisonLigne->article_id,
                        'unite_mesure_id' => $livraisonLigne->unite_vente_id,
                        'quantite' => $livraisonLigne->quantite,
                        'prix_unitaire' => $livraisonLigne->prix_unitaire,
                        'date_mouvement' => now(),
                        'notes' => "Entrée en stock via livraison dans le dépôt",
                        'user_id' => auth()->user()->id,
                        'livraison' => $livraisonDestockage->id, //pour mentionner qu'il s'agit d'un approvisionnement venant d'une livraison
                        'date_mouvement' => $livraisonDestockage->date_livraison,
                        'reference_mouvement' => $livraisonDestockage->numero,
                        'document_type' => 'BON_LIVRAISON_FOURNISSEUR_DESTOCKAGE',
                        'document_id' => $livraisonDestockage->id,
                        'notes' => "Livraison client #{$livraisonDestockage->numero}",
                    ];
                }
            }

            Log::debug('Les données:', $entrees);

            //cette opération se fait seulement quand un dépôt de destination est choisi
            if ($livraisonDestockage->depot_dest_id) {
                // Traiter les entrées en stock
                $resultatStock = $this->serviceStockEntree->traiterEntreesMultiples($entrees);
                Log::debug('Résultat traitement stock:', $resultatStock);
                if (!$resultatStock['succes']) {
                    throw new Exception("Erreur lors de la mise à jour du stock : " . $resultatStock['message']);
                }
            }

            // Valider la livraison
            $livraisonDestockage->update([
                'statut' => 'valide',
                'date_validation' => now(),
                'validated_at' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Livraison validée avec succès',
                'data' => [
                    'livraison' => $livraisonDestockage->fresh([
                        'lignes.mouvementStock',
                        'validatedBy'
                    ])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de la livraison:', [
                'livraison_id' => $livraisonDestockage->id,
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
     * Bordereau de livraison
     */
    public function bordereauLivraison(Request $request, LivraisonDestockage $livraison)
    {
        // Chargement des relations nécessaires
        $livraison->load([
            'facture.client',
            'lignes.article',
            'lignes.uniteVente',
            'createdBy',
            'validatedBy'
        ]);

        $entete = $request->get("entete");

        $pdf = Pdf::loadView('pages.ventes.livraison-destockage.partials.bordereau-livraison', compact('livraison', 'entete'));
        $pdf->setPaper('a4');

        return $pdf->stream("bordereau_{$livraison->numero}.pdf");
    }

    /**
     * Récupère les lignes de facture disponibles pour livraison
     */
    public function getLignesFactureDisponibles(Request $request, FactureRevendeur $factureRevendeur)
    {
        Log::debug("Debut de recuperation des lignes des livraisons destockage", ["data" => $factureRevendeur]);
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        // Charger les lignes avec leurs quantités déjà livrées
        $lignes = $factureRevendeur->lignes()
            ->with(['article', 'uniteVente'])
            ->where(function ($query) {
                /**on recupere seulement les lignes qui disposent encore de quantité */
                $query->where(function ($q) {
                    $q->whereNull('quantite_livree_simple')
                        ->orWhere(function ($subQ) {
                            $subQ->whereNotNull('quantite_livree_simple')
                                ->where('quantite', '>', DB::raw('quantite_livree_simple'));
                        });
                });
            })
            ->get()
            ->map(function ($ligne) use ($request) {
                // Log::debug("La quantité facturée : ", ["data" => $ligne]);
                /**
                 * Qte livrée
                 */
                $quantiteLivree = $ligne->quantite_livree_simple ?? $ligne->quantite_livree;

                // Récupérer le stock disponible
                $stockDisponible = $ligne->quantite - $quantiteLivree;

                Log::debug("La ligne: ", ["data" => $ligne]);

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
                    'quantite_facturee' => number_format($ligne->quantite, 2, ".", " "),
                    'quantite_base' => number_format($ligne->quantite_base, 2, ".", " "),
                    'quantite_livree' => number_format($quantiteLivree, 2, ".", " "),
                    'quantite_livree_simple' => number_format($ligne->quantite_livree_simple, 2, ".", " "),
                    'depot' => $ligne->facturedepot->libelle_depot,
                    'reste_a_livrer' => number_format($ligne->quantite - $quantiteLivree, 2, ".", " "),
                    'reste_max' => $ligne->quantite - $quantiteLivree,
                    'stock_disponible' => number_format($stockDisponible, 2, ".", " "),
                    'prix_unitaire' => $ligne->prix_unitaire_ht
                ];
            });

        return response()->json([
            'success' => true,
            'lignes' => $lignes,
            'facture' => [
                'numero' => $factureRevendeur->numero,
                'client' => [
                    'id' => $factureRevendeur->client->id,
                    'raison_sociale' => $factureRevendeur->client->raison_sociale
                ],
                'date_facture' => $factureRevendeur->date_facture->format('d/m/Y')
            ]
        ]);
    }

    /**
     * Supprime une livraison
     */
    public function destroy(Request $request, LivraisonDestockage $livraisonDestockage)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Vérifier si la livraison peut être supprimée
            if ($livraisonDestockage->statut !== 'brouillon') {
                throw new Exception('Seules les livraisons en brouillon peuvent être supprimées');
            }

            // Supprimer les lignes
            $livraisonDestockage->lignes()->delete();

            // Supprimer la livraison
            $livraisonDestockage->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison supprimé avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la livraison:', [
                'livraison_id' => $livraisonDestockage->id,
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

    public function edit(Request $request, LivraisonDestockage $livraisonDestockage)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            // Vérifier si la livraison est modifiable
            if ($livraisonDestockage->statut !== 'brouillon') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette livraison ne peut plus être modifiée'
                ], 422);
            }

            // Charger les relations nécessaires
            $livraisonDestockage->load([
                'facture.client',
                'depot',
                'lignes.article.uniteMesure',
                'lignes'
            ]);

            // Préparer les données des lignes
            $lignes = $livraisonDestockage->lignes->map(function ($ligne) use ($livraisonDestockage) {

                $ligneFacture = $ligne->ligneFacture;
                Log::debug("La ligne :", ["data" => $ligne]);

                /**
                 * Qte livrée
                 */
                $quantiteLivree = $ligneFacture->quantite_livree_simple ?? $ligneFacture->quantite_livree;

                Log::debug("quantiteLivree :", ["data" => $quantiteLivree]);

                // Récupérer le stock disponible
                $stockDisponible = $ligne->quantite - $quantiteLivree;

                return [
                    'id' => $ligne->id,
                    'ligne_facture_id' => $ligne->ligne_facture_id,
                    'article' => [
                        'id' => $ligne->article->id,
                        'designation' => $ligne->article->designation,
                        'reference' => $ligne->article->code_article
                    ],
                    'unite_mesure' => [
                        'id' => $ligne->unite_vente_id,
                        'libelle' => $ligne->uniteVente->libelle_unite
                    ],
                    'quantite' => $ligne->quantite,
                    'quantite_supplementaire' => $ligne->quantite_supplementaire,
                    'quantite_facturee' => $ligne->ligneFacture->quantite,
                    'quantite_livree' => $quantiteLivree,
                    'reste_a_livrer' => $ligne->ligneFacture->quantite - $quantiteLivree, //$ligne->quantite - $quantiteLivree,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'montant_total' => $ligne->montant_total,
                    'stock_disponible' => $stockDisponible
                ];
            });

            // Récupérer la liste des dépôts pour le select
            $depots = Depot::actif()->orderBy('libelle_depot')->get();

            return response()->json([
                'success' => true,
                'livraison' => [
                    'id' => $livraisonDestockage->id,
                    'numero' => $livraisonDestockage->numero,
                    'date_livraison' => $livraisonDestockage->date_livraison->format('d/m/Y'),
                    'depot_id' => $livraisonDestockage->depot_id,
                    'notes' => $livraisonDestockage->notes,
                    'facture' => [
                        'id' => $livraisonDestockage->facture->id,
                        'numero' => $livraisonDestockage->facture->numero,
                        'date_facture' => $livraisonDestockage->facture->date_facture->format('d/m/Y'),
                        'client' => [
                            'id' => $livraisonDestockage->facture->client->id,
                            'raison_sociale' => $livraisonDestockage->facture->client->raison_sociale
                        ]
                    ]
                ],
                'lignes' => $lignes,
                'depots' => $depots
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des données de la livraison:', [
                'livraison_id' => $livraisonDestockage->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données de la livraison'
            ], 500);
        }
    }

    public function update(Request $request, LivraisonDestockage $livraisonDestockage)
    {
        Log::debug("Les données de modification :", ["data" => $request->all()]);
        try {
            if ($livraisonDestockage->statut !== 'brouillon') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette livraison ne peut plus être modifiée'
                ], 422);
            }


            $validated = $request->validate([
                'facture_id' => 'required|exists:facture_revendeurs,id',
                'depot_id' => 'required|exists:depots,id',
                'depot_dest_id' => 'nullable|exists:depots,id',
                'lignes' => 'required|array',
                'lignes.*.ligne_facture_id' => 'required|exists:ligne_facture_revendeurs,id',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.unite_vente_id' => 'required|exists:unite_mesures,id',
                'lignes.*.quantite' => 'required|numeric|min:0',
                'lignes.*.quantite_supplementaire' => 'nullable|numeric',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'document'             => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max en Ko (5 Mo)
            ]);

            DB::beginTransaction();

            // Mettre à jour la livraison
            $livraisonDestockage->update([
                'depot_id' => $validated['depot_id'],
                'notes' => $validated['notes']
            ]);

            $uniteVenteId = $livraisonDestockage->lignes[0]?->unite_vente_id;

            // Supprimer les anciennes lignes
            $livraisonDestockage->lignes()->delete();

            // Remplacer les virgules par des points dans `lignes.*.quantite`
            if (isset($data['lignes'])) {
                $data['lignes'] = array_map(function ($ligne) {
                    if (isset($ligne['quantite'])) {
                        $ligne['quantite'] = str_replace(',', '.', $ligne['quantite']);
                    }
                    return $ligne;
                }, $data['lignes']);
            };

            // Création des lignes
            foreach ($validated['lignes'] as $data) {

                $QTeTotal = $data['quantite'] + $data['quantite_supplementaire'] ?? 0;

                if ($QTeTotal > 0) {
                    $ligneFacture = LigneFactureRevendeur::find($data['ligne_facture_id']);

                    /**Update du ligne facture */
                    $ligneFacture->update(["quantite_livree_simple" => $data['quantite']]);

                    // Vérifier les quantités par rapport à la facture
                    $quantiteLivree = $ligneFacture->lignesLivraison()
                        ->whereHas('livraison', function ($query) {
                            $query->where('statut', 'valide');
                        })
                        ->sum('quantite');

                    $article = Article::findOrFail($data["article_id"]);

                    // qte de base
                    $conversion = $this->serviceStockEntree->rechercherConversion(
                        $article->unite_mesure_id,
                        $data["unite_vente_id"],
                        $article->id
                    );

                    if (!$conversion) {
                        throw new Exception(sprintf(
                            "Aucune conversion trouvée de l'unité (%s) vers (%s) pour l'article %s ni l'inverse! Veuillez créer la conversion avant de continuer",
                            $ligneUniteMesure?->libelle_unite ?? '---',
                            $article->uniteMesure?->libelle_unite ?? '---',
                            $article->code_article ?? '---'
                        ));
                    }

                    // conversion en unite de base
                    $qteSaisieConvertie = $this->serviceStockEntree->convertirQuantite(
                        $data["quantite"],
                        $conversion,
                        $data['unite_vente_id']
                    );

                    // qte de base
                    $quantite_base = $this->serviceStockEntree->convertirQuantite(
                        $data['quantite'],
                        $conversion,
                        $data['unite_vente_id']
                    );

                    // qte livree
                    $qteLivreeConvertie = $this->serviceStockEntree->convertirQuantite(
                        $quantiteLivree,
                        $conversion,
                        $data['unite_vente_id']
                    );

                    $QTeTotalConvertie = $this->serviceStockEntree->convertirQuantite(
                        $QTeTotal,
                        $conversion,
                        $data['unite_vente_id']
                    );

                    $reste = $qteSaisieConvertie - $qteLivreeConvertie;

                    if ($QTeTotalConvertie > $reste) {
                        throw new Exception(
                            "La quantité saisie dépasse le reste à livrer pour l'article " .
                                $ligneFacture->article->designation
                        );
                    }

                    // if ($data['quantite'] > ($ligneFacture->quantite - $quantiteLivree)) {
                    //     throw new Exception(
                    //         "La quantité saisie dépasse le reste à livrer pour l'article " .
                    //             $ligneFacture->article->designation
                    //     );
                    // }

                    $ligneLivraison = new LigneLivraisonDestockage();
                    $ligneLivraison->livraison_destockage_id = $livraisonDestockage->id;
                    $ligneLivraison->ligne_facture_id = $data['ligne_facture_id'];
                    $ligneLivraison->article_id = $data['article_id'];
                    $ligneLivraison->unite_vente_id = $uniteVenteId; //(int) $data['unite_vente_id'];
                    $ligneLivraison->quantite = $data['quantite'];
                    $ligneLivraison->quantite_base = $quantite_base;
                    $ligneLivraison->quantite_supplementaire = $data["quantite_supplementaire"];
                    $ligneLivraison->prix_unitaire = $data['prix_unitaire'];
                    $ligneLivraison->montant_total = $data['quantite'] * $data['prix_unitaire'];
                    $ligneLivraison->save();
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Livraison modifiée avec succès',
                    'data' => [
                        'livraison' => $livraisonDestockage->fresh([
                            'facture.client',
                            'lignes.article.uniteMesure',
                            'lignes.ligneFacture',
                            'lignes.mouvementStock',
                            'depot'
                        ])
                    ]
                ]);
            } else {
                return redirect()
                    ->back()
                    ->with("message", "Modification éffectée avec succès!");
            }
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
                'livraison_id' => $livraisonDestockage->id,
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

    public function show(Request $request, LivraisonDestockage $livraisonDestockage)
    {
        try {

            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Charger les relations nécessaires
            $livraisonDestockage->load([
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
                    'id' => $livraisonDestockage->id,
                    'numero' => $livraisonDestockage->numero,
                    'date_livraison' => $livraisonDestockage->date_livraison->format('d/m/Y'),
                    'date_validation' => $livraisonDestockage->date_validation ? $livraisonDestockage->date_validation->format('d/m/Y H:i') : null,
                    'statut' => $livraisonDestockage->statut,
                    'notes' => $livraisonDestockage->notes,
                    'facture' => [
                        'numero' => $livraisonDestockage->facture->numero,
                        'date' => $livraisonDestockage->facture->date_facture->format('d/m/Y'),
                        'client' => [
                            'raison_sociale' => $livraisonDestockage->facture->client->raison_sociale,
                            'telephone' => $livraisonDestockage->facture->client->telephone,
                            'adresse' => $livraisonDestockage->facture->client->adresse
                        ]
                    ],
                    'depot' => [
                        'libelle' => $livraisonDestockage->depot->libelle_depot,
                        'adresse' => $livraisonDestockage->depot->adresse_depot
                    ],
                    'created_by' => $livraisonDestockage->createdBy ? $livraisonDestockage->createdBy->name : null,
                    'validated_by' => $livraisonDestockage->validatedBy ? $livraisonDestockage->validatedBy->name : null
                ],
                'lignes' => $livraisonDestockage->lignes->map(function ($ligne) {
                    return [
                        'id' => $ligne->id,
                        'article' => [
                            'id' => $ligne->article->id,
                            'reference' => $ligne->article->code_article,
                            'designation' => $ligne->article->designation
                        ],
                        'quantite' => number_format($ligne->quantite, 2, ".", " "),
                        'unite_id' => $ligne->uniteVente->id,
                        'unite' => $ligne->uniteVente->libelle_unite,
                        'prix_unitaire' => number_format($ligne->prix_unitaire, 2, ".", ""),
                        'montant_total' => number_format($ligne->montant_total, 2, ".", "")
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::debug("Erreure lors de la récuperation de la livraison", ["data" => $e->getMessage()]);
        }
    }

    public function generateBonA4(FactureRevendeur $facture)
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

    public function generateBonA5(FactureRevendeur $facture)
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
