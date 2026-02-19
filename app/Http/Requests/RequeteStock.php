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
            'depot_id' => 'required|integer|exists:depots,id',
            'article_id' => 'required|integer|exists:articles,id',
            'unite_mesure_id' => 'required|integer|exists:unite_mesures,id',
            'quantite' => 'required|numeric',
            'commentaire' => 'nullable|string|max:1000',
            'preuve' => 'nullable|file|mimes:pdf,doc,docx,png,jpeg|max:2048',
        ];
    }

    /**messages */
    public function messages(): array
    {
        return [
            'depot_id.required' => 'Le dépôt est obligatoire.',
            'depot_id.integer' => 'Le dépôt doit être un identifiant valide.',
            'depot_id.exists' => 'Le dépôt sélectionné n\'existe pas.',

            'article_id.required' => 'L\'article est obligatoire.',
            'article_id.integer' => 'L\'article doit être un identifiant valide.',
            'article_id.exists' => 'L\'article sélectionné n\'existe pas.',

            'unite_mesure_id.required' => 'L\'unité de mesure est obligatoire.',
            'unite_mesure_id.integer' => 'L\'unité de mesure doit être valide.',
            'unite_mesure_id.exists' => 'L\'unité de mesure sélectionnée n\'existe pas.',

            'quantite.required' => 'La quantité est obligatoire.',

            'commentaire.string' => 'Le commentaire doit être un texte.',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',

            'preuve.file' => 'La preuve doit être un fichier valide.',
            'preuve.mimes' => 'La preuve doit être un fichier de type : PDF,PNG,JPEG, DOC ou DOCX.',
            'preuve.max' => 'La taille du fichier ne doit pas dépasser 2 Mo.',
        ];
    }
}
