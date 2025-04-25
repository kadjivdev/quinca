<?php

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailInventaire extends Model
{
    use HasFactory;

    protected $table = 'detail_inventaires';

    protected $fillable = [
        // 'depot_id',
        'inventaire_id',
        'qte_reel',
        'qte_stock',
        'stock_depot_id'
    ];
}
