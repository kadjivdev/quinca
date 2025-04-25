<?php

namespace App\Models\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Parametre\Depot;

class TypeDepot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_type_depot',
        'libelle_type_depot',
        'description',
        'statut'
    ];

    // Constantes pour les types de magasin
    const PRINCIPAL = 'PRINCIPAL';
    const TRANSIT = 'TRANSIT';
    const POINT_VENTE = 'POINT_VENTE';
    const STOCKAGE = 'STOCKAGE';

    public static function getTypes()
    {
        return [
            self::PRINCIPAL => 'Magasin Principal/Central',
            self::TRANSIT => 'Magasin de Transit/Logistique',
            self::POINT_VENTE => 'Magasin Point de Vente',
            self::STOCKAGE => 'Magasin de Stockage'
        ];
    }


    public function depots()
    {
        return $this->hasMany(Depot::class);
    }
}
