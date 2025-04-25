<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\{AcompteClient, Client};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Validation\ValidationException;

class AcompteClientController extends Controller
{
    /**
     * Affiche la liste des acomptes
     */

    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec pagination
        $acomptes = AcompteClient::with(['client', 'createdBy'])
            ->where('point_de_vente_id', Auth()->user()->point_de_vente_id)
            ->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $acomptes->where('client_id', $request->client_id);
        }

        if ($request->filled('type_paiement')) {
            $acomptes->parType($request->type_paiement);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $acomptes->whereBetween('date', [
                Carbon::parse($request->date_debut)->startOfDay(),
                Carbon::parse($request->date_fin)->endOfDay()
            ]);
        }

        if ($request->filled('search')) {
            $acomptes->search($request->search);
        }

        // $acomptes = $acomptes->paginate(10);
        $acomptes = $acomptes->get();

        // Statistiques
        $stats = [
            'total_acomptes' => AcompteClient::count(),
            'total_montant' => AcompteClient::sum('montant'),
            'acomptes_mois' => AcompteClient::whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
            'montant_mois' => AcompteClient::whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('montant')
        ];

        $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)
            ->orderBy('raison_sociale')
            ->get(['id', 'raison_sociale', 'code_client']);

        return view('pages.ventes.acompte.index', compact(
            'acomptes',
            'stats',
            'clients',
            'date'
        ));
    }

    /**
     * Rafraîchit la liste des acomptes (pour AJAX)
     */
    public function refreshList(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $acomptes = AcompteClient::with(['client', 'createdBy'])
            ->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $acomptes->where('client_id', $request->client_id);
        }

        if ($request->filled('type_paiement')) {
            $acomptes->parType($request->type_paiement);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $acomptes->whereBetween('date', [
                Carbon::parse($request->date_debut)->startOfDay(),
                Carbon::parse($request->date_fin)->endOfDay()
            ]);
        }

        if ($request->filled('search')) {
            $acomptes->search($request->search);
        }

        $acomptes = $acomptes->paginate(10);

        return response()->json([
            'html' => view('pages.ventes.acompte.partials.list', compact('acomptes'))->render(),
            'stats' => [
                'total' => AcompteClient::count(),
                'montant_total' => AcompteClient::sum('montant'),
                'acomptes_mois' => AcompteClient::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count(),
                'montant_mois' => AcompteClient::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->sum('montant')
            ]
        ]);
    }

    /**
     * Enregistre un nouvel acompte
     */
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validated = $request->validate(AcompteClient::rules(), [
                'date.required' => 'La date est obligatoire',
                'date.date' => 'La date n\'est pas valide',
                'client_id.required' => 'Le client est obligatoire',
                'client_id.exists' => 'Le client sélectionné n\'existe pas',
                'type_paiement.required' => 'Le type de paiement est obligatoire',
                'type_paiement.in' => 'Le type de paiement sélectionné n\'est pas valide',
                'montant.required' => 'Le montant est obligatoire',
                'montant.numeric' => 'Le montant doit être un nombre',
                'montant.min' => 'Le montant doit être supérieur à 0'
            ]);

            DB::beginTransaction();

            // Vérifier si le client existe et est actif
            $client = Client::findOrFail($validated['client_id']);
            if (!$client->statut) {
                throw new Exception('Ce client est inactif');
            }

            // Ajouter le statut par défaut aux données validées
            $validated['statut'] = AcompteClient::STATUT_EN_ATTENTE;

            $acompte = new AcompteClient();
            $acompte->fill($validated);
            $acompte->created_by = auth()->id();
            // $acompte->reference = $request->reference?->reference;
            $acompte->point_de_vente_id = auth()->user()->point_de_vente_id;
            $acompte->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acompte enregistré avec succès',
                'data' => [
                    'acompte' => $acompte->load(['client', 'createdBy'])
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
            Log::error('Erreur lors de l\'enregistrement de l\'acompte:', [
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
    public function show(Request $request, AcompteClient $acompte)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $acompte->load(['client', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => [
                'acompte' => [
                    'id' => $acompte->id,
                    'reference' => $acompte->reference,
                    'date' => $acompte->date->format('Y-m-d'),
                    'type_paiement' => $acompte->type_paiement,
                    'montant' => $acompte->montant,
                    'observation' => $acompte->observation,
                    'created_at' => $acompte->created_at->format('d/m/Y H:i'),
                    'client' => [
                        'id' => $acompte->client->id,
                        'code_client' => $acompte->client->code_client,
                        'raison_sociale' => $acompte->client->raison_sociale
                    ],
                    'created_by' => $acompte->createdBy ? $acompte->createdBy->name : null
                ]
            ]
        ]);
    }

    /**
     * Supprime un acompte
     */
    public function destroy(Request $request, AcompteClient $acompte)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Vérifier si l'acompte est récent (moins de 24h)
            if ($acompte->created_at->diffInHours(now()) > 24) {
                throw new Exception('Impossible de supprimer un acompte de plus de 24 heures');
            }

            // Supprimer l'acompte (le modèle gère automatiquement la mise à jour du solde client)
            $acompte->delete();

            DB::commit();

            Log::info('Acompte supprimé:', [
                'acompte_id' => $acompte->id,
                'reference' => $acompte->reference,
                'client_id' => $acompte->client_id,
                'montant' => $acompte->montant,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Acompte supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'acompte:', [
                'acompte_id' => $acompte->id,
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
     * Met à jour un acompte
     */
    public function update(Request $request, AcompteClient $acompte)
    {
        try {
            // Validation des données
            $validated = $request->validate(AcompteClient::rules(), [
                'date.required' => 'La date est obligatoire',
                'date.date' => 'La date n\'est pas valide',
                'client_id.required' => 'Le client est obligatoire',
                'client_id.exists' => 'Le client sélectionné n\'existe pas',
                'type_paiement.required' => 'Le type de paiement est obligatoire',
                'type_paiement.in' => 'Le type de paiement sélectionné n\'est pas valide',
                'montant.required' => 'Le montant est obligatoire',
                'montant.numeric' => 'Le montant doit être un nombre'
            ]);

            DB::beginTransaction();

            // Récupérer les clients pour le select
            $clients = Client::where('point_de_vente_id', Auth()->user()->point_de_vente_id)
                ->orderBy('raison_sociale')
                ->get(['id', 'raison_sociale', 'code_client']);

            $acompte->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acompte modifié avec succès',
                'data' => [
                    'acompte' => $acompte->load(['client', 'createdBy']),
                    'clients' => $clients
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Valider un acompte
     */
    public function validate_acompte(Request $request, AcompteClient $acompte)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Vérifier si l'acompte peut être validé
            if (!$acompte->isEnAttente()) {
                throw new Exception('Cet acompte ne peut pas être validé car il n\'est pas en attente');
            }

            DB::beginTransaction();

            // Valider l'acompte
            $acompte->update([
                'statut' => AcompteClient::STATUT_VALIDE,
                'validated_at' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acompte validé avec succès',
                'data' => [
                    'acompte' => $acompte->load(['client', 'createdBy', 'validatedBy'])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de l\'acompte:', [
                'acompte_id' => $acompte->id,
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
     * Rejeter un acompte
     */
    public function reject(Request $request, AcompteClient $acompte)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Validation de la raison du rejet
            $validated = $request->validate([
                'motif_rejet' => 'required|string|max:255'
            ]);

            // Vérifier si l'acompte peut être rejeté
            if (!$acompte->isEnAttente()) {
                throw new Exception('Cet acompte ne peut pas être rejeté car il n\'est pas en attente');
            }

            DB::beginTransaction();

            // Rejeter l'acompte
            $acompte->update([
                'statut' => AcompteClient::STATUT_REJETE,
                'observation' => $validated['motif_rejet'],
                'validated_at' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acompte rejeté avec succès',
                'data' => [
                    'acompte' => $acompte->load(['client', 'createdBy', 'validatedBy'])
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du rejet de l\'acompte:', [
                'acompte_id' => $acompte->id,
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
