<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestockageRequest extends FormRequest
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
            "reference" => [
                "required",
                Rule::unique('destockages', 'reference')
                    ->whereNull('deleted_at'),
            ],
            "depot_id" => "required|integer|exists:depots,id",
            "client_id" => "required|integer|exists:clients,id",
            "date_op" => "required|date",
            "observation" => "nullable",

            "lignes" => "required|array",
            "lignes.*.article_id" => "required|integer|exists:articles,id",
            "lignes.*.unite_mesure_id" => "required|integer|exists:unite_mesures,id",
            "lignes.*.qte" => "required|numeric",
            "lignes.*.pu" => "required|numeric",
            "lignes.*.montant" => "required|numeric",
        ];
    }
}
