<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\Article;
use App\Models\Catalogue\DetailInventaire;
use App\Models\Catalogue\FamilleArticle;
use App\Models\Catalogue\Inventaire;
use App\Models\Parametre\Depot;
use App\Models\Parametre\UniteMesure;
use App\Models\Stock\StockDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Border, Alignment};
use App\Services\ServiceStockEntree;
use Illuminate\Support\Facades\Log;
use Exception;

class ArticleController extends Controller
{
    private $serviceStockEntree;

    public function __construct(ServiceStockEntree $serviceStockEntree)
    {
        $this->serviceStockEntree = $serviceStockEntree;
    }

    /**
     * Afficher la liste des articles
     */
    public function index(Request $request)
    {
        $query = Article::with("stocks")->orderBy('designation');

        if ($request->depot && $request->depot != 'tous') {
            $articles = $query->get()->filter(function ($article) use ($request) {
                $depotIds = $article->stocks->pluck("depot_id");

                foreach ($depotIds as $depotId) {
                    return $request->depot == $depotId;
                }
            });
        } else {
            $articles = $query->get();
        }

        $articles->map(function ($article) {
            $article->stocks->map(function ($stock) {
                $conversion = $this->serviceStockEntree
                    ->rechercherConversion(
                        $stock->unite_mesure_id,
                        $stock->article->unite_mesure_id,
                        $stock->article_id
                    );

                $stock->qantiteBase = $conversion ? $this->serviceStockEntree
                    ->convertirQuantite(
                        $stock->quantite_reelle,
                        $conversion,
                        $stock->unite_mesure_id
                    ) : 00;
            });
        });

        $articlesIds = $articles->pluck("id");

        // les depot liés à ces articles
        $depotIds = StockDepot::whereIn("article_id", $articlesIds)->distinct()->pluck("depot_id");

        $familles = FamilleArticle::where('statut', true)
            ->orderBy('libelle_famille')
            ->get();

        $totalArticles = Article::count();
        $articlesEnStock = Article::where('stockable', true)
            ->where('stock_actuel', '>', 0)
            ->count();
        $articlesCritiques = Article::where('stockable', true)
            ->whereRaw('stock_actuel <= stock_minimum')
            ->count();
        $articlesActifs = Article::where('statut', Article::STATUT_ACTIF)->count();

        $depots = Depot::get();

        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        $unites = UniteMesure::all();

        return view('pages.catalogues.article.index', compact(
            'articles',
            'familles',
            'totalArticles',
            'articlesEnStock',
            'articlesCritiques',
            'articlesActifs',
            'date',
            'unites',
            'depots',
            'depotIds'
        ));
    }

    /**
     * Affiche la page d'affectation d'article aux depots
     */

    public function show(Article $article)
    {
        $totalArticles = Article::count();
        $articlesEnStock = Article::where('stockable', true)
            ->where('stock_actuel', '>', 0)
            ->count();
        $articlesCritiques = Article::where('stockable', true)
            ->whereRaw('stock_actuel <= stock_minimum')
            ->count();
        $articlesActifs = Article::where('statut', Article::STATUT_ACTIF)->count();

        $familles = FamilleArticle::all();
        $depots = Depot::get();
        $unites = UniteMesure::all();


        return view("pages.catalogues.article.affect-depot", compact([
            "depots",
            "article",
            "articlesEnStock",
            "totalArticles",
            "articlesActifs",
            "articlesCritiques",
            "familles",
            "unites"
        ]));
    }

    /**
     * Recherche d'articles pour le select2
     */

    function articleAffect(Request $request, Article $article)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                "depots" => "required",
                "quantite_reelle" => "required",
                "unite_mesure_id" => "required|exists:unite_mesures,id",
            ], [
                'depots.required' => "Choisissez un dépôt",
                'quantite_reelle.required' => "La quantité est réquise!",
                'unite_mesure_id.required' => "L'unité de mesure est réquise!"
            ]);

            // 
            foreach ($request->depots as $depotId) {
                $entrees[] = [
                    'depot_id' => $depotId,
                    'article_id' => $article->id,
                    'unite_mesure_id' => $request->unite_mesure_id,
                    'quantite' => $request->quantite_reelle,
                    'prix_unitaire' => 0,
                    'date_mouvement' => now(),
                    'notes' => "Entrée en stock via attachement direct aux dépôts",
                    'user_id' => Auth::id()
                ];

                // Traiter les entrées en stock
                $resultatStock = $this->serviceStockEntree->traiterEntreesMultiples($entrees);

                Log::debug('Résultat traitement stock:', $resultatStock);

                if (!$resultatStock['succes']) {
                    throw new Exception("Erreur lors de la mise à jour du stock : " . $resultatStock['message']);
                }
            }

            DB::commit();

            return back()->with("success", "Affectation éffectuée avec succès!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::debug("Une erreure est survenue au cours de l'enregistrement " . $e->getMessage());

            return back()->with("error", "Opération échouée : " . $e->getMessage());
        }
    }

    public function searchArticles(Request $request)
    {
        try {
            $search = $request->get('q');
            $depot_id = $request->get('depot_id'); // Récupérer l'ID du magasin
            $page = $request->get('page', 1);
            $perPage = 10;

            $articles = Article::query()
                ->select([
                    'articles.id',
                    'articles.code_article',
                    'articles.designation',
                    'articles.stockable',
                    'articles.famille_id'
                ])
                ->with([
                    'famille:id,libelle_famille',
                    'famille.conversions' => function ($query) {
                        $query->select(
                            'conversion_unites.id',
                            'conversion_unites.unite_source_id',
                            'conversion_unites.unite_dest_id',
                            'conversion_unites.coefficient',
                            'conversion_unites.famille_id'
                        )
                            ->where('conversion_unites.statut', true)
                            ->with([
                                'uniteSource:id,code_unite,libelle_unite',
                                'uniteDest:id,code_unite,libelle_unite'
                            ]);
                    },
                    // Charger le stock du magasin spécifié
                    'stocks' => function ($query) use ($depot_id) {
                        $query->select('article_id', 'depot_id', 'quantite')
                            ->where('depot_id', $depot_id)
                            ->where('statut', true);
                    }
                ])
                ->where(function ($query) use ($search) {
                    $query->where('code_article', 'LIKE', "%{$search}%")
                        ->orWhere('designation', 'LIKE', "%{$search}%");
                })
                ->where('statut', Article::STATUT_ACTIF)
                ->orderBy('code_article')
                ->paginate($perPage);

            return response()->json([
                'items' => $articles->map(function ($article) use ($depot_id) {
                    // Récupérer le stock du magasin
                    $stockDepot = $article->stocks->first();
                    $stockDisponible = $stockDepot ? $stockDepot->quantite : 0;

                    // Récupérer toutes les unités disponibles pour cet article
                    $unites = collect();

                    if ($article->famille && $article->famille->conversions) {
                        // Récupérer les unités sources et destinations uniques
                        $article->famille->conversions->each(function ($conversion) use (&$unites, $stockDisponible) {
                            // Unité source (unité de base)
                            $unites->push([
                                'id' => $conversion->uniteSource->id,
                                'code' => $conversion->uniteSource->code_unite,
                                'libelle' => $conversion->uniteSource->libelle_unite,
                                'coefficient' => 1, // Unité de base
                                'stock_disponible' => $stockDisponible // Stock en unité de base
                            ]);

                            // Unité de destination avec conversion
                            $unites->push([
                                'id' => $conversion->uniteDest->id,
                                'code' => $conversion->uniteDest->code_unite,
                                'libelle' => $conversion->uniteDest->libelle_unite,
                                'coefficient' => $conversion->coefficient,
                                'stock_disponible' => $stockDisponible / $conversion->coefficient // Stock converti
                            ]);
                        });

                        // Supprimer les doublons et garder la première occurrence
                        $unites = $unites->unique('id')->values();
                    }

                    return [
                        'id' => $article->id,
                        'code_article' => $article->code_article,
                        'designation' => $article->designation,
                        'stock_disponible' => $stockDisponible,
                        'stockable' => $article->stockable,
                        'famille' => $article->famille ? $article->famille->libelle_famille : null,
                        'unites_mesure' => $unites,
                        'depot_id' => $depot_id
                    ];
                }),
                'pagination' => [
                    'more' => $articles->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur recherche articles: ' . $e->getMessage());
            return response()->json([
                'items' => [],
                'pagination' => ['more' => false],
                'error' => 'Erreur lors de la recherche d\'articles'
            ], 500);
        }
    }

    /**
     * Filtrer les articles
     */
    public function filter(Request $request)
    {
        $query = Article::with(['famille']);

        if ($request->famille) {
            $query->where('famille_id', $request->famille);
        }

        if ($request->stock) {
            switch ($request->stock) {
                case 'critique':
                    $query->whereRaw('stock_actuel <= stock_minimum');
                    break;
                case 'alerte':
                    $query->whereRaw('stock_actuel <= stock_securite AND stock_actuel > stock_minimum');
                    break;
                case 'normal':
                    $query->whereRaw('stock_actuel > stock_securite AND stock_actuel < stock_maximum');
                    break;
                case 'surplus':
                    $query->whereRaw('stock_actuel >= stock_maximum');
                    break;
            }
        }

        $articles = $query->get();

        $articlesIds = $articles->pluck("id");
        // les depot liés à ces articles
        $depotIds = StockDepot::whereIn("article_id", $articlesIds)->distinct()->pluck("depot_id");

        return view('pages.catalogues.article.partials.list', compact('articles', "depotIds"));
    }

    /**
     * Charge les données d'un article pour modification
     */

    public function edit($id)
    {
        $article = Article::with(['famille'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }

    public function generateUniqueCode()
    {
        do {
            $code = 'ART-' . strtoupper(Str::random(8));
        } while (Article::where('code_article', $code)->exists());

        return response()->json(['code' => $code]);
    }

    /**
     * Créer un nouvel article
     */

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            $data['stockable'] = filter_var($request->input('stockable'), FILTER_VALIDATE_BOOLEAN);

            $validator = Validator::make($data, [
                // 'depots*' => 'required',
                // 'designation' => 'required|string|max:255',
                // 'famille_id' => 'required|exists:famille_articles,id',
                // 'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                // 'unite_mesure_id ' => 'required|exists:unite_mesures,id',            
                'code_article' => 'required|unique:articles,code_article,',
                'designation' => 'required|string|max:255',
                'description' => 'nullable|string',
                'famille_id' => 'required|exists:famille_articles,id',
                'stock_minimum' => 'required_if:stockable,1|numeric|min:0',
                'stock_maximum' => 'required_if:stockable,1|numeric|gt:stock_minimum',
                'stock_securite' => 'required_if:stockable,1|numeric|min:0',
                'code_barre' => 'nullable|unique:articles,code_barre,',
                'stockable' => 'boolean',
                'emplacement_stock' => 'nullable|string|max:100',
                'photo' => 'nullable|image|max:2048',
                'unite_mesure_id' => 'required|exists:unite_mesures,id',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Veuillez remplir correctement tous les champs obligatoires',
                        'errors' => $validator->errors()
                    ], 422);
                } else {
                    return redirect()->back()->withErrors($validator)->withInput();
                }
            }

            // Gérer l'upload de l'image
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                // Stocker l'image dans le dossier public/images/articles
                $path = $file->storeAs('public/images/articles', $filename);
                // Convertir le chemin pour l'accès public
                $data['photo'] = 'storage/images/articles/' . $filename;
            }

            $article = Article::create($data);

            // // attachement de l'article au dépot
            // foreach ($request->depots as $depotId) {
            //     StockDepot::create([
            //         'depot_id' => $depotId,
            //         'article_id' => $article->id,
            //         'quantite_reelle' => $request->stock_actuel,
            //         'stock_minimum' => $request->stock_minimum,
            //         'stock_maximum' => $request->stock_maximum,
            //         'emplacement' => $request->emplacement_stock,
            //         'user_id' => auth()->user()->id,
            //         'unite_mesure_id' => $request->unite_mesure_id,
            //     ]);
            // }

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Article créé avec succès',
                    'data' => $article
                ], 201);
            } else {
                return redirect()->back()->with("success", "Article créé avec succès");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un article
     */

    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'success' => false,
                'message' => 'Article non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code_article' => 'required|unique:articles,code_article,' . $id,
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string',
            'famille_id' => 'required|exists:famille_articles,id',
            'stock_minimum' => 'required_if:stockable,1|numeric|min:0',
            'stock_maximum' => 'required_if:stockable,1|numeric|gt:stock_minimum',
            'stock_securite' => 'required_if:stockable,1|numeric|min:0',
            'code_barre' => 'nullable|unique:articles,code_barre,' . $id,
            'stockable' => 'boolean',
            'emplacement_stock' => 'nullable|string|max:100',
            'photo' => 'nullable|image|max:2048',
            'unite_mesure_id' => 'required|exists:unite_mesures,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['stockable'] = $request->has('stockable');

            // Gestion de la photo
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si elle existe
                if ($article->photo) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $article->photo));
                }

                $photo = $request->file('photo');
                $path = $photo->store('articles', 'public');
                $data['photo'] = Storage::url($path);
            }

            $article->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Article mis à jour avec succès',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'article',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Enregistrer plusieurs inventaires
     */
    public function storeMultipleInventaires(Request $request)
    {
        if (!auth()->user()->can("inventaires.create")) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé pour enregistrer un inventaire');
        }

        $idsToArray = explode(",", str_replace(["[", "]"], '', $request->depotIds));

        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'articles' => 'required|array',
                'articles.*' => 'required|array',
                'articles.*.*' => 'required|numeric|min:0',
                // 'depotIds' => 'required|array',
                'depotIds.*' => 'required|exists:depots,id'
            ], [
                'depotIds.required' => "Veuillez selectionner le(s) dépôt(s) concerné(s)",
                'articles.required' => "Aucun article n'a été sélectionné",
                'articles.*.required' => "Les quantités sont requises",
                'articles.*.*.numeric' => "Les quantités doivent être des nombres",
                'articles.*.*.min' => "Les quantités ne peuvent pas être négatives"
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            // Création de l'inventaire
            $inventaire = Inventaire::create([
                'date_inventaire' => now(),
                'user_id' => Auth::id(),
                'depot_ids' => $request->depotIds,
            ]);

            // Récupération de tous les articles concernés en une seule requête
            $articles = Article::whereIn('id', array_keys($request->articles))->get();

            // Récupération de tous les stocks concernés en une seule requête
            $stockDepots = StockDepot::whereIn('article_id', $articles->pluck('id'))
                ->whereIn('depot_id',  $idsToArray)
                ->get()
                ->keyBy(function ($stock) {
                    return $stock->article_id . '_' . $stock->depot_id;
                });

            $detailsInventaire = [];
            $stockUpdates = [];

            foreach ($request->articles as $articleId => $depots) {
                $article = $articles->firstWhere('id', $articleId);

                if (!$article) {
                    throw new \Exception("Article ID $articleId non trouvé");
                }

                foreach ($depots as $depotId => $qteReel) {
                    $stockKey = $articleId . '_' . $depotId;
                    $stockDepot = $stockDepots->get($stockKey);

                    if (!$stockDepot) {
                        throw new \Exception("Stock non trouvé pour l'article $articleId dans le dépôt $depotId");
                    }

                    // Préparation des détails d'inventaire
                    $detailsInventaire[] = [
                        'qte_stock' => $article->stock_actuel,
                        'qte_reel' => $qteReel,
                        'stock_depot_id' => $stockDepot->id,
                        'inventaire_id' => $inventaire->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];

                    // Préparation des mises à jour de stock
                    $stockUpdates[] = [
                        'id' => $stockDepot->id,
                        'quantite_reelle' => $qteReel
                    ];
                }
            }

            // Insertion en masse des détails d'inventaire
            DetailInventaire::insert($detailsInventaire);

            // Mise à jour en masse des stocks
            foreach ($stockUpdates as $update) {
                StockDepot::where('id', $update['id'])
                    ->update(['quantite_reelle' => $update['quantite_reelle']]);
            }

            DB::commit();

            Log::info('Inventaire créé avec succès', [
                'inventaire_id' => $inventaire->id,
                'user_id' => Auth::id(),
                'nombre_articles' => count($request->articles)
            ]);

            return redirect()->back()->with('success', 'Inventaire enregistré avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de l\'enregistrement de l\'inventaire', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Erreur lors de l\'enregistrement de l\'inventaire: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un article
     */

    public function destroy($id)
    {
        try {
            $article = Article::findOrFail($id);

            // Vérifier s'il y a des stocks ou des tarifications
            if ($article->stockDepots()->count() > 0 || $article->stockPointsVente()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cet article car il a des stocks associés'
                ], 403);
            }

            if ($article->tarifications()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cet article car il a des tarifications associées'
                ], 403);
            }

            // Supprimer la photo si elle existe
            if ($article->photo) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $article->photo));
            }

            // Supprimer l'article
            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'article',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le stock d'un article
     */

    public function updateStock(Request $request, $id)
    {
        $article = Article::findOrFail($id);

        if (!$article->stockable) {
            return response()->json([
                'success' => false,
                'message' => 'Cet article n\'est pas stockable'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric',
            'type' => 'required|in:add,subtract,set'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$article->updateStock($request->quantity, $request->type)) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de mettre à jour le stock'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock mis à jour avec succès',
            'data' => $article
        ]);
    }

    /**
     * Changer le statut d'un article
     */

    public function toggleStatus($id)
    {
        try {
            $article = Article::findOrFail($id);

            $article->statut = $article->statut === Article::STATUT_ACTIF
                ? Article::STATUT_INACTIF
                : Article::STATUT_ACTIF;

            $article->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut modifié avec succès',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        try {
            // Créer un nouveau spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Import Articles');

            // Définir les en-têtes
            $headers = [
                'Famille (Code)*',
                'Désignation*',
                'Description',
                'Stock Minimum',
                'Stock Maximum',
                'Stock Sécurité',
                'Code Barre',
                'Stockable',
                'Emplacement Stock'
            ];

            // Insérer les en-têtes
            foreach ($headers as $index => $header) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . '1', $header);

                // Style pour les en-têtes
                $sheet->getStyle($col . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '000000'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2EFDA']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN
                        ]
                    ]
                ]);

                // Ajuster la largeur des colonnes
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Exemple de données
            $example = [
                'FAM001',           // Code famille
                'Savon Liquide',    // Désignation
                'Savon liquide pour les mains', // Description
                '10',               // Stock minimum
                '100',              // Stock maximum
                '20',               // Stock sécurité
                '123456789',        // Code barre
                'OUI',              // Stockable
                'RAYON-A1'          // Emplacement
            ];

            // Ajouter l'exemple
            foreach ($example as $index => $value) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . '2', $value);
            }

            // Style pour l'exemple
            $sheet->getStyle('A2:I2')->getFont()->setItalic(true);
            $sheet->getStyle('A2:I2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('808080'));

            // Validation pour le champ Stockable
            $validation = $sheet->getCell('H2')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"OUI,NON"');

            // Copier la validation pour les 100 premières lignes
            for ($i = 3; $i <= 100; $i++) {
                $validation = $sheet->getCell('H' . $i)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"OUI,NON"');
            }

            // Ajouter une feuille d'instructions
            $instructionSheet = $spreadsheet->createSheet();
            $instructionSheet->setTitle('Instructions');

            $instructions = [
                ['Instructions d\'import des articles'],
                [''],
                ['1. Champs obligatoires :'],
                ['   - Code Article* : Code de l\'article'],
                ['   - Désignation* : Nom de l\'article'],
                ['   - Famille* : Nom de l\'article'],
                [''],
                ['2. Champs numériques :'],
                ['   - Stock Minimum : Nombre décimal'],
                ['   - Stock Maximum : Nombre décimal'],
                ['   - Stock Sécurité : Nombre décimal'],
                [''],
                ['3. Autres champs :'],
                ['   - Code Barre : Optionnel'],
                ['   - Stockable : OUI ou NON'],
                ['   - Emplacement : Optionnel'],
                [''],
                ['4. Notes :'],
                ['   - Le code article sera généré automatiquement'],
                ['   - Le statut sera actif par défaut'],
                ['   - Le stock initial sera de 0']
            ];

            foreach ($instructions as $index => $row) {
                $instructionSheet->setCellValue('A' . ($index + 1), $row[0]);
            }

            $instructionSheet->getStyle('A1')->getFont()->setBold(true);
            $instructionSheet->getColumnDimension('A')->setWidth(60);

            // Revenir à la première feuille
            $spreadsheet->setActiveSheetIndex(0);

            // Créer le fichier
            $writer = new Xlsx($spreadsheet);

            // Sauvegarder dans un fichier temporaire
            $fileName = 'modele_import_articles.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'template_');
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la génération du template : ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:5120' // 5MB max
            ]);

            $file = $request->file('file');

            $reader = IOFactory::createReaderForFile($file->getPathname());

            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Supprimer l'en-tête
            array_shift($rows);

            $errors = [];
            $imported = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                // Ignorer les lignes vides
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Validation des données de base
                    if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                        $errors[] = "Ligne $rowNumber : Le code article, la désignation et le libellé de la famille sont requis";
                        $skipped++;
                        continue;
                    }

                    // Vérifier l'existence de la famille par son libellé
                    $famille = FamilleArticle::where('libelle_famille', $row[2])->first();
                    if (!$famille) {
                        $errors[] = "Ligne $rowNumber : La famille avec le libellé '{$row[2]}' n'existe pas";
                        $skipped++;
                        continue;
                    }

                    // Vérifier l'existence de l'unité de mesure par son libellé
                    $uniteMesure = UniteMesure::where('libelle_unite', $row[10])->first();
                    if (!$uniteMesure) {
                        $errors[] = "Ligne $rowNumber : L\'unité d emesure avec le libellé '{$row[10]}' n'existe pas";
                        $skipped++;
                        continue;
                    }

                    // Générer un code article unique

                    // Conversion des valeurs numériques avec validation
                    try {
                        $stockMin = !empty($row[4]) ? floatval($row[4]) : 5;
                        if ($stockMin < 0) {
                            throw new \Exception("Le stock minimum doit être positif");
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Ligne $rowNumber : Erreur sur le stock minimum - " . $e->getMessage();
                        $skipped++;
                        continue;
                    }

                    try {
                        $stockMax = !empty($row[5]) ? floatval($row[5]) : 5000;
                        if ($stockMax < $stockMin) {
                            throw new \Exception("Le stock maximum doit être supérieur au stock minimum");
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Ligne $rowNumber : Erreur sur le stock maximum - " . $e->getMessage();
                        $skipped++;
                        continue;
                    }

                    try {
                        $stockSecurite = !empty($row[6]) ? floatval($row[6]) : 20;
                        if ($stockSecurite < 0) {
                            throw new \Exception("Le stock de sécurité doit être positif");
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Ligne $rowNumber : Erreur sur le stock de sécurité - " . $e->getMessage();
                        $skipped++;
                        continue;
                    }

                    // Log pour debug
                    \Log::info("Tentative de création d'article", [
                        'ligne' => $rowNumber,
                        'donnees' => [
                            'code_article' => $row[0],
                            'designation' => $row[1],
                            'famille_id' => $famille->id,
                            'stock_minimum' => $stockMin,
                            'stock_maximum' => $stockMax,
                            'stock_securite' => $stockSecurite,
                            'code_barre' => $row[7] ?? null,
                            'stockable' => strtoupper($row[8] ?? '') === 'OUI',
                            'emplacement_stock' => $row[9] ?? null,
                        ]
                    ]);

                    // Création de l'article
                    try {
                        Article::create([
                            'code_article' => $row[0],
                            'designation' => $row[1],
                            'description' => $row[3] ?? null,
                            'famille_id' => $famille->id,
                            'stock_minimum' => $stockMin,
                            'stock_maximum' => $stockMax,
                            'stock_securite' => $stockSecurite,
                            'stock_actuel' => 0,
                            'code_barre' => $row[7] ?? null,
                            'stockable' => 1,
                            'emplacement_stock' => $row[9] ?? null,
                            'statut' => Article::STATUT_ACTIF,
                            'unite_mesure_id' => $uniteMesure->id,
                        ]);
                    } catch (\Exception $e) {
                        throw new \Exception("Erreur lors de la création de l'article : " . $e->getMessage());
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Ligne $rowNumber : " . $e->getMessage();
                    \Log::error("Erreur d'import à la ligne $rowNumber", [
                        'erreur' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'donnees' => $row
                    ]);
                    $skipped++;
                    continue;
                }
            }

            if ($imported > 0) {
                DB::commit();

                $message = "$imported article(s) importé(s) avec succès.";
                if ($skipped > 0) {
                    $message .= " $skipped ligne(s) ignorée(s).";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors' => $errors,
                    'imported' => $imported,
                    'skipped' => $skipped
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun article n\'a été importé.',
                    'errors' => $errors,
                    'imported' => $imported,
                    'skipped' => $skipped
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur générale lors de l'import", [
                'erreur' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'import',
                'error_details' => $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }
}
