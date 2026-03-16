<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Transportation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TransportationController extends Controller
{
    public function index()
    {
        $transportations = Transportation::orderByDesc('created_at')->get();

        // Statistiques globales
        $stats = [
            'total_transportations' => $transportations->count(),
        ];

        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        return view('pages.parametre.transportation.index', compact(
            'transportations',
            'stats',
            'date'
        ));
    }

    public function store(Request $request)
    {
        Log::debug("Creation d'un moyen de transport :", ["data" => $request->all()]);

        try {
            $validated = $request->validate([
                'matricule' => 'required|string|min:6|max:100|unique:transportations,matricule',
                'libelle' => 'required|string',
                'type' => 'required|in:TRICYCLE,CAMIONNETTE'
            ], [
                'matricule.required' => 'Le matricule est obligatoire.',
                'matricule.min' => 'Le matricule doit contenir au moins 6 caractères.',
                'matricule.max' => 'Le matricule ne doit pas dépasser 100 caractères.',
                'matricule.unique' => 'Ce matricule existe déjà.',

                'libelle.required' => 'Le libellé est obligatoire.',

                'type.required' => 'Le type est obligatoire.',
                'type.in' => 'Le type doit être TRICYCLE ou CAMIONNETTE.'
            ]);

            DB::beginTransaction();

            $transportation = Transportation::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Moyen de transport créé avec succès',
                'data' => $transportation
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création du vehicule' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(Request $request, Transportation $transportation)
    {
        Log::debug("Recuperation des données d'un moyen de transport :", ["data" => $transportation]);
        try {
            return response()->json([
                'success' => true,
                'data' => $transportation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Moyen de transport non trouvé'
            ], 404);
        }
    }

    public function update(Request $request, Transportation $transportation)
    {
        Log::debug("Modification des données d'un moyen de transport :", ["data" => $transportation]);

        try {
            $validated = $request->validate([
                'matricule' => ['required', 'string', 'min:6', 'max:100', Rule::unique("transportations")->ignore($transportation->id)],
                'libelle' => 'required|string',
                'type' => 'required|in:TRICYCLE,CAMIONNETTE'
            ], [
                'matricule.required' => 'Le matricule est obligatoire.',
                'matricule.min' => 'Le matricule doit contenir au moins 6 caractères.',
                'matricule.max' => 'Le matricule ne doit pas dépasser 100 caractères.',
                'matricule.unique' => 'Ce matricule existe déjà.',

                'libelle.required' => 'Le libellé est obligatoire.',

                'type.required' => 'Le type est obligatoire.',
                'type.in' => 'Le type doit être TRICYCLE ou CAMIONNETTE.'
            ]);

            DB::beginTransaction();

            $transportation->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Moyen de transport mis à jour avec succès',
                'data' => $transportation
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, Transportation $transportation)
    {
        Log::debug("Suppression des données d'un moyen de transport :", ["data" => $transportation]);

        try {
            $transportation->delete();
            return response()->json([
                'success' => true,
                'message' => 'Moyen de transport supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression'
            ], 500);
        }
    }
}
