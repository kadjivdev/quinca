<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Revendeur\FactureRevendeur;
use Illuminate\Http\Request;

use App\Models\Vente\{ReglementRevendeur};
use App\Models\Vente\{SessionCaisse, Client};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class ReglementRevendeurController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des réglements avec pagination et relations
        $reglements = ReglementRevendeur::with([
            'facture.client',
            'facture.lignes.article',
            'createdBy',
            'validatedBy'
        ])->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $reglements->whereHas('facture', function ($query) use ($request) {
                $query->where('client_id', $request->client_id);
            });
        }

        if ($request->filled('facture_id')) {
            $reglements->where('facture_revendeur_id', $request->facture_id);
        }

        if ($request->filled('type_reglement')) {
            $reglements->where('type_reglement', $request->type_reglement);
        }

        if ($request->filled('statut')) {
            $reglements->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $reglements->whereDate('date_reglement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $reglements->whereDate('date_reglement', '<=', $request->date_fin);
        }

        if (
            auth()->user()->hasRole("Super Administrateur")
            || auth()->user()->hasRole("CONTROLE INTERNE")
            || auth()->user()->hasRole("CONTROLE EXTERNE ET CELLULE DE REQUETE")
        ) {
            $reglements = $reglements->get();
        } else {
            $reglements = $reglements
                ->whereHas("facture", function ($query) {
                    $query->where('point_de_vente_id', Auth()->user()->point_de_vente_id);
                })
                ->get();
        }

        // Données pour les filtres et le modal d'ajout
        $clients = Client::orderBy('raison_sociale')
            ->with(["facturesRevendeur" => function ($query) {
                /**on integre la somme des montant des reglements  dans les datas du facturesRevendeur*/
                $query->withSum('reglements', 'montant');
            }])
            ->get();

        // Statistiques pour le header
        $statsReglements = [
            // Total des règlements du mois
            'total_mois' => ReglementRevendeur::whereMonth('date_reglement', now()->month)
                ->whereYear('date_reglement', now()->year)
                ->where('statut', 'validee')
                ->sum('montant'),

            // Total des règlements
            'total_reglements' => ReglementRevendeur::where('statut', 'validee')
                ->sum('montant'),

            // Nombre de règlements en attente
            'reglements_en_attente' => ReglementRevendeur::where('statut', 'brouillon')->count(),

            // Montant des règlements en attente
            'montant_en_attente' => ReglementRevendeur::where('statut', 'brouillon')
                ->sum('montant'),

            // Répartition par mode de paiement
            'repartition_modes' => ReglementRevendeur::where('statut', 'valide')
                ->select('type_reglement', DB::raw('COUNT(*) as count'), DB::raw('SUM(montant) as total'))
                ->groupBy('type_reglement')
                ->get()
        ];

        // Récupérer les factures non soldées pour le modal d'ajout
        $factures = FactureRevendeur::with(["reglements"])
            ->where('statut', 'validee')
            ->whereRaw('IFNULL(montant_regle, 0) < montant_ttc')
            ->with('client')
            ->latest()
            ->get();

        // Types de règlement disponibles
        $typesReglement = ReglementRevendeur::getTypesReglement();

        return view('pages.ventes.reglement-revendeurs.index', compact(
            'reglements',
            'clients',
            'factures',
            'typesReglement',
            'statsReglements',
            'date'
        ));
    }

    public function refreshList(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $clients = Client::orderBy('raison_sociale')->get();

        $reglements = ReglementRevendeur::with([
            'facture.client',
            'createdBy',
            'validatedBy'
        ])->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $reglements->whereHas('facture', function ($query) use ($request) {
                $query->where('client_id', $request->client_id);
            });
        }

        if ($request->filled('facture_id')) {
            $reglements->where('facture_revendeur_id', $request->facture_id);
        }

        if ($request->filled('type_reglement')) {
            $reglements->where('type_reglement', $request->type_reglement);
        }

        if ($request->filled('statut')) {
            $reglements->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $reglements->whereDate('date_reglement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $reglements->whereDate('date_reglement', '<=', $request->date_fin);
        }

        $reglements = $reglements->paginate(10);

        // Mise à jour des statistiques
        $statsReglements = [
            'total_mois' => ReglementRevendeur::whereMonth('date_reglement', now()->month)
                ->whereYear('date_reglement', now()->year)
                ->where('statut', 'valide')
                ->sum('montant'),
            'total_reglements' => ReglementRevendeur::where('statut', 'validee')
                ->sum('montant'),
            'reglements_en_attente' => ReglementRevendeur::where('statut', 'brouillon')
                ->count(),
            'montant_en_attente' => ReglementRevendeur::where('statut', 'brouillon')
                ->sum('montant')
        ];

        return response()->json([
            'html' => view('pages.ventes.reglement-revendeurs.partials.list', compact('reglements', 'clients'))->render(),
            'stats' => $statsReglements
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            // Validation des données
            $validated = $request->validate(
                [
                    'facture_revendeur_id' => 'required|exists:facture_revendeurs,id',
                    'date_reglement' => 'required|date',
                    'type_reglement' => 'required|string',
                    'montant' => 'required|numeric|min:0',
                    'reference_preuve' => 'nullable|string|max:255|unique:reglement_revendeurs',
                    'banque' => 'nullable|string|max:255',
                    'date_echeance' => 'nullable|date|after_or_equal:date_reglement',
                    'notes' => 'nullable|string'
                ],
                [
                    "reference_preuve.unique" => "Cette reference existe déjà",
                    "facture_revendeur_id.facture_revendeurs" => "La facture doit être une facture revendeur"
                ]
            );

            DB::beginTransaction();

            // Charger la facture avec ses règlements
            $facture = FactureRevendeur::findOrFail($validated['facture_revendeur_id']);

            // Créer le règlement
            $reglement = new ReglementRevendeur();
            $reglement->facture_revendeur_id = $validated['facture_revendeur_id'];
            $reglement->facture()->associate($facture); // Important: associer la facture
            $reglement->date_reglement = $validated['date_reglement'];
            $reglement->type_reglement = $validated['type_reglement'];
            $reglement->montant = $validated['montant'];
            $reglement->reference_preuve = $validated['reference_preuve'];
            $reglement->banque = $validated['banque'];
            $reglement->date_echeance = $validated['date_echeance'];
            $reglement->notes = $validated['notes'];
            $reglement->created_by = auth()->id();
            $reglement->statut = ReglementRevendeur::STATUT_BROUILLON;

            // Vérifier si le montant est valide
            if (!$reglement->verifierMontant()) {
                throw new \Exception('Le montant du règlement dépasse le reste à payer de la facture');
            }

            $reglement->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement créé avec succès',
                'data' => [
                    'reglement' => $reglement->load([
                        'facture.client',
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
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du règlement:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all() // Pour le débogage
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du règlement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validate_reglement($id)
    {
        try {
            DB::beginTransaction();

            // Récupérer le règlement avec sa facture et les relations nécessaires
            $reglement = ReglementRevendeur::with(['facture.reglements'])
                ->findOrFail($id);

            // Vérifier si on a une session de caisse ouverte
            // $sessionCaisse = SessionCaisse::where('utilisateur_id', auth()->id())
            //     ->where('statut', 'ouverte')
            //     ->first();

            // if (!$sessionCaisse) {
            //     throw new \Exception('Vous devez avoir une session de caisse ouverte pour valider un règlement');
            // }

            // Valider le règlement
            if (!$reglement->valider(auth()->id())) {
                throw new \Exception("Erreur lors de la validation du règlement");
            }

            // Mettre à jour la session caisse
            // if (method_exists($sessionCaisse, 'mettreAJourTotaux')) {
            //     $sessionCaisse->mettreAJourTotaux();
            // }

            DB::commit();

            // Log de l'action
            Log::info('Règlement validé avec succès', [
                'reglement_id' => $id,
                'utilisateur_id' => auth()->id(),
                // 'session_caisse_id' => $sessionCaisse->id,
                'montant' => $reglement->montant
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Règlement validé avec succès',
                'data' => [
                    'reglement' => $reglement->fresh([
                        'facture.client',
                        'createdBy',
                        'validatedBy'
                    ])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la validation du règlement', [
                'reglement_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            Log::error('Erreure liée au montant des règlements', [
                'reglement_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function details($id)
    {
        try {
            Log::info('Début de la récupération des détails du règlement', [
                'reglement_id' => $id,
                'user_id' => auth()->id()
            ]);

            // Récupérer le règlement avec ses relations
            $reglement = ReglementRevendeur::with([
                'facture.client',
                'createdBy',
                'validatedBy'
            ])->find($id);

            if (!$reglement) {
                Log::warning('Règlement non trouvé', ['reglement_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Règlement non trouvé'
                ], 404);
            }

            // Log des informations de base du règlement
            Log::info('Règlement trouvé', [
                'reglement_id' => $id,
                'numero' => $reglement->numero,
                'montant' => $reglement->montant,
                'facture_id' => $reglement->facture_id ?? 'Non défini'
            ]);

            try {
                // Préparer la réponse avec gestion des valeurs nulles
                $response = [
                    // Informations du règlement
                    'id' => $reglement->id,
                    'numero' => $reglement->numero,
                    'date_reglement' => $reglement->date_reglement ? $reglement->date_reglement->format('Y-m-d') : null,
                    'montant' => $reglement->montant,
                    'type_reglement' => $reglement->type_reglement,
                    'reference_preuve' => $reglement->reference_preuve,
                    'banque' => $reglement->banque,
                    'statut' => $reglement->statut,
                    'notes' => $reglement->notes,
                    'date_echeance' => $reglement->date_echeance ? $reglement->date_echeance->format('Y-m-d') : null,

                    // Informations de la facture
                    'facture' => null,
                    'created_by' => null,
                    'validated_by' => null
                ];
                // Ajouter les informations de la facture si elle existe
                if ($reglement->facture) {
                    $response['facture'] = [
                        'id' => $reglement->facture->id,
                        'numero' => $reglement->facture->numero,
                        'date_facture' => optional($reglement->facture->date_facture)->format('d/m/Y'),
                        'montant_ttc' => $reglement->facture->montant_ttc,
                        'montant_regle' => $reglement->facture->montant_regle,
                        'client' => null
                    ];

                    // Ajouter les informations du client si il existe
                    if ($reglement->facture->client) {
                        $response['facture']['client'] = [
                            'id' => $reglement->facture->client->id,
                            'raison_sociale' => $reglement->facture->client->raison_sociale,
                            'telephone' => $reglement->facture->client->telephone ?? 'Non renseigné'
                        ];
                    }
                }

                // Ajouter les informations de création si elles existent
                if ($reglement->createdBy) {
                    $response['created_by'] = [
                        'id' => $reglement->createdBy->id,
                        'name' => $reglement->createdBy->name
                    ];
                }

                // Ajouter les informations de validation si elles existent
                if ($reglement->validatedBy) {
                    $response['validated_by'] = [
                        'id' => $reglement->validatedBy->id,
                        'name' => $reglement->validatedBy->name
                    ];
                }

                Log::info('Réponse préparée avec succès', [
                    'reglement_id' => $id,
                    'response_keys' => array_keys($response)
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $response
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur lors de la préparation de la réponse', [
                    'reglement_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la préparation des données du règlement'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des détails du règlement', [
                'reglement_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des détails'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Trouver le règlement avec ses relations
            $reglement = ReglementRevendeur::with(['facture', 'facture.reglements'])
                ->findOrFail($id);

            // Vérifier si le règlement est modifiable (statut brouillon)
            if ($reglement->statut !== ReglementRevendeur::STATUT_BROUILLON) {
                throw new \Exception('Ce règlement ne peut plus être modifié car il a déjà été validé ou annulé.');
            }

            // Validation des données
            $validated = $request->validate([
                'type_reglement' => 'required|string',
                'date_reglement' => 'required|date',
                'montant' => 'required|numeric|min:0',
                'reference_preuve' => 'nullable|string|max:255',
                'banque' => 'nullable|string|max:255',
                'date_echeance' => 'nullable|date|after_or_equal:date_reglement',
                'notes' => 'nullable|string'
            ]);

            // Calculer le reste à payer de la facture (en excluant le montant actuel du règlement)
            $montantDejaRegle = $reglement->facture->reglements()
                ->where('id', '!=', $reglement->id)
                ->where('statut', 'valide')
                ->sum('montant');

            $resteAPayer = $reglement->facture->montant_ttc - $montantDejaRegle;

            // Vérifier si le nouveau montant ne dépasse pas le reste à payer
            if ($validated['montant'] > $resteAPayer) {
                throw new \Exception('Le montant du règlement dépasse le reste à payer de la facture');
            }

            // Validation spécifique selon le type de règlement
            switch ($validated['type_reglement']) {
                case 'cheque':
                    if (empty($validated['banque']) || empty($validated['reference_preuve']) || empty($validated['date_echeance'])) {
                        throw ValidationException::withMessages([
                            'banque' => 'Pour un règlement par chèque, la banque est obligatoire',
                            'reference_preuve' => 'Pour un règlement par chèque, le numéro est obligatoire',
                            'date_echeance' => 'Pour un règlement par chèque, la date d\'échéance est obligatoire'
                        ]);
                    }
                    break;

                case 'virement':
                    if (empty($validated['banque']) || empty($validated['reference_preuve'])) {
                        throw ValidationException::withMessages([
                            'banque' => 'Pour un virement, la banque est obligatoire',
                            'reference_preuve' => 'Pour un virement, la référence est obligatoire'
                        ]);
                    }
                    break;

                case 'carte_bancaire':
                case 'MoMo':
                case 'Flooz':
                case 'Celtis_Pay':
                    if (empty($validated['reference_preuve'])) {
                        throw ValidationException::withMessages([
                            'reference_preuve' => 'Le numéro de transaction est obligatoire pour ce mode de paiement'
                        ]);
                    }
                    break;
            }

            // Mise à jour du règlement
            $reglement->type_reglement = $validated['type_reglement'];
            $reglement->date_reglement = $validated['date_reglement'];
            $reglement->montant = $validated['montant'];
            $reglement->reference_preuve = $validated['reference_preuve'];
            $reglement->banque = $validated['banque'];
            $reglement->date_echeance = $validated['date_echeance'];
            $reglement->notes = $validated['notes'];
            // $reglement->updated_by = auth()->id();
            $reglement->updated_at = now();

            // Sauvegarder les modifications
            $reglement->save();

            // Log de l'action
            Log::info('Règlement mis à jour', [
                'reglement_id' => $reglement->id,
                'utilisateur_id' => auth()->id(),
                'ancien_montant' => $reglement->getOriginal('montant'),
                'nouveau_montant' => $reglement->montant
            ]);

            DB::commit();

            // Charger les relations nécessaires pour la réponse
            $reglement->load([
                'facture.client',
                'createdBy',
                // Ne chargeons que les relations qui existent
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Règlement mis à jour avec succès',
                'data' => [
                    'reglement' => $reglement
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
            Log::error('Erreur lors de la mise à jour du règlement:', [
                'reglement_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour du règlement: ' . $e->getMessage()
            ], 500);
        }
    }

    // Dans ReglementClientController.php

    public function cancel(Request $request, $id) // Ajout du paramètre Request $request
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            $reglement = ReglementRevendeur::findOrFail($id);

            // Vérifier si le règlement n'est pas déjà annulé
            if ($reglement->statut === 'annule') {
                throw new \Exception('Ce règlement est déjà annulé');
            }

            // Stocker l'ancien statut pour le log
            $ancienStatut = $reglement->statut;

            // Mettre à jour le statut
            $reglement->statut = 'annulee';
            $reglement->annule_par = auth()->id();
            $reglement->date_annulation = now();
            $reglement->save();

            // Log de l'action
            Log::info('Règlement annulé', [
                'reglement_id' => $reglement->id,
                'utilisateur_id' => auth()->id(),
                'ancien_statut' => $ancienStatut
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement annulé avec succès',
                'data' => [
                    'reglement' => $reglement->fresh([
                        'facture.client',
                        'createdBy'
                    ])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation du règlement:', [
                'reglement_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'annulation du règlement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(ReglementRevendeur $reglement)
    {
        try {
            if ($reglement->isValidated()) {
                throw new \Exception('Impossible de supprimer un règlement validé');
            }

            DB::beginTransaction();
            $reglement->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Règlement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'un règlement
     *
     * @param ReglementRevendeur $reglement
     * @return JsonResponse
     */
    public function getDetailsReglement(ReglementRevendeur $reglement): JsonResponse
    {
        // Chargement des relations nécessaires
        $reglement->load('facture.client');

        return response()->json([
            'numero' => $reglement->numero,
            'date_reglement' => $reglement->date_reglement->format('d/m/Y'),
            'facture' => [
                'numero' => $reglement->facture->numero,
                'client' => [
                    'raison_sociale' => $reglement->facture->client->raison_sociale
                ]
            ],
            'type_reglement' => $reglement->type_reglement,
            'reference_preuve' => $reglement->reference_preuve,
            'montant' => number_format($reglement->montant, 0, ',', ' '),
            'notes' => $reglement->notes
        ]);
    }
}
