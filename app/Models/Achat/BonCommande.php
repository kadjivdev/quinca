<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use App\Models\Parametre\PointDeVente;
use App\Models\Achat\{LigneBonCommande, FactureFournisseur};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class BonCommande
 *
 * @property int $id
 * @property string $code
 * @property Carbon $date_commande
 * @property int $programmation_id
 * @property int $point_de_vente_id
 * @property int $fournisseur_id
 * @property float $montant_total
 * @property string|null $commentaire
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $validated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $validated_at
 * @property Carbon|null $deleted_at
 */
class BonCommande extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'bon_commandes';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'date_commande',
        'programmation_id',
        'point_de_vente_id',
        'fournisseur_id',
        'montant_total',
        'commentaire',
        'cout_transport',
        'cout_chargement',
        'autre_cout',
        'created_by',
        'updated_by',
        'validated_by',
        'validated_at',
        'motif_rejet',
        'rejected_by',
        'rejected_at',
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'code' => 'required|unique:bon_commandes,code',
        'date_commande' => 'required|date',
        'programmation_id' => 'required|exists:programmation_achats,id',
        'point_de_vente_id' => 'required|exists:point_ventes,id',
        'fournisseur_id' => 'required|exists:fournisseurs,id',
        'montant_total' => 'required|numeric|min:0',
        'commentaire' => 'nullable|string'
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux
     *
     * @var array
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'validated_by',
        'deleted_at'
    ];

    /**
     * Les attributs à caster
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_commande' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'validated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'programmation_id' => 'integer',
        'point_de_vente_id' => 'integer',
        'fournisseur_id' => 'integer',
        'montant_total' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'validated_by' => 'integer'
    ];

    /**
     * Les attributs qui doivent être mutés en dates
     *
     * @var array
     */
    protected $dates = [
        'date_commande',
        'created_at',
        'updated_at',
        'validated_at',
        'deleted_at'
    ];

    /**
     * Recherche de bons de commande
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
     * Relation avec la programmation
     */
    public function programmation()
    {
        return $this->belongsTo(ProgrammationAchat::class, 'programmation_id');
    }

    /**
     * Relation avec le point de vente
     */
    public function pointVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    /**
     * Relation avec le fournisseur
     */
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    /**
     * Relation avec les lignes de commande
     */
    public function lignes()
    {
        return $this->hasMany(LigneBonCommande::class, 'bon_commande_id');
    }

    /**
     * Relation avec les factures
     */
    public function factures()
    {
        return $this->hasMany(FactureFournisseur::class, 'bon_commande_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé le bon de commande
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour le bon de commande
     */

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relation avec l'utilisateur qui a validé le bon de commande
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Méthode pour mettre à jour le montant total
     */
    public function updateMontantTotal()
    {
        $this->montant_total = $this->lignes()->sum('montant_ligne');
        $this->save();
    }

    /**
     * Vérifie si le bon de commande est validé
     */
    public function isValidated()
    {
        return !is_null($this->validated_at);
    }

    /**
     * Valide le bon de commande
     */
    public function validate()
    {
        if (auth()->check() && !$this->isValidated()) {
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
            if (empty($model->date_commande)) {
                $model->date_commande = now();
            }
            if (empty($model->montant_total)) {
                $model->montant_total = 0;
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
