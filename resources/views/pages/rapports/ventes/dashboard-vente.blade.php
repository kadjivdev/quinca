@extends('layouts.rapport.facture')

@section('title', 'Tableau de bord des ventes')

@section('styles')
<style>
    .stat-card {
        @apply bg-white rounded-lg shadow-sm p-6 transition-transform duration-200;
    }
    .stat-card:hover {
        transform: translateY(-4px);
    }
    .stat-icon {
        @apply w-12 h-12 rounded-full flex items-center justify-center text-xl;
    }
    .chart-container {
        @apply bg-white rounded-lg shadow-sm p-4 mb-6;
    }
    .table-container {
        @apply bg-white rounded-lg shadow-sm overflow-hidden;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-6 py-4">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tableau de bord des ventes</h1>
        <div class="flex space-x-2">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print mr-2"></i>Imprimer
            </button>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-gray-500 text-sm">CA du jour</h3>
                    <p class="text-2xl font-bold">{{ number_format($ventesJour->ca_total, 0, ',', ' ') }} F</p>
                    <p class="text-sm text-green-600">
                        <i class="fas fa-arrow-up mr-1"></i>+12.5%
                    </p>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-gray-500 text-sm">Factures du jour</h3>
                    <p class="text-2xl font-bold">{{ $ventesJour->nombre_factures }}</p>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-gray-500 text-sm">Montant encaissé</h3>
                    <p class="text-2xl font-bold">{{ number_format($ventesJour->montant_encaisse, 0, ',', ' ') }} F</p>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-gray-500 text-sm">Reste à encaisser</h3>
                    <p class="text-2xl font-bold">{{ number_format($ventesJour->ca_total - $ventesJour->montant_encaisse, 0, ',', ' ') }} F</p>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="chart-container h-[400px]"> <!-- Hauteur fixe en Tailwind -->
        <h3 class="text-lg font-semibold mb-4">Évolution des ventes</h3>
        <div class="relative h-[calc(100%-2rem)]"> <!-- Hauteur relative moins le titre -->
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Top clients et articles -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="table-container">
            <div class="p-4 border-b">
                <h3 class="text-lg font-semibold">Top 5 clients</h3>
            </div>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th class="text-right">Factures</th>
                                <th class="text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topClients as $client)
                            <tr>
                                <td>{{ $client->raison_sociale }}</td>
                                <td class="text-right">{{ $client->nombre_factures }}</td>
                                <td class="text-right">{{ number_format($client->ca_total, 0, ',', ' ') }} F</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="p-4 border-b">
                <h3 class="text-lg font-semibold">Top 5 articles</h3>
            </div>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th class="text-right">Quantité</th>
                                <th class="text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topArticles as $article)
                            <tr>
                                <td>{{ $article->designation }}</td>
                                <td class="text-right">{{ number_format($article->quantite_vendue, 0, ',', ' ') }}</td>
                                <td class="text-right">{{ number_format($article->ca_total, 0, ',', ' ') }} F</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Chart des ventes journalières
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($ventesParJour->pluck('date')) !!},
            datasets: [{
                label: 'Chiffre d\'affaires',
                data: {!! json_encode($ventesParJour->pluck('ca_total')) !!},
                borderColor: '#4F46E5',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Chart des familles
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($ventesParFamille->pluck('libelle_famille')) !!},
            datasets: [{
                data: {!! json_encode($ventesParFamille->pluck('ca_total')) !!},
                backgroundColor: [
                    '#4F46E5',
                    '#10B981',
                    '#F59E0B',
                    '#EF4444',
                    '#6366F1'
                ]
            }]
        },

        options: {
    responsive: true,
    maintainAspectRatio: true,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return new Intl.NumberFormat('fr-FR').format(value) + ' F';
                }
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    },
    plugins: {
        legend: {
            display: false
        }
    }
}
    });
});
</script>
@endpush
