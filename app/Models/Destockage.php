<?php

namespace App\Models;

use App\Models\Parametre\Depot;
use App\Models\Securite\User;
use App\Models\Vente\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Destockage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "id",
        "code",
        "reference",
        "depot_id",
        "client_id",
        "date_op",
        "observation",
        "created_by",
        "validated_at",
        "validated_by"
    ];

    protected $casts = [
        "date_op" => "date",
        "validated_at" => "date"
    ];

    // depot
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, "depot_id");
    }

    // client
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    // creé par
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    // validé par
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "validated_by");
    }

    // lignes
    public function lignes(): HasMany
    {
        return $this->hasMany(DestockageLigne::class, "destockage_id");
    }

    // boot
    protected static function boot()
    {
        parent::boot();

        // creating
        static::creating(function ($model) {
            if (Auth::user()) {
                $model->created_by = Auth::id();
                }
                $model->code = "DES-" . time() . "-STOCK";
        });

        // // created
        // static::created(function ($model) {
        //     $model->saveQuietly();
        // });
    }
}
