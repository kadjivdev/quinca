<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Transportation;
use App\Models\Vente\{Client, TransportMouvement};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Validation\ValidationException;

class TransportMouvementController extends Controller
{
    /**
     * Affiche la liste des acomptes
     */
    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec pagination
        $mouvementQuery = TransportMouvement::with(['client', 'createdBy', "validatedBy"])
            ->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $mouvementQuery->where('client_id', $request->client_id);
        }

        if ($request->filled('transportation_id')) {
            $mouvementQuery->where('transportation_id',$request->transportation_id);
        }

        if ($request->filled('date')) {
            $mouvementQuery->whereDate('date', $request->date);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $mouvementQuery->whereBetween('date', [$request->date_debut, $request->date_fin]);
        }

        $mouvements = $mouvementQuery->get();
        $mouvementsMois = $mouvementQuery
            ->whereMonth('date', now()->year)
            ->whereYear('date', now()->year)
            ->get();

        // Statistiques
        $stats = [
            'total_versement' => $mouvements->count(),
            'total_montant' => $mouvements->sum('montant'),
            'versement_mois' => $mouvementsMois->count(),
            'montant_mois' => $mouvementsMois
                ->sum('montant')
        ];

        $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)
            ->orderBy('raison_sociale')
            ->get(['id', 'raison_sociale', 'code_client']);

        $transportations = Transportation::get(["id","matricule", "libelle", "type"]);

        return view('pages.ventes.transport-mouvement.index', compact(
            'mouvements',
            'stats',
            'clients',
            'transportations',
            'date'
        ));
    }

    /**
     * Enregistre un nouvel acompte
     */
    public function store(Request $request)
    {
        try {
            Log::debug("Début du stockage du mouvement du transport", ["data" => $request->all()]);


            // Validation des données
            $validated = $request->validate(TransportMouvement::rules(), TransportMouvement::messages());

            DB::beginTransaction();
            TransportMouvement::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mouvement de transport enregistré avec succès',
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
            Log::error('Erreur lors de l\'enregistrement du mouvement d etransport:', [
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
    public function show(Request $request, TransportMouvement $transport_mouvement)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $transport_mouvement->load(['client', 'transportation', 'createdBy',]);

        return response()->json([
            'success' => true,
            'data' => [
                'transport_mouvement' => [
                    'id' => $transport_mouvement->id,
                    'reference' => $transport_mouvement->reference,
                    'date' => $transport_mouvement->date->format('Y-m-d'),
                    'montant' => $transport_mouvement->montant,
                    'observation' => $transport_mouvement->comment,
                    'comment' => $transport_mouvement->comment,
                    'preuve' => $transport_mouvement->preuve,
                    'transportation' => $transport_mouvement->transportation,
                    'created_at' => $transport_mouvement->created_at->format('d/m/Y H:i'),
                    'client' => [
                        'id' => $transport_mouvement->client?->id,
                        'code_client' => $transport_mouvement->client?->code_client,
                        'raison_sociale' => $transport_mouvement->client?->raison_sociale
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
    public function update(Request $request, TransportMouvement $transport_mouvement)
    {
        try {
            // Validation des données
            Log::info("Data to update", ["data" => $request->all()]);

            $validated = $request->validate(TransportMouvement::rules($transport_mouvement->id), TransportMouvement::messages());
            Log::info("Data validated", ["data" => $validated]);

            DB::beginTransaction();

            // update du versement
            $transport_mouvement->update($validated);

            $transport_mouvement->refresh();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mouvement de transport modifié avec succès',
            ]);
        } catch (ValidationException $e) {
            Log::debug("Erreure de validation lors de la modification du mouvement de transport", ["errors" => $e->getMessage()]);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            Log::debug("Erreure lors de la modification du mouvement de transport", ["error" => $e->getMessage(), "ligne" => $e->getLine()]);
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
    public function destroy(Request $request, TransportMouvement $transport_mouvement)
    {
        Log::info("Tentative de suppression du mouvement de transport", ["data" => $transport_mouvement]);
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // suppression du versement
            $transport_mouvement->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mouvement de transport supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du mouvement de transport:', [
                'mouvement' => $transport_mouvement,
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
     * Valider un mouvement de transport
     */

    public function validateTransportMouvement(Request $request, TransportMouvement $transport_mouvement)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Vérifier si l'acompte peut être validé
            if ($transport_mouvement->validated_at) {
                throw new Exception('Cet mouvement de transport ne peut pas être validé car il est déjà validé');
            }

            DB::beginTransaction();

            // Valider le mouvement
            $transport_mouvement->update([
                'validated_at' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mouvement de transport validé avec succès',
                'data' => [
                    'transport_mouvement' => $transport_mouvement->load(['client', 'createdBy', 'transportation', 'validatedBy'])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation du versement:', [
                'transport_mouvement' => $transport_mouvement,
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
