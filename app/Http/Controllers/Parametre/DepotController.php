<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\DetailInventaire;
use App\Models\Catalogue\Inventaire;
use App\Models\Parametre\Depot;
use App\Models\Parametre\PointDeVente;
use App\Models\Parametre\TypeDepot;
use App\Models\Revendeur\FactureRevendeur;
use App\Models\Stock\StockDepot;
use App\Models\Vente\FactureClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepotController extends Controller
{
    /**
     * Afficher la liste des dépôts
     */

    public function index()
    {
        $depots = Depot::with(['pointsVente', 'typeDepot'])->get()
            ->map(function ($depot) {
                $depot->inventaires = $depot->inventaires();
                return $depot;
            });

        $typesDepot = TypeDepot::all();

        // Debug des types de magasin
        Log::info('Types de magasin disponibles:', $typesDepot->toArray());

        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        $pvs = PointDeVente::where('actif', true)->get();

        return view('pages.parametre.depot.index', compact('depots', 'typesDepot', 'date', 'pvs'));
    }

    /**
     * Charge les données d'un magasin pour modification
     */
    public function edit($id)
    {
        $depot = Depot::with('typeDepot')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $depot
        ]);
    }

    /**
     * Créer un nouveau magasin
     */
    public function store(Request $request)
    {
        // Logging des données reçues
        Log::info('Données reçues:', $request->all());

        // Vérifier si le type existe
        $typeExists = TypeDepot::where('id', $request->type_depot_id)->exists();
        Log::info('Type existe:', ['exists' => $typeExists, 'id' => $request->type_depot_id]);

        $validator = Validator::make($request->all(), [
            'code_depot' => 'required|unique:depots,code_depot',
            'libelle_depot' => 'required|string|max:100',
            'type_depot_id' => 'required|exists:type_depots,id',
            'point_de_vente_id' => 'required|exists:point_de_ventes,id',
            'adresse_depot' => 'nullable|string',
            'tel_depot' => 'nullable|string',
            'depot_principal' => 'boolean',
            'actif' => 'boolean'
        ], [
            'type_depot_id.required' => 'Le type de magasin est obligatoire',
            'type_depot_id.exists' => 'Le type de magasin sélectionné n\'est pas valide',
            'point_de_vente_id.required' => 'Le pointe de vente est obligatoire',
            'point_de_vente_id.exists' => 'Le pointe de vente sélectionné n\'est pas valide'
        ]);

        if ($validator->fails()) {
            Log::error('Erreurs de validation:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier si on essaie de créer un magasin principal alors qu'il en existe déjà un
            if ($request->boolean('depot_principal') && Depot::where('depot_principal', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un magasin principal existe déjà'
                ], 422);
            }

            // Convertir explicitement les valeurs checkbox en booléens
            $data = $request->all();
            $data['actif'] = $request->boolean('actif');
            $data['depot_principal'] = $request->boolean('depot_principal');

            $depot = Depot::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Magasin créé avec succès',
                'data' => $depot
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur création magasin:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du magasin',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Afficher un magasin spécifique
     */
    public function show($id)
    {
        $depot = Depot::with([
            'pointsVente',
            'stocks',
            'stocks.article',
            'stocks.depot',
            'stocks.uniteMesure',
            'typeDepot'
        ])->find($id);

        if (!$depot) {
            return response()->json([
                'success' => false,
                'message' => 'Magasin non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $depot
        ]);
    }

    /**
     * Mettre à jour un magasin
     */
    public function update(Request $request, $id)
    {
        $depot = Depot::find($id);

        if (!$depot) {
            return response()->json([
                'success' => false,
                'message' => 'Magasin non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code_depot' => 'required|unique:depots,code_depot,' . $id,
            'libelle_depot' => 'required|string|max:100',
            'type_depot_id' => 'required|exists:type_depots,id',
            'point_de_vente_id' => 'required|exists:point_de_ventes,id',
            'adresse_depot' => 'nullable|string',
            'tel_depot' => 'nullable|string',
            'depot_principal' => 'boolean',
            'actif' => 'boolean'
        ], [
            'type_depot_id.required' => 'Le type de magasin est obligatoire',
            'type_depot_id.exists' => 'Le type de magasin sélectionné n\'est pas valide',
            'point_de_vente_id.required' => 'Le pointe de vente est obligatoire',
            'point_de_vente_id.exists' => 'Le pointe de vente sélectionné n\'est pas valide'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier si on essaie de définir comme principal alors qu'il en existe déjà un autre
            if ($request->boolean('depot_principal') && !$depot->depot_principal) {
                $existingPrincipal = Depot::where('depot_principal', true)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($existingPrincipal) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un magasin principal existe déjà'
                    ], 422);
                }
            }

            // Mise à jour des données
            $data = $request->all();
            $data['actif'] = $request->boolean('actif');
            $data['depot_principal'] = $request->boolean('depot_principal');

            $depot->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Magasin mis à jour avec succès',
                'data' => $depot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du magasin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un magasin (soft delete)
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Cette Opération est bloquée temporairement! Contactez l\'administrateur du système'
        ], 403);

        try {
            $depot = Depot::findOrFail($id);

            // Vérifier si c'est un magasin principal
            if ($depot->depot_principal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer le magasin principal'
                ], 403);
            }

            // Vérifier s'il y a des points de vente associés
            if ($depot->pointsVente()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce magasin car il contient des points de vente'
                ], 403);
            }

            // Vérifier s'il y a des stocks associés
            if ($depot->stocks()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce magasin car il contient des stocks'
                ], 403);
            }

            // Supprimer le magasin
            $depot->delete();

            return response()->json([
                'success' => true,
                'message' => 'Magasin supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du magasin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un magasin
     */
    public function toggleStatus($id)
    {
        try {
            $depot = Depot::findOrFail($id);

            // Empêcher la désactivation du magasin principal
            if ($depot->depot_principal && $depot->actif) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de désactiver le magasin principal'
                ], 403);
            }

            $depot->actif = !$depot->actif;
            $depot->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut du magasin modifié avec succès',
                'data' => $depot
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uniqueCode(Request $request)
    {
        // Valider que "code_depot" est présent
        $request->validate([
            'code_depot' => 'required|string|max:255',
        ]);

        // Récupérer le code depuis la requête
        $code = $request->input('code_depot');

        // Vérifier si le code existe dans la table "depot"
        $exists = Depot::where('code_depot', $code)->exists();

        // Retourner une réponse JSON
        return response()->json(['exists' => $exists]);
    }

    /**
     * ============================
     * GESTION DES INVENTAIRES
     *=============================     
     */

    /**
     * Afficher les inventaires
     * d'un depot
     */
    public function inventaires($depotId)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
        $depot = Depot::with("stocks")->findOrFail($depotId);

        $inventaires = $depot->inventaires();
        return view("pages.parametre.depot.partials.inventaires", compact("depot", "inventaires", "date"));
    }

    /**
     * Creation d' inventaires
     * dans un depot
     */
    public function inventairesStore(Request $request, $depotId)
    {
        $depot = Depot::findOrFail($depotId);

        $gerantsDepot = $depot->pointsVente?->utilisateurs;

        /** Validation des données*/
        $validator = Validator::make($request->all(), [
            "date_inventaire" => "required|date",
            'stock_depots*' => 'required|array',
            'stock_depots*id' => 'required|exists:stock_depots,id',
            'stock_depots*depot_id' => 'required|exists:depots,id',
            'stock_depots*unite_mesure_id' => 'required|exists:unite_mesures,id',
            'stock_depots*qte_reel' => 'required',
            'stock_depots*qte_stock' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        /** */
        if ($request->check_all_article) {
            $request->validate(["all_qte_reel" => "required"], ["all_qte_reel.required" => "Veuillez préciser la quantité à laquelle vous voullez réinitialiser les inventaires"]);
        }

        if (!$request->check_all_article) {
            // On s'assure que c'est une collection
            $stockDepotCheckeds = collect($request->stock_depots)
                ->filter(function ($item) {
                    // Si 'checked' doit être true
                    return isset($item['checked']) && $item['checked'] == "on";
                })
                ->values(); // Pour réindexer la collection si besoin

            if (count($stockDepotCheckeds) == 0) {
                return back()->with("error", "Veuillez choisir au moins un article!");
            }
        }

        try {
            DB::beginTransaction();

            $inventaire = Inventaire::create([
                'date_inventaire' => $request->date_inventaire,
                'user_id' => Auth::id(),
                'depot_ids' => $request->check_all_article ?
                    $depot->stocks->pluck("depot_id") :
                    $stockDepotCheckeds->pluck("depot_id"),
            ]);

            /**Init detail */
            $detailsInventaires = [];

            if ($request->check_all_article) {
                // Préparation des détails d'inventaire
                foreach ($depot->stocks as $stockDepot) {
                    Log::info("Les infos du stock du depot :", ["data" => $stockDepot]);

                    $detailsInventaires[] = [
                        'qte_stock' => $stockDepot->quantite_reelle,
                        'qte_reel' => $request->all_qte_reel ?? 0,
                        'stock_depot_id' => $stockDepot->id,
                    ];

                    // Mise à jour en masse des stocks
                    $stok_depot = StockDepot::find($stockDepot["id"]);
                    if (!$stok_depot) {
                        throw new \Exception("Ce stock depot n'existe pas!");
                    }
                    $stok_depot->update(['quantite_reelle' => $request->all_qte_reel ?? $stok_depot->quantite_reelle]);
                }
            } else {

                // Préparation des détails d'inventaire
                foreach ($stockDepotCheckeds as $stockDepot) {
                    Log::info("Les infos du stock du depot :", ["data" => $stockDepot]);

                    $detailsInventaires[] = [
                        'qte_stock' => $stockDepot["qte_stock"] ?? 0,
                        'qte_reel' => $stockDepot["qte_reel"] ?? 0,
                        'stock_depot_id' => $stockDepot["id"],
                    ];

                    // Mise à jour en masse des stocks
                    $stok_depot = StockDepot::find($stockDepot["id"]);
                    if (!$stok_depot) {
                        throw new \Exception("Ce stock depot n'existe pas");
                    }
                    $stok_depot->update(['quantite_reelle' => $stockDepot["qte_reel"] ?? $stok_depot->quantite_reelle]);
                }
            }

            /** Classement des ventes(direction et revendeur) dans l'inventaires | il s'agit bien des ventes 
             * qui ne sont pas encore associées à un inventaire
             * 
             * on considère seulement les factures crées par les gérants du dépôt
             */

            // Pour les depots de cotonou
            if (in_array($depot->id, [3, 4, 6])) {
                FactureClient::whereNull("inventaire_id")->update(["inventaire_id" => $inventaire->id]);
            }

            // Pour les depots des revendeurs
            if (in_array($depot->id, [1, 2, 5])) {
                FactureRevendeur::whereIn("created_by", $gerantsDepot->pluck("id")->toArray())
                    ->whereNull("inventaire_id")->update(["inventaire_id" => $inventaire->id]);
            }

            /** CREATION DES DETAILS INVENTAIRES */
            $inventaire->details()
                ->createMany($detailsInventaires);

            DB::commit();

            Log::info('Inventaire créé avec succès', [
                'inventaire_id' => $inventaire->id,
            ]);

            return back()->with("success", "Inventaire enregistré avec succès!");
        } catch (\Exception $e) {
            DB::rollback();
            Log::info("Erreure d'enregistrement d'inventaire", ["error" => $e->getMessage(), "ligne" => $e->getLine()]);
            return back()->with("error", "Erreure d'enregistrement lors de l'inventaire " . $e->getMessage());
        }
    }

    /**
     * Afficher les details
     * d'un inventaire
     */
    public function inventairesDetails($inventaireId)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        $inventaire = Inventaire::with([
            "details",
            "details.stockDepot",
            "details.stockDepot.article",
            "details.stockDepot.depot",
            "details.stockDepot.uniteMesure",
            "auteur"
        ])->findOrFail($inventaireId);

        // $inventaire["depots"] = $inventaire->depots();

        if (request()->ajax()) {
            $data = [
                "success" => true,
                "data" => $inventaire
            ];
            return response()->json($data);
        }

        return view("pages.parametre.depot.partials.inventaire-details", compact("inventaire", "date"));
    }

    /**
     * Supprimer un inventaire
     */
    public function inventaireDelete($inventaireId)
    {
        try {
            DB::beginTransaction();
            $inventaire = Inventaire::with('details')
                ->find($inventaireId);

            if (!$inventaire) {
                throw new \Exception("Cet inventaire n'existe pas");
            }

            // suppression de sdétails
            $inventaire->details()
                ->delete();

            // updating the inventaire
            $inventaire->update(["deleted_by" => Auth::id()]);

            // suppression de l'inventaire
            $inventaire->delete();
            DB::commit();
            return redirect()->back()
                ->with("success", "Inventaire supprimé avec succès!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with("error", "Une erreure est survenue lors de la suppression de l'inventaire : " . $e->getMessage());
        }
    }
}
