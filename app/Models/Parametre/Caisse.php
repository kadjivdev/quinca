<?php

namespace App\Models\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Parametre\PointVente;
use App\Models\Vente\SessionCaisse;

class Caisse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_caisse',
        'point_de_vente_id',
        'libelle',
        'actif'
    ];

    // Définir les types de casting
    protected $casts = [
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function pointVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    public function sessions()
    {
        return $this->hasMany(SessionCaisse::class);
    }

    // Scopes pour filtrer les caisses
    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }

    public function scopeInactif($query)
    {
        return $query->where('actif', false);
    }

    // Méthodes utilitaires
    public function getSessionOuverte()
    {
        return $this->sessions()
            ->where('statut', 'ouverte')
            ->first();
    }

    public function hasSessionOuverte()
    {
        return $this->sessions()
            ->where('statut', 'ouverte')
            ->exists();
    }

    public function getSessionsDuJour()
    {
        return $this->sessions()
            ->whereDate('date_ouverture', now())
            ->get();
    }

    // Accesseurs et mutateurs
    public function getStatutTextAttribute()
    {
        return $this->actif ? 'Actif' : 'Inactif';
    }

    public function getCodeLibelleAttribute()
    {
        return "{$this->code_caisse} - {$this->libelle}";
    }
}
