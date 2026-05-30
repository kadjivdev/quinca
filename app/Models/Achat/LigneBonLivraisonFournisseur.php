<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use App\Models\Parametre\{ConversionUnite, UniteMesure};
use App\Models\Catalogue\{Article};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class LigneBonLivraisonFournisseur
 *
 * @property int $id
 * @property int $livraison_id
 * @property int $article_id
 * @property int $unite_mesure_id
 * @property float $quantite
 * @property float $quantite_supplementaire
 * @property string|null $commentaire
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class LigneBonLivraisonFournisseur extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */
    protected $table = 'ligne_bon_livraison_fournisseurs';

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'livraison_id',
        'article_id',
        'unite_mesure_id',
        'quantite',
        'quantite_supplementaire',
        'unite_supplementaire_id',
        'commentaire'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'livraison_id' => 'required|exists:bon_livraisons,id',
        'article_id' => 'required|exists:articles,id',
        'unite_mesure_id' => 'required|exists:unite_mesures,id',
        'quantite' => 'required|numeric|gt:0',
        'quantite_supplementaire' => 'nullable|numeric|min:0',
        'commentaire' => 'nullable|string'
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux
     *
     * @var array
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    /**
     * Les attributs à caster
     *
     * @var array<string, string>
     */
    protected $casts = [
        'livraison_id' => 'integer',
        'article_id' => 'integer',
        'unite_mesure_id' => 'integer',
        'quantite' => 'float',
        'quantite_supplementaire' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

   
    private function rechercherConversion(int $unite_source_id, int $unite_base_id, int $article_id): ?ConversionUnite
    {
        Log::info("Unité de mesure de base(destination) rechercherConversion", ["data" => $unite_base_id]);
        Log::info("Unité de mesure entrante rechercherConversion", ["data" => $unite_source_id]);
        Log::info("Article", ["data" => Article::find($article_id)->code_article]);

        $firstConversion = ConversionUnite::firstWhere([
            'unite_source_id' => $unite_source_id,
            'unite_dest_id' => $unite_base_id,
            'article_id' => $article_id,
        ]);

        $secondConversion = ConversionUnite::firstWhere([
            'unite_source_id' => $unite_base_id,
            'unite_dest_id' => $unite_source_id,
            'article_id' => $article_id,
        ]);

        return $firstConversion ? $firstConversion : $secondConversion;
    }

    /**
     * Convertit une quantité selon le sens de la conversion
     * @param $current_unite_id l'unité actuelle (ou entrante)
     */
    public function convertirQuantite(float $quantite, ConversionUnite $conversion, int $current_unite_id): float
    {
        // return $conversion->convertToBase($quantite);

        return $conversion->unite_dest_id === $current_unite_id //$conversion->unite_source_id === $current_unite_id
            ? $conversion->convertirInverse($quantite)
            : $conversion->convertir($quantite);
    }

    /**
     * Obtenir la quantité totale (normale livré + supplémentaire livré)
     *
     * @return float
     */
    public function getQuantiteTotaleSupplement($unite_entrante, $unite_dest, $quantite)
    {
        $conversion = $this->rechercherConversion(
            $unite_entrante, //entrante
            $unite_dest, //destination
            $this->article_id
        );

        Log::info("Unité de mesure de base(destination) getQuantiteTotaleSupplement", ["data" => $unite_dest]);
        Log::info("Unité de mesure entrante getQuantiteTotaleSupplement", ["data" => $unite_entrante]);

        if (!$conversion) {
            $uniteEntrante = UniteMesure::find($unite_entrante);
            $uniteDest = UniteMesure::find($this->unite_mesure_id);
            $article = Article::find($this->article_id);

            Log::info("Aucune conversion existante entre les unités $uniteEntrante->libelle_unite et $uniteDest->libelle_unite ");
            throw new \Exception("Ligne concernée $article->code_article : Aucune conversion existante entre les unités $uniteEntrante->libelle_unite et $uniteDest->libelle_unite pour l'article");
        }

        Log::info("Conversion retrouvée", ["data" => $conversion]);

        $quantite_base = $this->convertirQuantite(
            $quantite,
            $conversion,
            $unite_entrante
        );

        Log::info("Qte supplementaire convertie en unite de base", ["data" => $quantite_base]);

        return $quantite_base ?? 0;
    }

    /**
     * Relation avec le bon de livraison
     */
    public function bonLivraison()
    {
        return $this->belongsTo(BonLivraisonFournisseur::class, 'livraison_id');
    }

    /**
     * Relation avec l'article
     */
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    /**
     * Relation avec l'unité de mesure
     */
    public function uniteMesure()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_id');
    }

    /**
     * 
     */
    public function uniteSupplementaire()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_supplementaire_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé la ligne
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur qui a mis à jour la ligne
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (!isset($model->quantite_supplementaire)) {
                $model->quantite_supplementaire = 0;
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }
}
