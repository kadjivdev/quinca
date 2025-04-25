<?php

namespace App\Models\Vente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Securite\User;

class AcompteClient extends Model
{
    use SoftDeletes;

    // Constantes pour les types de paiement
    const TYPE_ESPECE = 'espece';
    const TYPE_VIREMENT = 'virement';
    const TYPE_CHEQUE = 'cheque';

    // Constantes pour les statuts
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_VALIDE = 'valide';
    const STATUT_REJETE = 'rejete';

    protected $fillable = [
        'date',
        'reference',
        'type_paiement',
        'montant',
        'client_id',
        'observation',
        'point_de_vente_id',
        'created_by',
        'statut',
        'validated_at',
        'validated_by'
    ];

    protected $casts = [
        'date' => 'datetime',
        'montant' => 'decimal:3',
        'validated_at' => 'datetime',
    ];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopeParType(Builder $query, string $type): Builder
    {
        return $query->where('type_paiement', $type);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('reference', 'like', "%{$term}%")
                ->orWhere('observation', 'like', "%{$term}%");
        });
    }

    // Méthode pour vérifier les statuts
    public function isEnAttente(): bool
    {
        return $this->statut === self::STATUT_EN_ATTENTE;
    }

    public function isValide(): bool
    {
        return $this->statut === self::STATUT_VALIDE;
    }

    public function isRejete(): bool
    {
        return $this->statut === self::STATUT_REJETE;
    }

    // Méthode pour générer automatiquement la référence
    public static function genererReference(): string
    {
        $prefix = 'ACP';
        $annee = date('Y');

        $dernierAcompte = self::withTrashed()
            ->where('reference', 'LIKE', "{$prefix}{$annee}%")
            ->orderBy('reference', 'desc')
            ->first();

        if (!$dernierAcompte) {
            return "{$prefix}{$annee}0001";
        }

        $numero = intval(substr($dernierAcompte->reference, -4)) + 1;
        return "{$prefix}{$annee}" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    // Boot du modèle
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($acompte) {
            // Générer automatiquement la référence si elle n'est pas fournie
            if (empty($acompte->reference)) {
                $acompte->reference = self::genererReference();
            }

            // Définir le statut par défaut si non fourni
            if (empty($acompte->statut)) {
                $acompte->statut = self::STATUT_EN_ATTENTE;
            }
        });

        static::created(function ($acompte) {
            // Mettre à jour le solde du client
            $acompte->client->updateSolde($acompte->montant, 'credit');
        });

        static::deleted(function ($acompte) {
            // Annuler l'effet sur le solde du client lors de la suppression
            $acompte->client->updateSolde($acompte->montant, 'debit');
        });
    }

    // Règles de validation
    public static function rules(): array
    {
        return [
            'date' => 'required|date',
            'type_paiement' => 'required|in:' . implode(',', [
                self::TYPE_ESPECE,
                self::TYPE_VIREMENT,
                self::TYPE_CHEQUE
            ]),
            'client_id' => 'required|exists:clients,id',
            'observation' => 'nullable|string',
            'statut' => 'nullable|in:' . implode(',', [
                self::STATUT_EN_ATTENTE,
                self::STATUT_VALIDE,
                self::STATUT_REJETE
            ]),
        ];
    }

    // Scopes supplémentaires pour les statuts
    public function scopeEnAttente(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    public function scopeValide(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_VALIDE);
    }

    public function scopeRejete(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_REJETE);
    }
}
