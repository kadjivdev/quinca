<?php

namespace App\Http\Controllers\Rapport;

use App\Http\Controllers\Controller;
use App\Models\Catalogue\{Article, FamilleArticle};
use App\Models\Vente\{AcompteClient, FactureClient, SessionCaisse, ReglementClient};
use App\Models\Vente\Client;
use App\Models\Parametre\PointDeVente;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class RapportVenteController extends Controller
{
    public function ventesParArticle(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $articleId = $request->get('article_id');

        $query = DB::table('ligne_facture_clients as lf')
            ->join('facture_clients as f', 'f.id', '=', 'lf.facture_client_id')
            ->join('articles as a', 'a.id', '=', 'lf.article_id')
            ->whereBetween('f.date_facture', [$dateDebut, $dateFin])
            ->where('f.statut', 'validee');

        if ($articleId) {
            $query->where('a.id', $articleId);
        }

        $rapportVentes = $query->select([
            'a.designation',
            'a.code_article',
            DB::raw('SUM(lf.quantite) as quantite_vendue'),
            DB::raw('SUM(lf.montant_ht) as montant_ht'),
            DB::raw('SUM(lf.montant_tva) as montant_tva'),
            DB::raw('SUM(lf.montant_aib) as montant_aib'),
            DB::raw('SUM(lf.montant_ttc) as montant_ttc')
        ])
            ->groupBy('a.id', 'a.designation', 'a.code_article')
            ->orderBy('montant_ttc', 'desc')
            ->get();

        // Récupérer la liste des articles pour le filtre
        $articles = DB::table('articles')
            ->where('statut', 'actif')
            ->select('id', 'designation', 'code_article')
            ->orderBy('designation')
            ->get();

        return view('pages.rapports.ventes.vente-par-article', compact(
            'rapportVentes',
            'dateDebut',
            'dateFin',
            'articles',
            'articleId'
        ));
    }

    public function ventesParFamille(Request $request)
    {
        $dateDebut = $request->get('date_debut', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->get('date_fin', Carbon::now()->format('Y-m-d'));
        $familleId = $request->get('famille_id');

        $query = DB::table('ligne_facture_clients as lf')
            ->join('facture_clients as f', 'f.id', '=', 'lf.facture_client_id')
            ->join('articles as a', 'a.id', '=', 'lf.article_id')
            ->join('famille_articles as fa', 'fa.id', '=', 'a.famille_id')
            ->whereBetween('f.date_facture', [$dateDebut, $dateFin])
            ->where('f.statut', 'validee');

        if ($familleId) {
            $query->where('fa.id', $familleId);
        }

        $rapportVentes = $query->select([
            'fa.code_famille',
            'fa.libelle_famille',
            DB::raw('COUNT(DISTINCT a.id) as nombre_articles'),
            DB::raw('SUM(lf.quantite) as quantite_vendue'),
            DB::raw('SUM(lf.montant_ht) as montant_ht'),
            DB::raw('SUM(lf.montant_tva) as montant_tva'),
            DB::raw('SUM(lf.montant_aib) as montant_aib'),
            DB::raw('SUM(lf.montant_ttc) as montant_ttc')
        ])
            ->groupBy('fa.id', 'fa.code_famille', 'fa.libelle_famille')
            ->orderBy('montant_ttc', 'desc')
            ->get();

        $familles = FamilleArticle::where('statut', true)
            ->orderBy('libelle_famille')
            ->get(['id', 'code_famille', 'libelle_famille']);

        return view('pages.rapports.ventes.vente-par-famille', compact(
            'rapportVentes',
            'dateDebut',
            'dateFin',
            'familles',
            'familleId'
        ));
    }


    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Ventes du jour
        $ventesJour = DB::table('facture_clients')
            ->where('statut', 'validee')
            ->whereDate('date_facture', $today)
            ->select([
                DB::raw('COUNT(*) as nombre_factures'),
                DB::raw('SUM(montant_ttc) as ca_total'),
                DB::raw('SUM(montant_regle) as montant_encaisse')
            ])->first();

        // Ventes mensuelles par jour
        $ventesParJour = DB::table('facture_clients')
            ->where('statut', 'validee')
            ->whereBetween('date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                DB::raw('DATE(date_facture) as date'),
                DB::raw('COUNT(*) as nombre_factures'),
                DB::raw('SUM(montant_ttc) as ca_total')
            ])
            ->groupBy('date')
            ->get();

        // Top 5 clients
        $topClients = DB::table('facture_clients as f')
            ->join('clients as c', 'c.id', '=', 'f.client_id')
            ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                'c.raison_sociale',
                DB::raw('COUNT(f.id) as nombre_factures'),
                DB::raw('SUM(f.montant_ttc) as ca_total')
            ])
            ->groupBy('c.id', 'c.raison_sociale')
            ->orderBy('ca_total', 'desc')
            ->limit(5)
            ->get();

        // Top 5 articles
        $topArticles = DB::table('ligne_facture_clients as lf')
            ->join('facture_clients as f', 'f.id', '=', 'lf.facture_client_id')
            ->join('articles as a', 'a.id', '=', 'lf.article_id')
            ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                'a.designation',
                DB::raw('SUM(lf.quantite) as quantite_vendue'),
                DB::raw('SUM(lf.montant_ttc) as ca_total')
            ])
            ->groupBy('a.id', 'a.designation')
            ->orderBy('ca_total', 'desc')
            ->limit(5)
            ->get();

        // Ventes par famille
        $ventesParFamille = DB::table('ligne_facture_clients as lf')
            ->join('facture_clients as f', 'f.id', '=', 'lf.facture_client_id')
            ->join('articles as a', 'a.id', '=', 'lf.article_id')
            ->join('famille_articles as fa', 'fa.id', '=', 'a.famille_id')
            ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$startOfMonth, $endOfMonth])
            ->select([
                'fa.libelle_famille',
                DB::raw('SUM(lf.montant_ttc) as ca_total')
            ])
            ->groupBy('fa.id', 'fa.libelle_famille')
            ->get();

        return view('pages.rapports.ventes.dashboard-vente', compact(
            'ventesJour',
            'ventesParJour',
            'topClients',
            'topArticles',
            'ventesParFamille'
        ));
    }

    public function ventesParClient(Request $request)
    {
        $date = Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY');

        // Récupération des paramètres de filtrage
        $clientId = $request->input('client_id');
        $dateDebut = $request->input('date_debut') ? Carbon::parse($request->input('date_debut')) : Carbon::now()->startOfMonth();
        $dateFin = $request->input('date_fin') ? Carbon::parse($request->input('date_fin')) : Carbon::now();

        $clients = Client::orderBy('raison_sociale')->get();

        // Construction de la requête de base
        $query = FactureClient::with(['client', 'reglements'])
            ->where('statut', 'validee')
            ->when($clientId, function ($q) use ($clientId) {
                return $q->where('client_id', $clientId);
            })
            ->when($dateDebut && $dateFin, function ($q) use ($dateDebut, $dateFin) {
                return $q->whereBetween('date_facture', [
                    $dateDebut->startOfDay(),
                    $dateFin->endOfDay()
                ]);
            });

        // Statistiques globales
        $stats = [
            'total_ventes' => $query->sum('montant_ttc') ?? 0,
            'total_regle' => $query->sum('montant_regle') ?? 0,
            'total_restant' => ($query->sum('montant_ttc') - $query->sum('montant_regle')) ?? 0,
            'nombre_factures' => $query->count() ?? 0,
            'moyenne_facture' => $query->count() > 0 ? $query->avg('montant_ttc') : 0
        ];

        // Evolution mensuelle avec filtres
        $evolutionVentes = DB::table('facture_clients')
            ->select([
                DB::raw('DATE_FORMAT(date_facture, "%Y-%m") as mois_annee'),
                DB::raw('DATE_FORMAT(date_facture, "%M %Y") as mois_format'),
                DB::raw('SUM(montant_ttc) as total_ventes'),
                DB::raw('SUM(montant_regle) as total_regle'),
                DB::raw('COUNT(*) as nombre_factures')
            ])
            ->where('statut', 'validee')
            ->when($clientId, function ($q) use ($clientId) {
                return $q->where('client_id', $clientId);
            })
            ->when($dateDebut && $dateFin, function ($q) use ($dateDebut, $dateFin) {
                return $q->whereBetween('date_facture', [
                    $dateDebut->startOfDay(),
                    $dateFin->endOfDay()
                ]);
            })
            ->groupBy('mois_annee', 'mois_format')
            ->orderBy('mois_annee', 'desc')
            ->limit(12)
            ->get();

        // Détails des factures avec filtres
        $factures = $query->select([
            'id',
            'numero',
            'date_facture',
            'client_id',
            'montant_ttc',
            'montant_regle',
            DB::raw('(montant_regle / montant_ttc * 100) as pourcentage_paiement'),
            DB::raw('montant_ttc - montant_regle as restant_a_payer')
        ])
            ->orderBy('date_facture', 'desc')
            ->paginate(10)
            ->withQueryString(); // Garde les paramètres de filtrage dans la pagination

        // Top clients avec filtres
        $topClients = FactureClient::with('client')
            ->select(
                'client_id',
                DB::raw('SUM(montant_ttc) as total_achats'),
                DB::raw('SUM(montant_regle) as total_regle'),
                DB::raw('COUNT(*) as nombre_factures')
            )
            ->where('statut', 'validee')
            ->when($dateDebut && $dateFin, function ($q) use ($dateDebut, $dateFin) {
                return $q->whereBetween('date_facture', [
                    $dateDebut->startOfDay(),
                    $dateFin->endOfDay()
                ]);
            })
            ->groupBy('client_id')
            ->orderByDesc('total_achats')
            ->limit(5)
            ->get();

        return view('pages.rapports.ventes.vente-par-client', compact(
            'clients',
            'clientId',
            'stats',
            'evolutionVentes',
            'factures',
            'topClients',
            'date',
            'dateDebut',
            'dateFin'
        ));
    }

    // public function suivieVente(Request $request)
    // {
    //     $dateDebut = $request->get('date_debut') ? Carbon::parse($request->get('date_debut')) : Carbon::now()->startOfMonth();
    //     $dateFin = $request->get('date_fin') ? Carbon::parse($request->get('date_fin')) : Carbon::now();
    //     $clientId = $request->get('client_id');

    //     // Statistiques globales
    //     $baseQuery = FactureClient::query()
    //         ->where('statut', 'validee')
    //         ->whereBetween('date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);

    //     if ($clientId) {
    //         $baseQuery->where('client_id', $clientId);
    //     }

    //     $stats = [
    //         'total_factures' => $baseQuery->count(),
    //         'montant_ht' => $baseQuery->sum('montant_ht'),
    //         'montant_remise' => $baseQuery->sum('montant_remise'),
    //         'montant_tva' => $baseQuery->sum('montant_tva'),
    //         'montant_ttc' => $baseQuery->sum('montant_ttc'),
    //         'montant_regle' => $baseQuery->sum('montant_regle'),
    //         'taux_recouvrement' => $baseQuery->sum('montant_ttc') > 0
    //             ? ($baseQuery->sum('montant_regle') / $baseQuery->sum('montant_ttc')) * 100
    //             : 0
    //     ];

    //     // Ventes par mois
    //     $ventesParMois = DB::table('facture_clients')
    //         ->select([
    //             DB::raw('DATE_FORMAT(date_facture, "%Y-%m") as mois'),
    //             DB::raw('COUNT(*) as nombre_factures'),
    //             DB::raw('SUM(montant_ht) as total_ht'),
    //             DB::raw('SUM(montant_ttc) as total_ttc'),
    //             DB::raw('SUM(montant_regle) as total_regle')
    //         ])
    //         ->where('statut', 'validee')
    //         ->whereBetween('date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
    //         ->when($clientId, fn($q) => $q->where('client_id', $clientId))
    //         ->groupBy('mois')
    //         ->orderBy('mois')
    //         ->get();

    //     // Top clients
    //     $topClients = Client::select([
    //             'clients.id',
    //             'clients.raison_sociale',
    //             'clients.code_client',
    //             DB::raw('COUNT(f.id) as nombre_factures'),
    //             DB::raw('SUM(f.montant_ttc) as total_achats'),
    //             DB::raw('SUM(f.montant_regle) as total_regle')
    //         ])
    //         ->join('facture_clients as f', 'clients.id', '=', 'f.client_id')
    //         ->where('f.statut', 'validee')
    //         ->whereBetween('f.date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
    //         ->groupBy('clients.id', 'clients.raison_sociale', 'clients.code_client')
    //         ->orderByDesc('total_achats')
    //         ->limit(10)
    //         ->get();

    //     // Articles les plus vendus
    //     $topArticles = DB::table('ligne_facture_clients as l')
    //         ->join('facture_clients as f', 'l.facture_client_id', '=', 'f.id')
    //         ->join('articles as a', 'l.article_id', '=', 'a.id')
    //         ->select([
    //             'a.code_article',
    //             'a.designation',
    //             DB::raw('SUM(l.quantite_base) as quantite_vendue'),
    //             DB::raw('SUM(l.montant_ht) as montant_total')
    //         ])
    //         ->where('f.statut', 'validee')
    //         ->whereBetween('f.date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
    //         ->when($clientId, fn($q) => $q->where('f.client_id', $clientId))
    //         ->groupBy('a.id', 'a.code_article', 'a.designation')
    //         ->orderByDesc('montant_total')
    //         ->limit(10)
    //         ->get();

    //     // Liste des clients pour le filtre
    //     $clients = Client::where('statut', true)
    //         ->orderBy('raison_sociale')
    //         ->get(['id', 'raison_sociale', 'code_client']);

    //     // Dernières factures
    //     $dernieresFactures = $baseQuery->select([
    //             'facture_clients.*',
    //             DB::raw('(montant_ttc - montant_regle) as reste_a_payer')
    //         ])
    //         ->with('client:id,raison_sociale,code_client')
    //         ->orderByDesc('date_facture')
    //         ->limit(10)
    //         ->get();

    //     return view('pages.rapports.ventes.etat-vente', compact(
    //         'stats',
    //         'ventesParMois',
    //         'topClients',
    //         'topArticles',
    //         'dernieresFactures',
    //         'clients',
    //         'dateDebut',
    //         'dateFin',
    //         'clientId'
    //     ));
    // }

    public function suivieVente(Request $request)
    {
        $dateDebut = $request->get('date_debut') ? Carbon::parse($request->get('date_debut')) : Carbon::now()->startOfMonth();
        $dateFin = $request->get('date_fin') ? Carbon::parse($request->get('date_fin')) : Carbon::now();
        $clientId = $request->get('client_id', null);
        $articleId = $request->get('article_id', null);
        // Base query
        $baseQuery = FactureClient::query()
            ->where('statut', 'validee')
            ->whereBetween('date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
            ->when($clientId, fn($q) => $q->where('client_id', $clientId));

        // Stats
        $stats = [
            'total_factures' => $baseQuery->count(),
            'montant_ht' => $baseQuery->sum('montant_ht'),
            'montant_ttc' => $baseQuery->sum('montant_ttc'),
            'montant_regle' => $baseQuery->sum('montant_regle')
        ];

        // Rapport des ventes par article
        $rapportVentes = DB::table('ligne_facture_clients as l')
            ->join('facture_clients as f', 'l.facture_client_id', '=', 'f.id')
            ->join('articles as a', 'l.article_id', '=', 'a.id')
            ->select([
                'a.code_article',
                'a.designation',
                DB::raw('SUM(l.quantite) as quantite_vendue'),
                DB::raw('SUM(l.montant_ht) as montant_ht'),
                DB::raw('SUM(l.montant_tva) as montant_tva'),
                DB::raw('SUM(l.montant_aib) as montant_aib'),
                DB::raw('SUM(l.montant_ttc) as montant_ttc')
            ])
            ->where('f.statut', 'validee')
            ->whereBetween('f.date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
            ->when($clientId, fn($q) => $q->where('f.client_id', $clientId))
            ->groupBy('a.id', 'a.code_article', 'a.designation')
            ->get();

        // Ventes par mois
        $ventesParMois = DB::table('facture_clients')
            ->select([
                DB::raw('DATE_FORMAT(date_facture, "%Y-%m") as mois'),
                DB::raw('COUNT(*) as nombre_factures'),
                DB::raw('SUM(montant_ht) as total_ht'),
                DB::raw('SUM(montant_ttc) as total_ttc'),
                DB::raw('SUM(montant_regle) as total_regle')
            ])
            ->where('statut', 'validee')
            ->whereBetween('date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()])
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->groupBy(DB::raw('DATE_FORMAT(date_facture, "%Y-%m")'))
            ->orderBy('mois')
            ->get();

        // Liste des articles
        $articles = Article::where('statut', Article::STATUT_ACTIF)
            ->orderBy('designation')
            ->get(['id', 'code_article', 'designation']);

        // Liste des clients
        $clients = Client::where('statut', true)
            ->orderBy('raison_sociale')
            ->get(['id', 'raison_sociale', 'code_client']);

        return view('pages.rapports.ventes.etat-vente', compact(
            'rapportVentes',
            'ventesParMois',
            'articles',
            'clients',
            'stats',
            'dateDebut',
            'dateFin',
            'clientId',
            'articleId'
        ));
    }

    public function ventesJournalieres(Request $request)
    {
        try {
            $date = Carbon::parse($request->date ?? now());

            try {
                $ventes = FactureClient::with([
                    'client',
                    'createdBy',
                    'lignes.article', // Ajout des lignes et de l'article
                    'reglements' => function ($query) {
                        $query->where('statut', ReglementClient::STATUT_VALIDE);
                    }
                ])
                    ->whereDate('date_facture', $date)
                    ->where('statut', 'validee')
                    ->get();

                if ($ventes->isEmpty()) {
                    return view('pages.rapports.ventes.vente-journaliere')
                        ->with('warning', 'Aucune vente trouvée pour cette date')
                        ->with('ventes', collect([]))
                        ->with('totaux', [
                            'total_global' => 0,
                            'total_comptant' => 0,
                            'total_credit' => 0,
                        ])
                        ->with('date', $date);
                }

                // Mapping des données
                $ventesFormatted = $ventes->map(function ($facture) {
                    try {
                        $type_vente = 'Crédit';
                        if ($facture->montant_ttc <= $facture->montant_regle) {
                            $type_vente = 'Comptant';
                        }

                        // Préparation des lignes de détail
                        $lignes = $facture->lignes->map(function ($ligne) {
                            return [
                                'produit' => $ligne->article->designation ?? 'N/A',
                                'quantite' => $ligne->quantite ?? 0,
                                'prix_unitaire' => $ligne->prix_unitaire_ht ?? 0,
                                'total' => $ligne->montant_ttc ?? 0
                            ];
                        });

                        return [
                            'id' => $facture->id,
                            'numero' => $facture->numero ?? 'N/A',
                            'date_ecriture' => $facture->created_at->format('d/m/Y H:i'),
                            'date_vente' => $facture->date_facture->format('d/m/Y'),
                            'reference' => $facture->numero ?? 'N/A',
                            'type_vente' => $type_vente,
                            'categorie_vente' => $facture->client->categorie ?? 'N/A',
                            'client' => $facture->client->raison_sociale ?? 'Client inconnu',
                            'montant_ttc' => $facture->montant_ttc ?? 0,
                            'montant_regle' => $facture->montant_regle ?? 0,
                            'reste_a_payer' => $facture->montant_ttc - ($facture->montant_regle ?? 0),
                            'lignes' => $lignes, // Ajout des lignes de détail
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Erreur lors du mapping de la facture #' . $facture->id . ': ' . $e->getMessage());
                        return null;
                    }
                })->filter();

                // Calcul des totaux
                $totaux = [
                    'total_global' => $ventesFormatted->sum('montant_ttc'),
                    'total_comptant' => $ventesFormatted->where('type_vente', 'Comptant')->sum('montant_ttc'),
                    'total_credit' => $ventesFormatted->where('type_vente', 'Crédit')->sum('montant_ttc'),
                ];

                return view('pages.rapports.ventes.vente-journaliere', [
                    'ventes' => $ventesFormatted,
                    'totaux' => $totaux,
                    'date' => $date
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la récupération des ventes: ' . $e->getMessage());
                return back()
                    ->with('error', 'Une erreur est survenue lors de la récupération des données: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Erreur générale: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur inattendue est survenue: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function _enregistrementsNonValides(Request $request)
    {
        try {
            $date = Carbon::parse($request->date);

            try {
                $query = FactureClient::with([
                    'client',
                    'createdBy',
                    'lignes.article', // Ajout des lignes et de l'article
                    'reglements'
                ])
                    ->orderBy('date_facture', 'desc');

                if ($request->date) {
                    $date = Carbon::parse($request->date);
                    $ventes = $query->whereDate('date_facture', $date)
                        ->get();
                } else {
                    $ventes = $query->get();
                }

                if ($ventes->isEmpty()) {
                    return view('pages.rapports.ventes.enregistrementsNonValides')
                        ->with('warning', 'Aucune vente trouvée pour cette date')
                        ->with('ventes', collect([]))
                        ->with('totaux', [
                            'total_global' => 0,
                            'total_comptant' => 0,
                            'total_credit' => 0,
                        ])
                        ->with('date', $date);
                }

                // Mapping des données
                $ventesFormatted = $ventes->map(function ($facture) {
                    try {
                        $type_vente = 'Crédit';
                        if ($facture->montant_ttc <= $facture->montant_regle) {
                            $type_vente = 'Comptant';
                        }

                        // Préparation des lignes de détail
                        $lignes = $facture->lignes->map(function ($ligne) {
                            return [
                                'produit' => $ligne->article->designation ?? 'N/A',
                                'quantite' => $ligne->quantite ?? 0,
                                'prix_unitaire' => $ligne->prix_unitaire_ht ?? 0,
                                'total' => $ligne->montant_ttc ?? 0
                            ];
                        });

                        return [
                            'id' => $facture->id,
                            'numero' => $facture->numero ?? 'N/A',
                            'date_ecriture' => $facture->created_at->format('m/d/Y H:i'),
                            'date_vente' => $facture->date_facture->format('m/d/Y'),
                            'reference' => $facture->numero ?? 'N/A',
                            'type_vente' => $type_vente,
                            'categorie_vente' => $facture->client->categorie ?? 'N/A',
                            'client' => $facture->client->raison_sociale ?? 'Client inconnu',
                            'montant_ttc' => $facture->montant_ttc ?? 0,
                            'montant_regle' => $facture->montant_regle ?? 0,
                            'reste_a_payer' => $facture->montant_ttc - ($facture->montant_regle ?? 0),
                            'lignes' => $lignes, // Ajout des lignes de détail
                            'statut' => $facture->statut // Ajout des lignes de détail
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Erreur lors du mapping de la facture #' . $facture->id . ': ' . $e->getMessage());
                        return null;
                    }
                })->filter();

                // Calcul des totaux
                $totaux = [
                    'total_global' => $ventesFormatted->sum('montant_ttc'),
                    'total_comptant' => $ventesFormatted->where('type_vente', 'Comptant')->sum('montant_ttc'),
                    'total_credit' => $ventesFormatted->where('type_vente', 'Crédit')->sum('montant_ttc'),
                ];

                return view('pages.rapports.ventes.enregistrementsNonValides', [
                    'ventes' => $ventesFormatted,
                    'totaux' => $totaux,
                    'date' => $date
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la récupération des ventes: ' . $e->getMessage());
                return back()
                    ->with('error', 'Une erreur est survenue lors de la récupération des données: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Erreur générale: ' . $e->getMessage());
            return back()
                ->with('error', 'Une erreur inattendue est survenue: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Détermine le type de vente en fonction du paiement
     */
    private function determinerTypeVente(FactureClient $facture): string
    {
        try {
            if ($facture->est_solde) {
                return 'Comptant';
            } elseif ($facture->montant_regle > 0) {
                return 'Partiellement payé';
            } else {
                return 'Crédit';
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la détermination du type de vente pour la facture #' . $facture->id . ': ' . $e->getMessage());
            return 'Indéterminé';
        }
    }


    public function sessionVente(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');
            $dateDebut = $request->get('date_debut') ? Carbon::parse($request->get('date_debut')) : Carbon::now()->startOfMonth();
            $dateFin = $request->get('date_fin') ? Carbon::parse($request->get('date_fin')) : Carbon::now();

            // Get session
            $session = $sessionId ?
                SessionCaisse::findOrFail($sessionId) :
                SessionCaisse::where('statut', 'ouverte')->latest()->firstOrFail();

            // Load relationships with filters
            $session->load([
                'factures' => function ($q) use ($dateDebut, $dateFin) {
                    $q->where('statut', 'validee')
                        ->whereBetween('date_facture', [$dateDebut->startOfDay(), $dateFin->endOfDay()]);
                },
                'factures.reglements' => function ($q) {
                    $q->where('statut', ReglementClient::STATUT_VALIDE);
                },
                'factures.client',
            ]);

            // Get sessions list for dropdown
            $sessions = SessionCaisse::where(function ($q) use ($session) {
                $q->where('statut', 'fermee')
                    ->orWhere('id', $session->id);
            })
                ->orderBy('date_ouverture', 'desc')
                ->limit(10)
                ->get();

            return view('pages.rapports.ventes.session-vente', compact(
                'session',
                'sessions',
                'dateDebut',
                'dateFin'
            ));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }


    //     public function rapportCompteClient(Request $request)
    // {
    //     // Construction de la requête principale
    //     $query = Client::query()
    //         ->withSum(['facturesClient as total_factures' => function($q) {
    //             $q->whereNotNull('facture_clients.date_validation')
    //               ->whereNull('facture_clients.deleted_at');
    //         }], 'montant_ttc')
    //         ->withSum(['facturesClient as total_reglements' => function($q) {
    //             $q->whereNotNull('facture_clients.date_validation')
    //               ->whereNull('facture_clients.deleted_at')
    //               ->whereHas('reglements', function($q) {
    //                   $q->whereNotNull('reglement_clients.validated_at')
    //                     ->whereNull('reglement_clients.deleted_at');
    //               })
    //               ->join('reglement_clients', 'facture_clients.id', '=', 'reglement_clients.facture_client_id')
    //               ->select(\DB::raw('COALESCE(SUM(reglement_clients.montant), 0) as total_reglements'));
    //         }], 'montant_ttc')
    //         ->with(['soldeInitial' => function($q) {
    //             $q->latest('date_solde');
    //         }]);

    //     // Filtre par client si spécifié
    //     if ($request->client_id) {
    //         $query->where('id', $request->client_id);
    //     }

    //     // Filtre par point de vente si spécifié
    //     if ($request->point_de_vente_id) {
    //         $query->where('point_de_vente_id', $request->point_de_vente_id);
    //     }

    //     // Récupération et calcul des soldes
    //     $clients = $query->get()
    //         ->map(function ($client) {
    //             // Calcul du solde : Factures - Règlements
    //             // Si un solde initial existe, l'ajouter au calcul
    //             $soldeInitial = $client->soldeInitial;
    //             $montantInitial = 0;

    //             if ($soldeInitial) {
    //                 $montantInitial = $soldeInitial->type === 'CREDITEUR' ? -$soldeInitial->montant : $soldeInitial->montant;
    //             }

    //             $client->solde = $montantInitial + ($client->total_factures ?? 0) - ($client->total_reglements ?? 0);
    //             return $client;
    //         });

    //     // Statistiques par mode de règlement pour un client spécifique
    //     $statsParMode = [];
    //     if ($request->client_id) {
    //         $modes = [
    //             ReglementClient::TYPE_ESPECE,
    //             ReglementClient::TYPE_CHEQUE,
    //             ReglementClient::TYPE_VIREMENT,
    //             ReglementClient::TYPE_CARTE_BANCAIRE,
    //             ReglementClient::TYPE_MOMO,
    //             ReglementClient::TYPE_FLOOZ,
    //             ReglementClient::TYPE_CELTIS,
    //             ReglementClient::TYPE_EFFET,
    //             ReglementClient::TYPE_AVOIR
    //         ];

    //         foreach ($modes as $mode) {
    //             $statsParMode[$mode] = ReglementClient::whereHas('facture', function($q) use ($request) {
    //                     $q->where('client_id', $request->client_id);
    //                 })
    //                 ->whereNotNull('validated_at')
    //                 ->where('type_reglement', $mode)
    //                 ->sum('montant');
    //         }
    //     }

    //     // Détail des mouvements
    //     $mouvements = collect();
    //     if ($request->client_id) {
    //         $client = Client::with('soldeInitial')->find($request->client_id);
    //         $soldeInitial = $client->soldeInitial;

    //         // Ajout du solde initial aux mouvements uniquement s'il existe
    //         if ($soldeInitial) {
    //             $mouvements->push([
    //                 'id' => $soldeInitial->id,
    //                 'date' => $soldeInitial->date_solde,
    //                 'type' => 'SOLDE_INITIAL',
    //                 'reference' => 'SI-' . $soldeInitial->id,
    //                 'debit' => $soldeInitial->type === 'DEBITEUR' ? $soldeInitial->montant : 0,
    //                 'credit' => $soldeInitial->type === 'CREDITEUR' ? $soldeInitial->montant : 0,
    //                 'commentaire' => $soldeInitial->commentaire
    //             ]);
    //         }

    //         // Récupération des factures
    //         $factures = FactureClient::where('client_id', $request->client_id)
    //             ->whereNotNull('date_validation')
    //             ->get()
    //             ->map(function ($facture) {
    //                 return [
    //                     'id' => $facture->id,
    //                     'date' => $facture->date_facture,
    //                     'type' => 'FACTURE',
    //                     'reference' => $facture->numero,
    //                     'debit' => $facture->montant_ttc,
    //                     'credit' => 0,
    //                     'statut_paiement' => $facture->est_solde ? 'SOLDEE' : 'NON_SOLDEE'
    //                 ];
    //             });

    //         // Récupération des règlements
    //         $reglements = ReglementClient::whereHas('facture', function ($q) use ($request) {
    //                 $q->where('client_id', $request->client_id);
    //             })
    //             ->whereNotNull('validated_at')
    //             ->get()
    //             ->map(function ($reglement) {
    //                 return [
    //                     'id' => $reglement->id,
    //                     'date' => $reglement->date_reglement,
    //                     'type' => 'REGLEMENT',
    //                     'reference' => $reglement->numero,
    //                     'mode' => $reglement->type_reglement,
    //                     'reference_paiement' => $reglement->reference_preuve,
    //                     'debit' => 0,
    //                     'credit' => $reglement->montant
    //                 ];
    //             });

    //         // Préparation des mouvements en assurant que le solde initial soit en premier
    //         if ($soldeInitial) {
    //             // On définit une date minimale pour le solde initial
    //             $soldeInitialDate = $soldeInitial->date_solde;
    //         } else {
    //             // S'il n'y a pas de solde initial, on ne change rien à la fusion
    //             $soldeInitialDate = null;
    //         }

    //         // Fusion des factures et règlements
    //         $operationsTemp = $factures->concat($reglements);

    //         // Si on a un solde initial, on s'assure qu'il apparaît uniquement pour les opérations après sa date
    //         if ($soldeInitialDate) {
    //             $operationsTemp = $operationsTemp->filter(function ($operation) use ($soldeInitialDate) {
    //                 return Carbon::parse($operation['date'])->greaterThanOrEqual(Carbon::parse($soldeInitialDate));
    //             });
    //         }

    //         // Tri chronologique de toutes les opérations
    //         $mouvements = $mouvements->concat($operationsTemp)->sortBy([
    //             ['date', 'asc'],
    //             ['type', 'desc'] // Pour que le solde initial apparaisse avant les autres opérations de la même date
    //         ]);
    //     }

    //     // Retour de la vue avec toutes les données
    //     return view('pages.rapports.ventes.compte-client', [
    //         'clients' => $clients,
    //         'mouvements' => $mouvements,
    //         'solde_initial' => $request->client_id ? $soldeInitial : null,
    //         'statistiques' => [
    //             'total_clients' => $clients->count(),
    //             'total_factures' => $clients->sum('total_factures'),
    //             'total_reglements' => $clients->sum('total_reglements'),
    //             'solde_global' => $clients->sum('solde'),
    //             'clients_debiteurs' => $clients->where('solde', '>', 0)->count(),
    //             'clients_crediteurs' => $clients->where('solde', '<', 0)->count(),
    //             'montant_debiteur' => $clients->where('solde', '>', 0)->sum('solde'),
    //             'montant_crediteur' => abs($clients->where('solde', '<', 0)->sum('solde')),
    //             'par_mode' => $statsParMode
    //         ],
    //         'filtres' => [
    //             'clients' => Client::select('id', 'raison_sociale as nom')
    //                 ->orderBy('raison_sociale')
    //                 ->get(),
    //             'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')
    //                 ->orderBy('nom_pv')
    //                 ->get()
    //         ],
    //         'params' => [
    //             'client_id' => $request->client_id,
    //             'point_de_vente_id' => $request->point_de_vente_id
    //         ]
    //     ]);
    // }


    public function rapportCompteClient(Request $request)
    {
        // Construction de la requête principale
        $query = Client::query()
            ->withSum(['facturesClient as total_factures' => function ($q) {
                $q->whereNotNull('facture_clients.date_validation')
                    ->whereNull('facture_clients.deleted_at');
            }], 'montant_ttc')
            ->withSum(['facturesClient as total_reglements' => function ($q) {
                $q->whereNotNull('facture_clients.date_validation')
                    ->whereNull('facture_clients.deleted_at')
                    ->whereHas('reglements', function ($q) {
                        $q->whereNotNull('reglement_clients.validated_at')
                            ->whereNull('reglement_clients.deleted_at');
                    })
                    ->join('reglement_clients', 'facture_clients.id', '=', 'reglement_clients.facture_client_id')
                    ->select(\DB::raw('COALESCE(SUM(reglement_clients.montant), 0) as total_reglements'));
            }], 'montant_ttc')
            ->withSum(['acomptes as total_acomptes' => function ($q) {
                $q->where('statut', AcompteClient::STATUT_VALIDE)
                    ->whereNull('deleted_at');
            }], 'montant')
            ->with(['soldeInitial' => function ($q) {
                $q->latest('date_solde');
            }]);

        // Filtre par client si spécifié
        if ($request->client_id) {
            $query->where('id', $request->client_id);
        }

        // Filtre par point de vente si spécifié
        if ($request->point_de_vente_id) {
            $query->where('point_de_vente_id', $request->point_de_vente_id);
        }

        // Récupération et calcul des soldes
        $clients = $query->get()
            ->map(function ($client) {
                // Calcul du solde : Factures - (Règlements + Acomptes)
                $soldeInitial = $client->soldeInitial;
                $montantInitial = 0;

                if ($soldeInitial) {
                    $montantInitial = $soldeInitial->type === 'CREDITEUR' ? -$soldeInitial->montant : $soldeInitial->montant;
                }

                $client->solde = $montantInitial + ($client->total_factures ?? 0)
                    - (($client->total_reglements ?? 0) + ($client->total_acomptes ?? 0));
                return $client;
            });

        // Statistiques par mode de règlement et acomptes pour un client spécifique
        $statsParMode = [];
        if ($request->client_id) {
            $modes = [
                ReglementClient::TYPE_ESPECE,
                ReglementClient::TYPE_CHEQUE,
                ReglementClient::TYPE_VIREMENT,
                ReglementClient::TYPE_CARTE_BANCAIRE,
                ReglementClient::TYPE_MOMO,
                ReglementClient::TYPE_FLOOZ,
                ReglementClient::TYPE_CELTIS,
                ReglementClient::TYPE_EFFET,
                ReglementClient::TYPE_AVOIR
            ];

            foreach ($modes as $mode) {
                $statsParMode[$mode] = ReglementClient::whereHas('facture', function ($q) use ($request) {
                    $q->where('client_id', $request->client_id);
                })
                    ->whereNotNull('validated_at')
                    ->where('type_reglement', $mode)
                    ->sum('montant');
            }

            // Ajout des statistiques pour les acomptes
            $statsParMode['acomptes'] = [
                'espece' => AcompteClient::where('client_id', $request->client_id)
                    ->where('statut', AcompteClient::STATUT_VALIDE)
                    ->where('type_paiement', AcompteClient::TYPE_ESPECE)
                    ->sum('montant'),
                'cheque' => AcompteClient::where('client_id', $request->client_id)
                    ->where('statut', AcompteClient::STATUT_VALIDE)
                    ->where('type_paiement', AcompteClient::TYPE_CHEQUE)
                    ->sum('montant'),
                'virement' => AcompteClient::where('client_id', $request->client_id)
                    ->where('statut', AcompteClient::STATUT_VALIDE)
                    ->where('type_paiement', AcompteClient::TYPE_VIREMENT)
                    ->sum('montant'),
            ];
        }

        // Détail des mouvements
        $mouvements = collect();
        if ($request->client_id) {
            $client = Client::with('soldeInitial')->find($request->client_id);
            $soldeInitial = $client->soldeInitial;

            // Ajout du solde initial aux mouvements uniquement s'il existe
            if ($soldeInitial) {
                $mouvements->push([
                    'id' => $soldeInitial->id,
                    'date' => $soldeInitial->date_solde,
                    'type' => 'SOLDE_INITIAL',
                    'reference' => 'SI-' . $soldeInitial->id,
                    'debit' => $soldeInitial->type === 'DEBITEUR' ? $soldeInitial->montant : 0,
                    'credit' => $soldeInitial->type === 'CREDITEUR' ? $soldeInitial->montant : 0,
                    'commentaire' => $soldeInitial->commentaire
                ]);
            }

            // Récupération des factures
            $factures = FactureClient::where('client_id', $request->client_id)
                ->whereNotNull('date_validation')
                ->get()
                ->map(function ($facture) {
                    return [
                        'id' => $facture->id,
                        'date' => $facture->date_facture,
                        'type' => 'FACTURE',
                        'reference' => $facture->numero,
                        'debit' => $facture->montant_ttc,
                        'credit' => 0,
                        'statut_paiement' => $facture->est_solde ? 'SOLDEE' : 'NON_SOLDEE'
                    ];
                });

            // Récupération des règlements
            $reglements = ReglementClient::whereHas('facture', function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            })
                ->whereNotNull('validated_at')
                ->get()
                ->map(function ($reglement) {
                    return [
                        'id' => $reglement->id,
                        'date' => $reglement->date_reglement,
                        'type' => 'REGLEMENT',
                        'reference' => $reglement->numero,
                        'mode' => $reglement->type_reglement,
                        'reference_paiement' => $reglement->reference_preuve,
                        'debit' => 0,
                        'credit' => $reglement->montant
                    ];
                });

            // Récupération des acomptes
            $acomptes = AcompteClient::where('client_id', $request->client_id)
                ->where('statut', AcompteClient::STATUT_VALIDE)
                ->get()
                ->map(function ($acompte) {
                    return [
                        'id' => $acompte->id,
                        'date' => $acompte->date,
                        'type' => 'ACOMPTE',
                        'reference' => $acompte->reference,
                        'mode' => $acompte->type_paiement,
                        'debit' => 0,
                        'credit' => $acompte->montant,
                        'observation' => $acompte->observation
                    ];
                });

            // Préparation des mouvements en assurant que le solde initial soit en premier
            if ($soldeInitial) {
                $soldeInitialDate = $soldeInitial->date_solde;
            } else {
                $soldeInitialDate = null;
            }

            // Fusion des factures, règlements et acomptes
            $operationsTemp = $factures->concat($reglements)->concat($acomptes);

            // Filtrage par date si solde initial existe
            if ($soldeInitialDate) {
                $operationsTemp = $operationsTemp->filter(function ($operation) use ($soldeInitialDate) {
                    return Carbon::parse($operation['date'])->greaterThanOrEqual(Carbon::parse($soldeInitialDate));
                });
            }

            // Tri chronologique de toutes les opérations
            $mouvements = $mouvements->concat($operationsTemp)->sortBy([
                ['date', 'asc'],
                ['type', 'desc']
            ]);
        }

        // Retour de la vue avec toutes les données
        return view('pages.rapports.ventes.compte-client', [
            'clients' => $clients,
            'mouvements' => $mouvements,
            'solde_initial' => $request->client_id ? $soldeInitial : null,
            'statistiques' => [
                'total_clients' => $clients->count(),
                'total_factures' => $clients->sum('total_factures'),
                'total_reglements' => $clients->sum('total_reglements'),
                'total_acomptes' => $clients->sum('total_acomptes'),
                'solde_global' => $clients->sum('solde'),
                'clients_debiteurs' => $clients->where('solde', '>', 0)->count(),
                'clients_crediteurs' => $clients->where('solde', '<', 0)->count(),
                'montant_debiteur' => $clients->where('solde', '>', 0)->sum('solde'),
                'montant_crediteur' => abs($clients->where('solde', '<', 0)->sum('solde')),
                'par_mode' => $statsParMode
            ],
            'filtres' => [
                'clients' => Client::select('id', 'raison_sociale as nom')
                    ->orderBy('raison_sociale')
                    ->get(),
                'points_vente' => PointDeVente::select('id', 'nom_pv as libelle')
                    ->orderBy('nom_pv')
                    ->get()
            ],
            'params' => [
                'client_id' => $request->client_id,
                'point_de_vente_id' => $request->point_de_vente_id
            ]
        ]);
    }
}
