<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Parametre\PointDeVente;
use App\Models\Parametre\Depot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PointVenteController extends Controller
{
    public function index()
    {
        $pointsVente = PointDeVente::with('depot')->get();
        $depots = Depot::where('actif', true)->get();
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');
        return view('pages.parametre.point_vente.index', compact('pointsVente', 'depots', 'date'));
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_pv' => 'required|string|size:6|unique:point_de_ventes,code_pv',
            'nom_pv' => 'required|string|min:3|max:100',
            'adresse_pv' => 'nullable|string|required',
            // 'depot_id' => 'required|exists:depots,id',
            'actif' => 'boolean'
        ], [
            'code_pv.required' => 'Le code du point de vente est requis',
            'code_pv.size' => 'Le code doit contenir exactement 6 caractères',
            'code_pv.unique' => 'Ce code est déjà utilisé',
            'nom_pv.required' => 'Le nom du point de vente est requis',
            'nom_pv.min' => 'Le nom doit contenir au moins 3 caractères',
            // 'depot_id.required' => 'Veuillez sélectionner un magasin',
            'adresse_pv.required' => 'Veuillez renseigner l\'adresse',
            // 'depot_id.exists' => 'Le magasin sélectionné n\'existe pas'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['actif'] = $request->has('actif');

            $pointVente = PointDeVente::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Point de vente créé avec succès',
                'data' => $pointVente
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du point de vente',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    public function edit($id)
    {
        $pointVente = PointDeVente::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $pointVente
        ]);
    }

    public function update(Request $request, $id)
    {
        $pointVente = PointDeVente::find($id);

        if (!$pointVente) {
            return response()->json([
                'success' => false,
                'message' => 'Point de vente non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code_pv' => 'required|string|size:6|unique:point_de_ventes,code_pv,' . $id,
            'nom_pv' => 'required|string|min:3|max:100',
            'adresse_pv' => 'nullable|string',
            // 'depot_id' => 'required|exists:depots,id',
            'actif' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['actif'] = $request->has('actif');

            $pointVente->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Point de vente mis à jour avec succès',
                'data' => $pointVente
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du point de vente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un point de vente (soft delete)
     */
    public function destroy($id)
    {
        try {
            $pointVente = PointDeVente::findOrFail($id);

            // Vérifier s'il y a des caisses associées
            if ($pointVente->caisses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce point de vente car il contient des caisses'
                ], 403);
            }

            // Vérifier s'il y a des utilisateurs associés
            if ($pointVente->utilisateurs()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce point de vente car il est associé à des utilisateurs'
                ], 403);
            }

            // Supprimer le point de vente
            $pointVente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Point de vente supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du point de vente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $pointVente = PointDeVente::findOrFail($id);

            $pointVente->actif = !$pointVente->actif;
            $pointVente->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut du point de vente modifié avec succès',
                'data' => $pointVente
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
        $code = $request->input('code_pv');

        // Vérifier si le code existe dans la table "depot"
        $exists = PointDeVente::where('code_pv', $code)->exists();

        // Retourner une réponse JSON
        return response()->json(['exists' => $exists]);
    }
}
