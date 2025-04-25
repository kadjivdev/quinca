<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\{Client, ReglementClient};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Border, Alignment};


class ClientController extends Controller
{
    /**
     * Affiche la liste des clients
     */

    public function index(Request $request)
    {
        if (request()->ajax()) {
            return response()->json(Client::all());
        }

        // 
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec pagination
        $clients = Client::with([
            'facturesClient',
            'departement',
            'agent',
            'facturesClient.reglements' // Chargement des règlements via les factures
        ])->where('point_de_vente_id', Auth()->user()->point_de_vente_id)->latest();

        // Application des filtres si présents dans la requête
        if ($request->filled('categorie')) {
            $clients->where('categorie', $request->categorie);
        }

        if ($request->filled('ville')) {
            $clients->where('ville', $request->ville);
        }

        if ($request->filled('statut')) {
            $clients->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $clients->search($request->search);
        }

        if ($request->has('avec_credit')) {
            $clients->avecCredit();
        }

        // $clients = $clients->paginate(10);
        $clients = $clients->get();

        // Statistiques pour le header
        $stats = [
            'total_clients' => Client::count(),
            'clients_actifs' => Client::where('statut', true)->count(),
            'clients_professionnels' => Client::where('categorie', 'professionnel')->count(),
            'clients_avec_credit' => Client::where('plafond_credit', '>', 0)->count(),
            'total_reglements' => DB::table('facture_clients')
                ->join('reglement_clients', 'facture_clients.id', '=', 'reglement_clients.facture_client_id')
                ->where('reglement_clients.statut', ReglementClient::STATUT_VALIDE)
                ->sum('reglement_clients.montant')
        ];


        // Liste des villes pour le filtre
        $villes = Client::distinct()->pluck('ville')->filter();

        return view('pages.ventes.client.index', compact(
            'clients',
            'stats',
            'villes',
            'date'
        ));
    }

    // clients pour les revendeurs
    public function clientRevendeur(Request $request)
    {
        // 
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec pagination
        $clients = Client::with([
            'facturesClient',
            'departement',
            'agent',
            'facturesClient.reglements' // Chargement des règlements via les factures
        ])->where('created_by', Auth()->user()->id)->latest();

        // Application des filtres si présents dans la requête
        if ($request->filled('categorie')) {
            $clients->where('categorie', $request->categorie);
        }

        if ($request->filled('ville')) {
            $clients->where('ville', $request->ville);
        }

        if ($request->filled('statut')) {
            $clients->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $clients->search($request->search);
        }

        if ($request->has('avec_credit')) {
            $clients->avecCredit();
        }

        // $clients = $clients->paginate(10);
        $clients = $clients->get();

        // Statistiques pour le header
        $stats = [
            'total_clients' => $clients->count(),
            'clients_actifs' => $clients->where('statut', true)->count(),
            'clients_professionnels' => $clients->where('categorie', 'professionnel')->count(),
            'clients_avec_credit' => $clients->where('plafond_credit', '>', 0)->count(),
            'total_reglements' => DB::table('facture_clients')
                ->join('reglement_clients', 'facture_clients.id', '=', 'reglement_clients.facture_client_id')
                ->where('reglement_clients.statut', ReglementClient::STATUT_VALIDE)
                ->sum('reglement_clients.montant')
        ];

        // Liste des villes pour le filtre
        $villes = Client::distinct()->pluck('ville')->filter();

        return view('pages.ventes.client.index', compact(
            'clients',
            'stats',
            'villes',
            'date'
        ));
    }

    /**
     * Rafraîchit la liste des clients (pour AJAX)
     */
    public function refreshList(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $clients = Client::with([
            'facturesClient',
            'reglements'
        ])->latest();

        // Appliquer les filtres
        if ($request->filled('categorie')) {
            $clients->where('categorie', $request->categorie);
        }

        if ($request->filled('ville')) {
            $clients->where('ville', $request->ville);
        }

        if ($request->filled('statut')) {
            $clients->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $clients->search($request->search);
        }

        $clients = $clients->paginate(10);

        // Récupérer la liste des villes
        $villes = Client::distinct()->pluck('ville')->filter();

        return response()->json([
            'html' => view('pages.ventes.client.partials.list', compact('clients', 'villes'))->render(),
            'stats' => [
                'total' => Client::count(),
                'actifs' => Client::where('statut', true)->count(),
                'professionnels' => Client::where('categorie', 'professionnel')->count(),
                'avec_credit' => Client::where('plafond_credit', '>', 0)->count()
            ]
        ]);
    }

    /**
     * Enregistre un nouveau client
     */


    public function store(Request $request)
    {
        try {
            // Conversion explicite du statut en booléen
            $data = $request->all();
            $data['statut'] = filter_var($request->statut, FILTER_VALIDATE_BOOLEAN);

            // Validation des données avec messages personnalisés
            $validator = Validator::make($data, Client::rules(), [
                'raison_sociale.required' => 'La raison sociale est obligatoire',
                'telephone.required' => 'Le numéro de téléphone est obligatoire',
                'categorie.required' => 'La catégorie du client est obligatoire',
                'categorie.in' => 'La catégorie sélectionnée n\'est pas valide',
                'email.email' => 'L\'adresse email n\'est pas valide',
                'plafond_credit.required' => 'Le plafond de crédit est obligatoire',
                'plafond_credit.numeric' => 'Le plafond de crédit doit être un nombre',
                'plafond_credit.min' => 'Le plafond de crédit doit être positif ou nul',
                'delai_paiement.required' => 'Le délai de paiement est obligatoire',
                'delai_paiement.integer' => 'Le délai de paiement doit être un nombre entier',
                'delai_paiement.min' => 'Le délai de paiement doit être positif ou nul',
                'solde_initial.required' => 'Le solde initial est obligatoire',
                'solde_initial.numeric' => 'Le solde initial doit être un nombre',
                'solde_initial.min' => 'Le solde initial doit être positif ou nul',
                'statut.required' => 'Le statut du client doit être défini',
                'statut.boolean' => 'Le statut doit être actif ou inactif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors(),
                    'type' => 'warning'
                ], 422);
            }

            DB::beginTransaction();

            $client = new Client();
            $client->fill($validator->validated());
            $client->created_by = auth()->id();
            $client->point_de_vente_id = auth()->user()->point_de_vente_id;
            $client->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Client créé avec succès',
                'data' => [
                    'client' => $client->load([
                        'facturesClient',
                        'reglements',
                        'createdBy'
                    ])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du client:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du client',
                'type' => 'error'
            ], 500);
        }
    }
    /**
     * Affiche les détails d'un client
     */
    public function show(Request $request, Client $client)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        // Charger les relations nécessaires
        $client->load([
            'facturesClient.lignes.article',
            'reglements',
            'createdBy'
        ]);

        // Préparer les données pour la réponse
        $data = [
            'client' => [
                'id' => $client->id,
                'code_client' => $client->code_client,
                'raison_sociale' => $client->raison_sociale,
                'categorie' => $client->categorie,
                'ifu' => $client->ifu,
                'rccm' => $client->rccm,
                'telephone' => $client->telephone,
                'email' => $client->email,
                'adresse' => $client->adresse,
                'ville' => $client->ville,
                'statut' => $client->statut,
                'taux_aib' => $client->taux_aib,
                'created_at' => $client->created_at->format('d/m/Y'),
                'credit' => [
                    'plafond' => $client->plafond_credit,
                    'delai_paiement' => $client->delai_paiement,
                    'solde_initial' => $client->solde_initial,
                    'solde_courant' => $client->solde_courant,
                    'depassement' => $client->depassement_credit
                ],
                'notes' => $client->notes,
                'created_by' => $client->createdBy ? $client->createdBy->name : null
            ],
            'statistiques' => [
                'total_factures' => $client->facturesClient->count(),
                'factures_impayees' => $client->facturesClient->where('statut_paiement', 'impaye')->count(),
                'chiffre_affaires' => $client->facturesClient->sum('montant_ttc'),
                'total_reglements' => $client->reglements->sum('montant')
            ],
            'dernieres_factures' => $client->facturesClient()
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($facture) {
                    return [
                        'numero' => $facture->numero,
                        'date' => $facture->date_facture->format('d/m/Y'),
                        'montant' => $facture->montant_ttc,
                        'statut_paiement' => $facture->statut_paiement
                    ];
                }),
            'derniers_reglements' => $client->reglements()
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($reglement) {
                    return [
                        'numero' => $reglement->numero,
                        'date' => $reglement->date_reglement->format('d/m/Y'),
                        'montant' => $reglement->montant,
                        'mode_reglement' => $reglement->mode_reglement
                    ];
                })
        ];

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Met à jour les informations d'un client
     */
    public function update(Request $request, Client $client)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            // Validation des données
            $validated = $request->validate(Client::rules($client->id));

            DB::beginTransaction();

            // Mettre à jour les informations de base
            $client->fill($validated);
            $client->taux_aib = $request->taux_aibMob;

            // Si le plafond de crédit a changé, vérifier le dépassement
            if ($client->isDirty('plafond_credit') && $client->solde_courant > $validated['plafond_credit']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le nouveau plafond de crédit est inférieur au solde courant du client',
                    'type' => 'warning'
                ], 422);
            }

            $client->save();

            DB::commit();

            // Journal des modifications importantes
            if ($client->wasChanged(['plafond_credit', 'delai_paiement', 'statut'])) {
                Log::info('Modification importante du client:', [
                    'client_id' => $client->id,
                    'modifications' => $client->getChanges(),
                    'user_id' => auth()->id()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Client modifié avec succès',
                'data' => [
                    'client' => $client->fresh([
                        'facturesClient',
                        'reglements',
                        'createdBy'
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
            Log::error('Erreur lors de la modification du client:', [
                'client_id' => $client->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la modification du client',
                'type' => 'error'
            ], 500);
        }
    }

    /**
     * Supprime un client
     */
    public function destroy(Request $request, Client $client)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Vérifier si le client peut être supprimé
            if ($client->facturesClient()->count() > 0) {
                throw new \Exception('Impossible de supprimer ce client car il a des factures associées');
            }

            if ($client->reglements()->count() > 0) {
                throw new \Exception('Impossible de supprimer ce client car il a des règlements associés');
            }

            // Vérifier le solde
            if ($client->solde_courant != 0) {
                throw new \Exception('Impossible de supprimer ce client car son solde n\'est pas nul');
            }

            // Supprimer le client
            $client->delete();

            DB::commit();

            Log::info('Client supprimé:', [
                'client_id' => $client->id,
                'code_client' => $client->code_client,
                'raison_sociale' => $client->raison_sociale,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du client:', [
                'client_id' => $client->id,
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
     * Télécharger le template d'import
     */
    // public function downloadTemplate()
    // {
    //     $filePath = storage_path('app/templates/import_clients_template.xlsx');
    //     return response()->download($filePath, 'modele_import_clients.xlsx');
    // }

    /**
     * Importer des clients depuis un fichier Excel
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
            $rows = $worksheet->toArray();

            // Supprimer l'en-tête
            array_shift($rows);

            $errors = [];
            $imported = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 car on a supprimé l'en-tête et les indices commencent à 0

                // Ignorer les lignes vides
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Validation des données de base
                    if (empty($row[0])) { // Raison sociale
                        $errors[] = "Ligne $rowNumber : La raison sociale est requise";
                        $skipped++;
                        continue;
                    }

                    if (empty($row[1])) { // Catégorie
                        $errors[] = "Ligne $rowNumber : La catégorie est requise";
                        $skipped++;
                        continue;
                    }

                    if (!in_array(strtolower($row[1]), ['particulier', 'professionnel', 'societe'])) {
                        $errors[] = "Ligne $rowNumber : Catégorie invalide. Valeurs acceptées : particulier, professionnel, societe";
                        $skipped++;
                        continue;
                    }

                    // Générer un code client unique
                    do {
                        $code = 'CLI' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
                    } while (Client::where('code_client', $code)->exists());

                    // Création du client
                    $client = new Client();
                    $client->code_client = $code;  // Assignation du code unique
                    $client->raison_sociale = $row[0];
                    $client->categorie = strtolower($row[1]);
                    $client->ifu = $row[2] ?? null;
                    $client->rccm = $row[3] ?? null;
                    $client->telephone = $row[4] ?? null;
                    $client->email = $row[5] ?? null;
                    $client->adresse = $row[6] ?? null;
                    $client->ville = $row[7] ?? null;
                    $client->plafond_credit = $row[8] ?? 0;
                    $client->delai_paiement = $row[9] ?? 0;
                    $client->solde_initial = $row[10] ?? 0;
                    $client->taux_aib = $row[11] ?? 0;
                    $client->notes = $row[12] ?? null;
                    $client->statut = true;
                    $client->created_by = auth()->id();

                    // Vérifier si un client avec le même IFU existe déjà
                    if (!empty($client->ifu) && Client::where('ifu', $client->ifu)->exists()) {
                        $errors[] = "Ligne $rowNumber : Un client avec cet IFU existe déjà";
                        $skipped++;
                        continue;
                    }

                    // Vérifier si un client avec le même téléphone existe déjà
                    if (!empty($client->telephone) && Client::where('telephone', $client->telephone)->exists()) {
                        $errors[] = "Ligne $rowNumber : Un client avec ce numéro de téléphone existe déjà";
                        $skipped++;
                        continue;
                    }

                    $client->save();
                    $imported++;

                    // Log de l'importation
                    Log::info('Client importé avec succès:', [
                        'code_client' => $client->code_client,
                        'raison_sociale' => $client->raison_sociale,
                        'imported_by' => auth()->id()
                    ]);
                } catch (\Exception $e) {
                    $errors[] = "Ligne $rowNumber : " . $e->getMessage();
                    $skipped++;
                    continue;
                }
            }

            if ($imported > 0) {
                DB::commit();

                $message = "$imported client(s) importé(s) avec succès.";
                if ($skipped > 0) {
                    $message .= "<br>$skipped ligne(s) ignorée(s).";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors' => $errors
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun client n\'a été importé.',
                    'errors' => $errors
                ]);
            }
        } catch (ReaderException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier Excel est invalide ou corrompu'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'import: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Générer et télécharger le template d'import
     */
    //     public function downloadTemplate()
    // {
    //     // Chemin vers le fichier template
    //     $filePath = public_path('templates/modele_import_clients.xlsx');

    //     // Si le fichier n'existe pas, on utilise storage_path
    //     if (!file_exists($filePath)) {
    //         $filePath = storage_path('app/templates/modele_import_clients.xlsx');
    //     }

    //     // Vérifier si le fichier existe
    //     if (!file_exists($filePath)) {
    //         return response()->json([
    //             'message' => 'Le fichier template n\'existe pas'
    //         ], 404);
    //     }

    //     return response()->download($filePath, 'modele_import_clients.xlsx', [
    //         'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    //     ]);
    // }


    public function downloadTemplate()
    {
        try {
            // S'assurer que le dossier templates existe
            Storage::makeDirectory('templates');

            $filePath = storage_path('app/templates/modele_import_clients.xlsx');

            // Créer un nouveau spreadsheet à chaque fois pour éviter les problèmes de cache
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Import Clients');

            // Définir les en-têtes
            $headers = [
                'Raison Sociale*',
                'Catégorie* (particulier/professionnel/societe)',
                'IFU',
                'RCCM',
                'Téléphone*',
                'Email',
                'Adresse',
                'Ville',
                'Plafond Crédit',
                'Délai Paiement (jours)',
                'Solde Initial',
                'Taux AIB',
                'Notes'
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
                'ENTREPRISE EXAMPLE',
                'professionnel',
                'IFU123456',
                'RCCM123456',
                '22670000000',
                'contact@example.com',
                'Rue 123',
                'Ouagadougou',
                '1000000',
                '30',
                '0',
                '1',
                'Client régulier'
            ];

            // Ajouter l'exemple
            foreach ($example as $index => $value) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . '2', $value);
            }

            // Style pour l'exemple
            $sheet->getStyle('A2:M2')->getFont()->setItalic(true);
            $sheet->getStyle('A2:M2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('808080'));

            // Protection de la feuille
            $sheet->getProtection()->setSheet(true);
            $sheet->getProtection()->setSort(false);
            $sheet->getProtection()->setInsertRows(false);

            // Sauvegarder dans un fichier temporaire
            $tempFile = tempnam(sys_get_temp_dir(), 'template_');
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            return response()->download($tempFile, 'modele_import_clients.xlsx', [
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
}
