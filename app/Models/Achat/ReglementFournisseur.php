<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ReglementFournisseur extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reglement_fournisseurs';

    const MODE_ESPECE = 'ESPECE';
    const MODE_CHEQUE = 'CHEQUE';
    const MODE_VIREMENT = 'VIREMENT';
    const MODE_DECHARGE = 'DECHARGE';
    const MODE_AUTRES = 'AUTRES';

    protected $fillable = [
        'code',
        'date_reglement',
        'facture_fournisseur_id',
        'mode_reglement',
        'reference_reglement',
        'reference_document',
        'montant_reglement',
        'commentaire',
        'created_by',
        'updated_by',
        'validated_by',
        'factures',
        "fournisseur_id"
    ];

    public static $rules = [
        'code' => 'required|unique:reglement_fournisseurs,code',
        'date_reglement' => 'required|date',
        'facture_fournisseur_id*' => 'required|exists:facture_fournisseurs,id',
        'mode_reglement' => 'required|in:ESPECE,CHEQUE,VIREMENT,DECHARGE,AUTRES',
        'reference_reglement' => 'required_if:mode_reglement,CHEQUE,VIREMENT',
        // 'montant_reglement' => 'required|numeric|min:0',
        'commentaire' => 'nullable|string',
        'reference_document' => 'nullable|string',
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'validated_by',
        'deleted_at'
    ];

    protected $casts = [
        'date_reglement' => 'date',
        'montant_reglement' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'validated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'validated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $dates = [
        'date_reglement',
        'created_at',
        'updated_at',
        'validated_at',
        'deleted_at'
    ];


    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($query) use ($term) {
            $query->where('code', 'LIKE', "%{$term}%")
                ->orWhere('reference_reglement', 'LIKE', "%{$term}%")
                ->orWhere('commentaire', 'LIKE', "%{$term}%");
        });
    }

    public function facture()
    {
        return $this->belongsTo(FactureFournisseur::class, 'facture_fournisseur_id');
    }

    // EN CAS DE REGLEMENTS A PLUSIEURES FACTURES COMBINES
    public function multiple_factures()
    {
        $factureIds = explode(",", $this->factures);
        // return $factureIds;
        return FactureFournisseur::whereIn("id", $factureIds)->get();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function setModeReglementAttribute($value)
    {
        $value = strtoupper($value);
        if (!in_array($value, [self::MODE_ESPECE, self::MODE_CHEQUE, self::MODE_VIREMENT, self::MODE_DECHARGE, self::MODE_AUTRES])) {
            throw new \Exception("Mode de règlement invalide");
        }
        $this->attributes['mode_reglement'] = $value;
    }

    public function isValidated()
    {
        return !is_null($this->validated_at);
    }

    public function validate()
    {
        if (auth()->check() && !$this->isValidated()) {
            $this->validated_by = auth()->id();
            $this->validated_at = now();
            $this->save();

            $this->updateFactureStatus();

            return true;
        }
        return false;
    }

    protected function updateFactureStatus()
    {
        if ($this->facture) {
            $totalReglements = $this->facture
                ->reglements()
                ->whereNotNull('validated_at')
                ->sum('montant_reglement');

            if ($totalReglements >= $this->facture->montant_ttc) {
                $this->facture->statut_paiement = 'PAYE';
            } elseif ($totalReglements > 0) {
                $this->facture->statut_paiement = 'PARTIELLEMENT_PAYE';
            }

            $this->facture->save();
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->date_reglement)) {
                $model->date_reglement = now();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if ($model->isValidated()) {
                throw new \Exception("Impossible de supprimer un règlement validé");
            }
        });

        static::saved(function ($model) {
            if ($model->isValidated()) {
                $model->updateFactureStatus();
            }
        });
    }
}
