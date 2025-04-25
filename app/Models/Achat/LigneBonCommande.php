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
 * Class LigneBonCommande
 *
 * @property int $id
 * @property int $bon_commande_id
 * @property int $article_id
 * @property int $unite_mesure_id
 * @property float $quantite
 * @property float $prix_unitaire
 * @property float $taux_remise
 * @property float $montant_ligne
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class LigneBonCommande extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'ligne_bon_commandes';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'bon_commande_id',
        'article_id',
        'unite_mesure_id',
        'quantite',
        'prix_unitaire',
        'taux_remise',
        'montant_ligne',
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'bon_commande_id' => 'required|exists:bon_commandes,id',
        'article_id' => 'required|exists:articles,id',
        'unite_mesure_id' => 'required|exists:unite_mesures,id',
        'quantite' => 'required|numeric|gt:0',
        'prix_unitaire' => 'required|numeric|gt:0',
        'taux_remise' => 'required|numeric|between:0,100',
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
        'bon_commande_id' => 'integer',
        'article_id' => 'integer',
        'unite_mesure_id' => 'integer',
        'quantite' => 'float',
        'prix_unitaire' => 'float',
        'taux_remise' => 'float',
        'montant_ligne' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Relation avec le bon de commande
     */
    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class, 'bon_commande_id');
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
     * Calcul du montant de la ligne
     */
    public function calculerMontantLigne()
    {
        $montant = $this->quantite * $this->prix_unitaire;
        if ($this->taux_remise > 0) {
            $montant = $montant * (1 - ($this->taux_remise / 100));
        }
        $this->montant_ligne = round($montant, 2);
    }

    /**
     * Mutateur pour la quantité
     */
    public function setQuantiteAttribute($value)
    {
        $this->attributes['quantite'] = $value;
        $this->calculerMontantLigne();
    }

    /**
     * Mutateur pour le prix unitaire
     */
    public function setPrixUnitaireAttribute($value)
    {
        $this->attributes['prix_unitaire'] = $value;
        $this->calculerMontantLigne();
    }

    /**
     * Mutateur pour le taux de remise
     */
    public function setTauxRemiseAttribute($value)
    {
        $this->attributes['taux_remise'] = $value;
        $this->calculerMontantLigne();
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
            if (empty($model->taux_remise)) {
                $model->taux_remise = 0;
            }
            $model->calculerMontantLigne();
        });

        // Avant la mise à jour
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            $model->calculerMontantLigne();
        });

        // Après la sauvegarde
        static::saved(function ($model) {
            // Met à jour le montant total du bon de commande
            if ($model->bonCommande) {
                $model->bonCommande->updateMontantTotal();
            }
        });
    }
}
