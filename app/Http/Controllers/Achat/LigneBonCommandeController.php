<?php

namespace App\Http\Controllers\Achat;

use App\Models\Achat\{LigneBonCommande, BonCommande};
use App\Models\Stock\Article;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LigneBonCommandeController extends Controller
{
    /**
     * Ajoute une nouvelle ligne
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('Début ajout ligne bon de commande', [
                'user' => auth()->id(),
                'bon_commande_id' => $request->bon_commande_id
            ]);

            $validated = $request->validate([
                'bon_commande_id' => 'required|exists:bon_commandes,id',
                'article_id' => 'required|exists:articles,id',
                'unite_mesure_id' => 'required|exists:unite_mesures,id',
                'prix_unitaire' => 'required|numeric|min:0',
                'taux_remise' => 'required|numeric|between:0,100'
            ]);

            $ligne = LigneBonCommande::create($validated);

            // Mise à jour du montant total du bon de commande
            $ligne->bonCommande->updateMontantTotal();

            DB::commit();

            Log::info('Fin ajout ligne bon de commande', [
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
            Log::error('Erreur lors de l\'ajout de ligne bon de commande', [
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
    public function update(Request $request, LigneBonCommande $ligne)
    {
        try {
            DB::beginTransaction();

            Log::info('Début mise à jour ligne bon de commande', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            $validated = $request->validate([
                'unite_mesure_id' => 'required|exists:unite_mesures,id',
                'prix_unitaire' => 'required|numeric|min:0',
                'taux_remise' => 'required|numeric|between:0,100'
            ]);

            $oldValues = $ligne->getAttributes();
            $ligne->update($validated);

            // Mise à jour du montant total du bon de commande
            $ligne->bonCommande->updateMontantTotal();

            DB::commit();

            Log::info('Fin mise à jour ligne bon de commande', [
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
            Log::error('Erreur lors de la mise à jour de ligne bon de commande', [
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
    public function destroy(LigneBonCommande $ligne)
    {
        try {
            DB::beginTransaction();

            Log::info('Début suppression ligne bon de commande', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            $bonCommande = $ligne->bonCommande;
            $oldValues = $ligne->getAttributes();

            $ligne->delete();

            // Mise à jour du montant total du bon de commande
            $bonCommande->updateMontantTotal();

            DB::commit();

            Log::info('Fin suppression ligne bon de commande', [
                'user' => auth()->id(),
                'ligne_id' => $ligne->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ligne supprimée avec succès'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de ligne bon de commande', [
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
     * Met à jour plusieurs lignes en une fois
     */
    public function bulkUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            Log::info('Début mise à jour multiple de lignes bon de commande', [
                'user' => auth()->id()
            ]);

            $validated = $request->validate([
                'lignes' => 'required|array|min:1',
                'lignes.*.id' => 'required|exists:ligne_bon_commandes,id',
                'lignes.*.unite_mesure_id' => 'required|exists:unite_mesures,id',
                'lignes.*.prix_unitaire' => 'required|numeric|min:0',
                'lignes.*.taux_remise' => 'required|numeric|between:0,100'
            ]);

            $lignesMaj = collect();
            $bonCommandeIds = [];

            foreach ($validated['lignes'] as $ligneData) {
                $ligne = LigneBonCommande::findOrFail($ligneData['id']);
                $oldValues = $ligne->getAttributes();

                $ligne->update([
                    'unite_mesure_id' => $ligneData['unite_mesure_id'],
                    'prix_unitaire' => $ligneData['prix_unitaire'],
                    'taux_remise' => $ligneData['taux_remise']
                ]);

                $lignesMaj->push($ligne);
                $bonCommandeIds[] = $ligne->bon_commande_id;
            }

            // Mise à jour des montants totaux des bons de commande concernés
            foreach (array_unique($bonCommandeIds) as $bonCommandeId) {
                $bonCommande = BonCommande::find($bonCommandeId);
                if ($bonCommande) {
                    $bonCommande->updateMontantTotal();
                }
            }

            DB::commit();

            Log::info('Fin mise à jour multiple de lignes bon de commande', [
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
            Log::error('Erreur lors de la mise à jour multiple de lignes bon de commande', [
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

    /**
     * Récupère les lignes d'un bon de commande
     */
    public function getByBonCommande($bonCommandeId)
    {
        try {
            $lignes = LigneBonCommande::with(['article', 'uniteMesure'])
                ->where('bon_commande_id', $bonCommandeId)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $lignes
            ]);

        } catch (Exception $e) {
            Log::error('Erreur lors de la récupération des lignes', [
                'user' => auth()->id(),
                'bon_commande_id' => $bonCommandeId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des lignes'
            ], 500);
        }
    }
}
