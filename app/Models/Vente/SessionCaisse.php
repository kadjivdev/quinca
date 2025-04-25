<?php

namespace App\Models\Vente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Vente\FactureClient;
use App\Models\Parametre\Caisse;
use App\Models\Parametre\PointDeVente;

class SessionCaisse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'utilisateur_id',
        'caisse_id',
        'date_ouverture',
        'date_fermeture',
        'montant_ouverture',
        'montant_fermeture',
        'observations',
        'observations_fermeture',
        'total_encaissements',
        'total_decaissements',
        'solde_theorique',
        'ecart',
        'statut',
        'latitude',
        'longitude',
        'point_de_vente_id'
    ];

    protected $casts = [
        'date_ouverture' => 'datetime',
        'date_fermeture' => 'datetime',
        'montant_ouverture' => 'decimal:2',
        'montant_fermeture' => 'decimal:2',
        'total_encaissements' => 'decimal:2',
        'total_decaissements' => 'decimal:2',
        'solde_theorique' => 'decimal:2',
        'ecart' => 'decimal:2'
    ];

    // Relations
    public function caisse()
    {
        return $this->belongsTo(Caisse::class);
    }

    public function factures()
    {
        return $this->hasMany(FactureClient::class, 'session_caisse_id');
    }

    public function utilisateur()
    {
        return $this->belongsTo('App\Models\Securite\User', 'utilisateur_id');
    }

    public function detailsComptage()
    {
        return $this->hasMany('App\Models\Vente\DetailComptage');
    }

    // Scopes
    public function scopeOuverte($query)
    {
        return $query->where('statut', 'ouverte');
    }

    public function scopeFermee($query)
    {
        return $query->where('statut', 'fermee');
    }

    // Méthodes utilitaires
    public function estOuverte()
    {
        return strtolower(trim($this->statut)) === 'ouverte';
    }

    public function calculerSoldeTheorique()
    {
        return $this->montant_ouverture + $this->total_encaissements - $this->total_decaissements;
    }

    public function calculerEcart()
    {
        if (!$this->montant_fermeture) {
            return 0;
        }
        return $this->montant_fermeture - $this->calculerSoldeTheorique();
    }

    public function mettreAJourTotaux()
    {
        $this->total_encaissements = $this->factures()->sum('montant_regle');
        $this->solde_theorique = $this->calculerSoldeTheorique();
        $this->ecart = $this->calculerEcart();
        $this->save();
    }

    // Accesseurs
    public function getStatutFormatteAttribute()
    {
        return $this->statut === 'ouverte'
            ? '<span class="badge bg-success">Ouverte</span>'
            : '<span class="badge bg-danger">Fermée</span>';
    }

    public function getEcartFormatteAttribute()
    {
        $class = $this->ecart >= 0 ? 'success' : 'danger';
        $signe = $this->ecart >= 0 ? '+' : '';
        return '<span class="text-' . $class . '">' . $signe . number_format($this->ecart, 0, ',', ' ') . ' F</span>';
    }

    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }
}
