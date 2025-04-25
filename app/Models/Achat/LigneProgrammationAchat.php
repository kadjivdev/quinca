<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use App\Models\Catalogue\{Article};
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class LigneProgrammationAchat
 *
 * @property int $id
 * @property int $programmation_id
 * @property int $article_id
 * @property int $unite_mesure_id
 * @property float $quantite
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class LigneProgrammationAchat extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'ligne_programmation_achats';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'programmation_id',
        'article_id',
        'unite_mesure_id',
        'quantite',
        'created_by',
        'updated_by',
        'depot'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'programmation_id' => 'required|exists:programmation_achats,id',
        'article_id' => 'required|exists:articles,id',
        'unite_mesure_id' => 'required|exists:unite_mesures,id',
        'quantite' => 'required|numeric|gt:0'
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux
     *
     * @var array
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    /**
     * Les attributs à caster
     *
     * @var array<string, string>
     */
    protected $casts = [
        'programmation_id' => 'integer',
        'article_id' => 'integer',
        'unite_mesure_id' => 'integer',
        'quantite' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Les attributs qui doivent être mutés en dates
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation avec la programmation d'achat
     */
    public function programmation()
    {
        return $this->belongsTo(ProgrammationAchat::class, 'programmation_id');
    }

    /**
     * Relation avec l'article
     */
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    /**
     * Relation avec l'unité de mesure
     */
    public function uniteMesure()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé la ligne
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour la ligne
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope pour filtrer par programmation
     */
    public function scopeByProgrammation(Builder $query, int $programmationId): Builder
    {
        return $query->where('programmation_id', $programmationId);
    }

    /**
     * Scope pour filtrer par article
     */
    public function scopeByArticle(Builder $query, int $articleId): Builder
    {
        return $query->where('article_id', $articleId);
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la création
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        // Avant la mise à jour
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
