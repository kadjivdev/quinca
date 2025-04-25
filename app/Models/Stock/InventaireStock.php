<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Exception;

class InventaireStock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'depot_id',
        'date_debut',
        'date_fin',
        'statut',
        'type_inventaire',
        'notes',
        'user_id',
        'validateur_id',
        'date_validation'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'date_validation' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Constantes
    public const STATUT_BROUILLON = 'BROUILLON';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_TERMINE = 'TERMINE';
    public const STATUT_VALIDE = 'VALIDE';
    public const STATUT_ANNULE = 'ANNULE';

    public const STATUTS = [
        self::STATUT_BROUILLON,
        self::STATUT_EN_COURS,
        self::STATUT_TERMINE,
        self::STATUT_VALIDE,
        self::STATUT_ANNULE
    ];

    public const TYPE_COMPLET = 'COMPLET';
    public const TYPE_PARTIEL = 'PARTIEL';
    public const TYPE_TOURNANT = 'TOURNANT';

    public const TYPES = [
        self::TYPE_COMPLET,
        self::TYPE_PARTIEL,
        self::TYPE_TOURNANT
    ];

    // Relations
    public function lignes(): HasMany
    {
        return $this->hasMany(LigneInventaire::class, 'inventaire_id');
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    // Accesseurs
    public function getTotalEcartsPositifsAttribute(): float
    {
        return $this->lignes()->where('ecart', '>', 0)->sum('ecart');
    }

    public function getTotalEcartsNegatifsAttribute(): float
    {
        return $this->lignes()->where('ecart', '<', 0)->sum('ecart');
    }

    public function getValeurTotaleEcartsAttribute(): float
    {
        return $this->lignes()->sum('valeur_ecart');
    }

    // Méthodes de validation
    public function validate(): bool
    {
        if (empty($this->reference)) {
            return false;
        }
        if (empty($this->depot_id)) {
            return false;
        }
        if (!in_array($this->statut, self::STATUTS)) {
            return false;
        }
        if (!in_array($this->type_inventaire, self::TYPES)) {
            return false;
        }
        if ($this->date_debut > $this->date_fin) {
            return false;
        }

        return true;
    }

    // Méthodes de gestion de l'inventaire
    public function demarrer(): bool
    {
        if ($this->statut !== self::STATUT_BROUILLON) {
            throw new Exception("L'inventaire ne peut être démarré que depuis l'état brouillon");
        }

        $this->statut = self::STATUT_EN_COURS;
        $this->date_debut = now();
        return $this->save();
    }

    public function terminer(): bool
    {
        if ($this->statut !== self::STATUT_EN_COURS) {
            throw new Exception("L'inventaire doit être en cours pour être terminé");
        }

        $this->statut = self::STATUT_TERMINE;
        $this->date_fin = now();
        return $this->save();
    }

    public function valider($validateur): bool
    {
        if ($this->statut !== self::STATUT_TERMINE) {
            throw new Exception("L'inventaire doit être terminé pour être validé");
        }

        $this->statut = self::STATUT_VALIDE;
        $this->validateur_id = $validateur->id;
        $this->date_validation = now();
        return $this->save();
    }

    public function annuler(): bool
    {
        if (in_array($this->statut, [self::STATUT_VALIDE, self::STATUT_ANNULE])) {
            throw new Exception("L'inventaire ne peut plus être annulé");
        }

        $this->statut = self::STATUT_ANNULE;
        return $this->save();
    }

    // Méthodes de création/mise à jour
    public static function creer(array $data, $user): self
    {
        $inventaire = new self();

        $inventaire->reference = $data['reference'];
        $inventaire->depot_id = $data['depot_id'];
        $inventaire->type_inventaire = $data['type_inventaire'];
        $inventaire->notes = $data['notes'] ?? null;
        $inventaire->statut = self::STATUT_BROUILLON;
        $inventaire->user_id = $user->id;

        if (!$inventaire->validate()) {
            throw new Exception("Données de l'inventaire invalides");
        }

        $inventaire->save();
        return $inventaire;
    }

    public function ajouterLigne(array $data): LigneInventaire
    {
        if (!in_array($this->statut, [self::STATUT_BROUILLON, self::STATUT_EN_COURS])) {
            throw new Exception("Impossible d'ajouter une ligne à cet inventaire");
        }

        return $this->lignes()->create($data);
    }

    public function isPeutEtreModifie(): bool
    {
        return in_array($this->statut, [
            self::STATUT_BROUILLON,
            self::STATUT_EN_COURS
        ]);
    }
}
