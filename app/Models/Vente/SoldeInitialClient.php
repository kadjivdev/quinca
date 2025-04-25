<?php

namespace App\Models\Vente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SoldeInitialClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solde_initial_clients';

    protected $fillable = [
        'client_id',
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
        'client_id' => 'required|exists:clients,id',
        'montant' => 'required|numeric|min:0',
        'type' => 'required|in:DEBITEUR,CREDITEUR',
        'date_solde' => 'required|date',
        'commentaire' => 'nullable|string',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
