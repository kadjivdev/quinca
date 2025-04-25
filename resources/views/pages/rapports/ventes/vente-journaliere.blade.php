<!-- resources/views/pages/rapports/ventes/vente-journaliere.blade.php -->
@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <!-- En-tête avec filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('rapports.vente-journaliere') }}" method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Date du rapport</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date"
                                       name="date"
                                       class="form-control"
                                       value="{{ request('date', now()->format('Y-m-d')) }}"
                                       required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sync-alt me-2"></i>Actualiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Message d'alerte -->
            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Tableau des ventes -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Rapport des ventes du {{ Carbon\Carbon::parse(request('date', now()))->format('d/m/Y') }}</h5>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-excel me-2"></i>Exporter
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>N°</th>
                                    <th>Date Écriture</th>
                                    <th>Date vente</th>
                                    <th>Référence</th>
                                    <th>Type vente</th>
                                    <th>Catégorie vente</th>
                                    <th>Client</th>
                                    <th class="text-end">Montant TTC</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventes as $vente)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $vente['date_ecriture'] }}</td>
                                    <td>{{ $vente['date_vente'] }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $vente['reference'] }}</span>
                                    </td>
                                    <td>
                                        @if($vente['type_vente'] === 'Comptant')
                                            <span class="badge bg-success">Comptant</span>
                                        @else
                                            <span class="badge bg-danger">Crédit</span>
                                        @endif
                                    </td>
                                    <td>{{ $vente['categorie_vente'] }}</td>
                                    <td>{{ $vente['client'] }}</td>
                                    <td class="text-end">
                                        {{ number_format($vente['montant_ttc'], 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal{{ $vente['id'] }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucune vente pour cette date
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="7" class="text-end">Total Global:</td>
                                    <td class="text-end">{{ number_format($totaux['total_global'], 0, ',', ' ') }} FCFA</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end">Total Comptant:</td>
                                    <td class="text-end">{{ number_format($totaux['total_comptant'], 0, ',', ' ') }} FCFA</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="7" class="text-end">Total Crédit:</td>
                                    <td class="text-end">{{ number_format($totaux['total_credit'], 0, ',', ' ') }} FCFA</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les détails -->
@foreach($ventes as $vente)
<div class="modal fade" id="detailsModal{{ $vente['id'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la facture {{ $vente['reference'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-end">Quantité</th>
                            <th class="text-end">Prix unitaire</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vente['lignes'] as $ligne)
                            <tr>
                                <td>{{ $ligne['produit'] }}</td>
                                <td class="text-end">{{ number_format($ligne['quantite'], 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($ligne['prix_unitaire'], 0, ',', ' ') }} FCFA</td>
                                <td class="text-end">{{ number_format($ligne['total'], 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Total TTC:</td>
                            <td class="text-end">{{ number_format($vente['montant_ttc'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Montant Réglé:</td>
                            <td class="text-end">{{ number_format($vente['montant_regle'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Reste à Payer:</td>
                            <td class="text-end">{{ number_format($vente['reste_a_payer'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
    .table > :not(caption) > * > * {
        padding: 1rem 1rem;
        background-color: transparent;
    }

    .table > thead {
        background-color: #f8f9fa;
    }

    .badge {
        padding: 0.5em 0.75em;
    }

    .card {
        border: none;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.querySelector('.btn-outline-primary');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const date = document.querySelector('input[name="date"]').value;
            window.location.href = `/rapports/ventes-journalier/export?date=${date}`;
        });
    }
});
</script>
@endpush
