<?php

namespace App\Models\Achat;

use App\Models\Catalogue\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequeteFournisseur extends Model
{
    use HasFactory;
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
    ];

    /** Many to many */
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'articles_fournisseurrequetes',"requete_id","article_id");
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }
}
