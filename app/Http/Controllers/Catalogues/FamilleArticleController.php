<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\FamilleArticle;
use App\Models\Parametre\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Border, Alignment};

class FamilleArticleController extends Controller
{

    public function index()
    {
        // Récupération des familles d'articles avec leurs relations
        $familleArticles = FamilleArticle::orderBy('created_at', 'desc')  // Les plus récentes en premier
            ->get();

        $uniteMesures = UniteMesure::orderBy('created_at', 'desc')->get();


        // Liste des familles pour le select du formulaire (exclure les familles désactivées)
        $familleParents = FamilleArticle::where('statut', true)
            ->orderBy('libelle_famille')
            ->get(['id', 'code_famille', 'libelle_famille']);

        // Statistiques globales
        $stats = [
            'total_familles' => $familleArticles->count(),
            'familles_actives' => $familleArticles->where('statut', true)->count(),
            // 'familles_avec_articles' => $familleArticles->filter(fn($f) => $f->articles->count() > 0)->count(),
            // 'total_articles' => $familleArticles->sum(fn($f) => $f->articles->count())
        ];

        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        return view('pages.catalogues.famille_article.index', compact(
            'familleArticles',
            'stats',
            'date',
        ));
    }
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code_famille' => [
                    'required',
                    'string',
                    'size:6',
                    'regex:/^[A-Z0-9]+$/',
                    'unique:famille_articles,code_famille'
                ],
                'libelle_famille' => 'required|string|min:3|max:100',
                'description' => 'nullable|string|max:255',
                'statut' => 'boolean'
            ]);

            DB::beginTransaction();

            $familleArticle = FamilleArticle::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Famille d\'articles créée avec succès',
                'data' => $familleArticle
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de la famille d\'articles'
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $familleArticle = FamilleArticle::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $familleArticle
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Famille d\'articles non trouvée'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $familleArticle = FamilleArticle::findOrFail($id);

            $validated = $request->validate([
                'code_famille' => [
                    'required',
                    'string',
                    'size:6',
                    'regex:/^[A-Z0-9]+$/',
                    Rule::unique('famille_articles')->ignore($id)
                ],
                'libelle_famille' => 'required|string|min:3|max:100',
                'description' => 'nullable|string|max:255',

                'statut' => 'boolean'
            ]);

            DB::beginTransaction();

            $familleArticle->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Famille d\'articles mise à jour avec succès',
                'data' => $familleArticle
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $familleArticle = FamilleArticle::findOrFail($id);

            // if ($familleArticle->articles->count() > 0 || $familleArticle->enfants->count() > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Impossible de supprimer une famille qui contient des articles ou des sous-familles'
            //     ], 422);
            // }

            $familleArticle->delete();

            return response()->json([
                'success' => true,
                'message' => 'Famille d\'articles supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        try {
            // Créer un nouveau spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Import Familles Articles');

            // Définir les en-têtes
            $headers = [
                'Libellé Famille*',
                'Description',
                'Méthode Valorisation*'
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
                'BOISSONS',
                'Famille des boissons',
                'PMP'
            ];

            // Ajouter l'exemple
            foreach ($example as $index => $value) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . '2', $value);
            }

            // Style pour l'exemple
            $sheet->getStyle('A2:C2')->getFont()->setItalic(true);
            $sheet->getStyle('A2:C2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('808080'));

            // Validation pour la Méthode de Valorisation (colonne C)
            $validation = $sheet->getCell('C2')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"FIFO,LIFO,PMP"');
            $validation->setErrorTitle('Erreur de saisie');
            $validation->setError('Veuillez sélectionner une méthode de valorisation valide');
            $validation->setPromptTitle('Méthode de valorisation');
            $validation->setPrompt('Choisissez FIFO, LIFO ou PMP');

            // Copier la validation pour les 100 premières lignes
            for ($i = 3; $i <= 100; $i++) {
                $validation = $sheet->getCell('C' . $i)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"FIFO,LIFO,PMP"');
            }

            // Ajouter une feuille d'instructions
            $instructionSheet = $spreadsheet->createSheet();
            $instructionSheet->setTitle('Instructions');

            $instructions = [
                ['Instructions d\'import des familles d\'articles'],
                [''],
                ['1. Format des données :'],
                ['   - Libellé Famille* : Obligatoire'],
                ['   - Description : Optionnelle'],
                ['   - Méthode Valorisation* : Choisir FIFO, LIFO ou PMP'],
                [''],
                ['2. Notes importantes :'],
                ['   - Le code famille sera généré automatiquement'],
                ['   - L\'unité de base sera automatiquement définie à 1'],
                ['   - Le statut sera actif par défaut'],
                [''],
                ['3. Exemple :'],
                ['   Libellé : BOISSONS'],
                ['   Description : Famille des boissons'],
                ['   Méthode : PMP']
            ];

            foreach ($instructions as $index => $row) {
                $instructionSheet->setCellValue('A' . ($index + 1), $row[0]);
            }

            $instructionSheet->getStyle('A1')->getFont()->setBold(true);
            $instructionSheet->getColumnDimension('A')->setWidth(60);

            $spreadsheet->setActiveSheetIndex(0);

            // Créer le fichier
            $tempFile = tempnam(sys_get_temp_dir(), 'template_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            return response()->download($tempFile, 'modele_import_familles.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du template:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la génération du template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            // Validation du fichier
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:5120' // 5MB max
            ]);

            $file = $request->file('file');

            // Lecture du fichier Excel
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

            // Récupérer l'unité de base par défaut
            try {
                $uniteBase = UniteMesure::where('statut', true)->firstOrFail();
            } catch (\Exception $e) {
                throw new \Exception("Aucune unité de base par défaut n'a été trouvée");
            }

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 car on a supprimé l'en-tête et l'index commence à 0

                try {
                    // Ignorer les lignes vides
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Validation des données obligatoires
                    if (empty($row[0]) || empty($row[1]) || empty($row[3])) {
                        $errors[] = "Ligne $rowNumber : Les champs Code, Libellé et Méthode de valorisation sont obligatoires";
                        $skipped++;
                        continue;
                    }

                    // Nettoyage et validation des données
                    $code = trim($row[0]);
                    $libelle = trim($row[1]);
                    $description = !empty($row[2]) ? trim($row[2]) : null;
                    $methodeValorisation = strtoupper(trim($row[3]));

                    // Vérification du code unique
                    if (FamilleArticle::where('code_famille', $code)->exists()) {
                        $errors[] = "Ligne $rowNumber : Le code famille '$code' existe déjà";
                        $skipped++;
                        continue;
                    }

                    // Validation de la méthode de valorisation
                    $methodesAutorisees = ['FIFO', 'LIFO', 'PMP'];
                    if (!in_array($methodeValorisation, $methodesAutorisees)) {
                        $errors[] = "Ligne $rowNumber : Méthode de valorisation '$methodeValorisation' invalide. Valeurs acceptées : " . implode(', ', $methodesAutorisees);
                        $skipped++;
                        continue;
                    }

                    // Création de la famille
                    FamilleArticle::create([
                        'code_famille' => $code,
                        'libelle_famille' => $libelle,
                        'description' => $description,
                        'methode_valorisation' => strtolower($methodeValorisation),
                        'unite_base_id' => $uniteBase->id,
                        'statut' => true
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    Log::error('Erreur lors de l\'import d\'une famille d\'articles', [
                        'ligne' => $rowNumber,
                        'donnees' => $row,
                        'erreur' => $e->getMessage()
                    ]);

                    $errors[] = "Ligne $rowNumber : Une erreur est survenue lors de l'import de cette ligne";
                    $skipped++;
                    continue;
                }
            }

            // Gestion de la transaction
            if ($imported > 0) {
                DB::commit();

                $message = trans_choice('{1} :count famille importée avec succès.|[2,*] :count familles importées avec succès.', $imported, ['count' => $imported]);
                if ($skipped > 0) {
                    $message .= ' ' . trans_choice('{1} :count ligne ignorée.|[2,*] :count lignes ignorées.', $skipped, ['count' => $skipped]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'details' => [
                        'imported' => $imported,
                        'skipped' => $skipped,
                        'total' => count($rows)
                    ],
                    'errors' => $errors
                ]);
            }

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Aucune famille n\'a été importée.',
                'errors' => $errors
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier fourni est invalide.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import des familles d\'articles', [
                'erreur' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'import.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $famille = FamilleArticle::findOrFail($id);

            $famille->statut = !$famille->statut;
            $famille->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de la famille modifié avec succès',
                'data' => $famille
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uniqueCode(Request $request)
    {
        // Valider que "code_famille" est présent
        $request->validate([
            'code_famille' => 'required|string|max:255',
        ]);

        // Récupérer le code depuis la requête
        $code = $request->input('code_famille');

        // Vérifier si le code existe dans la table "famille"
        $exists = FamilleArticle::where('code_famille', $code)->exists();

        // Retourner une réponse JSON
        return response()->json(['exists' => $exists]);
    }
}
