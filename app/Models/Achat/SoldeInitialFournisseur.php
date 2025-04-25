<?php

namespace App\Models\Achat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoldeInitialFournisseur extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solde_initial_fournisseurs';

    protected $fillable = [
        'fournisseur_id',
        'montant',
        'type',
        'date_solde',
        'commentaire',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_solde' => 'date',
    ];

    protected static $rules = [
        'fournisseur_id' => 'required|exists:fournisseurs,id',
        'montant' => 'required|numeric|min:0',
        'type' => 'required|in:DEBITEUR,CREDITEUR',
        'date_solde' => 'required|date',
        'commentaire' => 'nullable|string',
    ];

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }
}
