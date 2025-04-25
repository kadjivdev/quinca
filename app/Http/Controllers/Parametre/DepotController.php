<?php

namespace App\Http\Controllers\parametre;

use App\Http\Controllers\Controller;
use App\Models\parametre\Depot;
use App\Models\Parametre\PointDeVente;
use App\Models\parametre\TypeDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DepotController extends Controller
{
    /**
     * Afficher la liste des dépôts
     */


    public function index()
    {
        $depots = Depot::with(['pointsVente', 'typeDepot'])->get()->map(function ($depot) {
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
        $depot = Depot::with(['pointsVente', 'stocks', 'typeDepot'])->find($id);

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
}
