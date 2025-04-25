<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalogue\Article;
use App\Models\Securite\User;
use App\Models\Parametre\{UniteMesure,Depot};
use Exception;

class StockMouvement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'date_mouvement',
        'type_mouvement',
        'depot_id',
        'article_id',
        'unite_mesure_id',
        'quantite',
        'prix_unitaire',
        'document_type',
        'document_id',
        'depot_source_id',
        'depot_dest_id',
        'user_id',
        'notes'
    ];

    protected $casts = [
        'date_mouvement' => 'datetime',
        'quantite' => 'float',
        'prix_unitaire' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Constantes pour les types de mouvement
    public const TYPE_ENTREE = 'ENTREE';
    public const TYPE_SORTIE = 'SORTIE';
    public const TYPE_TRANSFERT = 'TRANSFERT';
    public const TYPE_AJUSTEMENT = 'AJUSTEMENT';
    public const TYPE_RETOUR = 'RETOUR';

    public const TYPES_MOUVEMENTS = [
        self::TYPE_ENTREE,
        self::TYPE_SORTIE,
        self::TYPE_TRANSFERT,
        self::TYPE_AJUSTEMENT,
        self::TYPE_RETOUR
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

    public function depotSource(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'depot_source_id');
    }

    public function depotDestination(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'depot_dest_id');
    }

    public function uniteMesure(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Méthodes de validation
    public function validate(): bool
    {
        if (empty($this->code)) {
            return false;
        }
        if (empty($this->date_mouvement)) {
            return false;
        }
        if (!in_array($this->type_mouvement, self::TYPES_MOUVEMENTS)) {
            return false;
        }
        if (empty($this->depot_id)) {
            return false;
        }
        if (empty($this->article_id)) {
            return false;
        }
        if (empty($this->unite_mesure_id)) {
            return false;
        }
        if (empty($this->quantite) || $this->quantite <= 0) {
            return false;
        }

        if ($this->type_mouvement === self::TYPE_TRANSFERT) {
            if (empty($this->depot_source_id)) {
                return false;
            }
            if (empty($this->depot_dest_id)) {
                return false;
            }
            if ($this->depot_source_id === $this->depot_dest_id) {
                return false;
            }
        }

        return true;
    }

    // Méthodes de calcul
    public function getValeurMouvement(): float
    {
        return $this->quantite * $this->prix_unitaire;
    }

    // Méthodes de création
    public static function creer(array $data, $user): self
    {
        $mouvement = new self();

        $mouvement->code = strtoupper($data['code']);
        $mouvement->type_mouvement = $data['type_mouvement'];
        $mouvement->depot_id = $data['depot_id'];
        $mouvement->article_id = $data['article_id'];
        $mouvement->unite_mesure_id = $data['unite_mesure_id'];
        $mouvement->quantite = $data['quantite'];
        $mouvement->prix_unitaire = $data['prix_unitaire'];
        $mouvement->date_mouvement = $data['date_mouvement'] ?? now();
        $mouvement->document_type = $data['document_type'] ?? null;
        $mouvement->document_id = $data['document_id'] ?? null;
        $mouvement->depot_source_id = $data['depot_source_id'] ?? null;
        $mouvement->depot_dest_id = $data['depot_dest_id'] ?? null;
        $mouvement->notes = $data['notes'] ?? null;
        $mouvement->user_id = $user->id;

        if (!$mouvement->validate()) {
            throw new Exception("Données du mouvement invalides");
        }

        $mouvement->save();
        return $mouvement;
    }

    // Méthodes de mise à jour
    public function mettreAJour(array $data, $user): bool
    {
        if (isset($data['date_mouvement'])) {
            $this->date_mouvement = $data['date_mouvement'];
        }
        if (isset($data['unite_mesure_id'])) {
            $this->unite_mesure_id = $data['unite_mesure_id'];
        }
        if (isset($data['quantite'])) {
            if ($data['quantite'] <= 0) {
                throw new Exception("La quantité doit être positive");
            }
            $this->quantite = $data['quantite'];
        }
        if (isset($data['prix_unitaire'])) {
            if ($data['prix_unitaire'] < 0) {
                throw new Exception("Le prix unitaire ne peut pas être négatif");
            }
            $this->prix_unitaire = $data['prix_unitaire'];
        }

        $this->user_id = $user->id;
        return $this->save();
    }
}
