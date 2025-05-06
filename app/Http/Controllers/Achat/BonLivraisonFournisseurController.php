<?php

namespace App\Http\Controllers\Achat;

use App\Models\Achat\BonLivraisonFournisseur;
use App\Models\Achat\{FactureFournisseur, LigneBonLivraisonFournisseur};
use App\Models\Parametre\{PointDeVente, Vehicule, Chauffeur, Depot};
use App\Http\Controllers\Controller;
use App\Models\Stock\StockDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ServiceStockEntree;
use Exception;
use Illuminate\Validation\ValidationException;

class BonLivraisonFournisseurController extends Controller
{

    private $serviceStockEntree;

    public function __construct(ServiceStockEntree $serviceStockEntree)
    {
        $this->serviceStockEntree = $serviceStockEntree;
    }
    /**
     * Affiche la liste des bons de livraison fournisseur
     */
    /**
     * Affiche la liste des bons de livraison fournisseur
     */

    public function index()
    {
        // Récupération des bons de livraison avec leurs relations
        $livraisons = BonLivraisonFournisseur::with([
            'fournisseur',
            'pointDeVente',
            'vehicule',
            'chauffeur',
            'lignes'
        ])->orderBy('created_at', 'desc')
            ->get();

        // Récupération des factures validées sans bon de livraison ou partiellement livrées
        $factures = FactureFournisseur::with('fournisseur')
            ->whereNotNull('validated_at')
            ->whereNull('rejected_at')
            ->where(function ($query) {
                $query->where('statut_livraison', 'NON_LIVRE')
                    ->orWhere('statut_livraison', 'PARTIELLEMENT_LIVRE');
            })
            ->orderBy('date_facture', 'desc')
            ->get();

        // Récupération des véhicules actifs
        $vehicules = Vehicule::where('statut', true)
            ->orderBy('matricule')
            ->get();

        // Récupération des chauffeurs actifs
        $chauffeurs = Chauffeur::where('statut', true)
            ->orderBy('nom_chauf')
            ->get();

        $depots = Depot::where('actif', true)->get();

        return view('pages.achat.livraison-frs.index', compact(
            'livraisons',
            'factures',
            'vehicules',
            'chauffeurs',
            'depots'
        ));
    }

    /**
     * Affiche le formulaire de création
     */

    public function create()
    {
        // Récupérer les données nécessaires pour le formulaire
        $factures = FactureFournisseur::where('statut', 'validee')
            ->whereDoesntHave('bonLivraison')
            ->get();
        $pointsVente = PointDeVente::all();
        $vehicules = Vehicule::where('actif', true)->get();
        $chauffeurs = Chauffeur::where('actif', true)->get();

        return view('achat.livraisons.create', compact(
            'factures',
            'pointsVente',
            'vehicules',
            'chauffeurs'
        ));
    }

    /**
     * Enregistre un nouveau bon de livraison
     */
    public function store(Request $request)
    {
        try {
            // Récupération du point de vente de l'utilisateur connecté
            $point_de_vente_id = Auth::user()->point_de_vente_id;

            if (!$point_de_vente_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être rattaché à un point de vente pour créer un bon de livraison'
                ], 422);
            }

            // Récupération de la facture pour avoir le fournisseur_id
            $facture = FactureFournisseur::findOrFail($request->facture_id);
            $fournisseur_id = $facture->fournisseur_id;

            // Validation des données
            $validated = $request->validate([
                'facture_id' => 'required|exists:facture_fournisseurs,id',
                'date_livraison' => 'required|date',
                'depot_id' => 'required|exists:depots,id',
                'vehicule_id' => 'required|exists:vehicules,id',
                'chauffeur_id' => 'required|exists:chauffeurs,id',
                'commentaire' => 'nullable|string',
                'lignes' => 'required|array',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.unite_mesure_id' => 'required|exists:unite_mesures,id',
                'lignes.*.quantite' => 'required|numeric|min:0',
                'lignes.*.quantite_supplementaire' => 'nullable|numeric|min:0',
                'lignes.*.unite_id' => 'nullable|exists:unite_mesures,id'
            ]);

            // Vérification des quantités
            $hasQuantity = collect($validated['lignes'])->some(function ($ligne) {
                return ($ligne['quantite'] + ($ligne['quantite_supplementaire'] ?? 0)) > 0;
            });

            if (!$hasQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins une ligne doit avoir une quantité supérieure à 0'
                ], 422);
            }


            DB::beginTransaction();

            // Création du bon de livraison avec le point de vente et le fournisseur automatiques
            $bonLivraison = BonLivraisonFournisseur::create([
                'code' => $this->generateCode(),
                'date_livraison' => $validated['date_livraison'],
                'facture_id' => $validated['facture_id'],
                'point_de_vente_id' => $point_de_vente_id,
                'fournisseur_id' => $fournisseur_id, // Ajout du fournisseur_id
                'depot_id' => $validated['depot_id'],
                'vehicule_id' => $validated['vehicule_id'],
                'chauffeur_id' => $validated['chauffeur_id'],
                'commentaire' => $validated['commentaire'],
                'created_by' => Auth::id()
            ]);


            // Création des lignes du bon de livraison
            foreach ($validated['lignes'] as $ligne) {
                if (($ligne['quantite'] + ($ligne['quantite_supplementaire'] ?? 0)) > 0) {
                    LigneBonLivraisonFournisseur::create([
                        'livraison_id' => $bonLivraison->id,  // C'était 'bon_livraison_id', la bonne colonne est 'livraison_id'
                        'article_id' => $ligne['article_id'],
                        'unite_mesure_id' => $ligne['unite_mesure_id'],
                        'quantite' => $ligne['quantite'],
                        'quantite_supplementaire' => $ligne['quantite_supplementaire'] ?? 0,
                        'unite_supplementaire_id' => $ligne['unite_id'],
                        'created_by' => Auth::id()
                    ]);
                }
            }

            // Mise à jour du statut de la facture
            // $totalQuantiteFacture = $facture->lignes->sum('quantite');
            // $totalQuantiteLivree = collect($validated['lignes'])->sum('quantite');

            // if ($totalQuantiteLivree >= $totalQuantiteFacture) {
            //     $facture->statut_livraison = 'LIVRE';
            // } else {
            //     $facture->statut_livraison = 'PARTIELLEMENT_LIVRE';
            // }

            $facture->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison créé avec succès',
                'data' => $bonLivraison->load(['pointDeVente', 'fournisseur', 'depot', 'vehicule', 'chauffeur', 'lignes'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création bon livraison:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du bon de livraison',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    public function show(BonLivraisonFournisseur $bonLivraison)
    {
        $bonLivraison->load([
            'fournisseur',
            'facture.lignes.article',
            'lignes.article.uniteMesure',
            'lignes.uniteMesure',
            'lignes.uniteSupplementaire',
            'vehicule',
            'depot',
            'chauffeur'
        ]);

        return response()->json([
            'success' => true,
            'livraison' => $bonLivraison,
            'pointsVente' => PointDeVente::all(),
            'vehicules' => Vehicule::where('statut', true)->get(),
            'chauffeurs' => Chauffeur::where('statut', true)->get()
        ]);
    }

    /**
     * Récupère les données pour l'édition
     */
    public function edit(BonLivraisonFournisseur $bonLivraison)
    {
        if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bon de livraison ne peut plus être modifié'
            ], 422);
        }

        $bonLivraison->load([
            'fournisseur',
            'facture.lignes.article',
            'lignes'
        ]);

        return response()->json([
            'success' => true,
            'livraison' => $bonLivraison,
            'pointsVente' => PointDeVente::all(),
            'vehicules' => Vehicule::where('actif', true)->get(),
            'chauffeurs' => Chauffeur::where('actif', true)->get()
        ]);
    }

    /**
     * Met à jour un bon de livraison
     */

    public function update(Request $request, BonLivraisonFournisseur $bonLivraison)
    {
        if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bon de livraison ne peut plus être modifié'
            ], 422);
        }

        try {
            $validated = $request->validate([
                'date_livraison' => 'required|date',
                'point_de_vente_id' => 'required|exists:point_de_ventes,id',
                'depot_id' => 'required|exists:point_de_ventes,id',
                'vehicule_id' => 'nullable|exists:vehicules,id',
                'chauffeur_id' => 'nullable|exists:chauffeurs,id',
                'commentaire' => 'nullable|string',
                'lignes' => 'required|array',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.unite_mesure_id' => 'required|exists:unite_mesures,id',
                'lignes.*.quantite' => 'required|numeric|min:0',
                'lignes.*.quantite_supplementaire' => 'nullable|numeric|min:0',
                'lignes.*.unite_id' => 'nullable|exists:unite_mesures,id'
            ]);

            DB::beginTransaction();

            // Mise à jour du bon de livraison
            $bonLivraison->update([
                'date_livraison' => $validated['date_livraison'],
                'point_de_vente_id' => $validated['point_de_vente_id'],
                'depot_id' => $validated['depot_id'],
                'vehicule_id' => $validated['vehicule_id'],
                'chauffeur_id' => $validated['chauffeur_id'],
                'commentaire' => $validated['commentaire'],
                'updated_by' => Auth::id()
            ]);

            // Supprimer les anciennes lignes
            $bonLivraison->lignes()->delete();

            // Créer les nouvelles lignes
            foreach ($validated['lignes'] as $ligne) {
                if (($ligne['quantite'] + ($ligne['quantite_supplementaire'] ?? 0)) > 0) {
                    LigneBonLivraisonFournisseur::create([
                        'livraison_id' => $bonLivraison->id,
                        'article_id' => $ligne['article_id'],
                        'unite_mesure_id' => $ligne['unite_mesure_id'],
                        'quantite' => $ligne['quantite'],
                        'quantite_supplementaire' => $ligne['quantite_supplementaire'] ?? 0,
                        'unite_supplementaire_id' => $ligne['unite_id'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valide un bon de livraison
     */
    public function validate_bon(BonLivraisonFournisseur $bonLivraison)
    {
        if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bon de livraison a déjà été traité'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Charger toutes les relations nécessaires
            $bonLivraison->load([
                'lignes.article.uniteMesure',
                'facture.lignes',
                'fournisseur'
            ]);

            // Log pour vérifier les données chargées
            \Log::debug('Données du bon de livraison:', [
                'bon_livraison' => $bonLivraison->toArray(),
                'lignes' => $bonLivraison->lignes->toArray()
            ]);

            // return response()->json($bonLivraison->lignes[0]->quantite);

            // Récupérer les prix unitaires de la facture
            $prixUnitaires = [];
            foreach ($bonLivraison->facture->lignes as $ligneFact) {
                $prixUnitaires[$ligneFact->article_id] = $ligneFact->prix_unitaire;

                // Vérifier si une ligne correspondante existe dans $bonLivraison->lignes
                $ligneBonLivraison = $bonLivraison->lignes->where('article_id', $ligneFact->article_id)->first();

                // Mettre à jour les données de la ligne de facture avec la quantité livrée
                $QteTotal = 0;
                if ($ligneBonLivraison) {
                    $QteTotal += $ligneBonLivraison->getQuantiteTotale();
                }

                $ligneFact->update([
                    'quantite_livree' => $ligneFact->quantite_livree + $QteTotal,
                ]);

                // Log des prix unitaires
                \Log::debug("Prix unitaire pour article {$ligneFact->article_id}: {$ligneFact->prix_unitaire}");
            }

            // Préparer les entrées en stock
            $entrees = [];
            foreach ($bonLivraison->lignes as $ligne) {
                // Vérifier les données de l'article
                if (!$ligne->article) {
                    throw new Exception("Article non trouvé pour la ligne ID: {$ligne->id}");
                }

                if (!$ligne->article->uniteMesure) {
                    throw new Exception("Unité de mesure non définie pour l'article: {$ligne->article->code_article}");
                }

                // Vérifier si le prix unitaire existe
                if (!isset($prixUnitaires[$ligne->article_id])) {
                    throw new Exception("Prix unitaire non trouvé pour l'article : " . $ligne->article->code_article);
                }

                // Log des données de conversion
                \Log::debug("Données de ligne:", [
                    'article_id' => $ligne->article_id,
                    'unite_mesure_id' => $ligne->unite_mesure_id,
                    'unite_base_id' => $ligne->article->unite_mesure_id,
                    'quantite' => $ligne->getQuantiteTotale()
                ]);

                $entrees[] = [
                    'depot_id' => $bonLivraison->depot_id,
                    'article_id' => $ligne->article_id,
                    'unite_mesure_id' => $ligne->unite_mesure_id,
                    'quantite' => $ligne->getQuantiteTotale(),
                    'prix_unitaire' => $prixUnitaires[$ligne->article_id],
                    'date_mouvement' => $bonLivraison->date_livraison,
                    'reference_mouvement' => $bonLivraison->code,
                    'document_type' => 'BON_LIVRAISON_FOURNISSEUR',
                    'document_id' => $bonLivraison->id,
                    'notes' => $bonLivraison->commentaire,
                    'user_id' => Auth::id()
                ];

                // attachement de l'article au dépot
                StockDepot::create([
                    'depot_id' => $bonLivraison->depot_id,
                    'article_id' => $ligne->article_id,
                    'quantite_reelle' => $ligne->quantite,
                    'stock_minimum' => $ligne->article->stock_minimum,
                    'stock_maximum' => $ligne->article->stock_maximum,
                    'emplacement' => $ligne->article->emplacement_stock,
                    'user_id' => auth()->user()->id,
                    'unite_mesure_id' => $ligne->unite_mesure_id,
                ]);
            }

            // Log des entrées préparées
            \Log::debug('Entrées préparées:', ['entrees' => $entrees]);

            // Traiter les entrées en stock
            $resultatStock = $this->serviceStockEntree->traiterEntreesMultiples($entrees);

            // dd($resultatStock);

            \Log::debug('Résultat traitement stock:', $resultatStock);

            if (!$resultatStock['succes']) {
                throw new Exception("Erreur lors de la mise à jour du stock : " . $resultatStock['message']);
            }

            // Mise à jour du bon de livraison
            $bonLivraison->update([
                'validated_at' => now(),
                'validated_by' => Auth::id()
            ]);

            // Mise à jour du statut de la facture
            $totalQuantiteFacture = $bonLivraison->facture->lignes->sum('quantite');
            $totalQuantiteLivree = $bonLivraison->facture->lignes->sum('quantite_livree');

            $bonLivraison->facture->update([
                'statut_livraison' => $totalQuantiteLivree >= $totalQuantiteFacture ? 'LIVRE' : 'PARTIELLEMENT_LIVRE'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison validé et stocks mis à jour avec succès',
                'details' => [
                    'mouvements' => $resultatStock['resultats'],
                    'conversions' => collect($resultatStock['resultats'])->filter(function ($res) {
                        return isset($res['quantite_origine']) && $res['quantite_origine'] !== $res['quantite_base'];
                    })
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Erreur validation bon livraison:', [
                'bon_livraison_id' => $bonLivraison->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'details' => $e instanceof ValidationException ? $e->errors() : null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la validation: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }

    /**
     * Rejette un bon de livraison
     */

    public function reject(Request $request, BonLivraisonFournisseur $bonLivraison)
    {
        if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce bon de livraison a déjà été traité'
            ], 422);
        }

        try {
            $validated = $request->validate([
                'motif_rejet' => 'required|string|max:255'
            ]);

            DB::beginTransaction();

            $bonLivraison->update([
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'motif_rejet' => $validated['motif_rejet']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison rejeté avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du rejet'
            ], 500);
        }
    }

    /**
     * Génère un code unique pour le bon de livraison
     */

    private function generateCode()
    {
        $prefix = 'BLF';
        $date = Carbon::now()->format('ymd');
        $lastBon = BonLivraisonFournisseur::withTrashed()
            ->where('code', 'like', "$prefix$date%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastBon) {
            $lastNumber = intval(substr($lastBon->code, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    /**
     * Supprime un bon de livraison
     */
    public function destroy(BonLivraisonFournisseur $bonLivraison)
    {
        try {
            if ($bonLivraison->validated_at || $bonLivraison->rejected_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce bon de livraison ne peut pas être supprimé car il a déjà été traité'
                ], 422);
            }

            DB::beginTransaction();

            // Suppression des lignes associées
            $bonLivraison->lignes()->delete();
            // Suppression du bon de livraison
            $bonLivraison->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur suppression bon livraison:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ], 500);
        }
    }
}
