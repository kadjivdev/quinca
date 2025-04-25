<?php

namespace App\Models\Parametre;

use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Class Vehicule
 *
 * @property int $id
 * @property string $matricule
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Vehicule extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'vehicules';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'matricule'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'matricule' => 'required|string|unique:vehicules,matricule'
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
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Mutateur pour le matricule
     */
    public function setMatriculeAttribute($value)
    {
        $this->attributes['matricule'] = strtoupper($value);
    }

    /**
     * Relation avec les chauffeurs assignés au véhicule
     */
    public function chauffeurs()
    {
        return $this->belongsToMany(Chauffeur::class, 'chauffeur_vehicule', 'vehicule_id', 'chauffeur_id')
                    ->withTimestamps();
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
