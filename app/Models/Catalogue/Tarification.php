<?php

namespace App\Models\Catalogue;

use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\Depot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Parametre\TypeTarif;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class Tarification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'article_id',
        'depot_id',
        'type_tarif_id',
        'prix',
        'statut',
        "unite_mesure_id"
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'statut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'depot_id' => 'integer',
        'unite_mesure_id' => 'integer',
    ];

    // Constantes
    const STATUT_ACTIF = true;
    const STATUT_INACTIF = false;

    // Relations
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function depotTarif()
    {
        return $this->belongsTo(Depot::class);
    }

    public function typeTarif()
    {
        return $this->belongsTo(TypeTarif::class);
    }

    function uniteMesure(): BelongsTo
    {
        return $this->belongsTo(UniteMesure::class,"unite_mesure_id");
    }

    // Scopes
    public function scopeActif(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    public function scopeParType(Builder $query, $typeTarifId): Builder
    {
        return $query->where('type_tarif_id', $typeTarifId);
    }

    // Méthodes utilitaires
    public function toggleStatut(): bool
    {
        $this->statut = !$this->statut;
        return $this->save();
    }

    // Règles de validation
    public static function rules($id = null): array
    {
        return [
            'article_id' => 'required|exists:articles,id',
            'type_tarif_id' => 'required|exists:type_tarifs,id',
            'prix' => 'required|numeric|min:0',
            'statut' => 'boolean'
        ];
    }
}
