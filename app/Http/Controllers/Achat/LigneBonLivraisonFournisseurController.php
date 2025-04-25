<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Achat\BonLivraisonFournisseur;
use App\Models\Achat\LigneBonLivraisonFournisseur;
use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LigneBonLivraisonFournisseurController extends Controller
{
    /**
     * Crée une nouvelle ligne de bon de livraison
     *
     * @param Request $request
     * @param BonLivraisonFournisseur $bonLivraison
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, BonLivraisonFournisseur $bonLivraison)
    {
        // Vérifier si le bon peut être modifié
        if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bon de livraison ne peut plus être modifié'
            ], 422);
        }

        try {
            // Valider les données
            $validated = $request->validate([
                'article_id' => 'required|exists:articles,id',
                'unite_mesure_id' => 'required|exists:unite_mesures,id',
                'quantite' => 'required|numeric|gt:0',
                'quantite_supplementaire' => 'nullable|numeric|min:0',
                'commentaire' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Créer la ligne
            $ligne = LigneBonLivraisonFournisseur::create([
                'livraison_id' => $bonLivraison->id,
                'article_id' => $validated['article_id'],
                'unite_mesure_id' => $validated['unite_mesure_id'],
                'quantite' => $validated['quantite'],
                'quantite_supplementaire' => $validated['quantite_supplementaire'] ?? 0,
                'commentaire' => $validated['commentaire'],
                'created_by' => Auth::id()
            ]);

            DB::commit();

            // Charger les relations pour la réponse
            $ligne->load(['article', 'uniteMesure']);

            return response()->json([
                'success' => true,
                'message' => 'Ligne ajoutée avec succès',
                'data' => $ligne
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout de la ligne'
            ], 500);
        }
    }

    /**
     * Met à jour une ligne de bon de livraison
     *
     * @param Request $request
     * @param BonLivraisonFournisseur $bonLivraison
     * @param LigneBonLivraisonFournisseur $ligne
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, BonLivraisonFournisseur $bonLivraison, LigneBonLivraisonFournisseur $ligne)
    {
        // Vérifier si le bon peut être modifié
        if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bon de livraison ne peut plus être modifié'
            ], 422);
        }

        // Vérifier si la ligne appartient au bon
        if ($ligne->livraison_id !== $bonLivraison->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cette ligne n\'appartient pas à ce bon de livraison'
            ], 422);
        }

        try {
            // Valider les données
            $validated = $request->validate([
                'unite_mesure_id' => 'required|exists:unite_mesures,id',
                'quantite' => 'required|numeric|gt:0',
                'quantite_supplementaire' => 'nullable|numeric|min:0',
                'commentaire' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Mettre à jour la ligne
            $ligne->update([
                'unite_mesure_id' => $validated['unite_mesure_id'],
                'quantite' => $validated['quantite'],
                'quantite_supplementaire' => $validated['quantite_supplementaire'] ?? 0,
                'commentaire' => $validated['commentaire'],
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            // Charger les relations pour la réponse
            $ligne->load(['article', 'uniteMesure']);

            return response()->json([
                'success' => true,
                'message' => 'Ligne mise à jour avec succès',
                'data' => $ligne
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour de la ligne'
            ], 500);
        }
    }

    /**
     * Supprime une ligne de bon de livraison
     *
     * @param BonLivraisonFournisseur $bonLivraison
     * @param LigneBonLivraisonFournisseur $ligne
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(BonLivraisonFournisseur $bonLivraison, LigneBonLivraisonFournisseur $ligne)
    {
        // Vérifier si le bon peut être modifié
        if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bon de livraison ne peut plus être modifié'
            ], 422);
        }

        // Vérifier si la ligne appartient au bon
        if ($ligne->livraison_id !== $bonLivraison->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cette ligne n\'appartient pas à ce bon de livraison'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $ligne->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ligne supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression de la ligne'
            ], 500);
        }
    }

    /**
     * Récupère les unités de mesure disponibles pour un article
     *
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnitesMesure(Article $article)
    {
        $unites = UniteMesure::whereHas('conversions', function ($query) use ($article) {
            $query->whereHas('familleArticle', function ($q) use ($article) {
                $q->where('id', $article->famille_article_id);
            });
        })->get();

        return response()->json([
            'success' => true,
            'data' => $unites
        ]);
    }
}
