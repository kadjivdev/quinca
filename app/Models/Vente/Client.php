<?php

namespace App\Models\Vente;

use App\Models\Parametre\Agent;
use App\Models\Parametre\Departement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Vente\{FactureClient, LigneFacture, ReglementClient};
use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Vente\SoldeInitialClient;

class Client extends Model
{
    use SoftDeletes;

    const CATEGORIE_PARTICULIER = 'particulier';
    const CATEGORIE_PROFESSIONNEL = 'professionnel';
    const CATEGORIE_SOCIETE = 'societe';

    protected $fillable = [
        'code_client',
        'raison_sociale',
        'ifu',
        'rccm',
        'telephone',
        'email',
        'adresse',
        'ville',
        'plafond_credit',
        'delai_paiement',
        'solde_initial',
        'solde_courant',
        'categorie',
        'statut',
        'notes',
        'created_by',
        'taux_aib',
        'point_de_vente_id'
    ];

    protected $casts = [
        'plafond_credit' => 'decimal:3',
        'solde_initial' => 'decimal:3',
        'solde_courant' => 'decimal:3',
        'delai_paiement' => 'integer',
        'statut' => 'boolean',
        'notes',
        'taux_aib',
    ];

    // Relations
    public function facturesClient(): HasMany
    {
        return $this->hasMany(FactureClient::class)->with("client");
    }

    public function reglements(): HasManyThrough
    {
        return $this->hasManyThrough(
            ReglementClient::class,
            FactureClient::class,
            'client_id', // Clé étrangère sur facture_clients
            'facture_client_id', // Clé étrangère sur reglement_clients
            'id', // Clé primaire sur clients
            'id' // Clé primaire sur facture_clients
        );
    }

    public function soldeInitial()
    {
        return $this->hasOne(SoldeInitialClient::class)->latestOfMany('date_solde');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    // Scopes
    public function scopeActif(Builder $query): Builder
    {
        return $query->where('statut', true);
    }

    public function scopeParCategorie(Builder $query, string $categorie): Builder
    {
        return $query->where('categorie', $categorie);
    }

    public function scopeAvecCredit(Builder $query): Builder
    {
        return $query->where('plafond_credit', '>', 0);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('raison_sociale', 'like', "%{$term}%")
                ->orWhere('code_client', 'like', "%{$term}%")
                ->orWhere('ifu', 'like', "%{$term}%")
                ->orWhere('rccm', 'like', "%{$term}%")
                ->orWhere('telephone', 'like', "%{$term}%");
        });
    }

    // Méthodes d'aide
    public function getFullIdentificationAttribute(): string
    {
        return "{$this->code_client} - {$this->raison_sociale}";
    }

    /**
     * Relation avec les acomptes clients
     */
    public function acomptes(): HasMany
    {
        return $this->hasMany(AcompteClient::class, 'client_id');
    }

    public function getEstActifAttribute(): bool
    {
        return $this->statut;
    }

    public function getDepassementCreditAttribute(): float
    {
        if ($this->plafond_credit <= 0) {
            return 0;
        }
        return max(0, $this->solde_courant - $this->plafond_credit);
    }

    public function hasDepassementCredit(): bool
    {
        return $this->depassement_credit > 0;
    }

    // Mise à jour du solde
    public function updateSolde(float $montant, string $type = 'debit'): bool
    {
        if ($type === 'debit') {
            $this->solde_courant += $montant;
        } else {
            $this->solde_courant -= $montant;
        }

        return $this->save();
    }

    // Vérification si le client peut être facturé
    public function peutEtreFacture(float $montant = 0): bool
    {
        if (!$this->statut) {
            return false;
        }

        if ($this->plafond_credit <= 0) {
            return true;
        }

        return ($this->solde_courant + $montant) <= $this->plafond_credit;
    }

    // Génération automatique du code client
    public static function genererCodeClient(): string
    {
        $prefix = 'CLI';
        $annee = date('Y');

        $dernierClient = self::withTrashed()->where('code_client', 'LIKE', "{$prefix}{$annee}%")
            ->orderBy('code_client', 'desc')
            ->first();

        if (!$dernierClient) {
            return "{$prefix}{$annee}0001";
        }

        $numero = intval(substr($dernierClient->code_client, -4)) + 1;
        return "{$prefix}{$annee}" . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    // Boot du modèle
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            // Générer automatiquement le code client s'il n'est pas fourni
            if (empty($client->code_client)) {
                $client->code_client = self::genererCodeClient();
            }
        });
    }

    // Règles de validation
    public static function rules($id = null): array
    {
        return [
            'raison_sociale' => 'required|string|max:255',
            'ifu' => 'nullable|string|unique:clients,ifu,' . $id,
            'rccm' => 'nullable|string|unique:clients,rccm,' . $id,
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string',
            'ville' => 'nullable|string',
            'plafond_credit' => 'required|numeric|min:0',
            'delai_paiement' => 'required|integer|min:0',
            'categorie' => 'required|in:particulier,professionnel,societe,comptoir',
            'solde_initial' => 'required|numeric|min:0',
            'statut' => 'required|boolean',
            'notes' => 'nullable|string',
            'taux_aib' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
