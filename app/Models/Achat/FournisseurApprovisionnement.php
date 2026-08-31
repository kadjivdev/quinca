<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FournisseurApprovisionnement extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        "montant",
        "fournisseur_id",
        "document",
        "source",
        "validated_at",
        "rejected_by",
        "validated_by",
        "date",
        'deleted_by',
        "reference"
    ];

    static public function rules($id = null)
    {
        // Récupère l'ID de l'approvisionnement concerné par la requête
        // $id = $this->route('approvisionnement'); // ou $this->approvisionnement si c'est un attribut/paramètre de route

        return [
            "fournisseur_id" => "required|integer",
            "montant" => "required|min:1",
            "source" => "required|in:DIRECTION,AGENT",
            "document" => $id ? "nullable" : "required",
            "date" => "required|date",
            "reference" => [
                "nullable",
                $id ?
                    Rule::unique('fournisseur_approvisionnements', 'reference')->ignore($id) :
                    Rule::unique('fournisseur_approvisionnements', 'reference'),
            ],
        ];
    }

    static function messages()
    {
        return [
            "fournisseur_id.required" => "Le fournisseur est réquis",
            "fournisseur_id.integer" => "Ce Champ doit être un entier",
            "montant.required" => "Le montant est requis",
            "montant.min" => "Le minimum du montant doit être 1",
            "source.required" => "Veuillez préciser la source(DIRECTION OU AGENT)",
            "document.required" => "La preuve est réquise",
            "date.required" => "Veuillez préciser la date",
            "date.date" => "La date doit être de format date",
            "reference.unique" => "Cette reference existe déjà",
        ];
    }

    function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, "fournisseur_id");
    }

    function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    protected static function boot()
    {
        parent::boot();

        // Avant la création
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->user_id = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->update(["deleted_by" => Auth::id()]);
            }
        });
    }
}
