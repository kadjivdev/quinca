<?php

namespace App\Models\Vente;

use App\Models\Parametre\Depot;
use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reversement extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'date_recette',
        'depot_id',
        'recette',
        'depense',
        'recette_to_reverse',
        'montant_reversed',
        'commentaire',
        'preuve',
        'deleted_by',
        'validated_by',
        'validated_at',
        'created_by'
    ];

    function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    function validatedBy()
    {
        return $this->belongsTo(User::class, "validated_by");
    }

    function deletedBy()
    {
        return $this->belongsTo(User::class, "deleted_by");
    }

    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            // Log the user who updated the model
            $model->created_by = auth()->id();
        });
    }
}
