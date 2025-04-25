<?php

namespace App\Models\Catalogue;

use App\Models\Achat\LigneProgrammationAchat;
use App\Models\Achat\ProgrammationAchat;
use App\Models\Parametre\Depot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Models\Stock\StockDepot;
use App\Models\Parametre\UniteMesure;
use App\Models\Vente\DevisDetail;
use App\Models\Vente\FactureClient;
use App\Models\Vente\LigneFacture;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Article
 *
 * @property int $id
 * @property string $code_article
 * @property string $designation
 * @property string|null $description
 * @property int $famille_id
 * @property float $stock_minimum
 * @property float $stock_maximum
 * @property float $stock_securite
 * @property float $stock_actuel
 * @property string|null $code_barre
 * @property bool $stockable
 * @property string|null $emplacement_stock
 * @property string $statut
 * @property string|null $photo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read FamilleArticle $famille
 * @property-read Collection|Tarification[] $tarifications
 * @property-read Collection|StockDepot[] $stockDepots
 * @property-read Collection|StockPointVente[] $stockPointsVente
 */
class Article extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * Statuts possibles pour un article
     */
    public const STATUT_ACTIF = 'actif';
    public const STATUT_INACTIF = 'inactif';

    public const STATUTS = [
        self::STATUT_ACTIF,
        self::STATUT_INACTIF
    ];

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'code_article',
        'designation',
        'description',
        'famille_id',
        'stock_minimum',
        'stock_maximum',
        'stock_securite',
        'stock_actuel',
        'code_barre',
        'stockable',
        'emplacement_stock',
        'statut',
        'photo',
        'unite_mesure_id',
    ];

    /**
     * Les attributs à caster
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stockable' => 'boolean',
        'stock_minimum' => 'float',
        'stock_maximum' => 'float',
        'stock_securite' => 'float',
        'stock_actuel' => 'float',
        'famille_id' => 'integer',
        'unite_mesure_id' => 'integer'
    ];

    function detail(): BelongsTo
    {
        return $this->belongsTo(DevisDetail::class, "article_id");
    }

    /**
     * Obtient la famille de l'article
     */

    public function famille(): BelongsTo
    {
        return $this->belongsTo(FamilleArticle::class, 'famille_id');
    }

    /**
     * Obtient les tarifications de l'article
     */

    public function tarifications(): HasMany
    {
        return $this->hasMany(Tarification::class);
    }

    public function stocks()
    {
        return $this->hasMany(StockDepot::class, 'article_id', 'id');
    }

    function depots(): BelongsToMany
    {
        return $this->belongsToMany(Depot::class, "stock_depots", "article_id", "depot_id")->withPivot(["quantite_reelle"]);
    }

    public function uniteMesure()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_id');
    }

    /**
     * Les programmations attachées à cet article
     */

    function programmations(): HasMany
    {
        return $this->hasMany(LigneProgrammationAchat::class, "article_id");
    }

    /**
     * Les ventes attachées à cet article
     */

    function ventes(): HasMany
    {
        return $this->hasMany(LigneFacture::class, "article_id");
    }

    /**
     * Qte vendue dans un depot
     */
    function qteVendu($depotId=null)
    {
        return $this->hasMany(LigneFacture::class, "article_id")->where("depot",$depotId)->get()->filter(function ($vente) {
            if ($vente->factureClient->validated_by) {
                return $vente; // facture validées
            }
        });
    }

    /**
     * Calcul du reste de stock de l'article dans un depot
     */

    function reste($depotId = null)
    {
        // on recupere le stock de cet article dans ce dépot
        $stock = $this->stocks->where("depot_id", $depotId)->first();
        $qteReelle = $stock ? $stock->quantite_reelle : 0;

        $qteVendu = $this->qteVendu($depotId);
        return $qteReelle - $qteVendu->sum("quantite");
    }

    /**
     * Obtient les stocks en points de vente
     */

    // public function stockPointsVente(): HasMany
    // {
    //     return $this->hasMany(StockPointVente::class);
    // }

    /**
     * Filtre les articles actifs
     */
    public function scopeActif(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    /**
     * Filtre les articles stockables
     */
    public function scopeStockable(Builder $query): Builder
    {
        return $query->where('stockable', true);
    }

    /**
     * Filtre les articles avec stock faible
     */
    public function scopeStockFaible(Builder $query): Builder
    {
        return $query->where('stock_actuel', '<=', 'stock_securite');
    }

    /**
     * Recherche d'articles
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($query) use ($term) {
            $query->where('designation', 'LIKE', "%{$term}%")
                ->orWhere('code_article', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%")
                ->orWhere('code_barre', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Vérifie si le stock est en alerte
     */
    public function isStockAlert(): bool
    {
        return $this->stockable && $this->stock_actuel <= $this->stock_securite;
    }

    /**
     * Vérifie si le stock est critique
     */
    public function isStockCritique(): bool
    {
        return $this->stockable && $this->stock_actuel <= $this->stock_minimum;
    }

    /**
     * Met à jour le stock
     */
    public function updateStock(float $quantite, string $type = 'add'): bool
    {
        if (!$this->stockable) {
            return false;
        }

        $newStock = match ($type) {
            'add' => $this->stock_actuel + $quantite,
            'subtract' => $this->stock_actuel - $quantite,
            'set' => $quantite,
            default => $this->stock_actuel
        };

        if ($newStock < 0) {
            return false;
        }

        $this->stock_actuel = $newStock;
        return $this->save();
    }

    /**
     * Obtient le statut du stock
     */
    public function getStockStatus(): string
    {
        if (!$this->stockable) {
            return 'non_stockable';
        }

        if ($this->stock_actuel <= $this->stock_minimum) {
            return 'critique';
        }

        if ($this->stock_actuel <= $this->stock_securite) {
            return 'alerte';
        }

        if ($this->stock_actuel >= $this->stock_maximum) {
            return 'surplus';
        }

        return 'normal';
    }

    /**
     * Vérifie si l'article peut être commandé
     */
    public function canBeOrdered(): bool
    {
        return $this->statut === self::STATUT_ACTIF;
    }

    /**
     * Vérifie si le stock peut être modifié
     */
    public function canUpdateStock(): bool
    {
        return $this->stockable && $this->statut === self::STATUT_ACTIF;
    }
}
