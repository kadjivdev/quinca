@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Filtres --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Client</label>
                    <select class="form-select select2" name="client_id">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                                {{ $client->code_client }} - {{ $client->raison_sociale }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Période</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_debut" value="{{ $dateDebut->format('Y-m-d') }}">
                        <span class="input-group-text">au</span>
                        <input type="date" class="form-control" name="date_fin" value="{{ $dateFin->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Créances</h6>
                    <h3 class="mb-2">{{ number_format($stats['total_creances'], 0, ',', ' ') }} FCFA</h3>
                    <span class="badge bg-danger">{{ $stats['total_factures'] }} factures</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Montant Total Facturé</h6>
                    <h3 class="mb-0">{{ number_format($stats['montant_factures'], 0, ',', ' ') }} FCFA</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Montant Total Réglé</h6>
                    <h3 class="mb-0">{{ number_format($stats['montant_regle'], 0, ',', ' ') }} FCFA</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Taux de Recouvrement</h6>
                    <h3 class="mb-0">
                        @if($stats['montant_factures'] > 0)
                            {{ number_format(($stats['montant_regle'] / $stats['montant_factures']) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphique et Top Clients --}}
    {{-- <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0">Répartition des Créances par Âge</h6>
                </div>
                <div class="card-body">
                    <canvas id="repartitionChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0">Top 10 Clients en Retard</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($topClientsRetard as $client)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $client->code_client }} - {{ $client->raison_sociale }}</h6>
                                        <small class="text-muted">{{ $client->nombre_factures }} facture(s)</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ number_format($client->total_du, 0, ',', ' ') }} FCFA</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- Liste des factures --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h6 class="card-title mb-0">Liste des Factures Impayées</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>N° Facture</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Échéance</th>
                        <th class="text-end">Montant TTC</th>
                        <th class="text-end">Réglé</th>
                        <th class="text-end">Reste à payer</th>
                        <th>Retard</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                        <tr>
                            <td>{{ $facture->numero }}</td>
                            <td>{{ $facture->client->code_client }} - {{ $facture->client->raison_sociale }}</td>
                            <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                            <td>{{ $facture->date_echeance->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($facture->montant_ttc, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($facture->montant_regle, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($facture->reste_a_payer, 0, ',', ' ') }}</td>
                            <td>
                                @if($facture->jours_retard > 0)
                                    <span class="badge bg-danger">{{ $facture->jours_retard }} jours</span>
                                @else
                                    <span class="badge bg-success">Non échue</span>
                                @endif
                            </td>
                            <td>
                                {{-- <a href="{{ route('factures.show', $facture->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a> --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">Aucune facture impayée trouvée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">
            {{ $factures->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation de select2
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    // Graphique de répartition des créances
    const ctx = document.getElementById('repartitionChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($repartitionCreances->pluck('age')),
            datasets: [{
                data: @json($repartitionCreances->pluck('montant')),
                backgroundColor: [
                    '#10B981', // Vert pour non échues
                    '#3B82F6', // Bleu pour 1-30 jours
                    '#F59E0B', // Orange pour 31-60 jours
                    '#EF4444', // Rouge pour 61-90 jours
                    '#6B7280'  // Gris pour plus de 90 jours
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        generateLabels: function(chart) {
                            const data = chart.data;
                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            return data.labels.map((label, i) => ({
                                text: `${label} - ${number_format(data.datasets[0].data[i], 0, ',', ' ')} FCFA (${Math.round(data.datasets[0].data[i] / total * 100)}%)`,
                                fillStyle: data.datasets[0].backgroundColor[i],
                                hidden: false,
                                index: i
                            }));
                        }
                    }
                }
            }
        }
    });
});

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ' ' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}
</script>
@endpush
@endsection
