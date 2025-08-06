<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Avance extends Model
{
    use HasFactory, SoftDeletes;

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
        'fournisseur_id',
        'type_paiement',
        'montant',
        'reference',
        'observation',
        'statut',
        'created_by',
        'validated_by',
        'validated_at',
        'rejected_by',
        'rejected_at',
        'point_de_vente_id',
        'requete_id',
        'deleted_by'
    ];

    protected $casts = [
        'date' => 'datetime',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'montant' => 'decimal:2'
    ];

    // Relations
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function requete()
    {
        return $this->belongsTo(RequeteFournisseur::class, 'requete_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Scopes
    public function scopeParType($query, $type)
    {
        return $query->where('type_paiement', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('reference', 'like', '%' . $search . '%')
                ->orWhereHas('fournisseur', function ($q) use ($search) {
                    $q->where('raison_sociale', 'like', '%' . $search . '%');
                });
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
        $prefix = 'AVF';
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
            // $acompte->fournisseur->updateSolde($acompte->montant, 'credit');
        });

        static::deleted(function ($acompte) {
            $acompte->update(["deleted_by" => Auth::id()]);
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
            'fournisseur_id' => 'required|exists:fournisseurs,id',
            'montant' => 'required|numeric|min:1',
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
