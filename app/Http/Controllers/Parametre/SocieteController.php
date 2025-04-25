<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Parametre\Societe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SocieteController extends Controller
{
   /**
    * Affiche la page de Societe
    */
   public function index()
   {
       // Récupérer ou créer une Societe
       $Societe = Societe::first() ?? new Societe();
       $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

       return view('pages.parametre.societe.index', compact('Societe', 'date'));
   }

   /**
    * Met à jour la Societe
    */
   public function update(Request $request)
   {
       try {
           $validator = Validator::make($request->all(), 
           [
               'nom_societe' => 'required|string|max:255',
               'raison_sociale' => 'nullable|string|max:255',
               'forme_juridique' => 'nullable|string|max:100',
               'rccm' => 'nullable|string|max:50',
               'ifu' => [
                   'nullable',
                   'string',
                   'size:13',
                   'regex:/^[0-9]{13}$/'
               ],
               'rib' => 'nullable|string|max:24',
               'email' => 'nullable|email|max:255',
               'telephone_1' => [
                   'required',
                   'string',
                   'max:20',
                   'regex:/^[0-9\s\+\-\(\)]{8,}$/'
               ],
               'telephone_2' => [
                   'nullable',
                   'string',
                   'max:20',
                   'regex:/^[0-9\s\+\-\(\)]{8,}$/'
               ],
               'adresse' => 'nullable|string|max:255',
               'ville' => 'nullable|string|max:100',
               'pays' => 'nullable|string|max:100',
               'description' => 'nullable|string',
               'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
           ], 
           [
               'nom_societe.required' => 'Le nom de la société est obligatoire',
               'telephone_1.required' => 'Le numéro de téléphone principal est obligatoire',
               'telephone_1.regex' => 'Le format du numéro de téléphone est invalide',
               'telephone_2.regex' => 'Le format du numéro de téléphone est invalide',
               'email.email' => 'L\'adresse email n\'est pas valide',
               'ifu.size' => 'L\'IFU doit contenir exactement 13 chiffres',
               'ifu.regex' => 'L\'IFU doit contenir uniquement des chiffres',
               'logo.image' => 'Le fichier doit être une image',
               'logo.mimes' => 'Le logo doit être au format JPEG, PNG ou JPG',
               'logo.max' => 'La taille du logo ne doit pas dépasser 2Mo'
           ]);

           if ($validator->fails()) {
               return response()->json([
                   'success' => false,
                   'errors' => $validator->errors()
               ], 422);
           }

           // Récupérer ou créer la Societe
           $Societe = Societe::first() ?? new Societe();

           // Gérer le téléchargement du logo
           if ($request->hasFile('logo')) {
               // Supprimer l'ancien logo s'il existe
               if ($Societe->logo_path) {
                   Storage::disk('public')->delete($Societe->logo_path);
               }

               // Enregistrer le nouveau logo
               $logoPath = $request->file('logo')->store('logos', 'public');
               $Societe->logo_path = $logoPath;
           }

           // Mettre à jour les champs de base
           $Societe->fill($request->except('logo'));

           // Sauvegarder les modifications
           $Societe->save();

           return response()->json([
               'success' => true,
               'message' => 'Societe mise à jour avec succès',
               'data' => $Societe
           ]);

       } catch (\Exception $e) {
           \Log::error('Erreur lors de la mise à jour de la Societe:', [
               'error' => $e->getMessage(),
               'trace' => $e->getTraceAsString()
           ]);

           return response()->json([
               'success' => false,
               'message' => 'Une erreur est survenue lors de la mise à jour de la Societe',
               'error' => $e->getMessage()
           ], 500);
       }
   }

   /**
    * Supprime le logo
    */
   public function deleteLogo()
   {
       try {
           $Societe = Societe::first();

           if ($Societe && $Societe->logo_path) {
               // Supprimer le fichier
               Storage::disk('public')->delete($Societe->logo_path);

               // Mettre à jour la Societe
               $Societe->logo_path = null;
               $Societe->save();
           }

           return response()->json([
               'success' => true,
               'message' => 'Logo supprimé avec succès'
           ]);

       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Erreur lors de la suppression du logo',
               'error' => $e->getMessage()
           ], 500);
       }
   }

   /**
    * Réinitialise la Societe
    */
   public function reset()
   {
       try {
           $Societe = Societe::first();

           if ($Societe) {
               // Supprimer le logo s'il existe
               if ($Societe->logo_path) {
                   Storage::disk('public')->delete($Societe->logo_path);
               }

               // Supprimer la Societe
               $Societe->delete();
           }

           return response()->json([
               'success' => true,
               'message' => 'Societe réinitialisée avec succès'
           ]);

       } catch (\Exception $e) {
           return response()->json([
               'success' => false,
               'message' => 'Erreur lors de la réinitialisation de la Societe',
               'error' => $e->getMessage()
           ], 500);
       }
   }

   public function updateLogo(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'logo.required' => 'Veuillez sélectionner une image',
            'logo.image' => 'Le fichier doit être une image',
            'logo.mimes' => 'Le logo doit être au format JPEG, PNG ou JPG',
            'logo.max' => 'La taille du logo ne doit pas dépasser 2Mo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer la Societe
        $Societe = Societe::first() ?? new Societe();

        // Supprimer l'ancien logo s'il existe
        if ($Societe->logo_path) {
            Storage::disk('public')->delete($Societe->logo_path);
        }

        // Enregistrer le nouveau logo
        $logoPath = $request->file('logo')->store('logos', 'public');
        $Societe->logo_path = $logoPath;
        $Societe->save();

        return response()->json([
            'success' => true,
            'message' => 'Logo mis à jour avec succès',
            'data' => [
                'logo_path' => asset('storage/' . $logoPath)
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Erreur lors de la mise à jour du logo:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la mise à jour du logo'
        ], 500);
    }
}
}
