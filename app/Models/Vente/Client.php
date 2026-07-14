<?php

namespace App\Models\Vente;

use App\Models\Parametre\Agent;
use App\Models\Parametre\Departement;
use App\Models\Revendeur\FactureRevendeur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Securite\User;
use App\Models\Vente\SoldeInitialClient;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        'point_de_vente_id',
        'deleted_by',
        "agent_id",
        "zone_id",
        'departement_id'
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

    /**zone */
    function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, "zone_id");
    }

    /**Compte Client */
    public function compteClient(): HasMany
    {
        return $this->hasMany(CompteClient::class, "client_id")
            ->orderByDesc("date_op")
            ->with("client");
    }

    // Relations
    public function facturesClient(): HasMany
    {
        return $this->hasMany(FactureClient::class)->with("client");
    }

    // Factures Revendeurs
    public function facturesRevendeur(): HasMany
    {
        return $this->hasMany(FactureRevendeur::class)->with(["client", 'reglements']);
    }

    // Transports
    public function transports(): HasMany
    {
        return $this->hasMany(Transport::class, "client_id");
    }

    /** SOLDE DU CLIENT */
    public function solde()
    {
        // Factures clients
        $facturesAmount = $this->facturesClient()
            ->whereNotNull('validated_by')
            ->sum("montant_ttc") - $this->facturesClient()
            ->whereNotNull('validated_by')
            ->sum("montant_remise");

        /** Les transports */
        $clientTransportAmount = $this->transports
            ->whereNotNull("validate_at")
            ->sum("montant");

        //sum des règlements de chaque factures

        $reglementsAmount = $this->facturesClient
            ->whereNotNull('validated_by')
            ->pluck("reglements")
            ->flatten() //le flatten permet de regrouper les tableaux des reglements en un seul seul tableau
            ->whereNotNull('validated_by')
            ->sum("montant");

        /** Les accomptes */
        $clientAccomptesAmount = $this->acomptes
            ->whereNotNull("validated_by")
            ->sum("montant");

        if ($facturesAmount == 0 && $reglementsAmount == 0 && $clientTransportAmount == 0) {
            return $clientAccomptesAmount;
        }

        // return ($reglementsAmount + $clientAccomptesAmount) - ($facturesAmount + $clientTransportAmount);
        return ($reglementsAmount + $clientAccomptesAmount) - ($facturesAmount);
    }

    /** SOLDE DU CLIENT DAN SLE PANEL DES REVENDEURS */
    public function soldeRevendeur()
    {
        // Factures Revendeurs
        $facturesRevendeurAmount = $this->facturesRevendeur
            ->whereNotNull('validated_by')
            ->sum("montant_ttc") - $this->facturesRevendeur
            ->whereNotNull('validated_by')
            ->sum("montant_remise");

        //sum des règlements de chaque factures

        $reglementsAmount = $this->facturesRevendeur
            ->whereNotNull('validated_by')
            ->pluck("reglements")
            ->flatten() //le flatten permet de regrouper les tableaux des reglements en un seul seul tableau
            ->whereNotNull('validated_by')
            ->sum("montant");

        return $reglementsAmount - $facturesRevendeurAmount;
    }

    // Reglements
    public function reglements(): HasMany
    {
        return $this->hasMany(ReglementClient::class);
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

    /** MONTANT DES REGLEMENT DES FACTURES CLIENTS */
    function reglementFacturesClientsAmount(): float
    {
        return $this->facturesClient
            ->whereNotNull("validated_by")
            ->sum(function ($factureClient) {
                return $factureClient->reglements
                    ->whereNotNull("validated_by")
                    ->sum("montant");
            });
    }

    /** MONTANT DES REGLEMENT DES FACTURES REVENDEURS */
    function reglementFacturesRevendeursAmount(): float
    {
        return $this->facturesRevendeur
            ->whereNotNull("validated_by")
            ->sum(function ($factureRevendeur) {
                return $factureRevendeur->reglements
                    ->whereNotNull("validated_by")
                    ->sum("montant");
            });
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

        static::deleted(function ($client) {
            $client->update(["deleted_by" => Auth::id()]);
        });
    }

    // Règles de validation
    public static function rules($id = null): array
    {
        return [
            'raison_sociale' => array_merge(
                ['required', 'string', 'max:255'],
                $id ? [] : ['unique:clients,raison_sociale']
            ),
            'ifu' => 'nullable|string',
            'rccm' => 'nullable|string' ,
            'telephone' => ['nullable', 'string', 'max:20'],
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
            'agent_id' => 'required|integer',
            'zone_id' => 'nullable|integer',
        ];
    }
}
