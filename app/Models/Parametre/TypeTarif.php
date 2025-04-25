<?php

namespace App\Models\Parametres;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeTarif extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_type_tarif',
        'libelle_type_tarif',
        'description',
        'statut'
    ];

    protected $casts = [
        'statut' => 'boolean'
    ];

    // Constantes pour les types de tarif
    const STANDARD = 'STANDARD';
    const PROMO = 'PROMO';
    const VIP = 'VIP';
    const SPECIAL = 'SPECIAL';
    const GROSSISTE = 'GROSSISTE';
    const DETAILLANT = 'DETAILLANT';

    /**
     * Obtenir la liste des types de tarif avec leurs libellés
     */
    public static function getTypes()
    {
        return [
            self::STANDARD => 'Tarif Standard',
            self::PROMO => 'Tarif Promotionnel',
            self::VIP => 'Tarif VIP',
            self::SPECIAL => 'Tarif Spécial',
            self::GROSSISTE => 'Tarif Grossiste',
            self::DETAILLANT => 'Tarif Détaillant'
        ];
    }

    /**
     * Relation avec les tarifications
     */
    public function tarifications()
    {
        return $this->hasMany(Tarification::class);
    }

    /**
     * Vérifie si c'est un tarif standard
     */
    public function isStandard()
    {
        return $this->code_type_tarif === self::STANDARD;
    }

    /**
     * Vérifie si c'est un tarif promotionnel
     */
    public function isPromo()
    {
        return $this->code_type_tarif === self::PROMO;
    }

    /**
     * Vérifie si c'est un tarif VIP
     */
    public function isVIP()
    {
        return $this->code_type_tarif === self::VIP;
    }

    /**
     * Vérifie si c'est un tarif spécial
     */
    public function isSpecial()
    {
        return $this->code_type_tarif === self::SPECIAL;
    }

    /**
     * Vérifie si c'est un tarif grossiste
     */
    public function isGrossiste()
    {
        return $this->code_type_tarif === self::GROSSISTE;
    }

    /**
     * Vérifie si c'est un tarif détaillant
     */
    public function isDetaillant()
    {
        return $this->code_type_tarif === self::DETAILLANT;
    }

    /**
     * Scope pour les types de tarif actifs
     */
    public function scopeActif($query)
    {
        return $query->where('statut', true);
    }

    /**
     * Scope pour filtrer par type de tarif
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('code_type_tarif', $type);
    }

    /**
     * Obtenir le libellé du type de tarif
     */
    public function getLibelle()
    {
        return self::getTypes()[$this->code_type_tarif] ?? $this->libelle_type_tarif;
    }

    /**
     * Vérifie si le tarif est d'un type spécifique
     */
    public function isOfType($type)
    {
        return $this->code_type_tarif === $type;
    }
}
