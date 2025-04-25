<?php

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Parametre\TypeTarif;

class Tarification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'article_id',
        'type_tarif_id',
        'prix',
        'statut'
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'statut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Constantes
    const STATUT_ACTIF = true;
    const STATUT_INACTIF = false;

    // Relations
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function typeTarif()
    {
        return $this->belongsTo(TypeTarif::class);
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
