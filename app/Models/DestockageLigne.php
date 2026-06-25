<?php

namespace App\Models;

use App\Models\Catalogue\Article;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestockageLigne extends Model
{
    use HasFactory;

    protected $fillable = [
        "destockage_id",
        "article_id",
        "unite_mesure_id",
        "montant",
        "qte",
        "pu",
    ];

    // destockage
    public function destockage(): BelongsTo
    {
        return $this->belongsTo(Destockage::class, "destockage_id");
    }

    // article
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, "article_id");
    }

    // unité de mesure
    public function uniteMesure(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class, "unite_mesure_id");
    }
}
