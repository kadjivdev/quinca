<?php
namespace App\Http\Controllers\Rapport;

use App\Http\Controllers\Controller;
use App\Models\Stock\{StockMouvement, StockDepot};
use App\Models\Parametre\{Depot, UniteMesure};
use App\Services\{ServiceStockEntree, ServiceStockSortie};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MouvementsStockExport;
use PDF;

class RapportStockController extends Controller
{
    protected $serviceEntree;
    protected $serviceSortie;

    public function mouvementReport(Request $request)
    {
        $depots = Depot::all();
        $selectedDepot = $request->depot_id ?
            Depot::findOrFail($request->depot_id) :
            $depots->first();

        $stats = $this->getStats($selectedDepot->id);
        $mouvements = $this->getMouvements($request);
        $stockDisponible = $this->getStockDisponible($selectedDepot->id);

        return view('pages.rapports.stocks.mouvement', compact(
            'stats',
            'mouvements',
            'depots',
            'selectedDepot',
            'stockDisponible'
        ));
    }

    public function __construct(
        ServiceStockEntree $serviceEntree,
        ServiceStockSortie $serviceSortie
    ) {
        $this->serviceEntree = $serviceEntree;
        $this->serviceSortie = $serviceSortie;
    }

    public function index(Request $request)
    {
        $depots = Depot::all();
        $selectedDepot = $request->depot_id ?
            Depot::findOrFail($request->depot_id) :
            $depots->first();

        $stats = $this->getStats($selectedDepot->id);
        $stockDisponible = $this->getStockDisponible($selectedDepot->id);
        $mouvements = $this->getMouvements($request);
        $alertes = $this->getAlertesStock($selectedDepot->id);

        return view('admin.rapports.stock.index', compact(
            'depots',
            'selectedDepot',
            'stats',
            'stockDisponible',
            'mouvements',
            'alertes'
        ));
    }

    private function getStockDisponible(int $depotId)
    {
        return StockDepot::with(['article'])
            ->where('depot_id', $depotId)
            ->where('quantite_reelle', '>', 0)
            ->get()
            ->map(function ($stock) {
                return [
                    'article' => $stock->article,
                    'quantite_reelle' => $stock->quantite_reelle,
                    'quantite_disponible' => $stock->getQuantiteDisponibleAttribute(),
                    'quantite_reservee' => $stock->quantite_reservee,
                    'prix_moyen' => $stock->prix_moyen,
                    'valeur_stock' => $stock->getValeurStockAttribute(),
                    'dernier_mouvement' => $stock->date_dernier_mouvement,
                    'dernier_inventaire' => $stock->date_dernier_inventaire,
                    'en_alerte' => $stock->isEnAlerte(),
                    'seuil_minimum' => $stock->isStockMinimum(),
                    'seuil_maximum' => $stock->isStockMaximum(),
                ];
            });
    }

    private function getStats(int $depotId)
    {
        $periode = Carbon::now()->startOfMonth();

        $mouvements = StockMouvement::where('depot_id', $depotId)
            ->whereMonth('date_mouvement', $periode->month)
            ->whereYear('date_mouvement', $periode->year)
            ->get();

        $entrees = $mouvements->where('type_mouvement', StockMouvement::TYPE_ENTREE);
        $sorties = $mouvements->where('type_mouvement', StockMouvement::TYPE_SORTIE);

        return [
            'entrees' => [
                'nombre' => $entrees->count(),
                'valeur' => $entrees->sum(function($m) {
                    return $m->quantite * $m->prix_unitaire;
                })
            ],
            'sorties' => [
                'nombre' => $sorties->count(),
                'valeur' => $sorties->sum(function($m) {
                    return $m->quantite * $m->prix_unitaire;
                })
            ],
            'stock_actuel' => [
                'articles' => StockDepot::where('depot_id', $depotId)
                    ->where('quantite_reelle', '>', 0)
                    ->count(),
                'valeur_totale' => StockDepot::where('depot_id', $depotId)
                    ->sum(DB::raw('quantite_reelle * prix_moyen'))
            ],
            'evolution_quotidienne' => $this->getEvolutionQuotidienne($depotId),
            'articles_critiques' => $this->getArticlesCritiques($depotId)
        ];
    }

    private function getEvolutionQuotidienne(int $depotId)
    {
        $debut = Carbon::now()->startOfMonth();
        $fin = Carbon::now();

        return StockMouvement::where('depot_id', $depotId)
            ->whereBetween('date_mouvement', [$debut, $fin])
            ->select(
                DB::raw('DATE(date_mouvement) as date'),
                DB::raw('SUM(CASE
                    WHEN type_mouvement = "' . StockMouvement::TYPE_ENTREE . '"
                    THEN quantite * prix_unitaire
                    ELSE -quantite * prix_unitaire
                END) as valeur_nette')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getArticlesCritiques(int $depotId)
    {
        return StockDepot::with('article')
            ->where('depot_id', $depotId)
            ->where(function($query) {
                $query->whereColumn('quantite_reelle', '<=', 'seuil_alerte')
                    ->orWhereColumn('quantite_reelle', '<=', 'stock_minimum');
            })
            ->get()
            ->map(function($stock) {
                return [
                    'article' => $stock->article,
                    'stock_actuel' => $stock->quantite_reelle,
                    'seuil_alerte' => $stock->seuil_alerte,
                    'stock_minimum' => $stock->stock_minimum,
                    'dernier_mouvement' => $stock->date_dernier_mouvement
                ];
            });
    }

    private function getAlertesStock(int $depotId)
    {
        return StockDepot::with('article')
            ->where('depot_id', $depotId)
            ->where(function($query) {
                $query->where('quantite_reelle', '<=', DB::raw('seuil_alerte'))
                    ->orWhere('quantite_reelle', '<=', DB::raw('stock_minimum'))
                    ->orWhere('quantite_reelle', '>=', DB::raw('stock_maximum'));
            })
            ->get()
            ->map(function($stock) {
                return [
                    'article' => $stock->article,
                    'type_alerte' => $this->determinerTypeAlerte($stock),
                    'quantite_actuelle' => $stock->quantite_reelle,
                    'seuil_reference' => $this->getSeuilReference($stock)
                ];
            });
    }

    private function determinerTypeAlerte(StockDepot $stock): string
    {
        if ($stock->isEnAlerte()) return 'ALERTE';
        if ($stock->isStockMinimum()) return 'MINIMUM';
        if ($stock->isStockMaximum()) return 'MAXIMUM';
        return 'NORMAL';
    }

    private function getSeuilReference(StockDepot $stock): float
    {
        if ($stock->isEnAlerte()) return $stock->seuil_alerte;
        if ($stock->isStockMinimum()) return $stock->stock_minimum;
        if ($stock->isStockMaximum()) return $stock->stock_maximum;
        return 0;
    }

    private function getMouvements(Request $request)
    {
        $query = StockMouvement::with(['article', 'depot', 'uniteMesure', 'user'])
            ->orderBy('date_mouvement', 'desc');

        if ($request->filled('depot_id')) {
            $query->where('depot_id', $request->depot_id);
        }

        if ($request->filled('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        if ($request->filled('type_mouvement')) {
            $query->where('type_mouvement', $request->type_mouvement);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_mouvement', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_mouvement', '<=', $request->date_fin);
        }

        return $query->paginate(15);
    }

    public function export(Request $request)
    {
        try {
            $stockDisponible = $this->getStockDisponible($request->depot_id);
            return Excel::download(
                new MouvementsStockExport($stockDisponible),
                'stock_disponible_' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    public function print(Request $request)
    {
        try {
            $depot = Depot::findOrFail($request->depot_id);
            $stockDisponible = $this->getStockDisponible($depot->id);
            $stats = $this->getStats($depot->id);
            $alertes = $this->getAlertesStock($depot->id);

            $pdf = PDF::loadView('admin.rapports.stock.print', compact(
                'depot',
                'stockDisponible',
                'stats',
                'alertes'
            ));

            return $pdf->download('rapport_stock_' . $depot->id . '_' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'impression: ' . $e->getMessage());
        }
    }

    public function changeDepot__(Request $request)
{
    $request->validate([
        'depot_id' => 'required|exists:depots,id'
    ]);

    $selectedDepot = Depot::findOrFail($request->depot_id);
    $stats = $this->getStats($selectedDepot->id);
    $stockDisponible = $this->getStockDisponible($selectedDepot->id);
    $mouvements = $this->getMouvements($request);
    $alertes = $this->getAlertesStock($selectedDepot->id);

    return view('pages.rapports.stocks.mouvement', compact(
        'selectedDepot',
        'stats',
        'stockDisponible',
        'mouvements',
        'alertes'
    ));
}

public function rapportStockDisponible(Request $request)
{
    $depots = Depot::actif()->get();
    $selectedDepot = $request->depot_id ?
        Depot::findOrFail($request->depot_id) :
        $depots->first();

    $stocks = StockDepot::with(['article.uniteMesure', 'depot'])
        ->where('depot_id', $selectedDepot->id)
        ->where('quantite_reelle', '>', 0)
        ->get()
        ->map(function ($stock) {
            return [
                'article' => [
                    'code' => $stock->article->code_article,
                    'designation' => $stock->article->designation,
                    'unite' => $stock->article->uniteMesure?->libelle_unite
                ],
                'depot' => $stock->depot->libelle_depot,
                'quantite_reelle' => $stock->quantite_reelle,
                'quantite_disponible' => $stock->quantite_disponible,
                'quantite_reservee' => $stock->quantite_reservee,
                'prix_moyen' => $stock->prix_moyen,
                'valeur_stock' => $stock->valeur_stock,
                'statut' => $stock->isEnAlerte() ? 'Alerte' :
                           ($stock->isStockMinimum() ? 'Minimum' :
                           ($stock->isStockMaximum() ? 'Maximum' : 'Normal'))
            ];
        });

    if ($request->wantsJson()) {
        return response()->json([
            'depots' => $depots,
            'selected_depot' => $selectedDepot,
            'stocks' => $stocks
        ]);
    }

    return view('pages.rapports.stocks.stock-dispo', compact('depots', 'selectedDepot', 'stocks'));
}

// public function changeDepot(Request $request)
// {
//     $request->validate(['depot_id' => 'required|exists:depots,id']);

//     $depot = Depot::findOrFail($request->depot_id);

//     if ($request->wantsJson()) {
//         return response()->json([
//             'depot' => $depot,
//             'stocks' => $this->getStockData($depot->id)
//         ]);
//     }

//     return redirect()->route('rapports.stock.disponible', ['depot_id' => $depot->id]);
// }

// private function getStockData($depotId)
// {
//     return StockDepot::with(['article.uniteMesure', 'depot'])
//         ->where('depot_id', $depotId)
//         ->where('quantite_reelle', '>', 0)
//         ->get()
//         ->map(function ($stock) {
//             return [
//                 'article' => [
//                     'code' => $stock->article->code_article,
//                     'designation' => $stock->article->designation,
//                     'unite' => $stock->article->uniteMesure?->libelle_unite
//                 ],
//                 'quantite_reelle' => $stock->quantite_reelle,
//                 'quantite_disponible' => $stock->quantite_disponible,
//                 'quantite_reservee' => $stock->quantite_reservee,
//                 'prix_moyen' => $stock->prix_moyen,
//                 'valeur_stock' => $stock->valeur_stock,
//                 'statut' => $stock->isEnAlerte() ? 'Alerte' :
//                           ($stock->isStockMinimum() ? 'Minimum' :
//                           ($stock->isStockMaximum() ? 'Maximum' : 'Normal'))
//             ];
//         });
// }

public function changeDepot(Request $request)
{
    $request->validate(['depot_id' => 'required|exists:depots,id']);

    $depot = Depot::findOrFail($request->depot_id);

    if ($request->wantsJson()) {
        return response()->json([
            'depot' => $depot,
            'stocks' => $this->getStockData($depot->id)
        ]);
    }

    return redirect()->route('rapports.stock-dispo', ['depot_id' => $depot->id]);
}

private function getStockData($depotId)
{
    return StockDepot::with(['article.uniteMesure', 'depot'])
        ->where('depot_id', $depotId)
        ->where('quantite_reelle', '>', 0)
        ->get()
        ->map(function ($stock) {
            return [
                'article' => [
                    'code' => $stock->article->code_article,
                    'designation' => $stock->article->designation,
                    'unite' => $stock->article->uniteMesure?->libelle_unite
                ],
                'quantite_reelle' => $stock->quantite_reelle,
                'quantite_disponible' => $stock->quantite_disponible,
                'quantite_reservee' => $stock->quantite_reservee,
                'prix_moyen' => $stock->prix_moyen,
                'valeur_stock' => $stock->valeur_stock,
                'statut' => $stock->isEnAlerte() ? 'Alerte' :
                          ($stock->isStockMinimum() ? 'Minimum' :
                          ($stock->isStockMaximum() ? 'Maximum' : 'Normal'))
            ];
        });
}

}
