<?php

namespace App\Models\Vente;

use App\Models\Parametre\Depot;
use App\Models\Securite\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevendeurDepense extends Model
{
    use HasFactory;

    protected $fillable = [
        "day",
        "amount",
        "numero",
        "created_by",
        "validated_by",
        "depot_id"
    ];

    protected $casts = [
        "day" => "datetime",
        // "amount" => "decimal",
        "numero" => "string",
        "created_by" => "integer",
        "validated_by" => "integer",
        "depot_id" => "integer",
    ];

    /**
     * Le depot concerné dans la base
     */
    function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, "depot_id");
    }

    /**
     * L'insereur de la depense dans la base
     */
    function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Le validateur de la depense dans la base
     */
    function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "validated_by");
    }

    /**
     * Génère un numéro de facture unique
     * Format: FAC-AAAAMMJJ-XXXX
     * où XXXX est un numéro séquentiel
     */
    public static function generateNumero()
    {
        $prefix = 'DEP';
        $date = Carbon::now()->format('Ymd');

        // Recherche de la dernière facture du jour
        $lastDepense = self::where('numero', 'like', "{$prefix}-{$date}-%")
            ->orderBy('numero', 'desc')
            ->first();

        if ($lastDepense) {
            // Extraction du numéro séquentiel et incrémentation
            $lastNumber = (int) substr($lastDepense->numero, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format du numéro sur 4 chiffres avec des zéros devant
        $sequence = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($depense) {
            // Génère automatiquement le numéro si non défini
            if (empty($depense->numero)) {
                $depense->numero = self::generateNumero();
            }

            // Assigne l'utilisateur connecté
            if (empty($depense->created_by)) {
                $depense->created_by = auth()->id();
            }
        });
    }
}
