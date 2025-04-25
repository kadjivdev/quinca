<?php

namespace App\Models\Parametre;

use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Class Chauffeur
 *
 * @property int $id
 * @property string $nom_chauf
 * @property string $telephone
 * @property string $numero_permis
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Chauffeur extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'chauffeurs';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'nom_chauf',
        'telephone',
        'numero_permis',
        'statut'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'nom_chauf' => 'required|string',
        'telephone' => 'required|string',
        'numero_permis' => 'required|string|unique:chauffeurs,numero_permis',
        'statut' => 'required|boolean',
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
     * Relation avec les véhicules assignés au chauffeur
     */
    public function vehicules()
    {
        return $this->belongsToMany(Vehicule::class, 'chauffeur_vehicule', 'chauffeur_id', 'vehicule_id')
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
