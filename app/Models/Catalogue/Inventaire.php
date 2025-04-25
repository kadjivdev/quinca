<?php

namespace App\Models\Catalogue;

use App\Models\Parametre\Depot;
use App\Models\Securite\User;
use App\Models\Vente\Magasin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventaire extends Model
{
    use HasFactory;

    protected $table = 'inventaires';

    protected $casts = [
        'date_inventaire' => 'datetime',
        'validated_at' => 'datetime',
    ];

    protected $fillable = [
        'depot_id',
        'date_inventaire',
        'user_id',
        'depot_ids',
        'validated_at',
        'validator_id',
    ];

    public function auteur() : BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    // à revoir
    public function depot() : BelongsTo {
        return $this->belongsTo(Depot::class, 'depot_id');
    }

    public function details() : HasMany {
        return $this->hasMany(DetailInventaire::class)->latest();
    }
}
