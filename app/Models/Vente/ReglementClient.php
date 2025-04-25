<?php

namespace App\Models\Vente;

use App\Models\securite\User;
use App\Models\Vente\FactureClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;


class ReglementClient extends Model
{
    use SoftDeletes;

    // Constantes de statut
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_VALIDE = 'validee';
    public const STATUT_ANNULE = 'annulee';

    // Constantes de type de règlement
    public const TYPE_ESPECE = 'espece';
    public const TYPE_CHEQUE = 'cheque';
    public const TYPE_VIREMENT = 'virement';
    public const TYPE_CARTE_BANCAIRE = 'carte_bancaire';
    public const TYPE_MOMO = 'MoMo';
    public const TYPE_FLOOZ = 'Flooz';
    public const TYPE_CELTIS = 'Celtis_Pay';
    public const TYPE_EFFET = 'Effet';
    public const TYPE_AVOIR = 'Avoir';

    protected $table = 'reglement_clients';

    protected $fillable = [
        'numero',
        'session_caisse_id',
        'facture_client_id',
        'date_reglement',
        'montant',
        'type_reglement',
        'reference_preuve',
        'banque',
        'date_echeance',
        'notes',
        'statut',
        'created_by',
        'validated_by',
        'validated_at'
    ];

    protected $casts = [
        'date_reglement' => 'datetime',
        'date_echeance' => 'datetime',
        'montant' => 'decimal:3',
        'validated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Validation rules
    private static array $validationRules = [
        'espece' => [],
        'cheque' => ['reference_preuve', 'banque', 'date_echeance'],
        'virement' => ['reference_preuve', 'banque'],
        'carte_bancaire' => ['reference_preuve'],
        'effet' => ['reference_preuve', 'banque', 'date_echeance'],
        'avoir' => ['reference_preuve']
    ];

    /**
     * Liste des types de règlement disponibles avec leurs libellés
     *
     * @return array<string, string>
     */
    public static function getTypesReglement(): array
    {
        return [
            self::TYPE_ESPECE => 'Espèce',
            self::TYPE_CHEQUE => 'Chèque',
            self::TYPE_VIREMENT => 'Virement',
            self::TYPE_CARTE_BANCAIRE => 'Carte Bancaire',
            self::TYPE_MOMO => 'MoMo',
            self::TYPE_FLOOZ => 'Flooz',
            self::TYPE_CELTIS => 'Celtis_Pay',
            self::TYPE_EFFET => 'Effet',
            self::TYPE_AVOIR => 'Avoir'
        ];
    }


    /**
     * Relation avec la facture client
     */
    public function facture(): BelongsTo
    {
        return $this->belongsTo(FactureClient::class, 'facture_client_id')->with("lignes");
    }

    /**
     * Relation avec l'utilisateur qui a créé le règlement
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a validé le règlement
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Génère un nouveau numéro de règlement
     *
     * @return string
     */
    public static function genererNumero(): string
    {
        $prefix = 'REG';
        $date = Carbon::now()->format('Ym');
        $lastNumber = 0;

        $dernierReglement = self::withTrashed()
            ->where('numero', 'LIKE', "{$prefix}{$date}%")
            ->orderByDesc('numero')
            ->value('numero');

        if ($dernierReglement) {
            $lastNumber = (int) substr($dernierReglement, -4);
        }

        return sprintf('%s%s%04d', $prefix, $date, $lastNumber + 1);
    }



    /**
     * Vérifie si le montant du règlement est valide
     *
     * @throws \InvalidArgumentException Si la facture n'est pas chargée
     * @return bool
     */

    public function verifierMontant()
    {
        // S'assurer que la facture est chargée
        if (!$this->facture) {
            throw new \Exception("La relation facture doit être chargée pour vérifier le montant");
        }

        // Si c'est un nouveau règlement
        if (!$this->exists) {
            // Pour un nouveau règlement, calculer le total déjà réglé
            $totalDejaRegle = $this->facture->reglements()
                ->where('statut', 'validee')
                ->sum(DB::raw('ROUND(montant, 3)'));
        } else {
            // Pour un règlement existant, exclure le règlement actuel
            $totalDejaRegle = $this->facture->reglements()
                ->where('id', '!=', $this->id)
                ->where('statut', 'validee')
                ->sum(DB::raw('ROUND(montant, 3)'));
        }

        // Arrondir les montants à 3 décimales pour la comparaison
        $montantTTC = round($this->facture->montant_ttc, 3);
        $montantReglement = round($this->montant, 3);
        $resteAPayer = round($montantTTC - $totalDejaRegle, 3);

        // Debug pour voir les valeurs
        Log::info('Vérification du montant du règlement', [
            'facture_id' => $this->facture->id,
            'montant_ttc' => $montantTTC,
            'total_deja_regle' => $totalDejaRegle,
            'reste_a_payer' => $resteAPayer,
            'montant_reglement' => $montantReglement
        ]);

        // Vérifier si le montant est positif et ne dépasse pas le reste à payer
        // Utiliser une petite marge d'erreur pour les arrondis
        $epsilon = 0.001; // Marge d'erreur de 0.001
        return $montantReglement > 0 &&
            $montantReglement <= ($resteAPayer + $epsilon);
    }

    /**
     * Vérifie si le règlement peut être validé
     *
     * @return bool
     */
    
    public function peutEtreValide(): bool
    {
        return $this->statut === self::STATUT_BROUILLON
            && $this->verifierMontant()
            && $this->facture->statut === FactureClient::STATUT_VALIDE
            && $this->verifierInformationsRequises()
            && !$this->facture->est_solde;  // Vérification supplémentaire
    }

    /**
     * Valide le règlement
     *
     * @param int $userId
     * @throws \Exception
     * @return bool
     */

    public function valider(int $userId): bool
    {
        // Vérifier si le règlement peut être validé
        if ($this->statut !== self::STATUT_BROUILLON) {
            throw new \Exception("Ce règlement ne peut pas être validé car il n'est pas en brouillon");
        }

        if (!$this->verifierMontant()) {
            throw new \Exception("Le montant du règlement est invalide par rapport au reste à payer");
        }

        return DB::transaction(function () use ($userId) {
            // Mise à jour du règlement
            $this->update([
                'statut' => self::STATUT_VALIDE,
                'validated_by' => $userId,
                'validated_at' => now()
            ]);

            // Mise à jour du montant réglé de la facture
            $nouveauMontantRegle = round($this->facture->montant_regle + $this->montant, 3);

            if ($nouveauMontantRegle > round($this->facture->montant_ttc + 0.001, 3)) {
                throw new \Exception("Le montant total réglé dépasserait le montant de la facture");
            }

            $this->facture->update([
                'montant_regle' => $nouveauMontantRegle
            ]);

            return true;
        });
    }

    /**
     * Annule le règlement
     *
     * @throws \Exception
     * @return bool
     */

    public function annuler(): bool
    {
        if ($this->statut !== self::STATUT_VALIDE) {
            throw new InvalidArgumentException("Ce règlement ne peut pas être annulé");
        }

        return DB::transaction(function () {
            $this->update(['statut' => self::STATUT_ANNULE]);
            $this->facture->decrement('montant_regle', $this->montant);

            return true;
        });
    }

    /**
     * Vérifie si toutes les informations requises sont présentes selon le type de règlement
     *
     * @return bool
     */
    private function verifierInformationsRequises(): bool
    {
        $champsRequis = self::$validationRules[$this->type_reglement] ?? [];

        foreach ($champsRequis as $champ) {
            if (empty($this->$champ)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reglement) {
            if (!$reglement->numero) {
                $reglement->numero = self::genererNumero();
            }

            if (!$reglement->statut) {
                $reglement->statut = self::STATUT_BROUILLON;
            }
        });

        static::saving(function ($reglement) {
            if (!$reglement->verifierMontant()) {
                throw new InvalidArgumentException(
                    "Le montant du règlement est invalide ou dépasse le reste à régler de la facture"
                );
            }

            if (!$reglement->verifierInformationsRequises()) {
                throw new InvalidArgumentException(
                    "Des informations requises sont manquantes pour ce type de règlement"
                );
            }
        });
    }

    public function isValidated()
    {
        return !is_null($this->validated_at);
    }
}
