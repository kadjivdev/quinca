<?php

namespace App\Models\Catalogue;

use App\Models\Stock\StockDepot;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailInventaire extends Model
{
    use HasFactory;

    protected $table = 'detail_inventaires';

    protected $fillable = [
        'inventaire_id',
        'qte_reel',
        'qte_stock',
        'stock_depot_id'
    ];

    function stockDepot() : BelongsTo {
        return $this->belongsTo(StockDepot::class,'stock_depot_id');
    }
}
