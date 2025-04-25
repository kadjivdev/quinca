<?php

namespace App\Http\Controllers\Rapport;



use App\Http\Controllers\Controller;
use App\Models\Stock\{StockMouvement, StockDepot, StockSortie, StockEntree};
use App\Models\Catalogue\Article;
use App\Models\Parametre\Depot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockRotationController extends Controller

{
    public function index(Request $request)
    {
          // Récupération des dépôts actifs
          $depots = Depot::actif()->get();

        // Période par défaut : 30 derniers jours
        $dateDebut = $request->date_debut ? Carbon::parse($request->date_debut) : Carbon::now()->subDays(30);

        $dateFin = $request->date_fin ? Carbon::parse($request->date_fin) : Carbon::now();

        // Récupérer les articles avec leurs mouvements
        $rotations = StockDepot::with(['article', 'depot'])
            ->select([
                'stock_depots.*',
                // Calcul des entrées sur la période
                DB::raw('(SELECT SUM(quantite) FROM stock_mouvements
                    WHERE article_id = stock_depots.article_id
                    AND depot_id = stock_depots.depot_id
                    AND type_mouvement = "ENTREE"
                    AND date_mouvement BETWEEN ? AND ?) as total_entrees'),
                // Calcul des sorties sur la période
                DB::raw('(SELECT SUM(quantite) FROM stock_mouvements
                    WHERE article_id = stock_depots.article_id
                    AND depot_id = stock_depots.depot_id
                    AND type_mouvement = "SORTIE"
                    AND date_mouvement BETWEEN ? AND ?) as total_sorties'),
                // Date dernier mouvement
                DB::raw('(SELECT date_mouvement FROM stock_mouvements
                    WHERE article_id = stock_depots.article_id
                    AND depot_id = stock_depots.depot_id
                    ORDER BY date_mouvement DESC LIMIT 1) as dernier_mouvement')
            ])
            ->setBindings([$dateDebut, $dateFin, $dateDebut, $dateFin])
            ->get()
            ->map(function ($stock) use ($dateDebut, $dateFin) {
                $nbJours = $dateFin->diffInDays($dateDebut) ?: 1;

                // Calculer le stock moyen
                $stockMoyen = ($stock->quantite + ($stock->quantite - ($stock->total_sorties ?? 0) + ($stock->total_entrees ?? 0))) / 2;

                // Calculer le taux de rotation
                $tauxRotation = $stockMoyen > 0 ? ($stock->total_sorties ?? 0) / $stockMoyen : 0;

                // Calculer la durée moyenne de stockage
                $dureeStockage = $tauxRotation > 0 ? $nbJours / $tauxRotation : 0;

                // Calculer la couverture de stock en jours
                $sortieMoyenneJour = ($stock->total_sorties ?? 0) / $nbJours;
                $couvertureStock = $sortieMoyenneJour > 0 ? $stock->quantite / $sortieMoyenneJour : 0;

                return [
                    'article' => $stock->article,
                    'depot' => $stock->depot,
                    'stock_actuel' => $stock->quantite,
                    'stock_moyen' => $stockMoyen,
                    'total_sorties' => $stock->total_sorties ?? 0,
                    'total_entrees' => $stock->total_entrees ?? 0,
                    'taux_rotation' => $tauxRotation,
                    'duree_stockage' => $dureeStockage,
                    'couverture_stock' => $couvertureStock,
                    'dernier_mouvement' => $stock->dernier_mouvement,
                    'statut_rotation' => $this->getStatutRotation($tauxRotation),
                    'alerte' => $this->getAlerteStock($stock)
                ];
            });

        // Statistiques globales
        $stats = [
            'articles_rotation_forte' => $rotations->where('taux_rotation', '>=', 3)->count(),
            'articles_rotation_faible' => $rotations->where('taux_rotation', '<', 1)->count(),
            'articles_dormants' => $rotations->where('total_sorties', 0)->count(),
            'valeur_stock_dormant' => $rotations->where('total_sorties', 0)
                ->sum(function($item) {
                    return $item['stock_actuel'] * $item['article']->dernier_prix_achat;
                }),
            'taux_rotation_moyen' => $rotations->avg('taux_rotation'),
            'couverture_moyenne' => $rotations->avg('couverture_stock')
        ];

        return view('pages.rapports.stocks.rotation-stock', compact(
            'rotations',
            'stats',
            'dateDebut',
            'dateFin',
            'depots'
        ));
    }

    private function getStatutRotation($tauxRotation)
    {
        if ($tauxRotation >= 3) return 'forte';
        if ($tauxRotation >= 1) return 'normale';
        return 'faible';
    }

    private function getAlerteStock($stock)
    {
        if ($stock->quantite <= $stock->article->stock_minimum) return 'critique';
        if ($stock->quantite <= $stock->article->stock_securite) return 'alerte';
        if ($stock->quantite >= $stock->article->stock_maximum) return 'surplus';
        return 'normal';
    }

    public function export(Request $request)
    {
        // Logique d'export similaire à l'index
    }
}
