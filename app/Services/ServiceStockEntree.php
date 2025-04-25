<?php

namespace App\Services;

use App\Models\Stock\{StockMouvement, StockDepot};
use App\Models\Catalogue\Article;
use App\Models\Parametre\{UniteMesure, ConversionUnite};
use Illuminate\Support\Facades\DB;
use Exception;

class ServiceStockEntree
{
    /**
     * Traite une entrée en stock
     * - Vérifie l'unité de base
     * - Convertit si nécessaire
     * - Applique le CUMP
     */
    public function traiterEntreeStock(array $donnees): array
    {
        try {
            DB::beginTransaction();

            // Ajout de logs de debug
            \Log::debug("Début traitement entrée stock", ['donnees' => $donnees]);

            // 1. Validation de base
            $this->validerDonneesEntree($donnees);

            // 2. Récupération de l'article avec son unité de base
            $article = Article::with('uniteMesure')->findOrFail($donnees['article_id']);

            if (!$article->unite_mesure_id) {
                throw new Exception(sprintf(
                    "L'article %s n'a pas d'unité de mesure de base définie",
                    $article->code_article
                ));
            }

            // 3. Initialisation quantité de base
            $quantite_base = $donnees['quantite'];
            $unite_origine_id = $donnees['unite_mesure_id'];

            // 4. Conversion si nécessaire
            if ($unite_origine_id !== $article->unite_mesure_id) {
                $uniteSource = UniteMesure::findOrFail($unite_origine_id);
                $uniteBase = $article->uniteMesure;

                $conversion = $this->rechercherConversion(
                    $unite_origine_id,
                    $article->unite_mesure_id,
                    $article->id
                );

                if (!$conversion) {
                    throw new Exception(sprintf(
                        "Aucune conversion trouvée de l'unité %s vers %s pour l'article %s",
                        $uniteSource->code_unite,
                        $uniteBase->code_unite,
                        $article->code_article
                    ));
                }

                $quantite_base = $this->convertirQuantite(
                    $donnees['quantite'],
                    $conversion,
                    $unite_origine_id
                );

                \Log::debug("Conversion effectuée", [
                    'quantite_origine' => $donnees['quantite'],
                    'unite_origine' => $uniteSource->libelle_unite,
                    'quantite_base' => $quantite_base,
                    'unite_base' => $uniteBase->code_unite
                ]);
            }

            // 5. Récupération ou création du stock
            $stock = StockDepot::firstOrNew([
                'depot_id' => $donnees['depot_id'],
                'article_id' => $article->id
            ]);

            $ancien_stock = $stock->quantite_reelle ?? 0;
            $ancien_cump = $stock->prix_moyen ?? 0;

            // 6. Calcul du nouveau CUMP
            $nouveau_cump = $this->calculerCUMP(
                $ancien_stock,
                $ancien_cump,
                $quantite_base,
                $donnees['prix_unitaire']
            );

            // 7. Création du mouvement de stock
            $mouvement = StockMouvement::create([
                'code' => $this->genererCodeMouvement(),
                'depot_id' => $donnees['depot_id'],
                'article_id' => $article->id,
                'date_mouvement' => $donnees['date_mouvement'],
                'type_mouvement' => StockMouvement::TYPE_ENTREE,
                'quantite' => $quantite_base,
                'quantite_origine' => $donnees['quantite'],
                'unite_mesure_id' => $article->unite_mesure_id,
                'unite_mesure_origine_id' => $unite_origine_id,
                'prix_unitaire' => $donnees['prix_unitaire'],
                'reference_mouvement' => $donnees['reference_mouvement'],
                'document_type' => $donnees['document_type'] ?? null,
                'document_id' => $donnees['document_id'] ?? null,
                'notes' => $donnees['notes'] ?? null,
                'user_id' => $donnees['user_id']
            ]);

            // 8. Mise à jour du stock
            $stock->fill([
                'quantite_reelle' => $ancien_stock + $quantite_base,
                'prix_moyen' => $nouveau_cump,
                'date_dernier_mouvement' => $donnees['date_mouvement'],
                'user_id' => $donnees['user_id']
            ]);

            if (!$stock->exists) {
                $stock->unite_mesure_id = $article->unite_mesure_id;
            }

            $stock->save();

            DB::commit();

            \Log::debug("Entrée en stock réussie", [
                'mouvement_id' => $mouvement->id,
                'nouveau_stock' => $stock->quantite_reelle,
                'nouveau_cump' => $nouveau_cump
            ]);

            return [
                'succes' => true,
                'message' => 'Entrée en stock effectuée avec succès',
                'donnees' => [
                    'mouvement_id' => $mouvement->id,
                    'code_mouvement' => $mouvement->code,
                    'quantite_origine' => $donnees['quantite'],
                    'unite_origine' => UniteMesure::find($unite_origine_id)->libelle_unite,
                    'quantite_base' => $quantite_base,
                    'unite_base' => $article->uniteMesure->libelle_unite,
                    'nouveau_stock' => $stock->quantite_reelle,
                    'nouveau_cump' => $nouveau_cump
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error("Erreur traitement entrée stock", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'donnees' => $donnees
            ]);

            return [
                'succes' => false,
                'message' => 'Erreur lors de l\'entrée en stock: ' . $e->getMessage()
            ];
        }
    }

    private function genererCodeMouvement(): string
    {
        $prefix = 'MVT';
        $date = now()->format('ymd');
        $lastMouvement = StockMouvement::where('code', 'like', "{$prefix}{$date}%")
            ->orderBy('code', 'desc')
            ->first();

        if ($lastMouvement) {
            $lastNumber = intval(substr($lastMouvement->code, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    /**
     * Traite plusieurs entrées en stock
     */
    public function traiterEntreesMultiples(array $entrees): array
    {
        $resultats = [];
        $erreurs = [];

        DB::beginTransaction();
        try {
            foreach ($entrees as $index => $entree) {
                $resultat = $this->traiterEntreeStock($entree);

                if (!$resultat['succes']) {
                    $erreurs[] = [
                        'ligne' => $index + 1,
                        'message' => $resultat['message'],
                        'article_id' => $entree['article_id'] ?? null,
                        'reference' => $entree['reference_mouvement'] ?? null
                    ];
                    continue;
                }

                $resultats[] = $resultat['donnees'];
            }

            if (empty($erreurs)) {
                DB::commit();
                return [
                    'succes' => true,
                    'message' => 'Toutes les entrées ont été traitées avec succès',
                    'resultats' => $resultats
                ];
            }

            DB::rollBack();
            return [
                'succes' => false,
                'message' => 'Certaines entrées n\'ont pas pu être traitées',
                'erreurs' => $erreurs
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'succes' => false,
                'message' => $e->getMessage(),
                'erreurs' => $erreurs
            ];
        }
    }

    /**
     * Recherche une conversion entre deux unités
     */
    private function rechercherConversion(int $unite_source_id, int $unite_base_id, int $article_id): ?ConversionUnite
    {
        return ConversionUnite::where(function ($query) use ($unite_source_id, $unite_base_id) {
            $query->where([
                'unite_source_id' => $unite_source_id,
                'unite_dest_id' => $unite_base_id
            ])->orWhere([
                'unite_source_id' => $unite_base_id,
                'unite_dest_id' => $unite_source_id
            ]);
        })
            ->where(function ($query) use ($article_id) {
                $query->where('article_id', $article_id)
                    ->orWhereNull('article_id');
            })
            ->where('statut', true)
            ->first();
    }

    /**
     * Convertit une quantité selon le sens de la conversion
     */
    private function convertirQuantite(float $quantite, ConversionUnite $conversion, int $unite_source_id): float
    {
        return $conversion->unite_source_id === $unite_source_id
            ? $conversion->convertir($quantite)
            : $conversion->convertirInverse($quantite);
    }

    /**
     * Calcule le Coût Unitaire Moyen Pondéré
     */
    private function calculerCUMP(float $ancien_stock, float $ancien_cump, float $quantite_entree, float $prix_entree): float
    {
        if ($ancien_stock + $quantite_entree == 0) {
            return 0;
        }

        return (($ancien_stock * $ancien_cump) + ($quantite_entree * $prix_entree))
            / ($ancien_stock + $quantite_entree);
    }

    /**
     * Valide les données d'entrée
     */
    private function validerDonneesEntree(array $donnees): void
    {
        $required = [
            'depot_id', 'article_id', 'unite_mesure_id',
            'quantite', 'prix_unitaire', 'date_mouvement',
            'reference_mouvement', 'user_id'
        ];

        foreach ($required as $field) {
            if (!isset($donnees[$field])) {
                throw new Exception("Le champ {$field} est requis");
            }
        }

        if ($donnees['quantite'] <= 0) {
            throw new Exception("La quantité doit être positive");
        }

        if ($donnees['prix_unitaire'] < 0) {
            throw new Exception("Le prix unitaire ne peut pas être négatif");
        }
    }
}
