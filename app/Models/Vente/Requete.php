<?php

namespace App\Models\Vente;

use App\Models\Catalogue\Article;
use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'motif_content',
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'requete_articles');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function accompte(): HasOne
    {
        return $this->hasOne(AcompteClient::class, "requete_id");
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
