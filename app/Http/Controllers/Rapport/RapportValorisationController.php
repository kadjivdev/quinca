<?php

namespace App\Http\Controllers\Rapport;


use App\Http\Controllers\Controller;
use App\Models\Stock\StockValorisation;
use App\Models\Parametre\Depot;
use App\Models\Catalogue\{Article, FamilleArticle};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class RapportValorisationController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des dépôts actifs
        $depots = Depot::actif()->get();

        // Récupération des familles d'articles
        $familles = FamilleArticle::all();

        // Construction de la requête de base pour les valorisations
        $query = StockValorisation::with(['article.famille', 'article.unite', 'depot'])
            ->select([
                'article_id',
                'depot_id',
                'methode_valorisation',
                DB::raw('SUM(CASE WHEN type_operation = "ENTREE" THEN quantite ELSE -quantite END) as quantite'),
                DB::raw('SUM(CASE WHEN type_operation = "ENTREE" THEN valeur_totale ELSE -valeur_totale END) as valeur_totale'),
                DB::raw('SUM(CASE WHEN type_operation = "ENTREE" THEN valeur_totale ELSE -valeur_totale END) /
                        NULLIF(SUM(CASE WHEN type_operation = "ENTREE" THEN quantite ELSE -quantite END), 0) as prix_unitaire')
            ])
            ->groupBy('article_id', 'depot_id', 'methode_valorisation');

        // Appliquer les filtres
        if ($request->filled('depot_id')) {
            $query->where('depot_id', $request->depot_id);
        }

        if ($request->filled('famille_id')) {
            $query->whereHas('article', function($q) use ($request) {
                $q->where('famille_id', $request->famille_id);
            });
        }

        // Filtrer par statut de stock si demandé
        if ($request->filled('stock_status')) {
            $query->whereHas('article', function($q) use ($request) {
                switch($request->stock_status) {
                    case 'critique':
                        $q->whereRaw('stock_actuel <= stock_minimum');
                        break;
                    case 'alerte':
                        $q->whereRaw('stock_actuel > stock_minimum AND stock_actuel <= stock_securite');
                        break;
                    case 'surplus':
                        $q->whereRaw('stock_actuel >= stock_maximum');
                        break;
                    case 'normal':
                        $q->whereRaw('stock_actuel > stock_securite AND stock_actuel < stock_maximum');
                        break;
                }
            });
        }

        $valorisations = $query->having('quantite', '>', 0)->get();

        // Calcul des statistiques
        $totalValeur = $valorisations->sum('valeur_totale');
        $totalArticles = $valorisations->count();
        $stocksCritiques = $valorisations->filter(function($val) {
            return $val->article->isStockCritique();
        })->count();
        $depotsActifs = $depots->count();

        return view('pages.rapports.stocks.valorisation', compact(
            'valorisations',
            'depots',
            'familles',
            'totalValeur',
            'totalArticles',
            'stocksCritiques',
            'depotsActifs',
            'date'
        ))->with('date', Carbon::now()->format('d/m/Y'));
    }

    // public function export(Request $request)
    // {
    //     // Logique d'export similaire à l'index mais formatée pour Excel/PDF
    //     $valorisations = $this->getValorisations($request);

    //     return Excel::download(new StockValorisationExport($valorisations), 'valorisation-stock-' . now()->format('d-m-Y') . '.xlsx');
    // }

    // public function printFicheStock($articleId, $depotId)
    // {
    //     $article = Article::findOrFail($articleId);
    //     $depot = Depot::findOrFail($depotId);

    //     // Récupérer l'historique des mouvements
    //     $mouvements = StockValorisation::where('article_id', $articleId)
    //         ->where('depot_id', $depotId)
    //         ->orderBy('date_valorisation', 'desc')
    //         ->get();

    //     $pdf = PDF::loadView('stock.valorisation.fiche-stock', compact('article', 'depot', 'mouvements'));

    //     return $pdf->download('fiche-stock-' . $article->code_article . '.pdf');
    // }

    public function getArticleHistory($articleId, $depotId)
{
    try {
        $article = Article::findOrFail($articleId);
        $depot = Depot::findOrFail($depotId);

        $mouvements = StockValorisation::where('article_id', $articleId)
            ->where('depot_id', $depotId)
            ->with(['mouvementStock'])
            ->orderBy('date_valorisation', 'desc')
            ->get()
            ->map(function($val) {
                return [
                    'date' => $val->date_valorisation->format('d/m/Y H:i'),
                    'type' => $val->type_operation,
                    'quantite' => $val->quantite,
                    'prix_unitaire' => $val->prix_unitaire,
                    'valeur_totale' => $val->valeur_totale,
                    'reference' => $val->reference_source,
                    'observations' => $val->observations
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'article' => [
                    'code_article' => $article->code_article,
                    'designation' => $article->designation
                ],
                'depot' => [
                    'libelle_depot' => $depot->libelle_depot
                ],
                'mouvements' => $mouvements
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération de l\'historique'
        ], 422);
    }
}

    protected function getValorisations(Request $request)
    {
        // Logique de récupération des valorisations avec les filtres
        // Similaire à la méthode index mais retourne uniquement la requête
        $query = StockValorisation::with(['article.famille', 'article.unite', 'depot']);

        // Appliquer les filtres...

        return $query->get();
    }
}
