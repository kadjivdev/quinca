<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use App\Models\Vente\{LivraisonClient, FactureClient, Client};
use App\Models\Parametre\Depot;
use App\Models\Stock\StockDepot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class LigneLivraisonClientController extends Controller
{
    /**
     * Affiche la liste des livraisons avec les données nécessaires
     */
    public function index(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des données avec pagination
        $livraisons = LivraisonClient::with([
            'facture.client',
            'depot',
            'lignes.article',
            'createdBy',
            'validatedBy'
        ])->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $livraisons->whereHas('facture', function ($query) use ($request) {
                $query->where('client_id', $request->client_id);
            });
        }

        if ($request->filled('depot_id')) {
            $livraisons->where('depot_id', $request->depot_id);
        }

        if ($request->filled('statut')) {
            $livraisons->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $livraisons->whereDate('date_livraison', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $livraisons->whereDate('date_livraison', '<=', $request->date_fin);
        }

        $livraisons = $livraisons->paginate(10);

        // Données pour les filtres
        $clients = Client::orderBy('raison_sociale')->get();
        $depots = Depot::actif()->orderBy('libelle_depot')->get();

        // Statistiques pour le header
        $totalArticlesLivres = DB::table('ligne_livraison_clients')
            ->join('livraison_clients', 'livraison_clients.id', '=', 'ligne_livraison_clients.livraison_client_id')
            ->where('livraison_clients.statut', 'valide')
            ->whereMonth('livraison_clients.date_livraison', now()->month)
            ->sum('ligne_livraison_clients.quantite');

        $stats = [
            'total_livraisons' => LivraisonClient::count(),
            'livraisons_validees' => LivraisonClient::where('statut', 'valide')->count(),
            'livraisons_en_attente' => LivraisonClient::where('statut', 'brouillon')->count(),
            'total_articles_livres' => $totalArticlesLivres
        ];

        // Factures disponibles pour livraison
        $factures = FactureClient::where('statut', 'validee')
            ->whereHas('lignes', function ($query) {
                $query->whereColumn('quantite_base', '>', 'quantite_livree');
            })
            ->with(['client', 'lignes' => function ($query) {
                $query->whereColumn('quantite_base', '>', 'quantite_livree')
                    ->with(['article', 'uniteVente']);
            }])
            ->latest()
            ->get();

        return view('pages.ventes.livraison.index', compact(
            'livraisons',
            'clients',
            'depots',
            'factures',
            'stats',
            'date'
        ));
    }

    /**
     * Rafraîchit la liste des livraisons (AJAX)
     */
    public function refreshList(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        $livraisons = LivraisonClient::with([
            'facture.client',
            'depot',
            'lignes.article'
        ])->latest();

        // Application des filtres
        if ($request->filled('client_id')) {
            $livraisons->whereHas('facture', function ($query) use ($request) {
                $query->where('client_id', $request->client_id);
            });
        }

        if ($request->filled('depot_id')) {
            $livraisons->where('depot_id', $request->depot_id);
        }

        if ($request->filled('statut')) {
            $livraisons->where('statut', $request->statut);
        }

        if ($request->filled('date_debut')) {
            $livraisons->whereDate('date_livraison', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $livraisons->whereDate('date_livraison', '<=', $request->date_fin);
        }

        $livraisons = $livraisons->paginate(10);

        // Mise à jour des statistiques
        $totalArticlesLivres = DB::table('ligne_livraison_clients')
            ->join('livraison_clients', 'livraison_clients.id', '=', 'ligne_livraison_clients.livraison_client_id')
            ->where('livraison_clients.statut', 'valide')
            ->whereMonth('livraison_clients.date_livraison', now()->month)
            ->sum('ligne_livraison_clients.quantite');

        return response()->json([
            'html' => view('pages.ventes.livraison.partials.table', compact('livraisons'))->render(),
            'stats' => [
                'total' => LivraisonClient::count(),
                'validees' => LivraisonClient::where('statut', 'valide')->count(),
                'en_attente' => LivraisonClient::where('statut', 'brouillon')->count(),
                'total_articles_livres' => $totalArticlesLivres
            ]
        ]);
    }

    /**
     * Vérifie le stock disponible pour un article dans un magasin (AJAX)
     */
    public function verifierStock(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        try {
            $request->validate([
                'article_id' => 'required|exists:articles,id',
                'depot_id' => 'required|exists:depots,id'
            ]);

            // Vérification du stock dans StockDepot
            $stock = StockDepot::where([
                'article_id' => $request->article_id,
                'depot_id' => $request->depot_id
            ])->first();

            return response()->json([
                'success' => true,
                'quantite' => number_format($stock ? $stock->quantite_reelle : 0, 3, '.', ''),
                'prix_moyen' => number_format($stock ? $stock->prix_moyen : 0, 2, '.', ''),
                'message' => 'Stock vérifié avec succès'
            ]);

        } catch (Exception $e) {
            \Log::error('Erreur lors de la vérification du stock:', [
                'article_id' => $request->article_id,
                'depot_id' => $request->depot_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du stock'
            ], 500);
        }
    }

    /**
     * Récupère les lignes de facture disponibles avec leurs stocks (AJAX)
     */
    public function getLignesFactureDisponibles(Request $request, FactureClient $factureClient)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Requête non autorisée'], 403);
        }

        if (!$request->filled('depot_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Le magasin est requis'
            ], 422);
        }

        $lignes = $factureClient->lignes()
            ->with(['article', 'uniteVente'])
            ->whereColumn('quantite_base', '>', 'quantite_livree')
            ->get()
            ->map(function ($ligne) use ($request) {
                // Stock disponible
                $stock = StockDepot::where([
                    'article_id' => $ligne->article_id,
                    'depot_id' => $request->depot_id
                ])->first();

                // Quantité déjà livrée
                $quantiteLivree = DB::table('ligne_livraison_clients')
                    ->join('livraison_clients', 'livraison_clients.id', '=', 'ligne_livraison_clients.livraison_client_id')
                    ->where('ligne_livraison_clients.ligne_facture_id', $ligne->id)
                    ->where('livraison_clients.statut', 'valide')
                    ->sum('ligne_livraison_clients.quantite_base');

                return [
                    'id' => $ligne->id,
                    'article' => [
                        'id' => $ligne->article->id,
                        'designation' => $ligne->article->designation,
                        'reference' => $ligne->article->reference
                    ],
                    'unite_vente' => [
                        'id' => $ligne->uniteVente->id,
                        'libelle' => $ligne->uniteVente->libelle_unite
                    ],
                    'quantite_facturee' => number_format($ligne->quantite, 3),
                    'quantite_base' => $ligne->quantite_base,
                    'quantite_livree' => number_format($quantiteLivree, 3),
                    'reste_a_livrer' => number_format($ligne->quantite_base - $quantiteLivree, 3),
                    'stock_disponible' => number_format($stock ? $stock->quantite_reelle : 0, 3),
                    'prix_moyen' => number_format($stock ? $stock->prix_moyen : 0, 2),
                    'prix_unitaire' => $ligne->prix_unitaire_ht
                ];
            });

        return response()->json([
            'success' => true,
            'lignes' => $lignes,
            'facture' => [
                'numero' => $factureClient->numero,
                'client' => [
                    'id' => $factureClient->client->id,
                    'raison_sociale' => $factureClient->client->raison_sociale
                ],
                'date_facture' => $factureClient->date_facture->format('d/m/Y')
            ]
        ]);
    }
}
