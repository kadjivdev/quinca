<?php

namespace App\Http\Controllers\Catalogues;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\Tarification;
use App\Models\Catalogue\Article;
use App\Models\Catalogue\FamilleArticle;
use App\Models\Parametres\TypeTarif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TarificationController extends Controller
{
    /**
     * Afficher la liste des tarifications
     */
    public function index()
    {
        try {
            $tarifications = Tarification::with(['article', 'typeTarif'])->get();
            $articles = Article::where('statut', 'actif')->get();
            $typesTarifs = TypeTarif::where('statut', true)->get();
            $familles = FamilleArticle::where('statut', true)->get();

            // Statistiques
            $stats = [
                'total' => $tarifications->count(),
                'actifs' => $tarifications->where('statut', true)->count(),
                'inactifs' => $tarifications->where('statut', false)->count(),
                'articlesTarifes' => $tarifications->pluck('article_id')->unique()->count()
            ];

            $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

            return view('pages.catalogues.tarification.index', compact(
                'tarifications',
                'articles',
                'typesTarifs',
                'familles',
                'stats',
                'date'
            ));
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des tarifications:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors du chargement des données.');
        }
    }

    /**
     * Créer une nouvelle tarification
     */
    public function store(Request $request)
    {
        Log::info('Données reçues:', $request->all());

        $validator = Validator::make($request->all(), [
            'article_id' => 'required|exists:articles,id',
            'type_tarif_id' => [
                'required',
                'exists:type_tarifs,id',
                function ($attribute, $value, $fail) use ($request) {
                    // Vérifier si une tarification existe déjà pour cet article et ce type
                    $exists = Tarification::where([
                        'article_id' => $request->article_id,
                        'type_tarif_id' => $value,
                        ['id', '!=', $request->id ?? 0]
                    ])->exists();

                    if ($exists) {
                        $fail('Une tarification existe déjà pour cet article avec ce type de tarif.');
                    }
                }
            ],
            'prix' => 'required|numeric|min:0',
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

            $data = $request->all();
            $data['statut'] = $request->boolean('statut');

            $tarification = Tarification::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarification créée avec succès',
                'data' => $tarification
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création tarification:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la tarification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Charger les données d'une tarification pour modification
     */
    public function edit($id)
    {
        try {
            $tarification = Tarification::with(['article', 'typeTarif'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $tarification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la tarification'
            ], 404);
        }
    }

    /**
     * Mettre à jour une tarification
     */
    public function update(Request $request, $id)
    {
        $tarification = Tarification::find($id);

        if (!$tarification) {
            return response()->json([
                'success' => false,
                'message' => 'Tarification non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'prix' => 'required|numeric|min:0',
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

            $data = $request->only(['prix', 'statut']);
            $data['statut'] = $request->boolean('statut');

            $tarification->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tarification mise à jour avec succès',
                'data' => $tarification
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la tarification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAll(Request $request, $articleId)
{
    try {
        DB::beginTransaction();

        foreach ($request->prix as $tarificationId => $nouveauPrix) {
            $tarification = Tarification::findOrFail($tarificationId);
            $tarification->update(['prix' => $nouveauPrix]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Tarifs mis à jour avec succès'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur lors de la mise à jour des tarifs:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour des tarifs'
        ], 500);
    }
}


    /**
     * Supprimer une tarification
     */
    public function destroy($id)
    {
        try {
            $tarification = Tarification::findOrFail($id);
            $tarification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tarification supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la tarification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/Désactiver une tarification
     */
    public function toggleStatus($id)
    {
        try {
            $tarification = Tarification::findOrFail($id);
            $tarification->statut = !$tarification->statut;
            $tarification->save();

            return response()->json([
                'success' => true,
                'message' => 'Statut de la tarification modifié avec succès',
                'data' => $tarification
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
     * Récupérer les tarifications d'un article
     */
   /**
 * Récupérer les tarifications d'un article
 */
public function getByArticle($articleId)
{
    try {
        $article = Article::with(['tarifications' => function($query) {
            $query->with('typeTarif'); // Charger la relation typeTarif
        }])->findOrFail($articleId);

        return response()->json([
            'success' => true,
            'article' => [
                'id' => $article->id,
                'code_article' => $article->code_article,
                'libelle_article' => $article->libelle_article
            ],
            'data' => $article->tarifications->map(function($tarif) {
                return [
                    'id' => $tarif->id,
                    'prix' => $tarif->prix,
                    'statut' => $tarif->statut,
                    'type_tarif' => [
                        'id' => $tarif->typeTarif->id,
                        'libelle_type_tarif' => $tarif->typeTarif->libelle_type_tarif
                    ]
                ];
            })
        ]);
    } catch (\Exception $e) {
        Log::error('Erreur lors de la récupération des tarifications:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du chargement des tarifications'
        ], 500);
    }
}
    /**
     * Récupérer le prix d'un article pour un type de tarif
     */
    public function getPrix($articleId, $typeTarifId)
    {
        try {
            $tarification = Tarification::where([
                'article_id' => $articleId,
                'type_tarif_id' => $typeTarifId,
                'statut' => true
            ])->first();

            return response()->json([
                'success' => true,
                'data' => $tarification ? $tarification->prix : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du prix'
            ], 500);
        }
    }
}
