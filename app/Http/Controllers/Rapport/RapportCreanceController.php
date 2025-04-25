<?php
namespace App\Http\Controllers\Rapport;

use App\Http\Controllers\Controller;
use App\Models\Vente\{FactureClient, Client};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RapportCreanceController extends Controller
{
    public function index(Request $request)
    {
        $dateDebut = Carbon::parse($request->get('date_debut', Carbon::now()->startOfMonth()));
        $dateFin = Carbon::parse($request->get('date_fin', Carbon::now()));
        $clientId = $request->get('client_id');

        // Base query for validated invoices with remaining balance
        $baseQuery = FactureClient::with(['client', 'reglements'])
        ->where('statut', 'validee')
            ->whereRaw('ROUND(montant_ttc - montant_regle, 3) > 0')
            ->whereBetween('date_facture', [$dateDebut, $dateFin])
            ->when($clientId, fn($q) => $q->where('client_id', $clientId));

        // Global statistics with precise decimal handling
        $stats = [
            'total_creances' => round($baseQuery->sum(DB::raw('montant_ttc - montant_regle')), 3),
            'total_factures' => $baseQuery->count(),
            'montant_factures' => round($baseQuery->sum('montant_ttc'), 3),
            'montant_regle' => round($baseQuery->sum('montant_regle'), 3)
        ];

        // Aging analysis of receivables
        $repartitionCreances = DB::table('facture_clients')
            ->select([
                DB::raw("
                    CASE
                        WHEN date_echeance >= CURRENT_DATE THEN 'Non échues'
                        WHEN DATEDIFF(CURRENT_DATE, date_echeance) <= 30 THEN '1-30 jours'
                        WHEN DATEDIFF(CURRENT_DATE, date_echeance) <= 60 THEN '31-60 jours'
                        WHEN DATEDIFF(CURRENT_DATE, date_echeance) <= 90 THEN '61-90 jours'
                        ELSE 'Plus de 90 jours'
                    END as age
                "),
                DB::raw('COUNT(*) as nombre'),
                DB::raw('ROUND(SUM(montant_ttc - montant_regle), 3) as montant')
            ])
            ->where('statut', FactureClient::STATUT_VALIDE)
            ->whereRaw('ROUND(montant_ttc - montant_regle, 3) > 0')
            ->whereBetween('date_facture', [$dateDebut, $dateFin])
            ->when($clientId, fn($q) => $q->where('client_id', $clientId))
            ->groupBy('age')
            ->orderByRaw("
                CASE age
                    WHEN 'Non échues' THEN 1
                    WHEN '1-30 jours' THEN 2
                    WHEN '31-60 jours' THEN 3
                    WHEN '61-90 jours' THEN 4
                    ELSE 5
                END
            ")
            ->get();

        // Top clients with unpaid invoices
        $topClientsRetard = Client::select([
                'clients.id',
                'clients.raison_sociale',
                'clients.code_client',
                DB::raw('ROUND(SUM(f.montant_ttc - f.montant_regle), 3) as total_du'),
                DB::raw('COUNT(f.id) as nombre_factures')
            ])
            ->join('facture_clients as f', 'clients.id', '=', 'f.client_id')
            ->where('f.statut', FactureClient::STATUT_VALIDE)
            ->whereRaw('ROUND(f.montant_ttc - f.montant_regle, 3) > 0')
            ->whereBetween('f.date_facture', [$dateDebut, $dateFin])
            ->when($clientId, fn($q) => $q->where('f.client_id', $clientId))
            ->groupBy('clients.id', 'clients.raison_sociale', 'clients.code_client')
            ->orderByDesc('total_du')
            ->limit(10)
            ->get();

        // Detailed invoice list
        $factures = $baseQuery->select([
                'facture_clients.*',
                DB::raw('DATEDIFF(CURRENT_DATE, date_echeance) as jours_retard'),
                DB::raw('ROUND(montant_ttc - montant_regle, 3) as reste_a_payer')
            ])
            ->orderByDesc('date_facture')
            ->paginate(15);

        // Active clients list for filter
        $clients = Client::actif()
            ->orderBy('raison_sociale')
            ->get(['id', 'raison_sociale', 'code_client']);

        return view('pages.rapports.creances.index', compact(
            'stats',
            'repartitionCreances',
            'topClientsRetard',
            'factures',
            'clients',
            'dateDebut',
            'dateFin',
            'clientId'
        ));
    }
}
