<?php

namespace App\Models\Achat;

use App\Models\Parametre\Depot;
use App\Models\Securite\User;
use App\Models\Parametre\PointDeVente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class ProgrammationAchat
 *
 * @property int $id
 * @property string $code
 * @property Carbon $date_programmation
 * @property int $point_de_vente_id
 * @property int $fournisseur_id
 * @property string|null $commentaire
 * @property int|null $validated_by
 * @property Carbon|null $validated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class ProgrammationAchat extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'programmation_achats';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'date_programmation',
        'point_de_vente_id',
        'fournisseur_id',
        'commentaire',
        'validated_by',
        'validated_at',
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
        'code' => 'required|unique:programmation_achats,code',
        'date_programmation' => 'required|date',
        'point_de_vente_id' => 'required|exists:point_ventes,id',
        'fournisseur_id' => 'required|exists:fournisseurs,id',
        'commentaire' => 'nullable|string',
        'validated_by' => 'nullable|exists:users,id',
        'validated_at' => 'nullable|date'
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
        'date_programmation' => 'date',
        'validated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'point_de_vente_id' => 'integer',
        'fournisseur_id' => 'integer',
        'validated_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer'
    ];

    /**
     * Les attributs qui doivent être mutés en dates
     *
     * @var array
     */
    protected $dates = [
        'date_programmation',
        'validated_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Recherche de programmations d'achat
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($query) use ($term) {
            $query->where('code', 'LIKE', "%{$term}%")
                  ->orWhere('commentaire', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Mutateur pour le code
     */
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    /**
     * Relation avec le point de vente
     */
    public function pointVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

     /**
     * Relation avec le dépôt
     */
    public function _depot()
    {
        return $this->belongsTo(Depot::class, 'depot');
    }

    /**
     * Relation avec le fournisseur
     */
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé la programmation
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour la programmation
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relation avec l'utilisateur qui a validé la programmation
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function bonCommande()
    {
        return $this->hasOne(BonCommande::class, 'programmation_id');
    }

    /**
 * Relation avec les lignes de programmation
 */
public function lignes()
{
    return $this->hasMany(LigneProgrammationAchat::class, 'programmation_id');
}

    /**
     * Méthode pour valider une programmation
     */
    public function validate()
    {
        if (auth()->check()) {
            $this->validated_by = auth()->id();
            $this->validated_at = now();
            $this->save();
            return true;
        }
        return false;
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
