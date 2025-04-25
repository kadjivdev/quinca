<?php

namespace App\Services;

use App\Models\Stock\{StockMouvement, StockDepot};
use App\Models\Catalogue\Article;
use App\Models\Parametre\{UniteMesure, ConversionUnite};
use Illuminate\Support\Facades\DB;
use Exception;

class ServiceStockSortie
{
    /**
     * Traite une sortie de stock
     * - Vérifie la disponibilité du stock
     * - Vérifie l'unité de base
     * - Convertit si nécessaire
     */
    public function traiterSortieStock(array $donnees): array
    {
        try {
            DB::beginTransaction();

            \Log::debug("Début traitement sortie stock", ['donnees' => $donnees]);

            // 1. Validation de base
            $this->validerDonneesSortie($donnees);

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
                    'unite_origine' => $uniteSource->code_unite,
                    'quantite_base' => $quantite_base,
                    'unite_base' => $uniteBase->code_unite
                ]);
            }

            // 5. Vérification de la disponibilité du stock
            $stock = StockDepot::where([
                'depot_id' => $donnees['depot_id'],
                'article_id' => $article->id
            ])->first();

            if (!$stock || $stock->quantite_reelle < $quantite_base) {
                throw new Exception(sprintf(
                    "Stock insuffisant pour l'article %s (Demandé: %s, Disponible: %s)",
                    $article->code_article,
                    $quantite_base,
                    $stock ? $stock->quantite_reelle : 0
                ));
            }

            // 6. Création du mouvement de stock
            $mouvement = StockMouvement::create([
                'code' => $this->genererCodeMouvement(),
                'depot_id' => $donnees['depot_id'],
                'article_id' => $article->id,
                'date_mouvement' => $donnees['date_mouvement'],
                'type_mouvement' => StockMouvement::TYPE_SORTIE,
                'quantite' => $quantite_base,
                'quantite_origine' => $donnees['quantite'],
                'unite_mesure_id' => $article->unite_mesure_id,
                'unite_mesure_origine_id' => $unite_origine_id,
                'prix_unitaire' => $stock->prix_moyen,
                'reference_mouvement' => $donnees['reference_mouvement'],
                'document_type' => $donnees['document_type'] ?? null,
                'document_id' => $donnees['document_id'] ?? null,
                'notes' => $donnees['notes'] ?? null,
                'user_id' => $donnees['user_id']
            ]);

            // 7. Mise à jour du stock
            $stock->update([
                'quantite_reelle' => $stock->quantite_reelle - $quantite_base,
                'date_dernier_mouvement' => $donnees['date_mouvement'],
                'user_id' => $donnees['user_id']
            ]);

            DB::commit();

            \Log::debug("Sortie de stock réussie", [
                'mouvement_id' => $mouvement->id,
                'nouveau_stock' => $stock->quantite_reelle
            ]);

            return [
                'succes' => true,
                'message' => 'Sortie de stock effectuée avec succès',
                'donnees' => [
                    'mouvement_id' => $mouvement->id,
                    'code_mouvement' => $mouvement->code,
                    'quantite_origine' => $donnees['quantite'],
                    'unite_origine' => UniteMesure::find($unite_origine_id)->code_unite,
                    'quantite_base' => $quantite_base,
                    'unite_base' => $article->uniteMesure->code_unite,
                    'nouveau_stock' => $stock->quantite_reelle
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error("Erreur traitement sortie stock", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'donnees' => $donnees
            ]);

            return [
                'succes' => false,
                'message' => 'Erreur lors de la sortie de stock: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traite plusieurs sorties de stock
     */
    public function traiterSortiesMultiples(array $sorties): array
    {
        $resultats = [];
        $erreurs = [];

        DB::beginTransaction();
        try {
            foreach ($sorties as $index => $sortie) {
                $resultat = $this->traiterSortieStock($sortie);

                if (!$resultat['succes']) {
                    $erreurs[] = [
                        'ligne' => $index + 1,
                        'message' => $resultat['message'],
                        'article_id' => $sortie['article_id'] ?? null,
                        'reference' => $sortie['reference_mouvement'] ?? null
                    ];
                    continue;
                }

                $resultats[] = $resultat['donnees'];
            }

            if (empty($erreurs)) {
                DB::commit();
                return [
                    'succes' => true,
                    'message' => 'Toutes les sorties ont été traitées avec succès',
                    'resultats' => $resultats
                ];
            }

            DB::rollBack();
            return [
                'succes' => false,
                'message' => 'Certaines sorties n\'ont pas pu être traitées',
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

    private function convertirQuantite(float $quantite, ConversionUnite $conversion, int $unite_source_id): float
    {
        return $conversion->unite_source_id === $unite_source_id
            ? $conversion->convertir($quantite)
            : $conversion->convertirInverse($quantite);
    }

    private function validerDonneesSortie(array $donnees): void
    {
        $required = [
            'depot_id', 'article_id', 'unite_mesure_id',
            'quantite', 'date_mouvement',
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
    }
}
