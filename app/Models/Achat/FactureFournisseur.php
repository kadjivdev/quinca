<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use App\Models\Parametre\PointDeVente;
use App\Models\Achat\{LigneFactureFournisseur, BonCommande};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class FactureFournisseur
 *
 * @property int $id
 * @property string $code
 * @property Carbon $date_facture
 * @property int $bon_commande_id
 * @property int $point_de_vente_id
 * @property int $fournisseur_id
 * @property float $montant_ht
 * @property float $montant_tva
 * @property float $montant_aib
 * @property float $montant_ttc
 * @property string $statut_livraison
 * @property string $statut_paiement
 * @property string|null $commentaire
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $validated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $validated_at
 * @property Carbon|null $deleted_at
 */
class FactureFournisseur extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'facture_fournisseurs';

    protected $fillable = [
        'code',
        'date_facture',
        'bon_commande_id',
        'point_de_vente_id',
        'fournisseur_id',
        'montant_ht',
        'montant_tva',
        'montant_aib',
        'montant_ttc',
        'statut_livraison',
        'statut_paiement',
        'commentaire',
        'created_by',
        'updated_by',
        'validated_by',
        'taux_tva',
        'taux_aib',
        'validated_at',
        'motif_rejet',
        'rejected_by',
        'rejected_at',
    ];

    public static $rules = [
        'code' => 'required|unique:facture_fournisseurs,code',
        'date_facture' => 'required|date',
        'bon_commande_id' => 'required|exists:bon_commandes,id',
        'point_de_vente_id' => 'required|exists:point_de_ventes,id',
        'fournisseur_id' => 'required|exists:fournisseurs,id',
        'statut_livraison' => 'required|in:NON_LIVRE,PARTIELLEMENT_LIVRE,LIVRE',
        'statut_paiement' => 'required|in:NON_PAYE,PARTIELLEMENT_PAYE,PAYE',
        'commentaire' => 'nullable|string',
        'created_by' => 'nullable|exists:users,id',
        'updated_by' => 'nullable|exists:users,id',
        'validated_by' => 'nullable|exists:users,id',
        'rejected_by' => 'nullable|exists:users,id'
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'validated_by',
        'deleted_at'
    ];

    protected $casts = [
        'date_facture' => 'date',
        'bon_commande_id' => 'integer',
        'point_de_vente_id' => 'integer',
        'fournisseur_id' => 'integer',
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

    protected $dates = [
        'date_facture',
        'created_at',
        'updated_at',
        'validated_at',
        'deleted_at'
    ];

    public function facture_amont()
    {
        $regleUnique = $this->reglements->whereNotNull("validated_by"); ## reglement par selection unique
        $regleMultiple = $this->reglements_grouped()->whereNotNull("validated_by")->count(); ## reglement par selection multiple

        $montant_reglement_unique = $regleUnique ? $regleUnique->sum('montant_reglement') : 0;
        $montant_reglement_multiple = $regleMultiple ? $this->montant_ttc : 0;
        $montant_reglement =  $montant_reglement_unique + $montant_reglement_multiple;

        return $this->montant_ttc - $montant_reglement;
    }

    function facture_reglements_amount()
    {
        $regleUnique = $this->reglements->whereNotNull("validated_by"); ## reglement par selection unique
        $regleMultiple = $this->reglements_grouped()->whereNotNull("validated_by")->count(); ## reglement par selection multiple

        $montant_reglement_unique = $regleUnique ? $regleUnique->sum('montant_reglement') : 0;
        $montant_reglement_multiple = $regleMultiple ? $this->montant_ttc : 0;
        $montant_reglement =  $montant_reglement_unique + $montant_reglement_multiple;

        return $montant_reglement;
    }

    /**
     * 
     * Recherche de factures
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
     * Relation avec le bon de commande
     */
    public function bonCommande()
    {
        return $this->belongsTo(BonCommande::class, 'bon_commande_id');
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
     * Relation avec les lignes de facture
     */
    public function lignes()
    {
        return $this->hasMany(LigneFactureFournisseur::class, 'facture_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé la facture
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour la facture
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relation avec l'utilisateur qui a validé la facture
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec le bon de livraison
     */
    public function bonLivraison()
    {
        return $this->hasOne(BonLivraisonFournisseur::class, 'facture_id');
    }

    public function reglements()
    {
        return $this->hasMany(ReglementFournisseur::class, 'facture_fournisseur_id');
    }

    // en cas de reglements multiple
    public function reglements_grouped()
    {
        $allRegleManyFactures =  ReglementFournisseur::whereNotNull("factures")->get()->filter(function ($query) {
            $factureIds = explode(",", $query->factures);
            return in_array($this->id, $factureIds);
        });

        return $allRegleManyFactures;
    }

    /**
     * Méthode pour mettre à jour les montants
     */
    public function updateMontants()
    {
        $this->montant_ht = $this->lignes()->sum('montant_ht');
        $this->montant_tva = $this->lignes()->sum('montant_tva');
        $this->montant_aib = $this->lignes()->sum('montant_aib');
        $this->montant_ttc = $this->lignes()->sum('montant_ttc');
        $this->save();
    }

    /**
     * Vérifie si la facture peut être modifiée
     */
    public function isModifiable()
    {
        return !$this->isValidated() && $this->statut_paiement === 'NON_PAYE';
    }

    /**
     * Méthode pour valider la facture
     */
    public function validate()
    {
        if (auth()->check() && $this->isModifiable()) {
            $this->validated_by = auth()->id();
            $this->validated_at = now();
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Vérifie si la facture est validée
     */
    public function isValidated()
    {
        return !is_null($this->validated_at);
    }

    /**
     * Marque la facture comme livrée
     */
    public function marquerCommeLivree()
    {
        if ($this->statut_livraison !== 'LIVRE') {
            $this->statut_livraison = 'LIVRE';
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Marque la facture comme payée
     */
    public function marquerCommePayee()
    {
        if ($this->statut_paiement !== 'PAYE') {
            $this->statut_paiement = 'PAYE';
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Marque la facture comme partiellement livrée
     */
    public function marquerCommePartiellementLivree()
    {
        if ($this->statut_livraison === 'NON_LIVRE') {
            $this->statut_livraison = 'PARTIELLEMENT_LIVRE';
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Marque la facture comme partiellement payée
     */
    public function marquerCommePartiellementPayee()
    {
        if ($this->statut_paiement === 'NON_PAYE') {
            $this->statut_paiement = 'PARTIELLEMENT_PAYE';
            $this->save();
            return true;
        }
        return false;
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->date_facture)) {
                $model->date_facture = now();
            }
            if (empty($model->statut_livraison)) {
                $model->statut_livraison = 'NON_LIVRE';
            }
            if (empty($model->statut_paiement)) {
                $model->statut_paiement = 'NON_PAYE';
            }
            if (empty($model->montant_ht)) {
                $model->montant_ht = 0;
            }
            if (empty($model->montant_tva)) {
                $model->montant_tva = 0;
            }
            if (empty($model->montant_aib)) {
                $model->montant_aib = 0;
            }
            if (empty($model->montant_ttc)) {
                $model->montant_ttc = 0;
            }
            $model->created_at = now();
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            $model->updated_at = now();
        });
    }
}
