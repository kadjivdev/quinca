<?php

namespace App\Models;

use App\Models\Catalogue\Article;
use App\Models\Catalogue\Inventaire;
use App\Models\Parametre\Depot;
use App\Models\Parametre\UniteMesure;
use App\Models\Securite\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RequeteStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero',
        'depot_id',
        'article_id',
        'unite_mesure_id',
        'user_id',
        'quantite',
        'commentaire',
        "preuve",
        'validated_by',
        'validated_at',

        'inventaire_id'
    ];

    protected $casts = [
        'depot_id' => "integer",
        'article_id' => "integer",
        'unite_mesure_id' => "integer",
        'user_id' => "integer",
        'quantite' => "float",
        'commentaire' => "string",
        'validated_by' => "integer",
        'validated_at' => "datetime",
    ];

    /**
     * Getter
     */
    // public function getCreatedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->locale("fr")->isoFormat("D MMMM YYYY H:m:s");
    // }

    public function getValidatedAtAttribute($value)
    {
        return Carbon::parse($value)->locale("fr")->isoFormat("D MMMM YYYY");
    }

    /**
     * Relations
     * */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, "article_id");
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, "depot_id");
    }

    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class, "inventaire_id");
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "validated_by");
    }

    public function uniteMesure(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, "unite_mesure_id");
    }

    // Validation
    public function validate(): bool
    {
        if (!$this->validated_by || !$this->validated_at) {
            return false;
        }
        return true;
    }

    // handle  preuve file
    public static function savePreuveFile($file)
    {
        if (!$file || !$file->isValid()) {
            Log::warning('Aucune preuve valide fournie pour la requête stock.', [
                'has_file' => (bool) $file,
            ]);
            return null;
        }

        $directory = public_path('stock_preuves');

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            Log::error('Impossible de créer le dossier de stockage des preuves.', ['directory' => $directory]);
            return null;
        }

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName) ?: 'preuve';
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . '_' . $safeName . ($extension ? '.' . $extension : '');

        try {
            $file->move($directory, $filename);
            return asset('stock_preuves/' . $filename);
        } catch (\Throwable $e) {
            Log::error('Erreur lors du déplacement de la preuve pour la requête stock.', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            return null;
        }
    }

    public function getPreuveUrl()
    {
        Log::debug("getPreuveUrl is called ...");

        $file = request()->file('preuve');
        if (!$file) {
            return $this->preuve;
        }

        return self::savePreuveFile($file) ?? $this->preuve;
    }

    // boot
    protected static function boot()
    {
        parent::boot();

        // creating
        static::creating(function ($model) {
            if (Auth::user()) {
                $model->user_id = Auth::id();
            }
        });

        // created
        static::created(function ($model) {
            $model->numero = "REQ-" . time() . "-STOCK";

            if (empty($model->preuve) && request()->hasFile('preuve') && request()->file('preuve')->isValid()) {
                $model->preuve = $model->getPreuveUrl();
            }

            $model->saveQuietly();
        });

        // updating
        static::updating(function ($model) {
            Log::info("Updating .....");
            if (request()->hasFile('preuve') && request()->file('preuve')->isValid()) {
                Log::info("Preuve existe .....");
                $model->preuve = $model->getPreuveUrl();
                $model->saveQuietly(); // VERY IMPORTANT
            }
        });
    }
}
