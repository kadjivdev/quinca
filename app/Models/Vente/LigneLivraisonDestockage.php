<?php

namespace App\Models\Vente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Stock\{StockMouvement, StockDepot, StockValorisation};
use App\Models\Vente\{FactureClient, LigneFacture, LivraisonClient};
use App\Models\Catalogue\{Article};
use App\Models\Parametre\{Depot, UniteMesure, ConversionUnite,};
use App\Models\Revendeur\LigneFactureRevendeur;
use Exception;
use Carbon\Carbon;

class LigneLivraisonDestockage extends Model
{
    use SoftDeletes;

    protected $table = 'ligne_livraison_destockages';

    protected $fillable = [
        'livraison_destockage_id',
        'ligne_facture_id',
        'article_id',
        'unite_vente_id',
        'quantite',
        'quantite_supplementaire',
        'quantite_base',
        'prix_unitaire',
        'montant_total',
        'mouvement_stock_id',
        'notes',
    ];

    protected $casts = [
        'quantite' => 'decimal:3',
        'quantite_base' => 'decimal:3',
        'prix_unitaire' => 'decimal:3',
        'montant_total' => 'decimal:3'
    ];

    function livraisonDestockage(): BelongsTo
    {
        return $this->belongsTo(LivraisonDestockage::class, "livraison_destockage_id");
    }

    function ligneFactureDestockage(): BelongsTo
    {
        return $this->belongsTo(LigneLivraisonDestockage::class, 'ligne_facture_id');
    }

    public function uniteVente(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, 'unite_vente_id');
    }

    public function mouvementStock(): BelongsTo
    {
        return $this->belongsTo(StockMouvement::class, 'mouvement_stock_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ligneLivraison) {
            if (empty($ligneLivraison->montant_total)) {
                $ligneLivraison->montant_total = $ligneLivraison->quantite * $ligneLivraison->prix_unitaire;
            }
        });
    }

    // Méthodes utiles pour la gestion des quantités
    public function getResteALivrerAttribute(): float
    {
        return $this->ligneFacture->quantite_base - $this->quantite_base;
    }

    public function getEstTotalementLivreAttribute(): bool
    {
        return $this->reste_a_livrer <= 0;
    }

    public function getQuantiteLivreeEnUniteVenteAttribute(): float
    {
        $article = $this->article;
        $uniteBaseId = $article->famille->unite_base_id;
        $uniteVenteId = $this->unite_vente_id;

        // Si même unité, retourner la quantité directement
        if ($uniteBaseId == $uniteVenteId) {
            return $this->quantite_base;
        }

        // Sinon, convertir de l'unité de base vers l'unité de vente
        $conversion = ConversionUnite::where(function ($query) use ($article, $uniteBaseId, $uniteVenteId) {
            $query->where('unite_source_id', $uniteBaseId)
                ->where('unite_dest_id', $uniteVenteId)
                ->where('famille_id', $article->famille_id)
                ->where('statut', true);
        })->orWhere(function ($query) use ($article, $uniteBaseId, $uniteVenteId) {
            $query->where('unite_source_id', $uniteVenteId)
                ->where('unite_dest_id', $uniteBaseId)
                ->where('famille_id', $article->famille_id)
                ->where('statut', true);
        })->first();

        if (!$conversion) {
            throw new Exception("Erreur de conversion d'unité pour l'article " . $this->article->designation);
        }

        // Conversion dans le bon sens
        return $conversion->unite_source_id == $uniteBaseId
            ? $conversion->convertir($this->quantite_base)
            : $conversion->convertirInverse($this->quantite_base);
    }

    public function verifierDisponibiliteStock(): bool
    {
        $stockDisponible = StockDepot::getStock($this->article_id, $this->livraison->depot_id);
        return $stockDisponible >= $this->quantite_base;
    }

    /**
     * Relation avec la livraison
     */
    public function livraison()
    {
        return $this->belongsTo(LivraisonDestockage::class, 'livraison_destockage_id');
    }

    /**
     * Relation avec la ligne de facture
     */
    public function ligneFacture()
    {
        return $this->belongsTo(LigneFactureRevendeur::class, 'ligne_facture_id');
    }

    /**
     * Relation avec l'article
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
