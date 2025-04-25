<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Caisse;
use App\Models\Parametre\PointDeVente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CaisseController extends Controller
{
    public function index()
    {
        $caisses = Caisse::with('pointVente')->get();
        $pointsVente = PointDeVente::where('actif', true)->get();
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // dd($caisses);

        return view('pages.parametre.caisse.index', compact('caisses', 'pointsVente', 'date'));
    }

    public function edit($id)
    {
        try {
            $caisse = Caisse::with(['pointVente'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $caisse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Caisse non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_caisse' => 'required|string|size:8|unique:caisses,code_caisse',
            'libelle' => 'required|string|min:3|max:100',
            'point_de_vente_id' => 'required|exists:point_de_ventes,id',
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
            // Correction pour la gestion du statut actif
            $data['actif'] = $request->input('actif') === '1';

            $caisse = Caisse::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Caisse créée avec succès',
                'data' => $caisse
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la caisse',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $caisse = Caisse::find($id);

        if (!$caisse) {
            return response()->json([
                'success' => false,
                'message' => 'Caisse non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|min:3|max:100',
            'point_vente_id' => 'required|exists:point_de_ventes,id',
            'actif' => 'required|in:0,1' // Modification ici pour forcer la présence de actif
        ], [
            'libelle.required' => 'Le libellé de la caisse est requis',
            'libelle.min' => 'Le libellé doit contenir au moins 3 caractères',
            'point_vente_id.required' => 'Veuillez sélectionner un point de vente',
            'point_vente_id.exists' => 'Le point de vente sélectionné n\'existe pas',
            'actif.required' => 'Le statut est requis',
            'actif.in' => 'Le statut doit être actif ou inactif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Récupérer les données
            $data = $request->only(['libelle', 'point_de_vente_id']);

            // Traiter explicitement le statut actif
            $data['actif'] = $request->input('actif') === '1' ? true : false;
            $data['point_de_vente_id'] = $request->point_vente_id;

            // Mettre à jour la caisse
            $caisse->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Caisse mise à jour avec succès',
                'data' => $caisse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la caisse',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Supprimer une caisse (soft delete)
     */
    public function destroy($id)
    {
        try {
            $caisse = Caisse::findOrFail($id);

            // Vérifier s'il y a des sessions associées
            // if ($caisse->sessions()->count() > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Impossible de supprimer cette caisse car elle a des sessions associées'
            //     ], 403);
            // }

            // Supprimer la caisse
            $caisse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Caisse supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la caisse',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Activer/Désactiver une caisse
 */
    public function toggleStatus($id)
    {
        try {
            $caisse = Caisse::findOrFail($id);

            // Vérifier si la caisse a des sessions actives avant de la désactiver
            // if ($caisse->actif && $caisse->sessions()->where('status', 'active')->count() > 0) {
            // if ($caisse->actif && $caisse->sessions()->where('status', 'active')->count() > 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Impossible de désactiver cette caisse car elle a des sessions actives'
            //     ], 403);
            // }

            // Inverser le statut
            $caisse->actif = !$caisse->actif;
            $caisse->save();

            $status = $caisse->actif ? 'activée' : 'désactivée';

            return response()->json([
                'success' => true,
                'message' => "Caisse {$status} avec succès",
                'data' => $caisse
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uniqueCode(Request $request) {
        // Valider que "code_depot" est présent
        $request->validate([
            'code_caisse' => 'required|string|max:255',
        ]);

        // Récupérer le code depuis la requête
        $code = $request->input('code_caisse');

        // Vérifier si le code existe dans la table "depot"
        $exists = Caisse::where('code_caisse', $code)->exists();

        // Retourner une réponse JSON
        return response()->json(['exists' => $exists]);
    
    }

}
