<?php

namespace App\Models\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Models\Catalogue\Article;

use Exception;

class ConversionUnite extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unite_source_id',
        'unite_dest_id',
        'article_id',
        'coefficient',
        'statut'
    ];

    protected $casts = [
        'coefficient' => 'decimal:10',
        'statut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Constantes
    const STATUT_ACTIF = true;
    const STATUT_INACTIF = false;

    // Relations
    public function uniteSource()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_source_id');
    }

    public function uniteDest()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_dest_id');
    }

    public function article() // Changé de famille à article
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    // Scopes
    public function scopeActif(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    public function scopeParUniteSource(Builder $query, $uniteId): Builder
    {
        return $query->where('unite_source_id', $uniteId);
    }

    public function scopeParUniteDest(Builder $query, $uniteId): Builder
    {
        return $query->where('unite_dest_id', $uniteId);
    }

    public function scopeParArticle(Builder $query, $articleId): Builder // Changé de parFamille à parArticle
    {
        return $query->where('article_id', $articleId);
    }

    // Accesseurs
    public function getConversionLibelleAttribute(): string
    {
        return "{$this->uniteSource->libelle_unite} → {$this->uniteDest->libelle_unite}";
    }

    public function getConversionCodeAttribute(): string
    {
        return "{$this->uniteSource->code_unite} → {$this->uniteDest->code_unite}";
    }

    public function getEstActifAttribute(): bool
    {
        return $this->statut === self::STATUT_ACTIF;
    }

    // Méthodes de conversion
    public function convertir(float $valeur): float
    {
        if ($this->coefficient <= 0) {
            throw new Exception("Le coefficient de conversion doit être positif");
        }
        return $valeur * $this->coefficient;
    }

    public function convertirInverse(float $valeur): float
    {
        if ($this->coefficient <= 0) {
            throw new Exception("Le coefficient de conversion doit être positif");
        }
        return $valeur / $this->coefficient;
    }

    // Méthodes utilitaires
    public function toggleStatut(): bool
    {
        $this->statut = !$this->statut;
        return $this->save();
    }

    public static function trouverConversion($uniteSourceId, $uniteDestId, $articleId = null)
    {
        // Si même unité, retourner null pour autoriser la création
        // if ($uniteSourceId === $uniteDestId) {
        //     return null;
        // }

        $conversion =  static::withTrashed()
            ->where(function ($query) use ($uniteSourceId, $uniteDestId) {
                $query->where([
                    'unite_source_id' => $uniteSourceId,
                    'unite_dest_id' => $uniteDestId,
                ]);
                // ->orWhere([
                //     'unite_source_id' => $uniteDestId,
                //     'unite_dest_id' => $uniteSourceId,
                // ]);
            })
            ->when($articleId, function ($query) use ($articleId) {
                return $query->where('article_id', $articleId);
            })
            ->first();

        // Restaurer la conversion si elle est supprimée
        if ($conversion && $conversion->trashed()) {
            $conversion->restore();
        }

        return $conversion;
    }

    // Règles de validation
    public static function rules($id = null): array
    {
        return [
            'unite_source_id' => ['required', 'exists:unite_mesures,id'],
            'unite_dest_id' => ['required', 'exists:unite_mesures,id',],
            'article_id' => ['nullable', 'exists:articles,id'],
            'coefficient' => ['required', 'numeric', 'gt:0'],
            'statut' => ['boolean'],
            // Règle d'unicité combinée
            'unite_dest_id' => [
                'required',
                'exists:unite_mesures,id',
                Rule::unique('conversion_unites')
                    ->where(function ($query) {
                        return $query->where('unite_source_id', request('unite_source_id'))
                                    ->where('article_id', request('article_id'));
                    })
                    ->ignore($id)
            ]
        ];
    }

    // Boot method pour les événements du modèle
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($conversion) {
            // Si même unité, forcer le coefficient à 1
            if ($conversion->unite_source_id === $conversion->unite_dest_id) {
                $conversion->coefficient = 1;
                return;
            }

            // Vérifier que le coefficient est positif
            if ($conversion->coefficient <= 0) {
                throw new Exception("Le coefficient de conversion doit être positif");
            }
        });

        static::creating(function ($conversion) {
            // Vérifier si une conversion inverse existe déjà
            $existingConversion = static::trouverConversion(
                $conversion->unite_source_id,
                $conversion->unite_dest_id,
                $conversion->article_id
            );

            if ($existingConversion) {
                throw new Exception("Une conversion entre ces unités existe déjà");
            }
        });
    }
}
