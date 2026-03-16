<?php

namespace App\Models\Vente;

use App\Models\Parametre\Transportation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Securite\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TransportMouvement extends Model
{
    use SoftDeletes;

    protected $appends = ["formated_date"];

    protected $fillable = [
        'reference',
        'transportation_id',
        'client_id',
        'date',
        'montant',
        'comment',
        'preuve',

        'validated_at',
        'created_by',
        'validated_by',
        'deleted_by',
        'extourned_by',
    ];

    protected $casts = [
        'date' => 'datetime',
        'montant' => 'decimal:2',

        'validated_at' => 'datetime',
        'extourned_at' => 'datetime',
    ];

    // Relations
    public function transportation(): BelongsTo
    {
        return $this->belongsTo(Transportation::class, "transportation_id");
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // getters
    function getFormatedDateAttribute($value)
    {
        return Carbon::parse($value)->locale("fr")->isoFormat("D MMMM YYYY");
    }

    // Méthode pour générer automatiquement la référence
    public static function genererReference(): string
    {
        $prefix = 'TRANS';
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
            'reference' => ["nullable", "string", Rule::unique("transport_mouvements", "reference")->ignore($id)],
            'transportation_id' => "required|exists:transportations,id",
            'client_id' => "required|exists:clients,id",
            'date' => "required|date",
            'montant' => "required|numeric",
            'comment' => "nullable|string",
            'preuve' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public static function messages(): array
    {
        return [
            'reference.string' => 'La référence doit être une chaîne de caractères.',
            'reference.unique' => 'Cette référence existe déjà.',

            'client_id.required' => 'Le client est obligatoire.',
            'client_id.exists' => 'Le client sélectionné est invalide.',

            'transportation_id.required' => 'Le moyen de transport est obligatoire.',
            'transportation_id.exists' => 'Le moyen de transport sélectionné est invalide.',

            'date.required' => 'La date est obligatoire.',
            'date.date' => 'La date doit être une date valide.',

            'montant.required' => 'Le montant est obligatoire.',
            'montant.numeric' => 'Le montant doit être un nombre.',

            'comment.string' => 'Le commentaire doit être une chaîne de caractères.',

            'preuve.required' => 'La preuve est obligatoire.',
            'preuve.file' => 'La preuve doit être un fichier.',
            'preuve.mimes' => 'La preuve doit être de type : jpg, jpeg, png ou pdf.',
            'preuve.max' => 'La taille de la preuve ne doit pas dépasser 2 Mo.',
        ];
    }
}
