<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\{FactureClient, LivraisonClient, MarchandBack, SessionCaisse, ReglementClient, RevendeurDepense};
use App\Models\Revendeur\FactureRevendeur;
use App\Models\Revendeur\LigneFactureRevendeur;
use App\Services\ServiceStockEntree;
use App\Services\ServiceStockSortie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MarchandBackController extends Controller
{
    private $serviceStockSortie, $serviceStockEntree;

    public function __construct(ServiceStockSortie $serviceStockSortie, ServiceStockEntree $serviceStockEntree)
    {
        $this->serviceStockSortie = $serviceStockSortie;
        $this->serviceStockEntree = $serviceStockEntree;
    }

    public function index(Request $request)
    {
        try {
            Log::info('Début du chargement de la liste des Retour de marchandises');
            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

            // User connecté
            $user = auth()->user();

            $day = $request->date;
            // Chargement des factures avec les relations nécessaires
            if ($day) {
                $query = MarchandBack::with(['livraison', 'createdBy', 'validatedBy'])
                    ->whereDate("date", $day)
                    ->orderByDesc('id');
            } else {
                $query = MarchandBack::with(['livraison', 'createdBy', 'validatedBy'])
                    ->orderByDesc('id');
            }

            if (
                auth()->user()->hasRole("Super Administrateur")
                || auth()->user()->hasRole("CONTROLE INTERNE")
                || auth()->user()->hasRole("CONTROLE EXTERNE ET CELLULE DE REQUETE")
            ) {
                $marchanBacks = $query->get();
                $livraisons = LivraisonClient::get();
            } else {
                $marchanBacks = $query
                    ->where('created_by', $user->id)
                    ->get();
                $livraisons = LivraisonClient::where("created_by")
                    ->whereNotNull("validated_by")
                    ->get();
            }

            return view('pages.ventes.marchand-back.index', compact(
                'livraisons',
                'marchanBacks',
                'date',
            ));
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement de la liste des depenses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Une erreur est survenue lors du chargement des depenses');
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Début création de retour de marchandise', ['request' => $request->all()]);

            // Validation
            $validated = $request->validate([
                'date' => 'required|date',
                'livraison_id' => 'required|exists:livraison_clients,id',
                'documents' => 'nullable|file|mimes:jpg,png,jpeg,gif,svg',

                //les lignes
                'lignes' => 'array',
                'lignes*article_id' => "required|exists:articles,id",
                'lignes*quantite' => "required",
                'lignes*prix_unitaire' => "required",
                'lignes*unite_vente_id' => "required|exists:unite_mesures,id",

            ], ["documents.mimes" => "Le document doit être de type jpg,png,jpeg,gif,svg"]);

            if ($request->hasFile("documents")) {
                $fileName = $request->file('documents')->getClientOriginalName();
                $request->file('documents')->move("marchand_docs", $fileName);
                $validated["documents"] = asset("marchand_docs/" . $fileName);
            }

            // dd($validated["lignes"]);
            DB::beginTransaction();

            try {
                // Création de la depense
                $marchandise = MarchandBack::create($validated);

                if (is_null($request->lignes)) {
                    throw new Exception("Aucun article n'est disponible sur cette livraison", 1);
                }

                /** Creation des lignes */
                foreach ($validated["lignes"] as $ligne) {
                    Log::info("Début d'enregistrement des lignes d'article");
                    $marchandise->lignes()->create([
                        "article_id" => $ligne["article_id"],
                        "quantite" => $ligne["quantite"],
                        "unite_vente_id" => $ligne["unite_vente_id"],
                        "prix_unitaire" => $ligne["prix_unitaire"],
                    ]);
                }

                DB::commit();

                return back()
                    ->with("success", 'Retour de marchandise éffectué avec succès');
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur création marchandise', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with("error", 'Erreur création marchandise: ' . $e->getMessage());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            Log::info('Début du chargement des détails de la marchandise ...', ['marchandise_id' => $id]);

            $marchand = MarchandBack::with([
                'client',
                'livraison',
                'lignes.article',
                'lignes.uniteVente',
                'createdBy',
            ])->findOrFail($id);

            // Formater les quantités après chargement
            $marchand->lignes->each(function ($ligne) {
                $ligne->quantite_formatted = number_format($ligne->quantite, 2, ".", "");
                $ligne->prix_formatted = number_format($ligne->prix_unitaire, 2, ".", "");
            });

            Log::info("Marchandise showing ...", ["marhandise" => $marchand]);

            return response()->json([
                'success' => true,
                'data' => [
                    'marchand' => $marchand,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erreur lors du chargement des détails de la marchandise', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors du chargement des détails de la facture ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info("Total de ligne en debut de requete ", ["lignes" => $request->lignes]);
            Log::info('Début mise à jour facture', ['request' => $request->all(), 'facture_id' => $id]);

            $facture = FactureRevendeur::findOrFail($id);

            // Validation
            $validator = Validator::make($request->all(), [
                'date_facture' => 'required|date',
                'client_id' => 'required|exists:clients,id',
                'date_echeance' => 'date',
                'montant_regle' => 'required|numeric|min:0',
                'moyen_reglement' => 'required|string',

                'lignes' => 'required|array|min:1',
                'lignes.*.article_id' => 'required|exists:articles,id',
                'lignes.*.depot_id' => 'required|exists:depots,id',
                'lignes.*.quantite' => 'required',
                'lignes.*.tarification_id' => 'required',

                'type_facture' => 'required|in:simple,normaliser',
                'observations' => 'nullable|string',
                'moyen_reglement' => "required|in:espece,cheque,virement,carte_bancaire,MoMo,Flooz,Celtis_Pay,Effet,Avoir",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Mise à jour de la facture
                $facture->update([
                    'date_facture' => Carbon::parse($request->date_facture)->startOfDay(),
                    'client_id' => $request->client_id,
                    'date_echeance' => Carbon::parse($request->date_echeance)->startOfDay(),
                    // 'session_caisse_id' => $sessionCaisse->id,
                    'created_by' => auth()->id(),
                    'observations' => $request->observations,
                    'statut' => 'brouillon',
                    'type_facture' => $request->type_facture === "normaliser" ? "NORMALISE" : "SIMPLE",
                    'moyen_reglement' => $request->moyen_reglement ?? $facture->moyen_reglement,
                    'montant_ht' => 0,
                    'montant_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'taux_tva' => 0.18,
                    'taux_aib' => 0.01,
                    'taux_remise' => 0,
                    'montant_ht_apres_remise' => 0,
                    'montant_tva' => 0,
                    'montant_aib' => 0,
                    'montant_ttc' => 0,
                    'montant_regle' => 0,
                    'montant_regle' => $request->montant_regle
                ]);

                Log::info("Total de ligne avant suppression des anciennes ", ["count" => count($request->lignes)]);

                // Suppression des anciens règlements
                $facture->reglements()->delete();

                // Réinitialisation des totaux et suppression des anciennes lignes
                $facture->lignes()->delete();

                // Création du règlement si nécessaire
                if ($request->montant_regle > 0) {
                    $reglement = new ReglementClient([
                        'facture_client_id' => $facture->id,
                        'date_reglement' => Carbon::parse($request->date_facture),
                        'type_reglement' => $request->moyen_reglement,
                        'montant' => $request->montant_regle,
                        'statut' => 'brouillon',
                        // 'session_caisse_id' => $sessionCaisse->id,
                        'created_by' => auth()->id(),
                    ]);
                    $facture->reglements()->save($reglement);
                }

                Log::info("Total de ligne après suppression des anciennes", ["count" => count($request->lignes)]);

                // Mise à jour des lignes
                foreach ($request->lignes as $index => $ligne) {
                    $ligneMontantTTC = $ligne['quantite'] * $ligne['tarification_id'];
                    $ligneMontantHt = $ligneMontantTTC / 1.19;
                    $ligneMontantTVA = $ligneMontantHt * 0.18;
                    $ligneMontantAIB = $ligneMontantHt * 0.01;
                    $ligneMontantRemise = $ligneMontantTTC * $ligne['taux_remise'] / 100;

                    Log::info("La ligne $index", [
                        "montantHt" => $ligneMontantHt,
                        "montantTva" => $ligneMontantTVA,
                        "montantAib" => $ligneMontantAIB,
                        "montantRemise" => $ligneMontantRemise,
                    ]);

                    $ligneFacture = new LigneFactureRevendeur([
                        'article_id' => $ligne['article_id'],
                        'unite_vente_id' => $ligne['unite_vente_id'],
                        'quantite' => $ligne['quantite'],
                        'depot' => $ligne['depot_id'],
                        'prix_unitaire_ht' => $ligne['tarification_id'],
                        'taux_remise' => $ligne['taux_remise'] ?? 0,
                        'taux_tva' => $request->type_facture === 'simple' ? 0 : 18 / 100,
                        'taux_aib' => $request->type_facture === 'simple' ? 0 : 1 / 100,
                        'montant_ht' => $ligneMontantHt,
                        'montant_tva' => $ligneMontantTVA,
                        'montant_aib' => $ligneMontantAIB,
                        'montant_ttc' => $ligneMontantTTC,
                        'montant_remise' => $ligneMontantRemise,
                        'montant_ht_apres_remise' => $ligneMontantHt - $ligneMontantRemise,
                        'quantite_livree' => 0,
                    ]);

                    $facture->lignes()->save($ligneFacture);
                }

                // Mise à jour des totaux
                $facture->update([
                    'montant_ht' => $facture->lignes->sum("montant_ht"),
                    'montant_remise' => $facture->lignes->sum("montant_remise"),
                    'montant_ht_apres_remise' => $facture->lignes->sum("montant_ht_apres_remise"),
                    'montant_tva' => $facture->lignes->sum("montant_tva"),
                    'montant_aib' => $facture->lignes->sum("montant_aib"),
                    'montant_ttc' => $facture->lignes->sum("montant_ttc"),
                    'montant_regle' => $request->montant_regle,
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Facture mise à jour avec succès',
                    'data' => ['facture' => $facture->load([
                        'client',
                        'lignes.article',
                        'lignes.uniteVente',
                    ])]
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur mise à jour facture', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur mise à jour facture: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validerMarchandise($id)
    {
        try {
            DB::beginTransaction();

            $marchand = MarchandBack::with("livraison.lignes")->findOrFail($id);
            // dd($marchand);

            if ($marchand->validated_by) {
                throw new Exception('Marchandise déjà validée');
            }

            $marchand->update([
                'validated_by' => auth()->id(),
            ]);

            $livraisonClient = $marchand
                ->livraison->load(['lignes.article', 'lignes.uniteVente']);

            /** RESTITUTION DE STOCK DE L'ARTICLE */

            $entrees = [];

            /** */
            foreach ($marchand->lignes as $ligne) {

                /** DATA POUR APPROVISIONNEMENT */
                $entrees[] = [
                    'depot_id' => $livraisonClient->depot_dest_id??$livraisonClient->depot_id,
                    'article_id' => $ligne->article_id,
                    'unite_mesure_id' => $ligne->unite_vente_id,
                    'quantite' => $ligne->quantite,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'date_mouvement' => now(),
                    'notes' => "Entrée en stock via retour de marchandise",
                    'user_id' => auth()->user()->id,
                    'livraison' => $livraisonClient->id, //pour mentionner qu'il s'agit d'un approvisionnement venant d'une livraison
                    'date_mouvement' => $livraisonClient->date_livraison,
                    'reference_mouvement' => $livraisonClient->numero,
                    'document_id' => $livraisonClient->id,
                    'notes' => "Retour de marchandise sur livraison #{$marchand->numero}",
                ];
            }

            Log::debug('Données d\'entrée en stock:', $entrees);

            // Traiter les entrées en stock
            $resultatStock = $this->serviceStockEntree->traiterEntreesMultiples($entrees);
            Log::debug('Résultat traitement stock:', $resultatStock);
            if (!$resultatStock['succes']) {
                throw new Exception("Erreur lors de la mise à jour du stock : " . $resultatStock['message']);
            }

            DB::commit();

            return back()
                ->with("success", "Marchandise ($marchand->numero) validée avec succès");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation marchandise', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->with("error" . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $marchand = MarchandBack::findOrFail($id);

            // Vérifier le statut
            if (!$marchand) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'La depense n\'existe pas'
                ], 422);
            }

            // Supprimer la facture
            $marchand->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Marchandise supprimée avec succès'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression marchandise', [
                'facture_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}
