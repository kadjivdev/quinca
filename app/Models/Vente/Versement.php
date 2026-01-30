<?php

namespace App\Models\Vente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class Versement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'reference_op',
        'client_id',
        'date_op',
        'type_op',
        'montant',
        'date_valeur',
        'comment',
        'extourned_comment',
        'banque',
        'preuve',
        'extourned_at',

        'validated_at',
        'created_by',
        'validated_by',
        'deleted_by',
        'extourned_by',
    ];

    protected $casts = [
        'date_op' => 'datetime',
        'montant' => 'decimal:2',

        'validated_at' => 'datetime',
        'extourned_at' => 'datetime',
        'date_depot' => 'datetime',
        'date_valeur' => 'datetime',
    ];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    function accompteClient(): HasOne
    {
        return $this->hasOne(AcompteClient::class, "versement_id");
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function extournedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'extourned_by');
    }

    // Scopes
    public function scopeParType(Builder $query, string $type): Builder
    {
        return $query->where('type_op', $type);
    }


    // Méthode pour générer automatiquement la référence
    public static function genererReference(): string
    {
        $prefix = 'VERS';
        $annee = date('Y');

        $dernierAcompte = self::withTrashed()
            ->where('reference', 'LIKE', "{$prefix}{$annee}%")
            ->orderBy('reference', 'desc')
            ->first();

        if (!$dernierAcompte) {
            return "{$prefix}{$annee}0001";
        }

        $numero = intval(substr($dernierAcompte->reference, -4)) + 1;
        return "{$prefix}{$annee}" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    // handle  preuve file
    static function getPreuveUrl()
    {
        Log::info("getPreuveUrl is called ...");
        $fileUrl = null;
        if (request()->hasFile("preuve")) {
            $file = request()->file("preuve");
            $name = time() . "_" . $file->getClientOriginalName();
            $file->move("preuves", $name);
            $fileUrl = asset("/preuves/" . $name);
        }

        return $fileUrl;
    }

    // Boot du modèle
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();

            // Générer automatiquement la référence si elle n'est pas fournie
            if (empty($model->reference)) {
                $model->reference = self::genererReference();
            }
            $model->preuve = self::getPreuveUrl();
        });

        static::deleted(function ($model) {
            $model->deleted_by = Auth::id();
            $model->saveQuietly();
        });

        static::updating(function ($model) {
            if (request()->hasFile("preuve")) {
                $model->preuve = self::getPreuveUrl();
            }
        });
    }

    // Règles de validation
    public static function rules($id = null): array
    {
        return [
            'reference' => ["nullable", "string", Rule::unique("versements", "reference")->ignore($id)],
            'reference_op' => ["required", "string", Rule::unique("versements", "reference_op")->ignore($id)],
            'client_id' => "required|exists:clients,id",
            'date_op' => "required|date",
            'type_op' => "required|in:Chèque,MoMo",
            'montant' => "required|numeric",
            'date_valeur' => "required|date",
            'comment' => "nullable|string",
            'extourned_comment' => "nullable",
            'banque' => "required",
            'extourned_at' => "nullable|string",
            'preuve' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public static function messages(): array
    {
        return [
            'reference.string' => 'La référence doit être une chaîne de caractères.',
            'reference.unique' => 'Cette référence existe déjà.',

            'reference_op.required' => 'La référence de l’opération est obligatoire.',
            'reference_op.string' => 'La référence de l’opération doit être une chaîne de caractères.',
            'reference_op.unique' => 'Cette référence d’opération existe déjà.',

            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné est invalide.',

            'date_op.required' => 'La date de dépôt est obligatoire.',
            'date_op.date' => 'La date de dépôt doit être une date valide.',

            'type_op.required' => 'Le type d’opération est obligatoire.',
            'type_op.in' => 'Le type d’opération doit être Chèque ou MoMo.',

            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',

            'date_valeur.required' => 'La date de valeur est obligatoire.',
            'date_valeur.date' => 'La date de valeur doit être une date valide.',

            'comment.string' => 'Le commentaire doit être une chaîne de caractères.',

            'banque.required' => 'La banque est obligatoire.',

            'extourned_at.string' => 'La date d’extourne doit être une chaîne de caractères.',

            'preuve.required' => 'La preuve est obligatoire.',
            'preuve.file' => 'La preuve doit être un fichier.',
            'preuve.mimes' => 'La preuve doit être de type : jpg, jpeg, png ou pdf.',
            'preuve.max' => 'La taille de la preuve ne doit pas dépasser 2 Mo.',
        ];
    }
}
