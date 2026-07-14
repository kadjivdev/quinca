<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\{AcompteClient, Client, SessionCaisse};
use App\Models\Vente\Versement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VersementController extends Controller
{
    /**
     * Affiche la liste des acomptes
     */
    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec 
        $versementsQuery = Versement::with(['client', 'createdBy', "accompteClient", "validatedBy", "extournedBy"])
            ->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $versementsQuery->where('client_id', $request->client_id);
        }

        if ($request->filled('type_op')) {
            $versementsQuery->parType($request->type_op);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $versementsQuery->whereBetween('date_op', [
                Carbon::parse($request->date_debut)->startOfDay(),
                Carbon::parse($request->date_fin)->endOfDay()
            ]);
        }

        if ($request->filled("status_op")) {
            switch ($request->status_op) {
                case 'VALIDE':
                    $versementsQuery->whereHas("accompteClient", function ($query) {
                        $query->whereNotNull("validated_by"); //les versements dont les accomptes sont validés
                    });
                    break;

                case 'ATTENTE':
                    $versementsQuery->whereHas("accompteClient", function ($query) {
                        $query->whereNull("validated_by"); //les versements dont les accomptes ne sont pas validés
                    });
                    break;

                case 'EXTOURNER':
                    $versementsQuery->whereNotNull("extourned_at");
                    break;
                default:
                    # code...
                    break;
            }
        }

        $versements = $versementsQuery->get();
        $versementsMois = $versementsQuery
            ->whereMonth('date_op', now()->year)
            ->whereYear('date_op', now()->year)
            ->get();

        // Statistiques
        $stats = [
            'total_versement' => $versements->count(),
            'total_montant' => $versements->sum('montant'),
            'versement_mois' => $versementsMois->count(),
            'montant_mois' => $versementsMois
                ->sum('montant')
        ];

        $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)
            ->orderBy('raison_sociale')
            ->get(['id', 'raison_sociale', 'code_client']);

        $versementMoisCount = $versements->whereBetween('date_op', [now()->startOfMonth(), now()->endOfMonth()])->count();

        return view('pages.ventes.versement.index', compact(
            'versements',
            'stats',
            'clients',
            'date',
            'versementMoisCount'
        ));
    }

    /**
     * Enregistre un nouvel acompte
     */
    public function store(Request $request)
    {
        try {
            Log::debug("Début du stockage de versement", ["data" => $request->all()]);

            // Vérifications initiales
            $sessionCaisse = SessionCaisse::ouverte()
                ->where('point_de_vente_id', auth()->user()->point_de_vente_id)
                // ->where('utilisateur_id', auth()->user()->id)
                ->first();

            if (!$sessionCaisse) {
                throw new Exception("Aucune session n'est ouverte!");
            }

            // Validation des données
            $validated = $request->validate(Versement::rules(), Versement::messages());

            DB::beginTransaction();
            $versement = Versement::create($validated);

            // Generation de l'acompte client associé
            $versement->accompteClient()->create([
                'date' => $versement->date_op,
                // 'reference' => $versement->reference,
                'type_paiement' => $versement->type_op == "Chèque" ? AcompteClient::TYPE_CHEQUE : AcompteClient::TYPE_MOMO,
                'montant' => $versement->montant,
                'preuve' => $versement->preuve,
                'client_id' => $versement->client_id,
                'observation' => $versement->comment,
                'point_de_vente_id' => $sessionCaisse?->point_de_vente_id,
                'session_caisse_id' => $sessionCaisse?->id,
                'created_by' => Auth::id(),// integré le 14/07/2026
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Versement enregistré avec succès',
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
            Log::error('Erreur lors de l\'enregistrement du versemeent:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Log::info("Request again", [
                "message" => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    /**
     * Affiche les détails d'un acompte
     */
    public function show(Request $request, Versement $versement)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $versement->load(['client', 'createdBy', "accompteClient"]);

        return response()->json([
            'success' => true,
            'data' => [
                'versement' => [
                    'id' => $versement->id,
                    'reference' => $versement->reference,
                    'reference_op' => $versement->reference_op,
                    'date_op' => $versement->date_op->format('Y-m-d'),
                    'date_valeur' => $versement->date_valeur->format('Y-m-d'),
                    'type_op' => $versement->type_op,
                    'montant' => $versement->montant,
                    'observation' => $versement->comment,
                    'comment' => $versement->comment,
                    'banque' => $versement->banque,
                    'preuve' => $versement->preuve,
                    'extourned_comment' => $versement->extourned_comment,
                    'acompte' => $versement->accompteClient,
                    'created_at' => $versement->created_at->format('d/m/Y H:i'),
                    'client' => [
                        'id' => $versement->client->id,
                        'code_client' => $versement->client->code_client,
                        'raison_sociale' => $versement->client->raison_sociale
                    ],
                    'created_by' => $versement->createdBy?->name ?? null,
                    'validated_by' => $versement->validedBy?->name ?? null
                ]
            ]
        ]);
    }

    /**
     * Met à jour un acompte
     */
    public function update(Request $request, Versement $versement)
    {
        try {
            // Validation des données
            Log::info("Data to update", ["data" => $request->all()]);

            $validated = $request->validate(Versement::rules($versement->id), Versement::messages());
            Log::info("Data validated", ["data" => $validated]);

            DB::beginTransaction();

            // update du versement
            $versement->update($validated);

            $versement->refresh();

            // update du acompte client attaché
            $versement->accompteClient()
                ->update([
                    'date' => $versement->date_op,
                    'type_paiement' => $versement->type_op == "Chèque" ? AcompteClient::TYPE_CHEQUE : AcompteClient::TYPE_MOMO,
                    'montant' => $versement->montant,
                    'preuve' => $versement->preuve,
                    'client_id' => $versement->client_id,
                    'observation' => $versement->comment,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Versement modifié avec succès',
            ]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation lors de la modification du versement", ["errors" => $e->getMessage()]);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            Log::debug("Erreure lors de la modification du versement", ["error" => $e->getMessage(), "ligne" => $e->getLine()]);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Supprime un acompte
     */
    public function destroy(Request $request, Versement $versement)
    {
        Log::info("Tentative de suppression du versement", ["versement_id" => $versement->id]);
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            $versement->update(["reference_op" => $versement->reference_op . "_deleted_" . time()]);

            // suppression des comptes client attaché à l'accompte du versement
            if ($versement->accompteClient?->compteClient?->isNotEmpty()) {
                $versement->accompteClient->compteClient()->delete();
            }

            // suppression du accompte client attaché au versement
            $versement->accompteClient()->delete();

            // suppression du versement
            $versement->delete();

            DB::commit();

            Log::info('versement supprimé:', [
                'versement_id' => $versement->id,
                'reference' => $versement->reference,
                'client_id' => $versement->client_id,
                'montant' => $versement->montant,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Versement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du versement:', [
                'versement_id' => $versement->id,
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
     * Valider un versement
     */

    public function validateVersement(Request $request, Versement $versement)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Vérifier si l'acompte peut être validé
            if ($versement->validated_at) {
                throw new Exception('Cet versement ne peut pas être validé car il est déjà validé');
            }

            DB::beginTransaction();

            // Valider l'acompte
            $versement->update([
                'validated_at' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Versement validé avec succès',
                'data' => [
                    'verse$versement' => $versement->load(['client', 'createdBy', 'validatedBy'])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation du versement:', [
                'versement_id' => $versement->id,
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
     * Extouner un versement
     */
    public function extournerVersement(Request $request, Versement $versement)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Validation de la raison du rejet
            $validated = $request->validate([
                'extourned_comment' => 'required|string'
            ]);

            // Vérifier si l'acompte peut être rejeté
            if ($versement->validated_at) {
                throw new Exception('Ce versement ne peut pas être rejeté car il est déjà validé');
            }

            DB::beginTransaction();

            // Rejeter l'acompte
            $versement->update([
                'extourned_comment' => $validated['extourned_comment'],
                'extourned_at' => now(),
                'extourned_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Versement extourné avec succès',
                'data' => [
                    'versement' => $versement->load(['client', 'createdBy', 'validatedBy'])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du extourne du versement:', [
                'versement_id' => $versement->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
