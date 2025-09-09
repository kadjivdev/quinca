<?php

namespace App\Http\Controllers\Achat;

use App\Models\Achat\{BonLivraisonFournisseur, FactureFournisseur, LigneBonLivraisonFournisseur, LigneFactureFournisseur};
use App\Models\Parametre\{PointDeVente, Vehicule, Chauffeur, Depot};
use App\Http\Controllers\Controller;
use App\Models\Catalogue\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ServiceStockEntree;
use Exception;
use Illuminate\Support\Facades\Log;
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
            ->whereNotNull("validated_by")
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
                'lignes.*.ligne_id' => 'required|exists:ligne_facture_fournisseurs,id',
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

            Log::debug("Les lignes entrante", ["data" => $validated['lignes']]);

            Log::debug("Les lignes de la facture avant conversion", ["data" => $bonLivraison->facture->lignes]);

            // Création des lignes du bon de livraison
            foreach ($validated['lignes'] as $ligne) {
                /**
                 * Verification de la quantité supplementaire
                 * et l'unité de mesure du supplement
                 */
                if ($ligne['quantite_supplementaire'] && !$ligne['unite_id']) {
                    $article = Article::find($ligne["article_id"]);
                    throw new Exception("Ligne concernée $article->code_article : Pour une valeur non nulle de la quantité supplementaire, l'unité de mesure du supplement est réquise!");
                }

                if (!$ligne['quantite_supplementaire'] && $ligne['unite_id']) {
                    $article = Article::find($ligne["article_id"]);
                    throw new Exception("Ligne concernée $article->code_article : Pour une valeur non nulle de l'unité de mesure du supplement, la quantité supplementaire est réquise!");
                }

                if (($ligne['quantite'] + ($ligne['quantite_supplementaire'] ?? 0)) > 0) {
                    LigneBonLivraisonFournisseur::create([
                        'livraison_id' => $bonLivraison->id,  // C'était 'bon_livraison_id', la bonne colonne est 'livraison_id'
                        'article_id' => $ligne['article_id'],
                        'unite_mesure_id' => $ligne['unite_mesure_id'],
                        'quantite' => $ligne['quantite'],
                        'quantite_supplementaire' => $ligne['quantite_supplementaire'] ?? 0,
                        'unite_supplementaire_id' => $ligne['unite_id'] ?? null,
                        'created_by' => Auth::id()
                    ]);
                }

                $ligneFacture = LigneFactureFournisseur::find($ligne['ligne_id']);
                if (!$ligneFacture) {
                    throw new Exception(sprintf("Le détail d'ID %s de la facture concernée n'existe pas", $ligne['ligne_id']));
                }

                /**
                 * Quantité supplementaire convertie en 
                 * unité de base de la ligne
                 */

                $QteBaseSupplementaire = 0;
                if (isset($ligne['unite_id'])) {
                    $QteBaseSupplementaire = $ligneFacture
                        ->getQuantiteTotaleSupplement(
                            $ligne['unite_id'], //unité de supplement entrant
                            $ligne['unite_mesure_id'], //unité de destination(unite de base)
                            $ligne['quantite_supplementaire']
                        );
                }

                /**
                 * Actualisation de la quantite_livree_simple
                 */
                $QteLivreSimple = $ligneFacture->quantite_livree_simple + $ligne['quantite'] + $QteBaseSupplementaire;
                Log::info("Qte totale livre simple", ["data" => $QteLivreSimple]);

                /**
                 * 
                 */
                $ligneFacture->update(["quantite_livree_simple" => $QteLivreSimple]);
            }

            $facture->save();

            Log::debug("Les lignes de la facture après conversion", ["data" => $bonLivraison->facture->lignes]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bon de livraison créé avec succès',
                'data' => $bonLivraison->load(['pointDeVente', 'fournisseur', 'depot', 'vehicule', 'chauffeur', 'lignes'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur de validation:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création bon livraison:', [
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
            'facture.lignes.article', //.uniteMesure.uniteMesureBase
            'facture.lignes.uniteMesure', //.uniteMesure.uniteMesureBase
            'facture.lignes.uniteMesureBase', //.uniteMesure.uniteMesureBase
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
            // $validated = $request->validate([
            //     'date_livraison' => 'required|date',
            //     'point_de_vente_id' => 'required|exists:point_de_ventes,id',
            //     'depot_id' => 'required|exists:depots,id',
            //     'vehicule_id' => 'nullable|exists:vehicules,id',
            //     'chauffeur_id' => 'nullable|exists:chauffeurs,id',
            //     'commentaire' => 'nullable|string',
            //     'lignes' => 'required|array',
            //     'lignes.*.article_id' => 'required|exists:articles,id',
            //     'lignes.*.unite_mesure_id' => 'required|exists:unite_mesures,id',
            //     'lignes.*.quantite' => 'required|numeric|min:0',
            //     'lignes.*.quantite_supplementaire' => 'nullable|numeric|min:0',
            //     'lignes.*.unite_id' => 'nullable|exists:unite_mesures,id'
            // ]);

            // Récupération de la facture pour avoir le fournisseur_id
            $facture = FactureFournisseur::findOrFail($request->facture_id);
            $fournisseur_id = $facture->fournisseur_id;

            // Validation des données
            $validated = $request->validate([
                // 'facture_id' => 'required|exists:facture_fournisseurs,id',
                'point_de_vente_id' => 'required|exists:point_de_ventes,id',
                'date_livraison' => 'required|date',
                'depot_id' => 'required|exists:depots,id',
                'vehicule_id' => 'required|exists:vehicules,id',
                'chauffeur_id' => 'required|exists:chauffeurs,id',
                'commentaire' => 'nullable|string',
                'lignes' => 'required|array',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.ligne_id' => 'required|exists:ligne_facture_fournisseurs,id',
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

            // Mise à jour du bon de livraison
            $bonLivraison->update([
                'date_livraison' => $validated['date_livraison'],
                'point_de_vente_id' => $validated['point_de_vente_id'],
                'fournisseur_id' => $fournisseur_id, // Ajout du fournisseur_id
                'depot_id' => $validated['depot_id'],
                'vehicule_id' => $validated['vehicule_id'],
                'chauffeur_id' => $validated['chauffeur_id'],
                'commentaire' => $validated['commentaire'],
                'updated_by' => Auth::id()
            ]);


            // Supprimer les anciennes lignes
            $bonLivraison->lignes()->delete();

            // Création des lignes du bon de livraison
            foreach ($validated['lignes'] as $ligne) {
                /**
                 * Verification de la quantité supplementaire
                 * et l'unité de mesure du supplement
                 */
                if ($ligne['quantite_supplementaire'] && !$ligne['unite_id']) {
                    $article = Article::find($ligne["article_id"]);
                    throw new Exception("Ligne concernée $article->code_article : Pour une valeur non nulle de la quantité supplementaire, l'unité de mesure du supplement est réquise!");
                }

                if (!$ligne['quantite_supplementaire'] && $ligne['unite_id']) {
                    $article = Article::find($ligne["article_id"]);
                    throw new Exception("Ligne concernée $article->code_article : Pour une valeur non nulle de l'unité de mesure du supplement, la quantité supplementaire est réquise!");
                }

                if (($ligne['quantite'] + ($ligne['quantite_supplementaire'] ?? 0)) > 0) {
                    LigneBonLivraisonFournisseur::create([
                        'livraison_id' => $bonLivraison->id,  // C'était 'bon_livraison_id', la bonne colonne est 'livraison_id'
                        'article_id' => $ligne['article_id'],
                        'unite_mesure_id' => $ligne['unite_mesure_id'],
                        'quantite' => $ligne['quantite'],
                        'quantite_supplementaire' => $ligne['quantite_supplementaire'] ?? 0,
                        'unite_supplementaire_id' => $ligne['unite_id'] ?? null,
                        'created_by' => Auth::id()
                    ]);
                }

                $ligneFacture = LigneFactureFournisseur::find($ligne['ligne_id']);
                if (!$ligneFacture) {
                    throw new Exception(sprintf("Le détail d'ID %s de la facture concernée n'existe pas", $ligne['ligne_id']));
                }

                /**
                 * Quantité supplementaire convertie en 
                 * unité de base de la ligne
                 */

                $QteBaseSupplementaire = 0;
                if (isset($ligne['unite_id'])) {
                    $QteBaseSupplementaire = $ligneFacture
                        ->getQuantiteTotaleSupplement(
                            $ligne['unite_id'], //unité de supplement entrant
                            $ligne['unite_mesure_id'], //unité de destination(unite de base)
                            $ligne['quantite_supplementaire']
                        );
                }

                /**
                 * Actualisation de la quantite_livree_simple
                 */
                $QteLivreSimple = $ligne['quantite'] + $QteBaseSupplementaire;
                Log::info("Qte totale livre simple", ["data" => $QteLivreSimple]);

                /**
                 * 
                 */
                $ligneFacture->update(["quantite_livree_simple" => $QteLivreSimple]);
            }

            $facture->save();

            Log::debug("Les lignes de la facture après conversion", ["data" => $bonLivraison->facture->lignes]);

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

            // Récupérer les prix unitaires de la facture
            $prixUnitaires = [];
            foreach ($bonLivraison->facture->lignes as $ligneFact) {
                $prixUnitaires[$ligneFact->article_id] = $ligneFact->prix_unitaire;

                // Vérifier si une ligne correspondante existe dans $bonLivraison->lignes
                // $ligneBonLivraison = $bonLivraison->lignes->where('article_id', $ligneFact->article_id)->first();

                // Mettre à jour les données de la ligne de facture avec la quantité livrée
                // $QteTotal = 0;
                // if ($ligneBonLivraison) {
                //     $QteTotal += $ligneBonLivraison->getQuantiteTotale();
                // }

                Log::info("Ligne facture avant update", ["data" => $ligneFact]);
                /**Qte total livré dans l'unité de base de la ligne de facture */

                $stockToAdd = $ligneFact->quantite_livree ?
                    $ligneFact->quantite_livree_simple - $ligneFact->quantite_livree : $ligneFact->quantite_livree_simple;

                $ligneFact->update([
                    'quantite_livree' => $ligneFact->quantite_livree  + $ligneFact->quantite_livree_simple,
                ]);

                Log::info("QTe ajouté", ["data" => $ligneFact->quantite_livree_simple]);
                Log::info("QTe Total", ["data" => $ligneFact->quantite_livree]);
                Log::info("Ligne facture après update", ["data" => $ligneFact]);

                if ($ligneFact->quantite_livree > $ligneFact->quantite_base) {
                    throw new \Exception("La quantité livrée pour l'article {$ligneFact->article?->code_article} dépasse la quantité facturée.");
                }

                // Log des prix unitaires
                Log::debug("Prix unitaire pour article {$ligneFact->article_id}: {$ligneFact->prix_unitaire}");
            }

            // Préparer les entrées en stock
            $entrees = [];
            foreach ($bonLivraison->lignes as $ligne) {

                $ligneFact = $bonLivraison->facture
                    ->lignes()
                    ->firstWhere("article_id", $ligne->article_id);

                // Log::debug("Ligne facture concernée", ["data" => $ligneFact]);

                // Vérifier les données de l'article
                if (!$ligne->article) {
                    throw new Exception("Article non trouvé pour la ligne ID: {$ligne->id}");
                }

                if (!$ligne->article->uniteMesure) {
                    throw new Exception("Unité de mesure non définie pour l'article: {$ligne->article?->code_article}");
                }

                // Vérifier si le prix unitaire existe
                if (!isset($prixUnitaires[$ligne->article_id])) {
                    throw new Exception("Prix unitaire non trouvé pour l'article : " . $ligne->article?->code_article);
                }

                // // Unité supplementaire
                // $QteBaseSupplementaire = 0;
                // if ($ligne->unite_supplementaire_id) {
                //     $QteBaseSupplementaire = $ligne
                //         ->getQuantiteTotaleSupplement(
                //             $ligne->unite_supplementaire_id, //unité de supplement entrant
                //             $ligne->unite_mesure_id, //unité de destination(unite de base)
                //             $ligne->quantite_supplementaire
                //         );
                // }

                /**
                 * Quantité total à livrer(quantité + QteBaseSupplementaire)
                 */

                // $qteTotal = $ligne->quantite + $QteBaseSupplementaire;

                // Log des données de conversion
                Log::debug("Données de ligne:", [
                    'article_id' => $ligne->article_id,
                    'unite_mesure_id' => $ligne->unite_mesure_id,
                    'unite_base_id' => $ligne->article?->unite_mesure_id,
                    'quantite' => $ligneFact->quantite_livree, //quantité precedement actualisé dans la boucle foreach precedente
                ]);

                $entrees[] = [
                    'depot_id' => $bonLivraison->depot_id,
                    'article_id' => $ligne->article_id,
                    'unite_mesure_id' => $ligne->unite_mesure_id,
                    'quantite' => $ligneFact->quantite_livree, //quantité precedement actualisé dans la boucle foreach precedente
                    'prix_unitaire' => $prixUnitaires[$ligne->article_id],
                    'date_mouvement' => $bonLivraison->date_livraison,
                    'reference_mouvement' => $bonLivraison->code,
                    'document_type' => 'BON_LIVRAISON_FOURNISSEUR',
                    'document_id' => $bonLivraison->id,
                    'notes' => $bonLivraison->commentaire,
                    'user_id' => Auth::id(),
                    'livraison' => $bonLivraison->id,
                ];
            }

            // Log des entrées préparées
            Log::debug('Entrées préparées:', ['entrees' => $entrees]);

            // Traiter les entrées en stock
            $resultatStock = $this->serviceStockEntree->traiterEntreesMultiples($entrees);

            Log::debug('Résultat traitement stock:', ["resultatStock" => $resultatStock]);

            if (!$resultatStock['succes']) {
                throw new Exception("Erreur lors de la mise à jour du stock : " . $resultatStock['message']);
            }

            // Mise à jour du bon de livraison
            $bonLivraison->update([
                'validated_at' => now(),
                'validated_by' => Auth::id()
            ]);

            // Mise à jour du statut de la facture
            $totalQuantiteFacture = $bonLivraison->facture->lignes->sum('quantite_base');
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
            Log::error('Erreur validation bon livraison:', [
                'bon_livraison_id' => $bonLivraison->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'details' => $e instanceof ValidationException ? $e->errors() : null
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
