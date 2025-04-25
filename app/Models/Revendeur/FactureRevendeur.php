<?php

namespace App\Models\Revendeur;

use App\Models\Parametre\PointDeVente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\Securite\User;
use App\Models\Vente\{Client, ReglementClient};


class FactureRevendeur extends Model
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_VALIDE = 'valide';
    public const STATUT_ANNULE = 'annulee';

    protected $table = 'facture_revendeurs';

    protected $fillable = [
        'numero',
        'date_facture',
        'date_echeance',
        'date_validation',
        'client_id',
        'statut',
        'montant_ht',
        'montant_remise',
        'taux_remise',
        'montant_ht_apres_remise',
        'montant_tva',
        'taux_tva',
        'montant_aib',
        'taux_aib',
        'montant_ttc',
        'montant_regle',
        'notes',
        'type_vente',
        'point_de_vente_id',
        'encaisse',
        'encaissed_at',
        'created_by',
        'validated_by',
    ];

    protected $casts = [
        'date_facture' => 'datetime',
        'date_echeance' => 'datetime',
        'date_validation' => 'datetime',
        'montant_ht' => 'decimal:3',
        'montant_remise' => 'decimal:3',
        'taux_remise' => 'decimal:2',
        'montant_ht_apres_remise' => 'decimal:3',
        'montant_tva' => 'decimal:3',
        'taux_tva' => 'decimal:2',
        'montant_aib' => 'decimal:3',
        'taux_aib' => 'decimal:2',
        'montant_ttc' => 'decimal:3',
        'montant_regle' => 'decimal:3'
    ];

    /**
     * Génère un numéro de facture unique
     * Format: FAC-AAAAMMJJ-XXXX
     * où XXXX est un numéro séquentiel
     */
    public static function generateNumero()
    {
        $prefix = 'FAC';
        $date = Carbon::now()->format('Ymd');

        // Recherche de la dernière facture du jour
        $lastFacture = self::where('numero', 'like', "{$prefix}-{$date}-%")
            ->orderBy('numero', 'desc')
            ->first();

        if ($lastFacture) {
            // Extraction du numéro séquentiel et incrémentation
            $lastNumber = (int) substr($lastFacture->numero, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format du numéro sur 4 chiffres avec des zéros devant
        $sequence = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($facture) {
            // Génère automatiquement le numéro si non défini
            if (empty($facture->numero)) {
                $facture->numero = self::generateNumero();
            }

            // Définit automatiquement la date de création si non définie
            if (empty($facture->date_facture)) {
                $facture->date_facture = Carbon::now();
            }

            // Assigne l'utilisateur connecté
            if (empty($facture->created_by)) {
                $facture->created_by = auth()->id();
            }

            // Assure que le montant réglé ne dépasse pas le montant total
            if ($facture->montant_regle > $facture->montant_ttc) {
                $facture->montant_regle = $facture->montant_ttc;
            }
        });
    }


    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pointDeVente(): BelongsTo
    {
        return $this->belongsTo(PointDeVente::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneFactureRevendeur::class);
    }

    public function reglements(): HasMany
    {
        return $this->hasMany(ReglementClient::class);
    }

    public function getResteAReglerAttribute(): float
    {
        return $this->montant_ttc - $this->montant_regle;
    }

    public function getEstSoldeAttribute(): bool
    {
        return $this->reste_a_regler <= 0;
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }


/**
 * Obtient le reste à livrer pour une ligne
 */
public function getResteALivrerAttribute(): float
{
    $totalLivre = $this->lignes()
        ->join('ligne_livraison_clients', 'ligne_facture_revendeurs.id', '=', 'ligne_livraison_clients.ligne_facture_id')
        ->join('livraison_clients', 'ligne_livraison_clients.livraison_client_id', '=', 'livraison_clients.id')
        ->where('livraison_clients.statut', 'valide')
        ->sum('ligne_livraison_clients.quantite_base');

    $totalFacture = $this->lignes()->sum('quantite_base');

    return max(0, $totalFacture - $totalLivre);
}

/**
 * Obtient le pourcentage livré
 */
public function getPourcentageLivreAttribute(): float
{
    $totalFacture = $this->lignes()->sum('quantite_base');
    if ($totalFacture == 0) return 0;

    $totalLivre = $this->lignes()
        ->join('ligne_livraison_clients', 'ligne_facture_revendeurs.id', '=', 'ligne_livraison_clients.ligne_facture_id')
        ->join('livraison_clients', 'ligne_livraison_clients.livraison_client_id', '=', 'livraison_clients.id')
        ->where('livraison_clients.statut', 'valide')
        ->sum('ligne_livraison_clients.quantite_base');

    return min(100, ($totalLivre / $totalFacture) * 100);
}

/**
 * Vérifie si la livraison a commencé
 */
public function getLivraisonCommenceeAttribute(): bool
{
    return $this->livraisons()
        ->where('statut', 'valide')
        ->exists();
}


public function estTotalementLivree(): bool
{
    // Récupérer toutes les lignes de facture avec leurs quantités livrées
    $lignes = $this->lignes()->withSum('lignesLivraison as quantite_livree', 'quantite_base')
        ->whereHas('lignesLivraison', function($query) {
            $query->whereHas('livraison', function($q) {
                $q->where('statut', 'valide');
            });
        })
        ->get();

    // Si aucune ligne n'a été livrée, la facture n'est pas totalement livrée
    if ($lignes->isEmpty()) {
        return false;
    }

    // Vérifier chaque ligne
    foreach ($lignes as $ligne) {
        $quantiteLivree = $ligne->quantite_livree ?? 0;
        if ($quantiteLivree < $ligne->quantite_base) {
            return false;
        }
    }

    return true;
}



/**
 * Vérifie si la ligne peut encore être livrée
 */
public function peutEtreLivree(): bool
{
    // Si la facture n'est pas validée, elle ne peut pas être livrée
    if ($this->statut !== 'validee') {
        return false;
    }

    $lignes = $this->lignes()
        ->with(['lignesLivraison' => function($query) {
            $query->whereHas('livraison', function($q) {
                $q->where('statut', 'valide');
            });
        }])
        ->get();

    foreach ($lignes as $ligne) {
        $quantiteLivree = $ligne->lignesLivraison->sum('quantite_base');
        if ($quantiteLivree < $ligne->quantite_base) {
            return true; // Il reste au moins une ligne à livrer
        }
    }

    return false; // Toutes les lignes sont totalement livrées
}

/**
 * Récupère le reste à livrer pour chaque ligne
 */
public function getQuantitesRestantes(): array
{
    $result = [];

    $lignes = $this->lignes()
        ->with(['lignesLivraison' => function($query) {
            $query->whereHas('livraison', function($q) {
                $q->where('statut', 'valide');
            });
        }])
        ->get();

    foreach ($lignes as $ligne) {
        $quantiteLivree = $ligne->lignesLivraison->sum('quantite_base');
        $resteALivrer = $ligne->quantite_base - $quantiteLivree;

        if ($resteALivrer > 0) {
            $result[$ligne->id] = [
                'ligne_id' => $ligne->id,
                'article' => $ligne->article->designation,
                'quantite_base' => $ligne->quantite_base,
                'quantite_livree' => $quantiteLivree,
                'reste_a_livrer' => $resteALivrer
            ];
        }
    }

    return $result;
}

/**
 * Pour le debug : obtenir l'état détaillé des livraisons
 */
public function getStatutLivraison(): array
{
    $result = [];

    $lignes = $this->lignes()
        ->with(['lignesLivraison' => function($query) {
            $query->whereHas('livraison', function($q) {
                $q->where('statut', 'valide');
            });
        }, 'article'])
        ->get();

    foreach ($lignes as $ligne) {
        $quantiteLivree = $ligne->lignesLivraison->sum('quantite_base');
        $result[] = [
            'article' => $ligne->article->designation,
            'reference' => $ligne->article->reference,
            'quantite_facturee' => $ligne->quantite_base,
            'quantite_livree' => $quantiteLivree,
            'reste_a_livrer' => $ligne->quantite_base - $quantiteLivree,
            'est_totalement_livree' => $quantiteLivree >= $ligne->quantite_base,
            'lignes_livraison' => $ligne->lignesLivraison->map(function($ll) {
                return [
                    'livraison' => $ll->livraison->numero,
                    'quantite' => $ll->quantite_base,
                    'date' => $ll->livraison->date_livraison->format('d/m/Y')
                ];
            })
        ];
    }

    return $result;
}

}
