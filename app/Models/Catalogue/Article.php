<?php

namespace App\Models\Catalogue;

use App\Models\Achat\LigneProgrammationAchat;
use App\Models\Parametre\ConversionUnite;
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
use App\Models\Revendeur\LigneFactureRevendeur;
use App\Models\Vente\DevisDetail;
use App\Models\Vente\LigneFacture;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
use App\Services\ServiceStockEntree;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

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
        'deleted_by'
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

    /**
     * Obtient les tarifications de l'article
     * en fonction de l'unité Id
     */
    public function tarifViaUnite($uniteId)
    {
        return Tarification::firstWhere(["unite_mesure_id" => $uniteId]);
    }

    public function getUnites()
    {
        try {
            Log::info('Début récupération des unités', ['article_id' => $this->id]);

            // Récupérer l'article avec son unité de mesure
            $article = Article::with('uniteMesure')->findOrFail($this->id);

            $unites = collect();

            // 1. Ajouter l'unité de base de l'article si elle existe
            if ($article->uniteMesure) {
                $unites->push([
                    'id' => $article->uniteMesure->id,
                    'text' => $article->uniteMesure->libelle_unite
                ]);
            }

            // 2. Obtenir toutes les unités ayant des conversions pour cet article
            $unitesConversion = ConversionUnite::where('article_id', $this->id)
                ->where('statut', true)
                ->with(['uniteSource', 'uniteDest'])
                ->get();

            // Ajouter les unités source actives
            $unitesConversion->pluck('uniteSource')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites) {
                    if (!$unites->contains('id', $unite->id)) {
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite
                        ]);
                    }
                });

            // Ajouter les unités destination actives
            $unitesConversion->pluck('uniteDest')
                ->where('statut', true)
                ->unique('id')
                ->each(function ($unite) use (&$unites) {
                    if (!$unites->contains('id', $unite->id)) {
                        $unites->push([
                            'id' => $unite->id,
                            'text' => $unite->libelle_unite
                        ]);
                    }
                });

            Log::info('Unités récupérées avec succès', [
                'article_id' => $this->id,
                'nombre_unites' => $unites->count(),
                'unites' => $unites->toArray()
            ]);

            return $unites->values()->all();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des unités', [
                'article_id' => $this->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [];
        }
    }

    // les stocks de cet article dans tous les depots
    public function stocks()
    {
        return $this->hasMany(StockDepot::class, 'article_id', 'id');
    }

    // le stock de cet article dans un depot donné
    public function stockInDepot($depotId)
    {
        return $this->stocks->firstWhere("depot_id", $depotId)?->quantite_reelle;
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

    // les ventes de cet article dans un depot donné
    public function ventesInDepot($depotId)
    {
        return $this->ventes->where("depot_id", $depotId);
    }

    /**
     * Les ventes au niveau des revendeurs à cet article
     */

    function venteRevendeurs(): HasMany
    {
        return $this->hasMany(LigneFactureRevendeur::class, "article_id");
    }

    /**
     * LES DETAILS DES FACTURES CLIENTS
     */

    function facturesVente($depotId = null)
    {
        // return $this->hasMany(LigneFacture::class, "article_id")->where("depot", $depotId)
        //     ->get()->filter(function ($vente) {
        //         if ($vente->factureClient) {
        //             // if ($vente->factureClient->validated_by) {
        //             //     return $vente; // facture validées
        //             // }
        //             return $vente->factureClient->validated_by;
        //         }
        //     });

        return $this->hasMany(LigneFacture::class, "article_id")
            ->where("depot", $depotId)
            ->whereHas("factureClient", function ($query) {
                $query
                    ->whereNotNull("validated_by")
                    ->whereNull("inventaire_id"); //les ventes qui n'appartiennent à aucun inventaire
            })->get();
    }

    /**
     * Qte vendue revendeur dans un depot
     */
    function facturesVenteRevendeur($depotId = null)
    {
        return $this->hasMany(LigneFactureRevendeur::class, "article_id")
            ->where("depot", $depotId)
            ->whereHas("factureRevendeur", function ($query) {
                $query
                    ->whereNotNull("validated_by")
                    ->whereNull("inventaire_id"); //les ventes qui n'appartiennent à aucun inventaire
            })->get();
    }

    /**
     * Calcul de la quantité vendue (vente client, vente revendeurs, vente speciale) de l'article dans un depot
     */

    function qteVendu($depotId = null)
    {
        //Vente à la directeur
        $factureVentes = $this->facturesVente($depotId);

        $factureRevendeurs = $this->facturesVenteRevendeur($depotId);
        // if ($this->id == 1418 && $depotId == 4) {
        //     // dd(Depot::find($depotId));
        //     dd($factureVentes);
        // }
        //Vente revendeur (ventes speciales et autres)

        $qteVenteConvertie = 0;
        if ($factureVentes->isEmpty() && $factureRevendeurs->isEmpty()) {
            $qteVenteConvertie = 0;
        }

        /**Conversion qteVendu Client*/
        if ($factureVentes->isNotEmpty()) {
            $qteVenteConvertie += $factureVentes->sum("quantite_base");
            // foreach ($factureVentes as $vente) {
            // $conversion = $serviceStockEntree
            //     ->rechercherConversion(
            //         $vente->unite_vente_id,
            //         $stock->unite_mesure_id,
            //         $stock->article_id
            //     );

            //     $qteVenteConvertie += $serviceStockEntree
            //         ->convertirQuantite($factureVentes->sum("quantite"), $conversion, $stock->unite_mesure_id);
            // }
        }

        /**Conversion qteVendu Revendeur*/
        if ($factureRevendeurs->isNotEmpty()) {
            $qteVenteConvertie += $factureRevendeurs->sum("quantite_base");

            // foreach ($factureRevendeurs as $vente) {
            //     $conversion = $serviceStockEntree
            //         ->rechercherConversion(
            //             $vente->unite_vente_id,
            //             $stock->unite_mesure_id,
            //             $stock->article_id
            //         );

            //     $qteVenteConvertie += $serviceStockEntree
            //         ->convertirQuantite($factureRevendeurs->sum("quantite"), $conversion, $stock->unite_mesure_id);
            // }
        }

        return $qteVenteConvertie;
    }

    /**
     * Calcul du montant vendu (vente client, vente revendeurs, vente speciale) de l'article dans un depot
     */

    function montantTotalsVendu($depotId = null)
    {
        $factureVente = $this->facturesVente($depotId);
        $factureRevendeur = $this->facturesVenteRevendeur($depotId);

        $totalAmount = 0;
        if ($factureVente->isEmpty() && $factureRevendeur->isEmpty()) {
            $totalAmount = 0;
        }

        /**Conversion qteVendu Client*/
        if ($factureVente->isNotEmpty()) {
            $totalAmount += $factureVente->sum("montant_ttc") - $factureVente->sum("montant_remise");
        }

        /**Conversion qteVendu Revendeur*/
        if ($factureRevendeur->isNotEmpty()) {
            $totalAmount += $factureRevendeur->sum("montant_ttc") - $factureRevendeur->sum("montant_remise");
        }

        // dd($totalAmount);
        return $totalAmount;
    }

    /**
     * Calcul du reste de stock de l'article dans un depot
     */
    function reste($depotId = null)
    {
        // on recupere le stock de cet article dans ce dépot
        $stock = $this->stocks->firstWhere("depot_id", $depotId);
        // $stock = $this->stocks->Where("depot_id", $depotId)->sum("quantite_reelle");

        return $stock->quantite_reelle ?? 00 - $this->qteVendu($depotId);
    }

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

    /**Boot */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($article) {
            $article->update(["deleted_by" => Auth::id()]);
        });
    }
}
