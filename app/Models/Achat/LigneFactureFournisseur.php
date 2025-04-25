<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class LigneFactureFournisseur
 *
 * @property int $id
 * @property int $facture_id
 * @property int $article_id
 * @property int $unite_mesure_id
 * @property float $quantite
 * @property float $prix_unitaire
 * @property float $taux_tva
 * @property float $taux_aib
 * @property float $montant_ht
 * @property float $montant_tva
 * @property float $montant_aib
 * @property float $montant_ttc
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $validated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $validated_at
 * @property Carbon|null $deleted_at
 */
class LigneFactureFournisseur extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'ligne_facture_fournisseurs';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'facture_id',
        'article_id',
        'unite_mesure_id',
        'quantite',
        'quantite_livree',
        'prix_unitaire',
        'taux_tva',
        'taux_aib',
        'montant_ht',
        'montant_tva',
        'montant_aib',
        'montant_ttc',
        'created_by',
        'updated_by',
        'validated_by'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'facture_id' => 'required|exists:facture_fournisseurs,id',
        'article_id' => 'required|exists:articles,id',
        'unite_mesure_id' => 'required|exists:unite_mesures,id',
        'quantite' => 'required|numeric|gt:0',
        'prix_unitaire' => 'required|numeric|gt:0',
        'taux_tva' => 'required|numeric|between:0,100',
        'taux_aib' => 'required|numeric|between:0,100',
        'created_by' => 'nullable|exists:users,id',
        'updated_by' => 'nullable|exists:users,id',
        'validated_by' => 'nullable|exists:users,id'
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
        'facture_id' => 'integer',
        'article_id' => 'integer',
        'unite_mesure_id' => 'integer',
        'quantite' => 'float',
        'prix_unitaire' => 'float',
        'taux_tva' => 'float',
        'taux_aib' => 'float',
        'montant_ht' => 'float',
        'montant_tva' => 'float',
        'montant_aib' => 'float',
        'montant_ttc' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'validated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'validated_at' => 'datetime',
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
        'validated_at',
        'deleted_at'
    ];

    /**
     * Relation avec la facture
     */
    public function facture()
    {
        return $this->belongsTo(FactureFournisseur::class, 'facture_id');
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
     * Relation avec l'utilisateur qui a validé la ligne
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Calcul des montants de la ligne
     */
    private function calculerMontants()
    {
        // Calcul du montant HT sans remise
        $this->montant_ht = $this->quantite * $this->prix_unitaire;

        // Calcul des taxes
        $this->montant_tva = $this->montant_ht * ($this->taux_tva / 100);
        $this->montant_aib = $this->montant_ht * ($this->taux_aib / 100);

        // Calcul du total TTC
        $this->montant_ttc = $this->montant_ht + $this->montant_tva + $this->montant_aib;
    }

    /**
     * Mutateur pour la quantité
     */
    public function setQuantiteAttribute($value)
    {
        $this->attributes['quantite'] = $value;
        $this->calculerMontants();
    }

    /**
     * Mutateur pour le prix unitaire
     */
    public function setPrixUnitaireAttribute($value)
    {
        $this->attributes['prix_unitaire'] = $value;
        $this->calculerMontants();
    }

    /**
     * Mutateur pour le taux TVA
     */
    public function setTauxTVAAttribute($value)
    {
        $this->attributes['taux_tva'] = $value;
        $this->calculerMontants();
    }

    /**
     * Mutateur pour le taux AIB
     */
    public function setTauxAIBAttribute($value)
    {
        $this->attributes['taux_aib'] = $value;
        $this->calculerMontants();
    }

    /**
     * Méthode de validation de la ligne
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
     * Vérifie si la ligne est validée
     */
    public function isValidated()
    {
        return !is_null($this->validated_at);
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
            if (empty($model->taux_tva)) {
                $model->taux_tva = 0;
            }
            if (empty($model->taux_aib)) {
                $model->taux_aib = 0;
            }
            $model->calculerMontants();
            $model->created_at = now();
        });

        // Avant la mise à jour
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            $model->calculerMontants();
            $model->updated_at = now();
        });

        // Après la sauvegarde
        static::saved(function ($model) {
            if ($model->facture) {
                $model->facture->updateMontants();
            }
        });
    }
}
