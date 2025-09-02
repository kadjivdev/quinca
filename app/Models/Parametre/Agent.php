<?php

namespace App\Models\Parametre;

use App\Models\Vente\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'contact',
        'created_by',
        'updated_by'
    ];

    function clients(): HasMany
    {
        return $this->hasMany(Client::class, "agent_id");
    }
}
