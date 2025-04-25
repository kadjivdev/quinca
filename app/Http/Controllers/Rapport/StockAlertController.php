<?php

namespace App\Http\Controllers\Rapport;



use App\Http\Controllers\Controller;
use App\Models\Catalogue\Article;
use App\Models\Stock\StockDepot;
use App\Models\Parametres\Depot;
use App\Models\Catalogue\FamilleArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAlertController extends Controller
{
    /**
     * Affiche le rapport des articles en alerte
     */
    public function index(Request $request)
    {
        try {
            // Récupération des filtres
            $depot_id = $request->input('depot_id');
            $niveau_alerte = $request->input('niveau_alerte');
            $famille_id = $request->input('famille_id');

            // Construction de la requête de base
            $query = StockDepot::query()
                ->join('articles', 'stock_depots.article_id', '=', 'articles.id')
                ->join('depots', 'stock_depots.depot_id', '=', 'depots.id')
                ->where('articles.stockable', true)
                ->where('articles.statut', Article::STATUT_ACTIF)
                ->where(function ($q) {
                    $q->whereRaw('stock_depots.quantite <= articles.stock_securite')
                      ->orWhereRaw('stock_depots.quantite <= articles.stock_minimum');
                });

            // Application des filtres
            if ($depot_id) {
                $query->where('stock_depots.depot_id', $depot_id);
            }

            if ($niveau_alerte) {
                if ($niveau_alerte === 'critique') {
                    $query->whereRaw('stock_depots.quantite <= articles.stock_minimum');
                } elseif ($niveau_alerte === 'alerte') {
                    $query->whereRaw('stock_depots.quantite <= articles.stock_securite')
                          ->whereRaw('stock_depots.quantite > articles.stock_minimum');
                }
            }

            if ($famille_id) {
                $query->where('articles.famille_id', $famille_id);
            }

            // Récupération des articles avec leurs informations
            $articles = $query->select([
                'articles.*',
                'stock_depots.quantite as stock_actuel',
                'depots.libelle_depot',
                'depots.id as depot_id'
            ])->get();

            // Calcul des statistiques
            $stats = $this->calculateStats();

            // Récupération des données pour les filtres
            $depots = Depot::where('statut', true)->orderBy('libelle_depot')->get();
            $familles = FamilleArticle::orderBy('libelle')->get();

            return view('stock.alerts', compact(
                'articles',
                'stats',
                'depots',
                'familles'
            ));

        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue lors du chargement des données: ' . $e->getMessage());
        }
    }

    /**
     * Calcule les statistiques des articles en alerte
     */
    private function calculateStats()
    {
        try {
            $stats = DB::table('stock_depots')
                ->join('articles', 'stock_depots.article_id', '=', 'articles.id')
                ->where('articles.stockable', true)
                ->where('articles.statut', Article::STATUT_ACTIF)
                ->select([
                    DB::raw('COUNT(DISTINCT CASE WHEN stock_depots.quantite <= articles.stock_minimum THEN stock_depots.id END) as articles_critiques'),
                    DB::raw('COUNT(DISTINCT CASE WHEN stock_depots.quantite <= articles.stock_securite AND stock_depots.quantite > articles.stock_minimum THEN stock_depots.id END) as articles_alerte'),
                    DB::raw('COUNT(DISTINCT CASE WHEN stock_depots.quantite <= articles.stock_securite THEN stock_depots.id END) as articles_a_commander'),
                    DB::raw('SUM(CASE WHEN stock_depots.quantite <= articles.stock_securite THEN stock_depots.quantite * COALESCE(articles.prix_achat, 0) ELSE 0 END) as valeur_stock_alerte')
                ])
                ->first();

            return [
                'articles_critiques' => $stats->articles_critiques ?? 0,
                'articles_alerte' => $stats->articles_alerte ?? 0,
                'articles_a_commander' => $stats->articles_a_commander ?? 0,
                'valeur_stock_alerte' => $stats->valeur_stock_alerte ?? 0
            ];

        } catch (\Exception $e) {
            \Log::error('Erreur lors du calcul des statistiques: ' . $e->getMessage());
            return [
                'articles_critiques' => 0,
                'articles_alerte' => 0,
                'articles_a_commander' => 0,
                'valeur_stock_alerte' => 0
            ];
        }
    }

    /**
     * Récupère l'historique des mouvements d'un article
     */
    public function getStockHistory(Request $request, $articleId)
    {
        try {
            $history = DB::table('mouvements_stock')
                ->where('article_id', $articleId)
                ->join('users', 'mouvements_stock.user_id', '=', 'users.id')
                ->select([
                    'mouvements_stock.*',
                    'users.name as user_name'
                ])
                ->orderBy('mouvements_stock.created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique'
            ], 500);
        }
    }

    /**
     * Exporte les données des articles en alerte
     */
    // public function export(Request $request)
    // {
    //     try {
    //         // Logique d'export similaire à la méthode index
    //         $query = StockDepot::query()
    //             ->join('articles', 'stock_depots.article_id', '=', 'articles.id')
    //             ->join('depots', 'stock_depots.depot_id', '=', 'depots.id')
    //             ->where('articles.stockable', true)
    //             ->where('articles.statut', Article::STATUT_ACTIF)
    //             ->where(function ($q) {
    //                 $q->whereRaw('stock_depots.quantite <= articles.stock_securite')
    //                   ->orWhereRaw('stock_depots.quantite <= articles.stock_minimum');
    //             });

    //         // Application des mêmes filtres que pour l'index
    //         if ($request->filled('depot_id')) {
    //             $query->where('stock_depots.depot_id', $request->depot_id);
    //         }

    //         if ($request->filled('niveau_alerte')) {
    //             if ($request->niveau_alerte === 'critique') {
    //                 $query->whereRaw('stock_depots.quantite <= articles.stock_minimum');
    //             } elseif ($request->niveau_alerte === 'alerte') {
    //                 $query->whereRaw('stock_depots.quantite <= articles.stock_securite')
    //                       ->whereRaw('stock_depots.quantite > articles.stock_minimum');
    //             }
    //         }

    //         if ($request->filled('famille_id')) {
    //             $query->where('articles.famille_id', $request->famille_id);
    //         }

    //         $articles = $query->select([
    //             'articles.code_article',
    //             'articles.designation',
    //             'depots.libelle_depot',
    //             'stock_depots.quantite as stock_actuel',
    //             'articles.stock_minimum',
    //             'articles.stock_securite',
    //             'articles.stock_maximum'
    //         ])->get();

    //         // En-têtes du fichier Excel
    //         $headers = [
    //             'Code Article',
    //             'Désignation',
    //             'Magasin',
    //             'Stock Actuel',
    //             'Stock Minimum',
    //             'Stock Sécurité',
    //             'Stock Maximum',
    //             'Statut'
    //         ];

    //         // Création du fichier Excel
    //         return \Excel::download(new \App\Exports\StockAlertsExport($articles, $headers), 'articles-en-alerte.xlsx');

    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
    //     }
    // }
}
