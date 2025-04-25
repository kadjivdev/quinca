<?php

namespace App\Http\Controllers\Achat;

use App\Models\Achat\LigneProgrammationAchat;
use App\Models\Stock\Article;
use App\Http\Controllers\Controller;
use App\Models\Log\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LigneProgrammationAchatController extends Controller
{
    /**
     * Ajoute une nouvelle ligne
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Log de début d'opération
            Log::info('Début ajout ligne programmation', [
                'user' => auth()->id(),
                'programmation_id' => $request->programmation_id
            ]);

            $validated = $request->validate([
                'programmation_id' => 'required|exists:programmation_achats,id',
                'article_id' => 'required|exists:articles,id',
                'unite_mesure_id' => 'required|exists:unite_mesures,id',
                'quantite' => 'required|numeric|min:0.01'
            ]);

            $ligne = LigneProgrammationAchat::create($validated);

            DB::commit();

            Log::info('Fin ajout ligne programmation', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ligne ajoutée avec succès',
                'data' => $ligne->load(['article', 'uniteMesure'])
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout de ligne programmation', [
                'user' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la ligne: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour une ligne
     */
    public function update(Request $request, LigneProgrammationAchat $ligne)
    {
        try {
            DB::beginTransaction();

            Log::info('Début mise à jour ligne programmation', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            $validated = $request->validate([
                'unite_mesure_id' => 'required|exists:unite_mesures,id',
                'quantite' => 'required|numeric|min:0.01'
            ]);

            // Sauvegarde des anciennes valeurs pour le log
            $oldValues = $ligne->getAttributes();

            $ligne->update($validated);

            DB::commit();

            Log::info('Fin mise à jour ligne programmation', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ligne mise à jour avec succès',
                'data' => $ligne->load(['article', 'uniteMesure'])
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de ligne programmation', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la ligne: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime une ligne
     */
    public function destroy(LigneProgrammationAchat $ligne)
    {
        try {
            DB::beginTransaction();

            Log::info('Début suppression ligne programmation', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            // Sauvegarde des valeurs pour le log
            $oldValues = $ligne->getAttributes();

            $ligne->delete();

            DB::commit();

            Log::info('Fin suppression ligne programmation', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ligne supprimée avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de ligne programmation', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la ligne: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajoute plusieurs lignes en une fois
     */
    public function bulkStore(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('Début ajout multiple de lignes programmation', [
                'user' => auth()->id(),
                'programmation_id' => $request->programmation_id
            ]);

            $validated = $request->validate([
                'programmation_id' => 'required|exists:programmation_achats,id',
                'lignes' => 'required|array|min:1',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.unite_mesure_id' => 'required|exists:unite_mesures,id',
                'lignes.*.quantite' => 'required|numeric|min:0.01'
            ]);

            $lignesCreees = collect();

            foreach ($validated['lignes'] as $ligneData) {
                $ligne = LigneProgrammationAchat::create([
                    'programmation_id' => $validated['programmation_id'],
                    'article_id' => $ligneData['article_id'],
                    'unite_mesure_id' => $ligneData['unite_mesure_id'],
                    'quantite' => $ligneData['quantite']
                ]);

                $lignesCreees->push($ligne);


            }

            DB::commit();

            Log::info('Fin ajout multiple de lignes programmation', [
                'user' => auth()->id(),
                'nb_lignes' => $lignesCreees->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lignes ajoutées avec succès',
                'data' => $lignesCreees->load(['article', 'uniteMesure'])
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'ajout multiple de lignes programmation', [
                'user' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout des lignes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour plusieurs lignes en une fois
     */
    public function bulkUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('Début mise à jour multiple de lignes programmation', [
                'user' => auth()->id()
            ]);

            $validated = $request->validate([
                'lignes' => 'required|array|min:1',
                'lignes.*.id' => 'required|exists:ligne_programmation_achats,id',
                'lignes.*.unite_mesure_id' => 'required|exists:unite_mesures,id',
                'lignes.*.quantite' => 'required|numeric|min:0.01'
            ]);

            $lignesMaj = collect();

            foreach ($validated['lignes'] as $ligneData) {
                $ligne = LigneProgrammationAchat::findOrFail($ligneData['id']);
                $oldValues = $ligne->getAttributes();

                $ligne->update([
                    'unite_mesure_id' => $ligneData['unite_mesure_id'],
                    'quantite' => $ligneData['quantite']
                ]);

                $lignesMaj->push($ligne);

            }

            DB::commit();

            Log::info('Fin mise à jour multiple de lignes programmation', [
                'user' => auth()->id(),
                'nb_lignes' => $lignesMaj->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lignes mises à jour avec succès',
                'data' => $lignesMaj->load(['article', 'uniteMesure'])
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour multiple de lignes programmation', [
                'user' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des lignes: ' . $e->getMessage()
            ], 500);
        }
    }
}
