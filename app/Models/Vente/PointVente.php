<?php

namespace App\Models\Vente;

use App\Models\Catalogue\Article;
use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PointVente extends Model
{
    use HasFactory;
    protected $table = "point_de_ventes";
    
    protected $fillable = [
        'nom',
        'adresse',
        'phone',
        // 'user_id',
    ];

    public function magasins(): HasMany
    {
        return $this->hasMany(Magasin::class);
    }

    public function magasin_principal(): BelongsTo
    {
        return $this->belongsTo(Magasin::class, 'magasin_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_point_ventes');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
