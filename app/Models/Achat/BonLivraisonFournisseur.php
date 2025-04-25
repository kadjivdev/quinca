<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use App\Models\Parametre\{PointDeVente, Vehicule, Chauffeur, Depot};
use App\Models\Achat\{Ligne, FactureFournisseur};
use App\Models\Catalogue\{Article};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class BonLivraisonFournisseur
 *
 * @property int $id
 * @property string $code
 * @property Carbon $date_livraison
 * @property int $facture_id
 * @property int $point_de_vente_id
 * @property int $depot_id
 * @property int $fournisseur_id
 * @property int|null $vehicule_id
 * @property int|null $chauffeur_id
 * @property string|null $commentaire
 * @property string|null $motif_rejet
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $validated_by
 * @property int|null $rejected_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $validated_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $deleted_at
 */
class BonLivraisonFournisseur extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'bon_livraison_fournisseurs';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'date_livraison',
        'facture_id',
        'point_de_vente_id',
        'depot_id',
        'fournisseur_id',
        'vehicule_id',
        'chauffeur_id',
        'commentaire',
        'motif_rejet',
        'created_by',
        'updated_by',
        'validated_by',
        'validated_at',
        'rejected_by',
        'rejected_at'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    
    public static $rules = [
        'code' => 'required|unique:bon_livraisons,code',
        'date_livraison' => 'required|date',
        'facture_id' => 'required|exists:facture_fournisseurs,id',
        'point_de_vente_id' => 'required|exists:point_ventes,id',
        'depot_id' => 'required|exists:depots,id',
        'fournisseur_id' => 'required|exists:fournisseurs,id',
        'vehicule_id' => 'nullable|exists:vehicules,id',
        'chauffeur_id' => 'nullable|exists:chauffeurs,id',
        'commentaire' => 'nullable|string',
        'motif_rejet' => 'nullable|string|required_with:rejected_by'
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
        'rejected_by',
        'deleted_at'
    ];

    /**
     * Les attributs à caster
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_livraison' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'deleted_at' => 'datetime',
        'facture_id' => 'integer',
        'point_de_vente_id' => 'integer',
        'depot_id' => 'integer',
        'fournisseur_id' => 'integer',
        'vehicule_id' => 'integer',
        'chauffeur_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'validated_by' => 'integer',
        'rejected_by' => 'integer'
    ];

    /**
     * Les attributs qui doivent être mutés en dates
     *
     * @var array
     */
    protected $dates = [
        'date_livraison',
        'created_at',
        'updated_at',
        'validated_at',
        'rejected_at',
        'deleted_at'
    ];

    /**
     * Relation avec le véhicule
     */
    public function vehicule()
    {
        return $this->belongsTo(Vehicule::class, 'vehicule_id');
    }

    /**
     * Relation avec le point de vente
     */
    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }

    /**
     * Relation avec les lignes du bon de livraison
     */
    public function lignes()
    {
        return $this->hasMany(LigneBonLivraisonFournisseur::class, 'livraison_id');
    }

    /**
     * Relation avec le chauffeur
     */
    public function chauffeur()
    {
        return $this->belongsTo(Chauffeur::class, 'chauffeur_id');
    }

    public function facture()
    {
        return $this->belongsTo(FactureFournisseur::class, 'facture_id');
    }

    /**
     * Relation avec l'utilisateur qui a validé le bon de livraison
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec l'utilisateur qui a créé le bon de livraison
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour le bon de livraison
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
