<?php

namespace App\Models\Achat;

use App\Models\Catalogue\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class RequeteFournisseur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'num_demande',
        'date_demande',
        'nature',
        'mention',
        'formulation',
        'fichier',
        'user_id',
        'fournisseur_id',
        'montant',
        'validator',
        'validate_at',
        'motif',
        'motif_content',

        'deleted_by',
        'deleted_at'
    ];

    /** Many to many */
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'articles_fournisseurrequetes', "requete_id", "article_id");
    }

    /**Fournisseur */
    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**Accompte Fournisseur */
    public function accompteFournisseurs(): HasMany
    {
        return $this->hasMany(AccompteFournisseur::class, "requete_id");
    }

    /**Boot */
    protected static function boot()
    {
        parent::boot();

        /**Deleting */
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->update(["deleted_by" => Auth::id()]);
            }
        });
    }
}
