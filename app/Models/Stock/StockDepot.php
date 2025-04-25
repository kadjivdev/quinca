<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Catalogue\Article;
use App\Models\Parametre\Depot;
use App\Models\Securite\User;
use Exception;

class StockDepot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'depot_id',
        'article_id',
        'quantite_reelle',
        'quantite_reservee',
        'prix_moyen',
        'date_dernier_mouvement',
        'date_dernier_inventaire',
        'seuil_alerte',
        'stock_minimum',
        'stock_maximum',
        'emplacement',
        'user_id',
        'unite_mesure_id'
    ];

    protected $casts = [
        'quantite_reelle' => 'float',
        'quantite_reservee' => 'float',
        'prix_moyen' => 'float',
        'seuil_alerte' => 'float',
        'stock_minimum' => 'float',
        'stock_maximum' => 'float',
        'date_dernier_mouvement' => 'datetime',
        'date_dernier_inventaire' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(StockMouvement::class, 'depot_id');
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accesseurs
    public function getQuantiteDisponibleAttribute(): float
    {
        return $this->quantite_reelle - $this->quantite_reservee;
    }

    public function getValeurStockAttribute(): float
    {
        return $this->quantite_reelle * $this->prix_moyen;
    }

    // Méthodes de gestion du stock
    public function reserver(float $quantite): bool
    {
        if ($quantite > $this->quantite_disponible) {
            throw new Exception("Quantité disponible insuffisante pour la réservation");
        }

        $this->quantite_reservee += $quantite;
        return $this->save();
    }

    public function annulerReservation(float $quantite): bool
    {
        if ($quantite > $this->quantite_reservee) {
            throw new Exception("Impossible d'annuler plus que la quantité réservée");
        }

        $this->quantite_reservee -= $quantite;
        return $this->save();
    }

    public function traiterMouvement(StockMouvement $mouvement): bool
    {
        switch ($mouvement->type_mouvement) {
            case StockMouvement::TYPE_ENTREE:
                $this->traiterEntree($mouvement);
                break;
            case StockMouvement::TYPE_SORTIE:
                $this->traiterSortie($mouvement);
                break;
            case StockMouvement::TYPE_AJUSTEMENT:
                $this->traiterAjustement($mouvement);
                break;
            default:
                throw new Exception("Type de mouvement non géré");
        }

        $this->date_dernier_mouvement = now();
        return $this->save();
    }

    protected function traiterEntree(StockMouvement $mouvement): void
    {
        $ancien_stock = $this->quantite_reelle;
        $ancien_prix = $this->prix_moyen;
        $nouvelle_quantite = $mouvement->quantite;
        $nouveau_prix = $mouvement->prix_unitaire;

        // Calcul du nouveau CUMP
        $this->prix_moyen = (($ancien_stock * $ancien_prix) + ($nouvelle_quantite * $nouveau_prix))
                           / ($ancien_stock + $nouvelle_quantite);
        $this->quantite_reelle += $nouvelle_quantite;
    }

    protected function traiterSortie(StockMouvement $mouvement): void
    {
        if ($mouvement->quantite > $this->quantite_disponible) {
            throw new Exception("Stock insuffisant pour cette sortie");
        }

        $this->quantite_reelle -= $mouvement->quantite;
        // Le CUMP reste inchangé lors d'une sortie
    }

    protected function traiterAjustement(StockMouvement $mouvement): void
    {
        $this->quantite_reelle = $mouvement->quantite;
        $this->date_dernier_inventaire = now();
    }

    // Validation
    public function validate(): bool
    {
        if (empty($this->depot_id)) {
            return false;
        }
        if (empty($this->article_id)) {
            return false;
        }
        if ($this->quantite_reelle < 0) {
            return false;
        }
        if ($this->quantite_reservee < 0) {
            return false;
        }
        if ($this->quantite_reservee > $this->quantite_reelle) {
            return false;
        }
        if ($this->prix_moyen < 0) {
            return false;
        }
        if ($this->stock_maximum < $this->stock_minimum) {
            return false;
        }
        return true;
    }

    // Méthodes de création/mise à jour
    public static function creer(array $data, $user): self
    {
        $stock = new self();

        $stock->depot_id = $data['depot_id'];
        $stock->article_id = $data['article_id'];
        $stock->quantite_reelle = $data['quantite_reelle'] ?? 0;
        $stock->quantite_reservee = $data['quantite_reservee'] ?? 0;
        $stock->prix_moyen = $data['prix_moyen'] ?? 0;
        $stock->seuil_alerte = $data['seuil_alerte'] ?? null;
        $stock->stock_minimum = $data['stock_minimum'] ?? null;
        $stock->stock_maximum = $data['stock_maximum'] ?? null;
        $stock->emplacement = $data['emplacement'] ?? null;
        $stock->user_id = $user->id;

        if (!$stock->validate()) {
            throw new Exception("Données du stock invalides");
        }

        $stock->save();
        return $stock;
    }

    public function mettreAJour(array $data, $user): bool
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->$key = $value;
            }
        }

        $this->user_id = $user->id;

        if (!$this->validate()) {
            throw new Exception("Données du stock invalides");
        }

        return $this->save();
    }

    // Méthodes utilitaires
    public function isEnAlerte(): bool
    {
        return $this->seuil_alerte && $this->quantite_reelle <= $this->seuil_alerte;
    }

    public function isStockMinimum(): bool
    {
        return $this->stock_minimum && $this->quantite_reelle <= $this->stock_minimum;
    }

    public function isStockMaximum(): bool
    {
        return $this->stock_maximum && $this->quantite_reelle >= $this->stock_maximum;
    }
}
