<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Depot;
use App\Models\Vente\{FactureClient, LivraisonClient, Magasin, MarchandBack, SessionCaisse, ReglementClient, RevendeurDepense};
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
                $query = MarchandBack::with(['livraison', 'createdBy', 'validatedBy', "depot"])
                    ->whereDate("date", $day)
                    ->orderByDesc('id');
            } else {
                $query = MarchandBack::with(['livraison', 'createdBy', 'validatedBy', "depot"])
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

            $depots = Depot::all();
            return view('pages.ventes.marchand-back.index', compact(
                'livraisons',
                'marchanBacks',
                'date',
                'depots'
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

                'depot_id' => 'required|exists:depots,id',

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
            Log::info('Début création de retour de marchandise', ['request' => $request->all()]);

            // Validation
            $validated = $request->validate([
                'date' => 'required|date',
                'livraison_id' => 'required|exists:livraison_clients,id',

                'depot_id' => 'required|exists:depots,id',

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

                //suppression des anciennes lignes
                $marchandise->lignes()->delete();

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
                    ->with("success", 'Modification de retour de marchandise éffectué avec succès');
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Erreur modification marchandise', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with("error", 'Erreur création marchandise: ' . $e->getMessage());
        }
    }

    public function validerMarchandise($id)
    {
        try {
            DB::beginTransaction();

            $marchand = MarchandBack::with("livraison.lignes")->findOrFail($id);
            
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
                    'depot_id' => $marchand->depot_id ?? ($livraisonClient->depot_dest_id ?? $livraisonClient->depot_id), //$livraisonClient->depot_dest_id ?? $livraisonClient->depot_id,
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
