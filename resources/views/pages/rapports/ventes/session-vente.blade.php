@extends('layouts.rapport.facture')
@section('title', 'Rapport de Session')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <header class="bg-white shadow-sm rounded-3 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-cash-register text-primary me-2"></i>Rapport de Session
            </h1>

        </div>
    </header>

    <!-- Session Selector Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="sessionForm" class="row g-3 align-items-end" action="{{ route('vente.sessions.rapport') }}" method="GET">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>Session
                    </label>
                    <select name="session_id" id="session_id" class="form-select select2">
                        <option value="">Session courante</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>
                                Session #{{ $s->id }} - {{ $s->date_ouverture->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">
                        <i class="fas fa-calendar me-2"></i>Date début
                    </label>
                    <input type="date" class="form-control" name="date_debut"
                           value="{{ $dateDebut->format('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">
                        <i class="fas fa-calendar me-2"></i>Date fin
                    </label>
                    <input type="date" class="form-control" name="date_fin"
                           value="{{ $dateFin->format('Y-m-d') }}">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Solde Initial -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Solde Initial</h6>
                            <h3 class="mb-0">{{ number_format($session->montant_ouverture, 0, ',', ' ') }} F</h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-money-bill text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Encaissements -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Total Encaissements</h6>
                            <h3 class="mb-0">{{ number_format($session->total_encaissements, 0, ',', ' ') }} F</h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-cash-register text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Solde Théorique -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Solde Théorique</h6>
                            <h3 class="mb-0">{{ number_format($session->solde_theorique, 0, ',', ' ') }} F</h3>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-calculator text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Écart (si session fermée) -->
        @if($session->statut === 'fermee')
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Écart</h6>
                            <h3 class="mb-0 {{ $session->ecart >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $session->ecart >= 0 ? '+' : '' }}{{ number_format($session->ecart, 0, ',', ' ') }} F
                            </h3>
                        </div>
                        <div class="rounded-circle bg-{{ $session->ecart >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3">
                            <i class="fas fa-balance-scale text-{{ $session->ecart >= 0 ? 'success' : 'danger' }} fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Détail des encaissements -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list text-primary me-2"></i>Détail des Encaissements par Type
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="encaissementsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Type de Règlement</th>
                                    <th class="text-end">Nombre</th>
                                    <th class="text-end">Montant Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $reglements = [];
                                    $nombreReglements = [];
                                    foreach($session->factures as $facture) {
                                        foreach($facture->reglements as $reglement) {
                                            if(!isset($reglements[$reglement->type_reglement])) {
                                                $reglements[$reglement->type_reglement] = 0;
                                                $nombreReglements[$reglement->type_reglement] = 0;
                                            }
                                            $reglements[$reglement->type_reglement] += $reglement->montant;
                                            $nombreReglements[$reglement->type_reglement]++;
                                        }
                                    }
                                @endphp
                                @foreach($reglements as $type => $montant)
                                <tr>
                                    <td>
                                        <i class="fas fa-{{ $type === 'espece' ? 'money-bill' :
                                            ($type === 'cheque' ? 'money-check' :
                                            ($type === 'carte_bancaire' ? 'credit-card' : 'exchange-alt')) }}
                                            text-muted me-2"></i>
                                        {{ ucfirst($type) }}
                                    </td>
                                    <td class="text-end">{{ $nombreReglements[$type] }}</td>
                                    <td class="text-end fw-bold">{{ number_format($montant, 0, ',', ' ') }} F</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des factures -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice text-primary me-2"></i>
                            Factures <span class="badge bg-primary ms-2">{{ $session->factures->count() }}</span>
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="facturesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Client</th>
                                    <th class="text-end">Montant TTC</th>
                                    <th class="text-end">Réglé</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($session->factures as $facture)
                                <tr>
                                    <td>
                                        <i class="fas fa-file-alt text-muted me-2"></i>
                                        {{ $facture->numero }}
                                    </td>
                                    <td>
                                        <i class="fas fa-user text-muted me-2"></i>
                                        {{ $facture->client->raison_sociale }}
                                    </td>
                                    <td class="text-end">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} F</td>
                                    <td class="text-end">{{ number_format($facture->montant_regle, 0, ',', ' ') }} F</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $facture->est_solde ? 'success' : 'warning' }} rounded-pill">
                                            <i class="fas fa-{{ $facture->est_solde ? 'check-circle' : 'clock' }} me-1"></i>
                                            {{ $facture->est_solde ? 'Soldée' : 'En cours' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

.select2-container--bootstrap4 .select2-selection--single {
    height: 38px;
    line-height: 1.5;
    padding: 0.375rem 0.75rem;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.8em;
}

.table > :not(caption) > * > * {
    padding: 1rem 1rem;
}

.rounded-circle {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Configuration Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Sélectionnez une session',
        language: 'fr'
    });

    // Configuration DataTables commune
    const dataTableConfig = {
        language: {
            url: '/js/datatables-fr.json'
        },
        pageLength: 10,
        order: [[0, 'desc']],
        responsive: true
    };

    // Initialisation des DataTables
    $('#facturesTable').DataTable({
        ...dataTableConfig,
        dom: 'Bfrtip',
        buttons: [{
            extend: 'excel',
            text: '<i class="fas fa-file-excel me-1"></i>Exporter Excel',
            className: 'btn btn-success btn-sm',
            title: `Rapport Session #{{ $session->id }}`,
            exportOptions: {
                columns: [0, 1, 2, 3, 4]
            }
        }]
    });

    $('#encaissementsTable').DataTable({
        ...dataTableConfig,
        searching: false,
        info: false
    });

    // Gestion du formulaire de filtrage
    $('#sessionForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const searchParams = new URLSearchParams(formData);

        window.location.href = `{{ route('vente.sessions.rapport') }}?${searchParams.toString()}`;
    });

    // Fonction d'impression
    $('#printBtn').on('click', function() {
        window.print();
    });

    // Export Excel direct
    $('#exportBtn').on('click', function() {
        $('#facturesTable').DataTable().button(0).trigger();
    });
});
</script>
@endpush


