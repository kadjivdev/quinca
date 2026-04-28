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
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->locale("fr")->isoFormat("D MMMM YYYY");
    }

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
    function getPreuveUrl()
    {
        Log::debug("getPreuveUrl is called ...");

        $fileUrl = null;
        if (request()->hasFile("preuve")) {
            $file = request()->file("preuve");
            $name = time() . "_" . $file->getClientOriginalName();
            $file->move("stock_preuves", $name);
            $fileUrl = asset("/stock_preuves/" . $name);
        }

        return $fileUrl;
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
            $model->preuve = $model->getPreuveUrl();
            $model->saveQuietly();
        });

        // updating
        static::updating(function ($model) {
            Log::info("Updating .....");
            if (request()->hasFile("preuve")) {
                Log::info("Preuve existe .....");
                $model->preuve = $model->getPreuveUrl();
                $model->saveQuietly(); // VERY IMPORTANT
            }
        });
    }
}
