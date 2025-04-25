<?php

namespace App\Models\Vente;

use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DevisDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'devis_id',
        'article_id',
        'qte_cmde',
        'prix_unit',
        'unite_mesure_id',
    ];

    function mesureunit(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, "unite_mesure_id");
    }

    function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, "article_id");
    }
}
