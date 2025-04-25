<?php

namespace App\Models\Vente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Stock\{MouvementStock, StockDepot};
use App\Models\Vente\{FactureClient, LigneLivraisonClient};
use App\Models\Catalogue\{Article};
use App\Models\Securite\User;
use App\Services\ServiceStockSortie;
use App\Models\Parametre\{Depot, UniteMesure, ConversionUnite};
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Auth};

class LivraisonClient extends Model
{
    use SoftDeletes;

    protected $table = 'livraison_clients';

    const STATUT_BROUILLON = 'brouillon';
    const STATUT_VALIDE = 'valide';
    const STATUT_ANNULE = 'annule';

    protected $fillable = [
        'facture_client_id',
        'depot_id',
        'depot_dest_id',
        'numero',
        'date_livraison',
        'date_validation',
        'statut',
        'notes',
        'created_by',
        'validated_by',
        'validated_at'
    ];

    protected $casts = [
        'date_livraison' => 'datetime',
        'date_validation' => 'datetime',
        'validated_at' => 'datetime'
    ];

    public static function generateNumero()
    {
        $prefix = 'BL';
        $date = Carbon::now()->format('ymd');
        $random = substr(md5(uniqid()), 0, 3);

        return sprintf('%s-%s-%s', $prefix, $date, $random);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($livraison) {
            if (empty($livraison->numero)) {
                $livraison->numero = self::generateNumero();
            }

            if (empty($livraison->date_livraison)) {
                $livraison->date_livraison = Carbon::now();
            }

            if (empty($livraison->created_by)) {
                $livraison->created_by = auth()->id();
            }
        });
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(FactureClient::class, 'facture_client_id');
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneLivraisonClient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function valider(int $userId): bool
    {
        if ($this->statut !== self::STATUT_BROUILLON) {
            throw new Exception("Cette livraison ne peut pas être validée");
        }

        DB::beginTransaction();
        try {
            $stockSortieService = new ServiceStockSortie();

            foreach ($this->lignes as $ligne) {
                // Création du mouvement de sortie de stock
                $resultat = $stockSortieService->traiterSortieStock([
                    'date_mouvement' => $this->date_livraison,
                    'depot_id' => $this->depot_id,
                    'article_id' => $ligne->article_id,
                    'unite_mesure_id' => $ligne->unite_vente_id,
                    'quantite' => $ligne->quantite,
                    'reference_mouvement' => $this->numero,
                    'document_type' => 'LIVRAISON_CLIENT',
                    'document_id' => $this->id,
                    'user_id' => $userId,
                    'notes' => "Livraison client #{$this->numero}"
                ]);

                if (!$resultat['succes']) {
                    throw new Exception($resultat['message']);
                }

                // Mise à jour de la ligne avec l'ID du mouvement
                $ligne->mouvement_stock_id = $resultat['donnees']['mouvement_id'];
                $ligne->save();
            }

            // Validation de la livraison
            $this->statut = self::STATUT_VALIDE;
            $this->validated_by = $userId;
            $this->validated_at = now();
            $this->date_validation = now();
            $this->save();

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
