<?php

namespace App\Models\Achat;

use App\Models\Securite\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class Fournisseur
 *
 * @property int $id
 * @property string $code
 * @property string $nom
 * @property string|null $adresse
 * @property string|null $telephone
 * @property string|null $email
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Fournisseur extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'fournisseurs';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'code_fournisseur',
        'raison_sociale',
        'adresse',
        'telephone',
        'email',
        'created_by',
        'updated_by',
        'statut'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'nom' => 'required|unique:fournisseurs,nom',
        'code' => 'required|unique:fournisseurs,code',
        'email' => 'nullable|email',
        'telephone' => 'nullable|string'
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer'
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
     * Recherche de fournisseurs
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($query) use ($term) {
            $query->where('nom', 'LIKE', "%{$term}%")
                ->orWhere('code', 'LIKE', "%{$term}%")
                ->orWhere('email', 'LIKE', "%{$term}%");
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
     * Relation avec l'utilisateur qui a créé le fournisseur
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour le fournisseur
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    function facture_fournisseurs()
    {
        return $this->hasMany(FactureFournisseur::class, "fournisseur_id");
    }

    public function approvisionnements(): HasMany
    {
        return $this->hasMany(FournisseurApprovisionnement::class, "fournisseur_id")->whereNotNull("validated_by");
    }

    function reste_solde()
    {
        $appro_solde = $this->approvisionnements()->sum("montant");
        $reglements_amount = $this->facture_fournisseurs->sum(function ($query) {
            return $query->facture_reglements_amount();
        });
        return $appro_solde - $reglements_amount;
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

    // Dans le modèle Fournisseur

    public function factures()
    {
        return $this->hasMany(FactureFournisseur::class, 'fournisseur_id');
    }

    public function soldeInitial()
    {
        return $this->hasOne(SoldeInitialFournisseur::class, 'fournisseur_id');
    }
}
