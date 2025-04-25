<?php

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilleArticle extends Model
{
    use SoftDeletes;

    protected $table = 'famille_articles';

    protected $fillable = [
        'code_famille',
        'libelle_famille',
        'description',
        'statut'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'statut' => 'boolean',
    ];



    public static $rules = [
        'code_famille' => 'required|unique:famille_articles',
        'libelle_famille' => 'required',
    ];

    /**
     * Relation avec les articles
     *
     * @return HasMany
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'famille_id');
    }

    /**
     * Retourne le nom complet de la famille (code + libellé)
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->code_famille . ' - ' . $this->libelle_famille;
    }





}
