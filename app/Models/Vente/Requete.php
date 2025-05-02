<?php

namespace App\Models\Vente;

use App\Models\Catalogue\Article;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requete extends Model
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
        'client_id',
        'montant',
        'validator',
        'validate_at',
        'motif',
        'motif_content'
    ];

    public function articles(){
        return $this->belongsToMany(Article::class, 'requete_articles');
    }

    public function client(){
        return $this->belongsTo(Client::class);
    }
}
