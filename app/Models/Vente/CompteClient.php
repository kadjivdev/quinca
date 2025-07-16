<?php

namespace App\Models\Vente;

use App\Models\Revendeur\FactureRevendeur;
use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompteClient extends Model
{
    use HasFactory;
    protected $fillable = [
        'date_op',
        'type_op',
        'client_id',
        'accompte_client',
        'facture_client_id',
        'facture_revendeur_id',
        'reglement_clt',
        'reglement_rev',
        'montant_op',
        'user_id',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_op' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    public function accompteClient(): BelongsTo
    {
        return $this->belongsTo(AcompteClient::class, "accompte_client");
    }

    public function factureClient(): BelongsTo
    {
        return $this->belongsTo(FactureClient::class, "facture_client_id");
    }

    public function factureRevendeur(): BelongsTo
    {
        return $this->belongsTo(FactureRevendeur::class, "facture_revendeur_id");
    }

    public function reglementClient(): BelongsTo
    {
        return $this->belongsTo(ReglementClient::class, "reglement_clt");
    }

    public function reglementRevendeur(): BelongsTo
    {
        return $this->belongsTo(ReglementRevendeur::class, "reglement_rev");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
