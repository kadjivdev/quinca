<?php

namespace App\Models\Vente;

use App\Models\Securite\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarchandBack extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "numero",
        "date",
        "client_id",
        "livraison_id",
        "created_by",
        "validated_by",
        "documents",
        "observation"
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    /**
     * Livraison concernée
     */

    function livraison(): BelongsTo
    {
        return $this->belongsTo(LivraisonClient::class, "livraison_id");
    }

    /**
     * Client concerné
     */
    function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
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
        $prefix = 'MARCH';
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
