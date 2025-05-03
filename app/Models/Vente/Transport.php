<?php

namespace App\Models\Vente;

use App\Models\Vente\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transport extends Model
{
    use HasFactory;

    protected $fillable = [
        'montant',
        'date_op',
        'observation',
        'client_id',
        'validator',
        'validate_at'
    ];

    public function client() : BelongsTo {
        return $this->belongsTo(Client::class);
    }
}
