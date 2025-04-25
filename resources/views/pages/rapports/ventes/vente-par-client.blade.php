@extends('layouts.rapport.facture')
@section('content')
    <div class="container-fluid px-4 py-4">
        @include('pages.rapports.ventes.header');

        {{-- Section des filtres --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <form id="filterForm" method="GET" action="{{ route('rapports.ventes-clients') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Client</label>
                            <select class="form-select form-select-sm select2-clients" name="client_id">
                                <option value="">Tous les clients</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected($client->id == $clientId)>
                                        {{ $client->raison_sociale }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Période</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control" name="date_debut"
                                    value="{{ request('date_debut') }}">
                                <span class="input-group-text">au</span>
                                <input type="date" class="form-control" name="date_fin"
                                    value="{{ request('date_fin') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-none d-md-block small mb-1">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary btn-sm" id="resetFilters">
                                    <i class="fas fa-redo me-1"></i>Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm" id="applyFilters">
                                    <i class="fas fa-filter me-1"></i>Filtrer
                                </button>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <br>
        {{-- Cartes de statistiques --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-stats bg-primary bg-opacity-10">
                                    <i class="fas fa-shopping-cart text-primary"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title mb-0">Total Ventes</h6>
                                <span class="text-muted small">Chiffre d'affaires global</span>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($stats['total_ventes'], 0, ',', ' ') }} FCFA</h3>
                        <div class="mt-2">
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="fas fa-chart-line me-1"></i>+15%
                            </span>
                            <span class="text-muted small ms-1">vs mois dernier</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-stats bg-success bg-opacity-10">
                                    <i class="fas fa-money-bill-wave text-success"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title mb-0">Total Réglé</h6>
                                <span class="text-muted small">Paiements reçus</span>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($stats['total_regle'], 0, ',', ' ') }} FCFA</h3>
                        <div class="mt-2 d-flex align-items-center">
                            <div class="progress flex-grow-1" style="height: 6px;">
                                @php
    $reglePercentage = $stats['total_ventes'] > 0 ? ($stats['total_regle']/$stats['total_ventes'])*100 : 0;
@endphp
<div class="progress-bar bg-success" style="width: {{ $reglePercentage }}%"></div>
<span class="ms-2 small">{{ number_format($reglePercentage, 1) }}%</span>
                            </div>
                            <span class="ms-2 small">{{ number_format($stats['total_ventes'] > 0 ? ($stats['total_regle']/$stats['total_ventes'])*100 : 0, 1) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-stats bg-danger bg-opacity-10">
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title mb-0">Reste à Payer</h6>
                                <span class="text-muted small">Montant dû</span>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($stats['total_restant'], 0, ',', ' ') }} FCFA</h3>
                        <div class="mt-2">
                            <span class="badge bg-danger bg-opacity-10 text-danger">
                                {{ number_format($stats['total_ventes'] > 0 ? ($stats['total_restant']/$stats['total_ventes'])*100 : 0, 1) }}% des ventes
                             </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-stats bg-info bg-opacity-10">
                                    <i class="fas fa-file-invoice text-info"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title mb-0">Factures</h6>
                                <span class="text-muted small">Moyenne par facture</span>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ $stats['nombre_factures'] }}</h3>
                        <div class="mt-2">
                            <span class="text-muted small">
                                Moyenne: {{ number_format($stats['moyenne_facture'], 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        {{-- Liste des factures --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Détail des Factures</h5>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">N° Facture</th>
                            <th class="border-0">Client</th>
                            <th class="border-0">Date</th>
                            <th class="border-0 text-end">Montant TTC</th>
                            <th class="border-0 text-end">Réglé</th>
                            <th class="border-0">Progression</th>
                            <th class="border-0 text-end">Reste à payer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($factures->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aucune facture trouvée pour les critères sélectionnés</td>
                        </tr>
                     @else
                        @foreach($factures as $facture)
                            <tr>
                                <td><span class="numero-facture">{{ $facture->numero }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-client me-2">
                                            {{ substr($facture->client->raison_sociale ?? '', 0, 2) }}
                                        </div>
                                        {{ $facture->client->raison_sociale ?? 'Client inconnu' }}
                                    </div>
                                </td>
                                <td>{{ $facture->date_facture ? $facture->date_facture->format('d/m/Y') : '' }}</td>
                                <td class="text-end">{{ number_format($facture->montant_ttc ?? 0, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($facture->montant_regle ?? 0, 0, ',', ' ') }}</td>
                                <td>
                                    @php
                                        $montantTtc = $facture->montant_ttc ?? 0;
                                        $montantRegle = $facture->montant_regle ?? 0;
                                        $pourcentage = $montantTtc > 0 ? min(($montantRegle / $montantTtc) * 100, 100) : 0;
                                        $progressClass = $pourcentage >= 100 ? 'bg-success' : ($pourcentage >= 50 ? 'bg-info' : 'bg-warning');
                                    @endphp
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar {{ $progressClass }}" style="width: {{ $pourcentage }}%"></div>
                                        </div>
                                        <span class="ms-2 small">{{ number_format($pourcentage, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="text-end {{ ($facture->montant_ttc ?? 0) - ($facture->montant_regle ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(($facture->montant_ttc ?? 0) - ($facture->montant_regle ?? 0), 0, ',', ' ') }}
                                </td>
                            </tr>
                        @endforeach
                     @endif
                    </tbody>
                </table>
            </div>
            @if ($factures->hasPages())
                <div class="card-footer bg-transparent border-0">
                    {{ $factures->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .avatar-stats {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .avatar-client {
            width: 35px;
            height: 35px;
            background-color: var(--bs-primary-rgb);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .numero-facture {
            font-family: 'Monaco', monospace;
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .progress {
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .table>:not(caption)>*>* {
            padding: 1rem;
            border-bottom-color: rgba(0, 0, 0, 0.05);
        }

        .table>thead {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>



    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialisation de Select2
                $('.select2-clients').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Sélectionner un client',
                    allowClear: true,
                    width: '100%'
                });

                // Réinitialisation des filtres
                document.getElementById('resetFilters').addEventListener('click', function() {
                    document.querySelector('select[name="client_id"]').value = '';
                    document.querySelector('input[name="date_debut"]').value = '';
                    document.querySelector('input[name="date_fin"]').value = '';
                    $('.select2-clients').trigger('change');
                    document.getElementById('filterForm').submit();
                });

                // Validation des dates
                function validateDates() {
                    const dateDebut = document.querySelector('input[name="date_debut"]').value;
                    const dateFin = document.querySelector('input[name="date_fin"]').value;

                    if (dateDebut && dateFin && dateDebut > dateFin) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Dates invalides',
                            text: 'La date de début doit être antérieure à la date de fin',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        document.querySelector('input[name="date_fin"]').value = '';
                        return false;
                    }
                    return true;
                }

                // Validation du formulaire avant soumission
                document.getElementById('filterForm').addEventListener('submit', function(e) {
                    if (!validateDates()) {
                        e.preventDefault();
                    }
                });

                // Export des données
                document.getElementById('exportData').addEventListener('click', function() {
                    const clientId = document.querySelector('select[name="client_id"]').value;
                    const dateDebut = document.querySelector('input[name="date_debut"]').value;
                    const dateFin = document.querySelector('input[name="date_fin"]').value;

                    const url =
                        `/rapports/ventes-client/export?client_id=${clientId}&date_debut=${dateDebut}&date_fin=${dateFin}`;
                    window.location.href = url;
                });
            });
        </script>
    @endpush
@endsection
