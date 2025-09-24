<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\Tarification;
use App\Models\Catalogue\Article;
use App\Models\Catalogue\FamilleArticle;
use App\Models\Parametre\Depot;
use App\Models\Parametre\TypeTarif;
use App\Models\Parametre\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Border, Alignment};

class TarificationController extends Controller
{
    /**
     * Afficher la liste des tarifications
     */
    public function index()
    {
        try {

            $tarifications = Tarification::with(['article', 'typeTarif'])->get();
            $articles = Article::where('statut', 'actif')->get();
            $typesTarifs = TypeTarif::where('statut', true)->get();
            $familles = FamilleArticle::where('statut', true)->get();
            $depots = Depot::get();
            $uniteMesures = UniteMesure::get();

            // Statistiques
            $stats = [
                'total' => $tarifications->count(),
                'actifs' => $tarifications->where('statut', true)->count(),
                'inactifs' => $tarifications->where('statut', false)->count(),
                'articlesTarifes' => $tarifications->pluck('article_id')->unique()->count()
            ];

            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

            return view('pages.catalogues.tarification.index', compact(
                'tarifications',
                'articles',
                'depots',
                'typesTarifs',
                'familles',
                'stats',
                'date',
                "uniteMesures",
            ));
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des tarifications:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors du chargement des données.');
        }
    }

    /**
     * Créer une nouvelle tarification
     */
    public function store(Request $request)
    {
        Log::info('Données reçues:', $request->all());

        $validator = Validator::make($request->all(), [
            'article_id' => 'required|exists:articles,id',
            'depot_id' => 'required|exists:depots,id',
            'unite_mesure_id' => 'required|exists:unite_mesures,id',
            'type_tarif_id' => [
                'required',
                'exists:type_tarifs,id',
                function ($attribute, $value, $fail) use ($request) {
                    // Vérifier si une tarification existe déjà pour cet article et ce type
                    $exists = Tarification::where([
                        'article_id' => $request->article_id,
                        'type_tarif_id' => $value,
                        'unite_mesure_id'=>$request->unite_mesure_id,
                        ['id', '!=', $request->id ?? 0]
                    ])->exists();

                    if ($exists) {
                        $fail('Une tarification existe déjà pour cet article avec ce type de tarif & cette unité de mesure.');
                    }
                }
            ],
            'prix' => 'required|numeric|min:0',
            'statut' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['statut'] = $request->boolean('statut');

            $tarification = Tarification::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarification créée avec succès',
                'data' => $tarification
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création tarification:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la tarification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Importation des tarifications
     */
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
            // $rows = $worksheet->toArray();
            $rows = $worksheet->toArray(null, true, true, true); //ceci permet de ne pas considerer le header

            // Supprimer l'en-tête
            array_shift($rows);

            $errors = [];
            $imported = 0;
            $skipped = 0;

            DB::beginTransaction();

            Log::info("Début traitements", ["data" => $request->all()]);

            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    continue;
                }
                // Log::info("Index", ["data" => $index]);
                Log::info("Row", ["data" => $row]);
                $rowNumber = $index + 2;

                // Ignorer les lignes vides
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Validation des données de base
                    if (empty($row["A"])) {
                        $errors[] = "Ligne $rowNumber : Le code article est réquis";
                        $skipped++;
                        continue;
                    }

                    /**
                     * 0=A
                     * 1=B
                     * 2=C
                     * 3=D
                     * 4=E
                     * 5=F
                     * 6=G
                     * 7=H
                     * 8=I
                     */

                    // Validation des tarifs
                    if (empty($row["C"]) && empty($row["D"]) && empty($row["E"]) && empty($row["F"]) && empty($row["G"]) && empty($row["H"])) {
                        $errors[] = "Ligne $rowNumber : Précisez au moins un prix de tarification";
                        $skipped++;
                        continue;
                    }

                    // Vérifier l'existence de l'article
                    $article = Article::firstWhere('code_article', $row["A"]);
                    if (!$article) {
                        $errors[] = "Ligne $rowNumber : L'article avec le code {{$row['A']}} n'existe pas";
                        $skipped++;
                        continue;
                    }

                    // Vérifier l'existence de l'unité de mesure
                    $uniteMesure = Article::firstWhere('code_article', $row["I"]);
                    if (!$uniteMesure) {
                        $errors[] = "Ligne $rowNumber : Precisez l'unité de mesure de l'article ayant le code {{$row['A']}}";
                        $skipped++;
                        continue;
                    }

                    $type_tarif_id = null;

                    if ($row['C']) {
                        $type_tarif_id = 1; //tarif special
                    } elseif ($row['D']) {
                        $type_tarif_id = 2; //tarif Hyper Grossiste
                    } elseif ($row['E']) {
                        $type_tarif_id = 3; //tarif Grossiste
                    } elseif ($row['F']) {
                        $type_tarif_id = 4; //tarif Semi Grossiste
                    } elseif ($row['G']) {
                        $type_tarif_id = 5; //tarif Particulier
                    } elseif ($row['H']) {
                        $type_tarif_id = 7; //tarif BTP
                    };

                    $type = TypeTarif::find($type_tarif_id);

                    if (!$type) {
                        $errors[] = "Ligne $rowNumber : Ce type de tarification n'existe pas!";
                        $skipped++;
                        continue;
                    }

                    // Vérifier l'existence de la tarification
                    $tarification = Tarification::firstWhere(['article_id' => $article->id, "type_tarif_id" => $type_tarif_id]);
                    if ($tarification) {
                        $errors[] = "Ligne $rowNumber : Un prix existe déjà pour la tarification {{$tarification->typeTarif?->libelle_type_tarif}} : $tarification->prix";
                        $skipped++;
                        continue;
                    }

                    // Log pour debug
                    Log::info("Tentative de création de tarification", [
                        'ligne' => $rowNumber,
                        'donnees' => [
                            'code_article' => $row['A'],
                            'type_tarification' => $type->libelle_type_tarif,
                            'prix_special' => $row['C'],
                            'prix_hyper_grossiste' => $row['D'],
                            'prix_grossiste' => $row['E'],
                            'prix_semi_grossiste' => $row['F'],
                            'prix_particulier' => $row['G'],
                            'prix_btp' => $row['H'],
                        ]
                    ]);

                    // Création de l'article
                    try {

                        foreach (TypeTarif::get(["id",]) as $type) {
                            $data = [
                                'article_id' => $article->id,
                                'type_tarif_id' => $type->id,
                                'statut' => 1,
                                'prix' => 0,
                            ];

                            Log::info("Data debut", ["data" => $data]);

                            switch ($type->id) {
                                case 1: //prix special
                                    $data["prix"] = $row['C'] ?? 0;
                                    break;

                                case 2: //prix hyper grossiste
                                    $data["prix"] = $row['D'] ?? 0;
                                    break;

                                case 3: //prix grossiste
                                    $data["prix"] = $row['E'] ?? 0;
                                    break;

                                case 4: //prix semi grossiste
                                    $data["prix"] = $row['F'] ?? 0;
                                    break;

                                case 5: //prix particulier
                                    $data["prix"] = $row['G'] ?? 0;
                                    break;

                                case 6: //prix BTP
                                    $data["prix"] = $row['H'] ?? 0;
                                    break;

                                default:
                                    break;
                            };

                            /** */
                            Log::info("Data fin", ["data" => $data]);
                            Tarification::create($data);
                        }
                    } catch (\Exception $e) {
                        throw new \Exception("Erreur lors de la création de l'article : " . $e->getMessage());
                    }

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Ligne $rowNumber : " . $e->getMessage();
                    Log::error("Erreur d'import à la ligne $rowNumber", [
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

                $message = "$imported tarification(s) importé(s) avec succès.";
                if ($skipped > 0) {
                    $message .= " $skipped ligne(s) ignorée(s).";
                }

                return back()
                    ->with("success", $message);

                // return response()->json([
                //     'success' => true,
                //     'message' => $message,
                //     'errors' => $errors,
                // ]);
            } else {
                DB::rollBack();

                return back()
                    ->withErrors(["errors" => $errors]);

                // return response()->json([
                //     'success' => false,
                //     'message' => 'Aucun article n\'a été importé.',
                //     'errors' => $errors,
                // ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur générale lors de l'import", [
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

    /**
     * Charger les données d'une tarification pour modification
     */
    public function edit($id)
    {
        try {
            $tarification = Tarification::with(['article', 'typeTarif', 'depotTarif'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    "tarification" => $tarification,
                    'unites' => $tarification->article?->getUnites(), //ulites de l'article
                    'depots' => $tarification->article?->depots, //depots de l'article
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la tarification ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mettre à jour une tarification
     */
    public function update(Request $request, $id)
    {
        $tarification = Tarification::find($id);

        if (!$tarification) {
            return response()->json([
                'success' => false,
                'message' => 'Tarification non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'prix' => 'required|numeric|min:0',
            'statut' => 'boolean',
            'depot_id' => 'required|integer',
            'unite_mesure_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $request->only(['prix', 'statut', 'depot_id', 'unite_mesure_id']);
            $data['statut'] = $request->boolean('statut');

            $tarification->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarification mise à jour avec succès',
                'data' => $tarification
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la tarification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAll(Request $request, $articleId)
    {
        try {
            DB::beginTransaction();

            foreach ($request->prix as $tarificationId => $nouveauPrix) {
                $tarification = Tarification::findOrFail($tarificationId);
                $tarification->update(['prix' => $nouveauPrix]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarifs mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour des tarifs:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des tarifs'
            ], 500);
        }
    }

    /**
     * Supprimer une tarification
     */
    public function destroy($id)
    {
        try {
            $tarification = Tarification::findOrFail($id);
            $tarification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tarification supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la tarification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une tarification
     */
    public function toggleStatus($id)
    {
        try {
            $tarification = Tarification::findOrFail($id);
            $tarification->statut = !$tarification->statut;
            $tarification->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de la tarification modifié avec succès',
                'data' => $tarification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les tarifications d'un article
     */
    /**
     * Récupérer les tarifications d'un article
     */
    public function getByArticle($articleId)
    {
        try {
            $article = Article::with(['tarifications' => function ($query) {
                $query->with('typeTarif'); // Charger la relation typeTarif
            }])->findOrFail($articleId);

            return response()->json([
                'success' => true,
                'article' => [
                    'id' => $article->id,
                    'code_article' => $article->code_article,
                    'libelle_article' => $article->libelle_article
                ],
                'data' => $article->tarifications->map(function ($tarif) {
                    return [
                        'id' => $tarif->id,
                        'prix' => $tarif->prix,
                        'statut' => $tarif->statut,
                        'type_tarif' => [
                            'id' => $tarif->typeTarif->id,
                            'libelle_type_tarif' => $tarif->typeTarif->libelle_type_tarif
                        ],
                        'depot_tarif' => [
                            'id' => $tarif->depotTarif?->id,
                            'libelle_depot_tarif' => $tarif->depotTarif?->libelle_depot
                        ]
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des tarifications:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                // 'message' => 'Erreur lors du chargement des tarifications',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    /**
     * Récupérer le prix d'un article pour un type de tarif
     */
    public function getPrix($articleId, $typeTarifId)
    {
        try {
            $tarification = Tarification::where([
                'article_id' => $articleId,
                'type_tarif_id' => $typeTarifId,
                'statut' => true
            ])->first();

            return response()->json([
                'success' => true,
                'data' => $tarification ? $tarification->prix : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du prix'
            ], 500);
        }
    }
}
