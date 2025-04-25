<?php

namespace App\Models\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Securite\User;
use App\Models\Parametre\Depot;
use App\Models\Vente\AcompteClient;
use App\Models\Parametre\caisse;
use App\Models\Revendeur\FactureRevendeur;

class PointDeVente extends Model
{
    use SoftDeletes;

    protected $table = 'point_de_ventes'; // Assurez-vous que le nom de la table est correct

    protected $fillable = [
        'code_pv',
        'nom_pv',
        'adresse_pv',
        // 'depot_id',
        'actif'
    ];

    // Si vous utilisez le softDelete, assurez-vous que la colonne existe

    protected $casts = [
        'statut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function depot()
    {
        return $this->hasMany(Depot::class)->with("articles");
    }

    public function caisses()
    {
        return $this->hasMany(Caisse::class);
    }

    public function factureRevendeur()
    {
        return $this->hasMany(FactureRevendeur::class);
    }

    // Relation avec les utilisateurs
    public function utilisateurs()
    {
        return $this->hasMany(User::class);
    }


    // Relation avec les caisses

    // Obtenir la caisse active du point de vente
    public function getCaisseActive()
    {
        return $this->caisses()
            ->where('statut', 'actif')
            ->first();
    }
    public function acomptes()
    {
        return $this->hasMany(AcompteClient::class);
    }
}
