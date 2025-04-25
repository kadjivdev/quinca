<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Achat\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Border, Alignment};

class FournisseurController extends Controller
{
    public function index()
    {
        // Récupération des fournisseurs avec tri par date de création décroissante
        $fournisseurs = Fournisseur::with("approvisionnements")->orderBy('created_at', 'desc')->get();
        $fournisseurs->map(function ($fournisseur) {
            $fournisseur->totalAppro = $fournisseur->approvisionnements()->sum("montant");
            $fournisseur->reste_solde = $fournisseur->reste_solde();
            $fournisseur->factureAchatAmount = $fournisseur->facture_fournisseurs->sum(function ($query) {
                return $query->facture_amont();
            });
            $fournisseur->reglementsAmount = $fournisseur->facture_fournisseurs->sum(function ($query) {
                return $query->facture_reglements_amount();
            });
            $fournisseur->articles = collect( $fournisseur->facture_fournisseurs->map(function ($factureFournisseur) {
                foreach ($factureFournisseur->lignes as $ligne) {
                    return $ligne->article;
                }
            }));
        });

        // dd($fournisseurs->where('id',1));
        // Statistiques globales
        $stats = [
            'total_fournisseurs' => $fournisseurs->count(),
            'fournisseurs_actifs' => $fournisseurs->where('statut', true)->count(),
        ];

        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        return view('pages.achat.fournisseur.index', compact(
            'fournisseurs',
            'stats',
            'date'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code_fournisseur' => [
                    'required',
                    'string',
                    'size:6',
                    'regex:/^[A-Z0-9]+$/',
                    'unique:fournisseurs,code_fournisseur'
                ],
                'nom' => 'required|string|min:3|max:100|unique:fournisseurs,raison_sociale',
                'adresse' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'actif' => 'boolean'
            ]);

            DB::beginTransaction();

            $fournisseur = Fournisseur::create([
                'code_fournisseur' => $request->code_fournisseur,
                'raison_sociale' => $request->nom,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'actif' => $request->input('actif') === '1',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fournisseur créé avec succès',
                'data' => $fournisseur
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
                'message' => 'Une erreur est survenue lors de la création du fournisseur' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $fournisseur = Fournisseur::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $fournisseur
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fournisseur non trouvé'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $fournisseur = Fournisseur::findOrFail($id);

            $validated = $request->validate([
                'nom' => [
                    'required',
                    'string',
                    'min:3',
                    'max:100',
                    Rule::unique('fournisseurs', 'raison_sociale')->ignore($id)
                ],
                'adresse' => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100'
            ]);

            DB::beginTransaction();

            $fournisseur->raison_sociale = $request->nom;
            $fournisseur->adresse = $request->adresse;
            $fournisseur->telephone = $request->telephone;
            $fournisseur->email = $request->email;
            $fournisseur->statut =  $request->input('actif') === '1';

            $fournisseur->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fournisseur mis à jour avec succès',
                'data' => $fournisseur
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
                'message' => 'Une erreur est survenue lors de la mise à jour ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $fournisseur = Fournisseur::findOrFail($id);

            // Vérification des relations avant suppression
            // À adapter selon vos besoins
            // if ($fournisseur->commandes->count() > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Impossible de supprimer un fournisseur qui a des commandes associées'
            //     ], 422);
            // }

            $fournisseur->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fournisseur supprimé avec succès'
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
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Import Fournisseurs');

            // Définir les en-têtes
            $headers = [
                'Code*',
                'Nom*',
                'Adresse',
                'Téléphone',
                'Email'
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
                'FOUR01',
                'FOURNISSEUR EXEMPLE',
                '123 Rue Example',
                '0123456789',
                'contact@exemple.com'
            ];

            // Ajouter l'exemple
            foreach ($example as $index => $value) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . '2', $value);
            }

            // Style pour l'exemple
            $sheet->getStyle('A2:E2')->getFont()->setItalic(true);
            $sheet->getStyle('A2:E2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('808080'));

            // Ajouter une feuille d'instructions
            $instructionSheet = $spreadsheet->createSheet();
            $instructionSheet->setTitle('Instructions');

            $instructions = [
                ['Instructions d\'import des fournisseurs'],
                [''],
                ['1. Format des données :'],
                ['   - Code* : Obligatoire, 6 caractères, lettres majuscules et chiffres uniquement'],
                ['   - Nom* : Obligatoire, 3 à 100 caractères'],
                ['   - Adresse : Optionnelle'],
                ['   - Téléphone : Optionnel'],
                ['   - Email : Optionnel, format email valide'],
                [''],
                ['2. Notes importantes :'],
                ['   - Les champs marqués d\'un * sont obligatoires'],
                ['   - Le code et le nom doivent être uniques'],
                [''],
                ['3. Exemple :'],
                ['   Code : FOUR01'],
                ['   Nom : FOURNISSEUR EXEMPLE'],
                ['   Adresse : 123 Rue Example'],
                ['   Téléphone : 0123456789'],
                ['   Email : contact@exemple.com']
            ];

            foreach ($instructions as $index => $row) {
                $instructionSheet->setCellValue('A' . ($index + 1), $row[0]);
            }

            $instructionSheet->getStyle('A1')->getFont()->setBold(true);
            $instructionSheet->getColumnDimension('A')->setWidth(60);

            $spreadsheet->setActiveSheetIndex(0);

            // Créer le fichier
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'template_');
            $writer->save($tempFile);

            return response()->download($tempFile, 'modele_import_fournisseurs.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la génération du template: ' . $e->getMessage()
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

                try {
                    // Ignorer les lignes vides
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Validation des données obligatoires
                    if (empty($row[0]) || empty($row[1])) {
                        $errors[] = "Ligne $rowNumber : Le code et le nom sont obligatoires";
                        $skipped++;
                        continue;
                    }

                    $code = strtoupper(trim($row[0]));
                    $nom = trim($row[1]);
                    $adresse = !empty($row[2]) ? trim($row[2]) : null;
                    $telephone = !empty($row[3]) ? trim($row[3]) : null;
                    $email = !empty($row[4]) ? trim($row[4]) : null;

                    // Vérifications d'unicité
                    if (Fournisseur::where('code', $code)->exists()) {
                        $errors[] = "Ligne $rowNumber : Le code '$code' existe déjà";
                        $skipped++;
                        continue;
                    }

                    if (Fournisseur::where('nom', $nom)->exists()) {
                        $errors[] = "Ligne $rowNumber : Le nom '$nom' existe déjà";
                        $skipped++;
                        continue;
                    }

                    // Création du fournisseur
                    Fournisseur::create([
                        'code' => $code,
                        'nom' => $nom,
                        'adresse' => $adresse,
                        'telephone' => $telephone,
                        'email' => $email
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Ligne $rowNumber : Une erreur est survenue lors de l'import de cette ligne";
                    $skipped++;
                    continue;
                }
            }

            if ($imported > 0) {
                DB::commit();

                $message = "$imported fournisseur(s) importé(s) avec succès.";
                if ($skipped > 0) {
                    $message .= " $skipped ligne(s) ignorée(s).";
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
                'message' => 'Aucun fournisseur n\'a été importé.',
                'errors' => $errors
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier fourni est invalide.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'import.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
