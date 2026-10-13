<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequeteStock extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lignes'                    => 'required|array|min:1',
            'lignes.*.article_id'       => 'required|exists:articles,id',
            'lignes.*.depot_id'         => 'required|exists:depots,id',
            'lignes.*.unite_mesure_id'  => 'required|exists:unite_mesures,id',
            'lignes.*.quantite'         => 'required|numeric',
            'commentaire' => 'nullable|string|max:1000',
            'preuve' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg|max:2048',
        ];
    }

    /**messages */
    public function messages(): array
    {
        return [
            // Lignes
            'lignes.required' => 'Veuillez ajouter au moins une ligne à la requête.',
            'lignes.array'    => 'Le format des lignes est invalide.',
            'lignes.min'      => 'Veuillez ajouter au moins une ligne à la requête.',

            // Article
            'lignes.*.article_id.required' => 'Ligne :position : veuillez sélectionner un article.',
            'lignes.*.article_id.exists'   => 'Ligne :position : l\'article sélectionné est introuvable.',

            // Dépôt
            'lignes.*.depot_id.required' => 'Ligne :position : veuillez sélectionner un dépôt.',
            'lignes.*.depot_id.exists'   => 'Ligne :position : le dépôt sélectionné est introuvable.',

            // Unité de mesure
            'lignes.*.unite_mesure_id.required' => 'Ligne :position : veuillez sélectionner une unité de mesure.',
            'lignes.*.unite_mesure_id.exists'   => 'Ligne :position : l\'unité de mesure sélectionnée est introuvable.',

            // Quantité
            'lignes.*.quantite.required' => 'Ligne :position : la quantité est obligatoire.',
            'lignes.*.quantite.numeric'  => 'Ligne :position : la quantité doit être un nombre.',
            'lignes.*.quantite.gt'       => 'Ligne :position : la quantité doit être supérieure à 0.',

            // Commentaire
            'commentaire.string' => 'Le commentaire doit être une chaîne de caractères.',
            'commentaire.max'    => 'Le commentaire ne doit pas dépasser :max caractères.',

            // Preuve
            'preuve.file'  => 'La preuve justificative doit être un fichier valide.',
            'preuve.mimes' => 'La preuve justificative doit être de type : pdf, doc, docx, png ou jpeg.',
            'preuve.max'   => 'La preuve justificative ne doit pas dépasser 2 Mo.',
        ];
    }
}
