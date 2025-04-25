<?php

namespace App\Models\Revendeur;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Catalogue\Article;
use App\Models\Catalogue\Tarification;
use App\Models\Parametre\UniteMesure;
use App\Models\Parametre\ConversionUnite;
use App\Models\Vente\{FactureClient, LigneLivraisonClient};
use Exception;
use Illuminate\Support\Facades\Log;


class LigneFactureRevendeur extends Model
{
    protected $table = 'ligne_facture_revendeurs';  // Ajout de cette ligne

    protected $fillable = [
        'facture_revendeur_id',
        'article_id',
        'tarification_id',   // La tarification choisie
        'unite_vente_id',    // Unité choisie pour la vente
        'quantite',          // Quantité dans l'unité de vente
        'quantite_base',     // Quantité convertie en unité de base
        'quantite_livree',   // Quantité livrée en unité de base
        'prix_unitaire_ht',  // Prix unitaire de la tarification
        'taux_remise',
        'montant_remise',
        'montant_ht',
        'montant_ht_apres_remise',
        'taux_tva',
        'montant_tva',
        'taux_aib',
        'montant_aib',
        'montant_ttc'
    ];

    protected $casts = [
        'quantite' => 'decimal:3',
        'quantite_base' => 'decimal:3',
        'quantite_livree' => 'decimal:3',
        'prix_unitaire_ht' => 'decimal:3',
        'taux_remise' => 'decimal:2',
        'montant_remise' => 'decimal:3',
        'montant_ht' => 'decimal:3',
        'montant_ht_apres_remise' => 'decimal:3',
        'taux_tva' => 'decimal:2',
        'montant_tva' => 'decimal:3',
        'taux_aib' => 'decimal:2',
        'montant_aib' => 'decimal:3',
        'montant_ttc' => 'decimal:3'
    ];



    public function tarification(): BelongsTo
    {
        return $this->belongsTo(Tarification::class);
    }

    public function uniteVente(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, 'unite_vente_id');
    }




    // Convertit la quantité de l'unité de vente vers l'unité de base
public function convertirEnUniteBase(float $quantite): float
{
    try {
        $article = $this->article->load('uniteMesure');
        $uniteBaseId = $article->unite_mesure_id;
        $uniteVenteId = $this->unite_vente_id;

        // Log pour debug
        Log::info('Vérification des unités', [
            'unite_vente_id' => $uniteVenteId,
            'unite_base_id' => $uniteBaseId,
            'article_id' => $this->article_id
        ]);

        // Si l'unité de vente est la même que l'unité de base
        if ($uniteVenteId == $uniteBaseId) {
            Log::info('Unités identiques, pas de conversion nécessaire');
            return $quantite;
        }

        // Si on arrive ici, c'est que les unités sont différentes
        // On cherche alors une conversion
        $conversion = ConversionUnite::where(function($query) use ($uniteBaseId, $uniteVenteId) {
            $query->where('unite_source_id', $uniteVenteId)
                  ->where('unite_dest_id', $uniteBaseId)
                  ->where(function($q) {
                      $q->where('article_id', $this->article_id)
                        ->orWhereNull('article_id');
                  })
                  ->where('statut', true);
        })->orWhere(function($query) use ($uniteBaseId, $uniteVenteId) {
            $query->where('unite_source_id', $uniteBaseId)
                  ->where('unite_dest_id', $uniteVenteId)
                  ->where(function($q) {
                      $q->where('article_id', $this->article_id)
                        ->orWhereNull('article_id');
                  })
                  ->where('statut', true);
        })->first();

        if (!$conversion) {
            $uniteVente = UniteMesure::find($uniteVenteId);
            $uniteBase = UniteMesure::find($uniteBaseId);

            throw new Exception(
                "Aucune conversion trouvée entre l'unité de vente (" .
                $uniteVente->libelle_unite . ") et l'unité de base (" .
                $uniteBase->libelle_unite . ") pour l'article '" .
                $article->designation . "'"
            );
        }

        // Si la conversion est dans le sens inverse
        if ($conversion->unite_source_id == $uniteBaseId) {
            return $conversion->convertirInverse($quantite);
        }

        return $conversion->convertir($quantite);
    } catch (Exception $e) {
        Log::error('Erreur lors de la conversion d\'unité', [
            'message' => $e->getMessage(),
            'article_id' => $this->article_id,
            'unite_vente_id' => $this->unite_vente_id,
            'quantite' => $quantite
        ]);
        throw $e;
    }
}



    // Calcul des montants
    protected function calculerMontants()
    {
        // Montant HT avant remise
        $this->montant_ht = $this->quantite * $this->prix_unitaire_ht;

        // Calcul de la remise
        $this->montant_remise = $this->montant_ht * ($this->taux_remise / 100);

        // Montant HT après remise
        $this->montant_ht_apres_remise = $this->montant_ht - $this->montant_remise;

        // Calcul TVA
        $this->montant_tva = $this->montant_ht_apres_remise * ($this->taux_tva / 100);

        // Calcul AIB
        $this->montant_aib = $this->montant_ht_apres_remise * ($this->taux_aib / 100);

        // Montant TTC
        $this->montant_ttc = $this->montant_ht_apres_remise + $this->montant_tva + $this->montant_aib;
    }

    public function getResteALivrerBaseAttribute(): float
    {
        return $this->quantite_base - $this->quantite_livree;
    }

    public function getEstTotalementLivreAttribute(): bool
    {
        return $this->reste_a_livrer_base <= 0;
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ligneFacture) {
            // Convertir la quantité en unité de base
            $ligneFacture->quantite_base = $ligneFacture->convertirEnUniteBase($ligneFacture->quantite);

            // S'assurer que le taux_remise est défini
            if (!isset($ligneFacture->taux_remise)) {
                $ligneFacture->taux_remise = 0;
            }

            // Recalculer les montants
            $ligneFacture->calculerMontants();
        });
    }
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::saving(function ($ligneFacure) {
    //         // Définir le prix depuis la tarification
    //         $ligneFacure->prix_unitaire_ht = $ligneFacure->tarification_id;

    //         // Convertir la quantité en unité de base
    //         $ligneFacure->quantite_base = $ligneFacure->convertirEnUniteBase($ligneFacure->quantite);

    //         // Recalculer les montants
    //         $ligneFacure->calculerMontants();
    //     });
    // }

    /**
 * Relation avec les lignes de livraison
 */
public function lignesLivraison()
{
    return $this->hasMany(LigneLivraisonClient::class, 'ligne_facture_id');
}

/**
 * Relation avec la facture
 */
public function facture()
{
    return $this->belongsTo(FactureClient::class, 'facture_client_id');
}

/**
 * Relation avec l'article
 */
public function article()
{
    return $this->belongsTo(Article::class);
}

/**
 * Calcule le reste à livrer pour cette ligne
 */
public function getResteALivrerAttribute(): float
{
    $quantiteLivree = $this->lignesLivraison()
        ->whereHas('livraison', function($query) {
            $query->where('statut', 'valide');
        })
        ->sum('quantite_base');

    return max(0, $this->quantite_base - $quantiteLivree);
}

/**
 * Vérifie si la ligne est totalement livrée
 */
public function estTotalementLivree(): bool
{
    return $this->reste_a_livrer <= 0;
}

}
