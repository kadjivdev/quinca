<?php

namespace App\Models;

use App\Models\Catalogue\Article;
use App\Models\Parametre\TypeTarif;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarification extends Model
{
    use HasFactory;

    protected $fillable = [
        "article_id",
        "type_tarif_id",
        "prix",
        "statut",
        "point_vente_id",
        "unite_mesure_id",
    ];


    function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, "article_id");
    }

    function uniteMesure(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, "unite_mesure_id");
    }

    function typeTarif(): BelongsTo
    {
        return $this->belongsTo(TypeTarif::class, "type_tarif_id");
    }
}
