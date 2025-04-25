<?php

namespace App\Http\Controllers\Achat;

use App\Models\Achat\{
    FactureFournisseur,
    LigneFactureFournisseur,
    BonCommande
};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FactureFournisseurController extends Controller
{

    /**
     * Affiche la liste des factures
     */
    public function index(Request $request)
    {
        // Construction de la requête de base avec les relations nécessaires
        $query = FactureFournisseur::with([
            'bonCommande',
            'pointVente',
            'fournisseur',
            'lignes.article',
            'lignes.uniteMesure'
        ]);

        // Filtres
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('date_facture', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('date_facture', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereYear('date_facture', Carbon::now()->year)
                        ->whereMonth('date_facture', Carbon::now()->month);
                    break;
            }
        }

        // FILTRE PAR TYPE DE FACTURE
        if ($request->filled('type')) {
            switch ($request->type) {
                case 'SIMPLE':
                    $query->where("type_facture", 'SIMPLE');
                    break;
                default:
                    $query->where("type_facture", "NORMALISE");
                    break;
            }
        }

        if ($request->filled('status')) {
            $query->where('statut_livraison', $request->status);
        }

        if ($request->filled('payment')) {
            $query->where('statut_paiement', $request->payment);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Récupération des factures paginées
        // $factures = $query->latest('date_facture')->paginate(10);
        $factures = $query->latest('date_facture')->get();

        // Calcul des statistiques pour le header
        $statsQuery = FactureFournisseur::query();

        // Si un filtre de période est actif, l'appliquer aux stats aussi
        if ($request->filled('period')) {
            switch ($request->period) {
                case 'today':
                    $statsQuery->whereDate('date_facture', Carbon::today());
                    break;
                case 'week':
                    $statsQuery->whereBetween('date_facture', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $statsQuery->whereYear('date_facture', Carbon::now()->year)
                        ->whereMonth('date_facture', Carbon::now()->month);
                    break;
            }
        }

        // Statistiques
        $nombreFactures = $statsQuery->count();
        $montantTotal = $statsQuery->sum('montant_ttc');
        $montantMoyen = $nombreFactures > 0 ? $montantTotal / $nombreFactures : 0;
        $facturesNonPayees = $statsQuery->where('statut_paiement', 'NON_PAYE')->count();

        // Bons de commande disponibles pour nouvelle facture
        $bonsCommande = BonCommande::whereDoesntHave('factures')
            ->whereNotNull('validated_at')
            ->with(['pointVente', 'fournisseur'])
            ->get();


        // Retour de la vue avec toutes les données nécessaires
        return view('pages.achat.facture-frs.index', [
            // Données principales
            'factures' => $factures,
            'bonsCommande' => $bonsCommande,

            // Données du header
            'date' => Carbon::now()->format('d/m/Y'),
            'nombreFactures' => $nombreFactures,
            'montantTotal' => $montantTotal,
            'montantMoyen' => $montantMoyen,
            'facturesNonPayees' => $facturesNonPayees,

            // Données supplémentaires pour la vue
            'filtres' => [
                'period' => $request->period,
                'status' => $request->status,
                'payment' => $request->payment,
                'search' => $request->search
            ]
        ]);
    }

    /**
     * Enregistre une nouvelle facture
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validation de base
            $request->validate([
                'code' => 'required|unique:facture_fournisseurs,code',
                'date_facture' => 'required|date',
                'bon_commande_id' => 'required|exists:bon_commandes,id',
                'point_de_vente_id' => 'required|exists:point_de_ventes,id',
                'fournisseur_id' => 'required|exists:fournisseurs,id',
                'type_facture' => 'required|in:SIMPLE,NORMALISE',
                'articles' => 'required|array',
                'articles.*.quantite' => 'required|numeric|min:0',
                'articles.*.prix_unitaire' => 'required|numeric|min:0',
            ]);

            // Création de la facture avec les montants par défaut
            $facture = new FactureFournisseur();
            $facture->code = $request->code;
            $facture->date_facture = $request->date_facture;
            $facture->bon_commande_id = $request->bon_commande_id;
            $facture->point_de_vente_id = $request->point_de_vente_id;
            $facture->fournisseur_id = $request->fournisseur_id;
            $facture->type_facture = $request->type_facture;
            $facture->commentaire = $request->commentaire;
            $facture->taux_tva = $request->type_facture === 'NORMALISE' ? ($request->taux_tva ?? 0) : 0;
            $facture->taux_aib = $request->type_facture === 'NORMALISE' ? ($request->taux_aib ?? 0) : 0;
            // $facture->montant_tva = $request->commentaire;
            $facture->save();

            // Création des lignes
            foreach ($request->articles as $articleId => $data) {
                $ligne = new LigneFactureFournisseur();
                $ligne->facture_id = $facture->id;
                $ligne->article_id = $articleId;
                $ligne->unite_mesure_id = $data['unite_mesure_id'];
                $ligne->quantite = $data['quantite'];
                $ligne->prix_unitaire = $data['prix_unitaire'];
                $ligne->taux_tva = $request->type_facture === 'NORMALISE' ? ($request->taux_tva ?? 0) : 0;;
                $ligne->taux_aib = $request->type_facture === 'NORMALISE' ? ($request->taux_aib ?? 0) : 0;
                $ligne->save();
            }

            // Mise à jour des montants
            $facture->updateMontants();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Facture créée avec succès',
                'data' => $facture
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la facture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'une facture
     */
    public function show(FactureFournisseur $facture)
    {
        $facture->load(['bonCommande', 'pointVente', 'fournisseur', 'lignes.article', 'lignes.uniteMesure']);

        return response()->json([
            'success' => true,
            'data' => $facture
        ]);
    }

    /**
     * Met à jour une facture
     */
    public function update(Request $request, FactureFournisseur $facture)
    {
        try {
            // if (!$facture->isModifiable()) {
            //     throw new \Exception('Cette facture ne peut plus être modifiée');
            // }

            // dd($request->commentaireMod);

            DB::beginTransaction();

            // Validation similaire au store
            $request->validate([
                'date_facture' => 'required|date',
                'articles' => 'required|array',
                'articles.*.quantite' => 'required|numeric|min:0',
                'articles.*.prix_unitaire' => 'required|numeric|min:0',
                'articles.*.taux_tva' => 'required|numeric|between:0,100',
                'articles.*.taux_aib' => 'required|numeric|between:0,100'
            ]);

            // Mise à jour des informations de base
            $facture->date_facture = $request->date_facture;
            $facture->commentaire = $request->commentaireMod;
            $facture->taux_tva = $request->type_facture === 'NORMALISE' ? ($request->taux_tva ?? 0) : 0;
            $facture->taux_aib = $request->type_facture === 'NORMALISE' ? ($request->taux_aib ?? 0) : 0;
            $facture->save();

            // Mise à jour des lignes
            foreach ($request->articles as $data) {
                $ligne = $facture->lignes()->where('article_id', $data['article_id'])->first();
                if ($ligne) {
                    $ligne->update([
                        'quantite' => $data['quantite'],
                        'prix_unitaire' => $data['prix_unitaire'],
                        'taux_tva' =>  $request->type_facture === 'NORMALISE' ? ($request->taux_tva ?? 0) : 0,
                        'taux_aib' => $request->type_facture === 'NORMALISE' ? ($request->taux_aib ?? 0) : 0
                    ]);
                }
            }

            // Mise à jour des montants
            $facture->updateMontants();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Facture mise à jour avec succès',
                'data' => $facture
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la facture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime une facture
     */
    public function destroy(FactureFournisseur $facture)
    {
        try {
            // if (!$facture->isModifiable()) {
            //     throw new \Exception('Cette facture ne peut plus être supprimée');
            // }

            DB::beginTransaction();

            // Suppression des lignes
            $facture->lignes()->delete();

            // Suppression de la facture
            $facture->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Facture supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la facture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valide une facture
     */
    public function validated(FactureFournisseur $facture)
    {
        try {
            DB::beginTransaction();

            if ($facture->isValidated()) {
                throw new \Exception('Cette facture est déjà validée');
            }

            // if (!$facture->isModifiable()) {
            //     throw new \Exception('Cette facture ne peut plus être validée');
            // }

            $facture->validated_by = auth()->id();
            $facture->validated_at = now();
            $facture->save();

            foreach ($facture->lignes as $ligne) {
                $ligne->validated_by = auth()->id();
                $ligne->validated_at = now();
                $ligne->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Facture validée avec succès']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    /**
     * Met à jour le statut de livraison
     */
    public function updateDeliveryStatus(Request $request, FactureFournisseur $facture)
    {
        try {
            $request->validate([
                'statut_livraison' => 'required|in:NON_LIVRE,PARTIELLEMENT_LIVRE,LIVRE'
            ]);

            $facture->statut_livraison = $request->statut_livraison;
            $facture->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de livraison mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour le statut de paiement
     */
    public function updatePaymentStatus(Request $request, FactureFournisseur $facture)
    {
        try {
            $request->validate([
                'statut_paiement' => 'required|in:NON_PAYE,PARTIELLEMENT_PAYE,PAYE'
            ]);

            $facture->statut_paiement = $request->statut_paiement;
            $facture->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de paiement mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectFacture(Request $request, $id)
    {
        $facture = FactureFournisseur::findorFail($id);

        $request->validate([
            'motif_rejet' => 'required|string'
        ]);

        $facture->motif_rejet = $request->motif_rejet;
        $facture->rejected_by = Auth::id();
        $facture->rejected_at = now();

        $facture->save();

        return response()->json([
            'success' => true,
            'message' => 'Facture fournisseur rejetée avec succès',
            'data' => $facture
        ]);
    }
}
