<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Parametre\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UniteMesureController extends Controller
{
    /**
     * Afficher la liste des unités de mesure
     */
    public function index()
    {
        // $uniteMesures = UniteMesure::with('conversions')->get();
        $uniteMesures = UniteMesure::All();
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        return view('pages.parametre.unite_mesure.index', compact('uniteMesures', 'date'));
    }

    public function list()
    {
        // $uniteMesures = UniteMesure::with('conversions')->get();
        $uniteMesures = UniteMesure::All();

        return response()->json([
            'success' => true,
            'data' => $uniteMesures
        ]);

    }

    /**
     * Charge les données d'une unité de mesure pour modification
     */
    public function edit($id)
    {
        $uniteMesure = UniteMesure::with('conversions')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $uniteMesure
        ]);
    }

    /**
     * Créer une nouvelle unité de mesure
     */
    public function store(Request $request)
    {
        // Logging des données reçues
        Log::info('Données reçues:', $request->all());

        $validator = Validator::make($request->all(), [
            'code_unite' => 'required|unique:unite_mesures,code_unite|max:3|regex:/^[A-Z0-9]+$/',
            'libelle_unite' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'unite_base' => 'boolean',
            'statut' => 'boolean'
        ], [
            'code_unite.required' => 'Le code est obligatoire',
            'code_unite.unique' => 'Ce code existe déjà',
            'code_unite.size' => 'Le code doit contenir exactement 3 caractères',
            'code_unite.regex' => 'Le code ne doit contenir que des lettres majuscules et des chiffres',
            'libelle_unite.required' => 'Le libellé est obligatoire',
            'libelle_unite.max' => 'Le libellé ne doit pas dépasser 50 caractères'
        ]);

        if ($validator->fails()) {
            Log::error('Erreurs de validation:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Convertir explicitement les valeurs checkbox en booléens
            $data = $request->all();
            $data['statut'] = $request->boolean('statut');
            $data['unite_base'] = $request->boolean('unite_base');

            $uniteMesure = UniteMesure::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Unité de mesure créée avec succès',
                'data' => $uniteMesure
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur création unité de mesure:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'unité de mesure',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Mettre à jour une unité de mesure
     */
    public function update(Request $request, $id)
    {
        $uniteMesure = UniteMesure::find($id);

        if (!$uniteMesure) {
            return response()->json([
                'success' => false,
                'message' => 'Unité de mesure non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code_unite' => 'required|size:3|regex:/^[A-Z0-9]+$/|unique:unite_mesures,code_unite,' . $id,
            'libelle_unite' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'unite_base' => 'boolean',
            'statut' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['statut'] = $request->boolean('statut');
            $data['unite_base'] = $request->boolean('unite_base');

            $uniteMesure->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Unité de mesure mise à jour avec succès',
                'data' => $uniteMesure
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'unité de mesure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une unité de mesure (soft delete)
     */
    public function destroy($id)
    {
        try {
            $uniteMesure = UniteMesure::findOrFail($id);

            // Vérifier s'il y a des conversions associées
            if ($uniteMesure->conversions()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer cette unité car elle est utilisée dans des conversions'
                ], 403);
            }

            $uniteMesure->delete();

            return response()->json([
                'success' => true,
                'message' => 'Unité de mesure supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'unité de mesure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une unité de mesure
     */
    public function toggleStatus($id)
    {
        try {
            $uniteMesure = UniteMesure::findOrFail($id);
            $uniteMesure->statut = !$uniteMesure->statut;
            $uniteMesure->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de l\'unité de mesure modifié avec succès',
                'data' => $uniteMesure
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
            'code_unite' => 'required|string|max:255',
        ]);

        // Récupérer le code depuis la requête
        $code = $request->input('code_unite');

        // Vérifier si le code existe dans la table "depot"
        $exists = UniteMesure::where('code_unite', $code)->exists();

        // Retourner une réponse JSON
        return response()->json(['exists' => $exists]);
    
    }
}
