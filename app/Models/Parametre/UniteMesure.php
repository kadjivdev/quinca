<?php
namespace App\Models\Parametre;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use App\Models\Parametre\ConversionUnite;


class UniteMesure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_unite',
        'libelle_unite',
        'description',
        'unite_base',
        'statut'
    ];

    protected $casts = [
        'unite_base' => 'boolean',
        'statut' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Constantes de statut
    const STATUT_ACTIF = true;
    const STATUT_INACTIF = false;

    // Relations
    // public function conversions()
    // {
    //     return $this->hasMany(ConversionUnite::class, 'unite_source_id')
    //         ->orWhere('unite_dest_id', $this->id);
    // }

    // public function conversionsSource()
    // {
    //     return $this->hasMany(ConversionUnite::class, 'unite_source_id');
    // }

    // public function conversionsDest()
    // {
    //     return $this->hasMany(ConversionUnite::class, 'unite_dest_id');
    // }

    // Scopes

    public function conversions()
    {
        return $this->hasMany(ConversionUnite::class, 'unite_source_id');
    }

    /**
     * Relation avec les conversions où cette unité est la destination
     */
    public function conversionsInverses()
    {
        return $this->hasMany(ConversionUnite::class, 'unite_dest_id');
    }

    public function scopeActif(Builder $query): Builder
    {
        return $query->where('statut', self::STATUT_ACTIF);
    }

    public function scopeUniteBase(Builder $query): Builder
    {
        return $query->where('unite_base', true);
    }

    public function scopeRecherche(Builder $query, string $terme): Builder
    {
        return $query->where(function($q) use ($terme) {
            $q->where('code_unite', 'LIKE', "%{$terme}%")
              ->orWhere('libelle_unite', 'LIKE', "%{$terme}%")
              ->orWhere('description', 'LIKE', "%{$terme}%");
        });
    }

    // Accesseurs et Mutateurs
    public function getEstActifAttribute(): bool
    {
        return $this->statut === self::STATUT_ACTIF;
    }

    public function getLibelleCompletAttribute(): string
    {
        return "{$this->code_unite} - {$this->libelle_unite}";
    }

    // Méthodes utilitaires
    public function toggleStatut(): bool
    {
        $this->statut = !$this->statut;
        return $this->save();
    }

    public function hasConversion(UniteMesure $uniteDest): bool
    {
        return $this->conversions()
            ->where(function($query) use ($uniteDest) {
                $query->where([
                    'unite_source_id' => $this->id,
                    'unite_dest_id' => $uniteDest->id
                ])->orWhere([
                    'unite_source_id' => $uniteDest->id,
                    'unite_dest_id' => $this->id
                ]);
            })
            ->exists();
    }

    // Règles de validation
    public static function rules($id = null): array
    {
        return [
            'code_unite' => [
                'required',
                'string',
                'max:10',
                Rule::unique('unite_mesures', 'code_unite')->ignore($id)
            ],
            'libelle_unite' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
            'unite_base' => 'boolean',
            'statut' => 'boolean'
        ];
    }

    // Boot method pour les événements du modèle
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($uniteMesure) {
            // Vérifier si l'unité est utilisée dans des conversions
            if ($uniteMesure->conversions()->count() > 0) {
                throw new \Exception("Impossible de supprimer cette unité car elle est utilisée dans des conversions.");
            }
        });
    }
}
