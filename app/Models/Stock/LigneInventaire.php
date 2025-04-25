<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalogue\Article;
use Exception;

class LigneInventaire extends Model
{
    protected $fillable = [
        'inventaire_id',
        'article_id',
        'quantite_theorique',
        'quantite_physique',
        'ecart',
        'prix_unitaire',
        'valeur_ecart',
        'justification',
        'status',
        'user_id',
        'date_comptage'
    ];

    protected $casts = [
        'quantite_theorique' => 'float',
        'quantite_physique' => 'float',
        'ecart' => 'float',
        'prix_unitaire' => 'float',
        'valeur_ecart' => 'float',
        'date_comptage' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Constantes
    public const STATUS_OK = 'OK';
    public const STATUS_ECART_POSITIF = 'ECART_POSITIF';
    public const STATUS_ECART_NEGATIF = 'ECART_NEGATIF';

    public const STATUTS = [
        self::STATUS_OK,
        self::STATUS_ECART_POSITIF,
        self::STATUS_ECART_NEGATIF
    ];

    // Relations
    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(InventaireStock::class, 'inventaire_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Méthodes de calcul
    public function calculerEcart(): void
    {
        $this->ecart = $this->quantite_physique - $this->quantite_theorique;
        $this->valeur_ecart = $this->ecart * $this->prix_unitaire;

        $this->status = match(true) {
            $this->ecart > 0 => self::STATUS_ECART_POSITIF,
            $this->ecart < 0 => self::STATUS_ECART_NEGATIF,
            default => self::STATUS_OK
        };
    }

    // Méthodes de validation
    public function validate(): bool
    {
        if (empty($this->inventaire_id)) {
            return false;
        }
        if (empty($this->article_id)) {
            return false;
        }
        if ($this->quantite_theorique < 0) {
            return false;
        }
        if ($this->quantite_physique < 0) {
            return false;
        }
        if ($this->prix_unitaire < 0) {
            return false;
        }
        if (!in_array($this->status, self::STATUTS)) {
            return false;
        }

        return true;
    }

    // Méthodes de création/mise à jour
    public static function creer(array $data, $user): self
    {
        $ligne = new self();

        $ligne->inventaire_id = $data['inventaire_id'];
        $ligne->article_id = $data['article_id'];
        $ligne->quantite_theorique = $data['quantite_theorique'];
        $ligne->quantite_physique = $data['quantite_physique'];
        $ligne->prix_unitaire = $data['prix_unitaire'];
        $ligne->justification = $data['justification'] ?? null;
        $ligne->date_comptage = $data['date_comptage'] ?? now();
        $ligne->user_id = $user->id;

        $ligne->calculerEcart();

        if (!$ligne->validate()) {
            throw new Exception("Données de la ligne d'inventaire invalides");
        }

        $ligne->save();
        return $ligne;
    }

    public function mettreAJourComptage(float $quantite_physique, ?string $justification, $user): bool
    {
        if (!$this->inventaire->isPeutEtreModifie()) {
            throw new Exception("Cette ligne d'inventaire ne peut plus être modifiée");
        }

        $this->quantite_physique = $quantite_physique;
        $this->justification = $justification;
        $this->user_id = $user->id;
        $this->date_comptage = now();

        $this->calculerEcart();

        if (!$this->validate()) {
            throw new Exception("Données de la ligne d'inventaire invalides");
        }

        return $this->save();
    }

    // Accesseurs
    public function getPourcentageEcartAttribute(): float
    {
        if ($this->quantite_theorique == 0) {
            return $this->quantite_physique > 0 ? 100 : 0;
        }

        return ($this->ecart / $this->quantite_theorique) * 100;
    }

    public function getRequiertJustificationAttribute(): bool
    {
        return abs($this->pourcentage_ecart) > 5; // Seuil de 5% configurable
    }
}
