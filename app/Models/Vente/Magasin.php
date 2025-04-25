<?php

namespace App\Models\Vente;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Magasin extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'adresse',
        'user_id',
        'point_vente_id',
    ];

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pointVente(): BelongsTo
    {
        return $this->belongsTo(PointVente::class);
    }

    public function stock_articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'stock_magasins');
    }

    public function livraisons(): HasMany
    {
        return $this->hasMany(LivraisonVenteMagasin::class, 'livraison_vente_magasins');
    }
}
