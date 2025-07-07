<?php

namespace App\Models\Catalogue;

use App\Models\Parametre\Depot;
use App\Models\Securite\User;
// use App\Models\Vente\Magasin;
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
    public function depots() {
        if ($this->depot_ids) {
            $inventaires = Depot::whereIn("id",json_decode($this->depot_ids))->get();
        }else {
            $inventaires = collect();
        }
        return $inventaires;
    }

    public function details() : HasMany {
        return $this->hasMany(DetailInventaire::class)->latest();
    }
}
