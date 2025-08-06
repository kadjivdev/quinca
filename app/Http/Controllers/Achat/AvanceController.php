<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Achat\Avance;
use App\Models\Achat\Fournisseur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AvanceController extends Controller
{
    /**
     * Affiche la liste des Avances
     */

    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec pagination
        $avances = Avance::with(['fournisseur', 'createdBy'])
            ->where('point_de_vente_id', Auth()->user()->point_de_vente_id)
            ->latest();

        // Application des filtres
        if ($request->filled('fournisseur_id')) {
            $avances->where('fournisseur_id', $request->fournisseur_id);
        }

        if ($request->filled('type_paiement')) {
            $avances->parType($request->type_paiement);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $avances->whereBetween('date', [
                Carbon::parse($request->date_debut)->startOfDay(),
                Carbon::parse($request->date_fin)->endOfDay()
            ]);
        }

        if ($request->filled('search')) {
            $avances->search($request->search);
        }

        $avances = $avances->get();

        // // Statistiques
        // $stats = [
        //     'total_avances' => Avance::whereNotNull("validated_by")->count(),
        //     'total_montant' => Avance::whereNotNull("validated_by")->sum('montant'),
        //     'avances_mois' => Avance::whereNotNull("validated_by")->whereMonth('date', now()->month)
        //         ->whereYear('date', now()->year)
        //         ->count(),
        //     'montant_mois' => Avance::whereNotNull("validated_by")->whereMonth('date', now()->month)
        //         ->whereYear('date', now()->year)
        //         ->sum('montant')
        // ];

        $fournisseurs = Fournisseur::get(["id", "raison_sociale"]);

        return view('pages.achat.avance.index', compact(
            'avances',
            // 'stats',
            'fournisseurs',
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

        $avances = Avance::with(['fournisseur', 'createdBy'])
            ->latest();

        // Application des filtres
        if ($request->filled('fournisseur_id')) {
            $avances->where('fournisseur_id', $request->fournisseur_id);
        }

        if ($request->filled('type_paiement')) {
            $avances->parType($request->type_paiement);
        }

        if ($request->filled('date_debut') && $request->filled('date_fin')) {
            $avances->whereBetween('date', [
                Carbon::parse($request->date_debut)->startOfDay(),
                Carbon::parse($request->date_fin)->endOfDay()
            ]);
        }

        if ($request->filled('search')) {
            $avances->search($request->search);
        }

        $avances = $avances->get();

        return response()->json([
            'html' => view('pages.achat.avance.partials.list', compact('acomptes'))->render(),
            'stats' => [
                'total' => Avance::count(),
                'montant_total' => Avance::sum('montant'),
                'avances_mois' => Avance::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count(),
                'montant_mois' => Avance::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->sum('montant')
            ]
        ]);
    }

    /**
     * Enregistre un nouvel Avance
     */
    public function store(Request $request)
    {
        try {
            // Validation des données
            $validated = $request->validate(Avance::rules(), [
                'date.required' => 'La date est obligatoire',
                'date.date' => 'La date n\'est pas valide',
                'fournisseur_id.required' => 'Le fournisseur est obligatoire',
                'fournisseur_id.exists' => 'Le fournisseur sélectionné n\'existe pas',
                'type_paiement.required' => 'Le type de paiement est obligatoire',
                'type_paiement.in' => 'Le type de paiement sélectionné n\'est pas valide',
                'montant.required' => 'Le montant est obligatoire',
                'montant.numeric' => 'Le montant doit être un nombre',
                'montant.min' => 'Le montant doit être supérieur à 0'
            ]);

            DB::beginTransaction();

            // Ajouter le statut par défaut aux données validées
            $validated['statut'] = Avance::STATUT_EN_ATTENTE;

            $avance = new Avance();
            $avance->fill($validated);
            $avance->created_by = auth()->id();
            // $avance->reference = $request->reference?->reference;
            $avance->point_de_vente_id = auth()->user()->point_de_vente_id;
            $avance->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avance enregistré avec succès',
                'data' => [
                    'avance' => $avance->load(['fournisseur', 'createdBy'])
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
            Log::error('Erreur lors de l\'enregistrement de l\'Avance:', [
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
     * Affiche les détails d'un Avance
     */
    public function show(Request $request, Avance $avance)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $avance->load(['fournisseur', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => [
                'avance' => [
                    'id' => $avance->id,
                    'reference' => $avance->reference,
                    'date' => $avance->date->format('Y-m-d'),
                    'type_paiement' => $avance->type_paiement,
                    'montant' => $avance->montant,
                    'observation' => $avance->observation,
                    'created_at' => $avance->created_at->format('d/m/Y H:i'),
                    'fournisseur' => [
                        'id' => $avance->fournisseur->id,
                        'code_fournisseur' => $avance->fournisseur->code_fournisseur,
                        'raison_sociale' => $avance->fournisseur->raison_sociale
                    ],
                    'created_by' => $avance->createdBy ? $avance->createdBy->name : null
                ]
            ]
        ]);
    }

    /**
     * Supprime un Avance
     */
    public function destroy(Request $request, Avance $avance)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            DB::beginTransaction();

            // Vérifier si l'Avance est récent (moins de 24h)
            if ($avance->created_at->diffInHours(now()) > 24) {
                throw new \Exception('Impossible de supprimer un Avance de plus de 24 heures');
            }

            // Supprimer l'Avance (le modèle gère automatiquement la mise à jour du solde client)
            $avance->delete();

            DB::commit();

            Log::info('Avance supprimé:', [
                'avance_id' => $avance->id,
                'reference' => $avance->reference,
                'fournisseur_id' => $avance->fournisseur_id,
                'montant' => $avance->montant,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avance supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'Avance:', [
                'acompte_id' => $avance->id,
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
     * Met à jour un Avance
     */
    public function update(Request $request, Avance $avance)
    {
        try {
            // Validation des données
            $validated = $request->validate(Avance::rules(), [
                'date.required' => 'La date est obligatoire',
                'date.date' => 'La date n\'est pas valide',
                'fournisseur_id.required' => 'Le client est obligatoire',
                'fournisseur_id.exists' => 'Le client sélectionné n\'existe pas',
                'type_paiement.required' => 'Le type de paiement est obligatoire',
                'type_paiement.in' => 'Le type de paiement sélectionné n\'est pas valide',
                'montant.required' => 'Le montant est obligatoire',
                'montant.numeric' => 'Le montant doit être un nombre'
            ]);

            DB::beginTransaction();

            // Récupérer les clients pour le select
            $fournisseurs = Fournisseur::get(['id', 'raison_sociale', 'code_fournisseur']);

            $avance->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avance modifié avec succès',
                'data' => [
                    'acompte' => $avance->load(['fournisseur', 'createdBy']),
                    'fournisseurs' => $fournisseurs
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Valider un Avance
     */
    public function validate_acompte(Request $request, Avance $avance)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Vérifier si l'Avance peut être validé
            if (!$avance->isEnAttente()) {
                throw new \Exception('Cet Avance ne peut pas être validé car il n\'est pas en attente');
            }

            DB::beginTransaction();

            // Valider l'Avance
            $avance->update([
                'statut' => Avance::STATUT_VALIDE,
                'validated_at' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avance validé avec succès',
                'data' => [
                    'acompte' => $avance->load(['fournisseur', 'createdBy', 'validatedBy'])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de l\'Avance:', [
                'acompte_id' => $avance->id,
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
     * Rejeter un Avance
     */
    public function reject(Request $request, Avance $avance)
    {
        try {
            if (!$request->ajax()) {
                return response()->json(['error' => 'Requête non autorisée'], 403);
            }

            // Validation de la raison du rejet
            $validated = $request->validate([
                'motif_rejet' => 'required|string|max:255'
            ]);

            // Vérifier si l'Avance peut être rejeté
            if (!$avance->isEnAttente()) {
                throw new \Exception('Cet Avance ne peut pas être rejeté car il n\'est pas en attente');
            }

            DB::beginTransaction();

            // Rejeter l'Avance
            $avance->update([
                'statut' => Avance::STATUT_REJETE,
                'observation' => $validated['motif_rejet'],
                'validated_at' => now(),
                'validated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Avance rejeté avec succès',
                'data' => [
                    'acompte' => $avance->load(['fournisseur', 'createdBy', 'validatedBy'])
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du rejet de l\'Avance:', [
                'acompte_id' => $avance->id,
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
