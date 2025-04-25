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
use Illuminate\Support\Facades\DB;

class Devis extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_devis',
        'client_id',
        'user_id',
        'reference',
        'statut',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    protected $casts = [
        'date_devis' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(DevisDetail::class,);
    }

    public function redacteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getMontantTotalAttribute(): float
    {
        return $this->details()->sum(DB::raw('qte_cmde * prix_unit'));
    }

    function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, "devis_details","devis_id","article_id");
    }
}
