<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Models\Parametre\ConversionUnite;
use App\Models\Parametre\UniteMesure;
use App\Models\Catalogue\{FamilleArticle,Article};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;


class ConversionUniteController extends Controller
{
    /**
     * Afficher la liste des conversions
     */

     public function index()
     {
         try {
             // Chargement des conversions avec leurs relations
             $conversions = ConversionUnite::with(['uniteSource', 'uniteDest', 'article'])
                                         ->get();

             // Chargement des unités de mesure actives
             $unitesMesure = UniteMesure::where('statut', true)
                                       ->orderBy('code_unite')
                                       ->get();

             // Chargement des familles avec leurs articles actifs
             $familles = FamilleArticle::with(['articles' => function($query) {
                             $query->where('statut', Article::STATUT_ACTIF)
                                  ->orderBy('code_article');
                         }])
                         ->where('statut', true)
                         ->orderBy('libelle_famille')
                         ->get();

             // Chargement des articles actifs avec leurs familles
             $articles = Article::with('famille')
                              ->where('statut', Article::STATUT_ACTIF)
                              ->orderBy('code_article')
                              ->get();

             // Calcul des statistiques
             $conversionsActives = $conversions->where('statut', true)->count();
             $conversionsParArticle = $conversions->whereNotNull('article_id')->count();
             $conversionsGenerales = $conversions->whereNull('article_id')->count();

             // Date formatée en français
             $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

             // Log pour debug
             Log::info('Chargement des données de conversion:', [
                 'nb_unites' => $unitesMesure->count(),
                 'nb_familles' => $familles->count(),
                 'nb_articles' => $articles->count(),
                 'details_familles' => $familles->map(function($famille) {
                     return [
                         'id' => $famille->id,
                         'libelle' => $famille->libelle_famille,
                         'nb_articles' => $famille->articles->count()
                     ];
                 })
             ]);

             return view('pages.parametre.conversion_unite.index', compact(
                 'conversions',
                 'unitesMesure',
                 'articles',
                 'familles',
                 'conversionsActives',
                 'conversionsParArticle',
                 'conversionsGenerales',
                 'date'
             ));

         } catch (\Exception $e) {
             Log::error('Erreur dans l\'affichage des conversions:', [
                 'message' => $e->getMessage(),
                 'trace' => $e->getTraceAsString()
             ]);

             return redirect()->back()->with('error',
                 'Une erreur est survenue lors du chargement des données.'
             );
         }
     }

    public function store(Request $request)
    {
        Log::info('Données reçues pour création conversion:', $request->all());

        // Validation de base
        $validator = Validator::make($request->all(), [
            'unite_source_id' => 'required|exists:unite_mesures,id',
            'unite_dest_id' => 'required|exists:unite_mesures,id',
            'coefficient' => 'required|numeric|gt:0',
            'conversion_type' => 'required|in:generale,famille,articles',
            'famille_id' => 'required_if:conversion_type,famille|exists:famille_articles,id',
            'article_ids' => 'required_if:conversion_type,articles|array',
            'article_ids.*' => 'exists:articles,id',
            'statut' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $conversionType = $request->conversion_type;
            $articleIds = [];

            // Déterminer les articles à traiter selon le type de conversion
            switch ($conversionType) {
                case 'generale':
                    // Conversion générale, pas d'articles spécifiques
                    break;

                case 'famille':
                    // Récupérer tous les articles de la famille
                    $articleIds = Article::where('famille_id', $request->famille_id)
                                      ->where('statut', true)
                                      ->pluck('id')
                                      ->toArray();
                    break;

                case 'articles':
                    // Utiliser les articles sélectionnés
                    $articleIds = $request->article_ids;
                    break;
            }

            $createdConversions = [];

            // Créer la conversion générale si demandé
            if ($conversionType === 'generale') {
                $data = $request->all();
                $data['article_id'] = null; // Explicitement mettre article_id à null
                $conversion = $this->createConversion($data);
                $createdConversions[] = $conversion;
            } else {
                // Créer les conversions pour chaque article
                foreach ($articleIds as $articleId) {
                    $data = array_merge($request->all(), ['article_id' => $articleId]);

                    // Vérifier si une conversion similaire existe déjà
                    $existingConversion = ConversionUnite::trouverConversion(
                        $request->unite_source_id,
                        $request->unite_dest_id,
                        $articleId
                    );

                    if (!$existingConversion) {
                        $conversion = $this->createConversion($data);
                        $createdConversions[] = $conversion;
                    } else {
                        // Mettre à jour l'existant si nécessaire
                        $existingConversion->update($data);
                    }
                }
            }

            DB::commit();

            $message = match ($conversionType) {
                'generale' => 'Conversion générale créée avec succès',
                'famille' => 'Conversion créée pour tous les articles de la famille',
                'articles' => count($createdConversions) . ' conversion(s) créée(s) avec succès'
            };

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $createdConversions
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création conversion:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création des conversions: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createConversion(array $data): ConversionUnite
    {
        // Si même unité, forcer le coefficient à 1
        if ($data['unite_source_id'] === $data['unite_dest_id']) {
            $data['coefficient'] = 1;
        }

        // S'assurer que le statut est un booléen
        $data['statut'] = isset($data['statut']) ? (bool)$data['statut'] : true;

        return ConversionUnite::create($data);
    }

    // ... [Les autres méthodes restent inchangées] ...

    /**
     * Récupérer les articles d'une famille
     */
    public function getArticlesByFamille($familleId)
    {
        try {
            $articles = Article::where('famille_id', $familleId)
                             ->where('statut', true)
                             ->get(['id', 'code_article', 'designation']);

            return response()->json([
                'success' => true,
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des articles'
            ], 500);
        }
    }


    /**
     * Mettre à jour une conversion
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'unite_source_id' => 'required|exists:unite_mesures,id',
            'unite_dest_id' => 'required|exists:unite_mesures,id',
            'coefficient' => 'required|numeric|gt:0',
            'article_id' => 'nullable|exists:articles,id',
            'statut' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $conversion = ConversionUnite::findOrFail($id);

            // Vérifier si une conversion similaire existe déjà
            $existingConversion = ConversionUnite::where(function($query) use ($request) {
                $query->where([
                    'unite_source_id' => $request->unite_source_id,
                    'unite_dest_id' => $request->unite_dest_id,
                    'article_id' => $request->article_id
                ])
                ->orWhere(function($query) use ($request) {
                    $query->where([
                        'unite_source_id' => $request->unite_dest_id,
                        'unite_dest_id' => $request->unite_source_id,
                        'article_id' => $request->article_id
                    ]);
                });
            })
            ->where('id', '!=', $id)
            ->exists();

            if ($existingConversion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une conversion entre ces unités existe déjà pour cet article'
                ], 422);
            }

            $data = $request->all();
            $data['coefficient'] = $request->unite_source_id === $request->unite_dest_id ? 1 : $request->coefficient;

            $conversion->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Conversion mise à jour avec succès',
                'data' => $conversion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la conversion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Charger les données d'une conversion
     */
    public function edit($id)
    {
        try {
            $conversion = ConversionUnite::with(['uniteSource', 'uniteDest', 'article'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $conversion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la conversion'
            ], 404);
        }
    }

    /**
     * Supprimer une conversion
     */
    public function destroy($id)
    {
        try {
            $conversion = ConversionUnite::findOrFail($id);
            $conversion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Conversion supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la conversion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une conversion
     */
    public function toggleStatus($id)
    {
        try {
            $conversion = ConversionUnite::findOrFail($id);
            $conversion->toggleStatut();

            return response()->json([
                'success' => true,
                'message' => 'Statut de la conversion modifié avec succès',
                'data' => $conversion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les conversions par unité
     */
    public function getByUnite($uniteId)
    {
        try {
            $conversions = ConversionUnite::where('unite_source_id', $uniteId)
                ->orWhere('unite_dest_id', $uniteId)
                ->with(['uniteSource', 'uniteDest', 'article'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $conversions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des conversions'
            ], 500);
        }
    }

    /**
     * Récupérer les conversions par article
     */
    public function getByArticle($articleId)
    {
        try {
            $conversions = ConversionUnite::where('article_id', $articleId)
                ->with(['uniteSource', 'uniteDest', 'article'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $conversions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des conversions'
            ], 500);
        }
    }

    /**
     * Créer des conversions pour plusieurs articles
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unite_source_id' => 'required|exists:unite_mesures,id',
            'unite_dest_id' => 'required|exists:unite_mesures,id',
            'coefficient' => 'required|numeric|gt:0',
            'article_ids' => 'required|array',
            'article_ids.*' => 'exists:articles,id',
            'statut' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $results = [];
            foreach ($request->article_ids as $articleId) {
                $existingConversion = ConversionUnite::trouverConversion(
                    $request->unite_source_id,
                    $request->unite_dest_id,
                    $articleId
                );

                if (!$existingConversion) {
                    $conversion = ConversionUnite::create([
                        'unite_source_id' => $request->unite_source_id,
                        'unite_dest_id' => $request->unite_dest_id,
                        'article_id' => $articleId,
                        'coefficient' => $request->coefficient,
                        'statut' => $request->boolean('statut', true)
                    ]);
                    $results[] = $conversion;
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($results) . ' conversion(s) créée(s) avec succès',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création des conversions',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
