<?php

namespace App\Models\Achat;

use App\Models\Securite\User;
use App\Models\Catalogue\Article;
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\UniteMesure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class LigneFactureFournisseur
 *
 * @property int $id
 * @property int $facture_id
 * @property int $article_id
 * @property int $unite_mesure_id
 * @property float $quantite
 * @property float $prix_unitaire
 * @property float $taux_tva
 * @property float $taux_aib
 * @property float $montant_ht
 * @property float $montant_tva
 * @property float $montant_aib
 * @property float $montant_ttc
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $validated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $validated_at
 * @property Carbon|null $deleted_at
 */
class LigneFactureFournisseur extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * La table associée au modèle
     *
     * @var string
     */

    protected $table = 'ligne_facture_fournisseurs';
    protected $appends = ["qte_deja_livrer"];

    /**
     * Les attributs assignables en masse
     *
     * @var array<string>
     */
    protected $fillable = [
        'facture_id',
        'article_id',
        'unite_mesure_id',
        'quantite',
        'quantite_livree',
        'quantite_livree_simple',
        'prix_unitaire',
        'taux_tva',
        'taux_aib',
        'montant_ht',
        'montant_tva',
        'montant_aib',
        'montant_ttc',
        'created_by',
        'updated_by',
        'validated_by',

        'unite_mesure_base_id',
        'quantite_base'
    ];

    /**
     * Les règles de validation
     *
     * @var array<string, string>
     */
    public static $rules = [
        'facture_id' => 'required|exists:facture_fournisseurs,id',
        'article_id' => 'required|exists:articles,id',
        'unite_mesure_id' => 'required|exists:unite_mesures,id',
        'quantite' => 'required|numeric|gt:0',
        'prix_unitaire' => 'required|numeric|gt:0',
        'taux_tva' => 'required|numeric|between:0,100',
        'taux_aib' => 'required|numeric|between:0,100',
        'created_by' => 'nullable|exists:users,id',
        'updated_by' => 'nullable|exists:users,id',
        'validated_by' => 'nullable|exists:users,id'
    ];

    /**
     * Les attributs qui doivent être cachés pour les tableaux
     *
     * @var array
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'validated_by',
        'deleted_at'
    ];

    /**
     * Les attributs à caster
     *
     * @var array<string, string>
     */
    protected $casts = [
        'facture_id' => 'integer',
        'article_id' => 'integer',
        'unite_mesure_id' => 'integer',
        'quantite' => 'float',
        'prix_unitaire' => 'float',
        'taux_tva' => 'float',
        'taux_aib' => 'float',
        'montant_ht' => 'float',
        'montant_tva' => 'float',
        'montant_aib' => 'float',
        'montant_ttc' => 'float',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'validated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'validated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Les attributs qui doivent être mutés en dates
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'validated_at',
        'deleted_at'
    ];

    /**
     * Les getters personnalisés
     */
    function getQteDejaLivrerAttribute()
    {
        $ligneBonLivraison = $this->facture?->bonLivraison
            ?->flatMap(fn ($bon) => $bon->lignes)
            ?->firstWhere("article_id", $this->article_id);
        Log::info("Ligne bon de livraison retrouvée", ["data" => $this->facture->load("bonLivraison.lignes")]);

        // quantite supplementaire convertie en unite de base
        $qteSupplementaire = 0;
        if ($ligneBonLivraison) {
            $qteSupplementaire = $ligneBonLivraison->unite_supplementaire_id ? $this->getQuantiteTotaleSupplement(
                $ligneBonLivraison->unite_supplementaire_id,
                $ligneBonLivraison->unite_mesure_id,
                $ligneBonLivraison->quantite_supplementaire
            ) : 0;
        }

        return $this->quantite_livree_simple - $qteSupplementaire;
    }

    /**
     * Relation avec la facture
     */
    public function facture()
    {
        return $this->belongsTo(FactureFournisseur::class, 'facture_id');
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
     * Relation avec l'unité de mesure
     * de base
     */
    public function uniteMesureBase()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_base_id');
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
     * Relation avec l'utilisateur qui a validé la ligne
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Calcul des montants de la ligne
     */
    private function calculerMontants()
    {
        // Calcul du montant HT sans remise
        $this->montant_ht = $this->quantite * $this->prix_unitaire;

        // Calcul des taxes
        $this->montant_tva = $this->montant_ht * ($this->taux_tva / 100);
        $this->montant_aib = $this->montant_ht * ($this->taux_aib / 100);

        // Calcul du total TTC
        $this->montant_ttc = $this->montant_ht + $this->montant_tva + $this->montant_aib;
    }

    /**
     * Mutateur pour la quantité
     */
    public function setQuantiteAttribute($value)
    {
        $this->attributes['quantite'] = $value;
        $this->calculerMontants();
    }

    /**
     * Mutateur pour le prix unitaire
     */
    public function setPrixUnitaireAttribute($value)
    {
        $this->attributes['prix_unitaire'] = $value;
        $this->calculerMontants();
    }

    /**
     * Mutateur pour le taux TVA
     */
    public function setTauxTVAAttribute($value)
    {
        $this->attributes['taux_tva'] = $value;
        $this->calculerMontants();
    }

    /**
     * Mutateur pour le taux AIB
     */
    public function setTauxAIBAttribute($value)
    {
        $this->attributes['taux_aib'] = $value;
        $this->calculerMontants();
    }

    /**
     * Méthode de validation de la ligne
     */
    public function validate()
    {
        if (auth()->check()) {
            $this->validated_by = auth()->id();
            $this->validated_at = now();
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Vérifie si la ligne est validée
     */
    public function isValidated()
    {
        return !is_null($this->validated_at);
    }

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
        // return ConversionUnite::where(function ($query) use ($unite_source_id, $unite_base_id) {
        //     $query->where([
        //         'unite_source_id' => $unite_source_id,
        //         'unite_dest_id' => $unite_base_id
        //     ])->orWhere([
        //         'unite_source_id' => $unite_base_id,
        //         'unite_dest_id' => $unite_source_id
        //     ]);
        // })
        //     ->where(function ($query) use ($article_id) {
        //         $query->where('article_id', $article_id)
        //             ->orWhereNull('article_id');
        //     })
        //     ->where('statut', true)
        //     ->first();
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
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Avant la création
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
            if (empty($model->taux_tva)) {
                $model->taux_tva = 0;
            }
            if (empty($model->taux_aib)) {
                $model->taux_aib = 0;
            }
            $model->calculerMontants();
            $model->created_at = now();
        });

        // Avant la mise à jour
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
            $model->calculerMontants();
            $model->updated_at = now();
        });

        // Après la sauvegarde
        static::saved(function ($model) {
            if ($model->facture) {
                $model->facture->updateMontants();
            }
        });
    }
}
