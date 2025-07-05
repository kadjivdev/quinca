<?php

namespace App\Models\Vente;

use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneMarchandise extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'article_id',
        'quantite',
        'unite_vente_id',
        'prix_unitaire',
    ];

    function article() : BelongsTo {
        return $this->belongsTo(Article::class,"article_id");
    }

    function uniteVente() : BelongsTo {
        return $this->belongsTo(UniteMesure::class,"unite_vente_id");
    }
}
