@extends('layouts.rapport.facture')
@section('title', 'État des ventes')

@section('styles')
<style>
    /* Styles existants conservés */
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold text-dark">
            <i class="fas fa-chart-line me-2"></i>État des ventes
        </h2>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Factures</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_factures'], 0, ',', ' ') }}</h3>
                    </div>
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-file-invoice fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Montant HT</h6>
                        <h3 class="mb-0">{{ number_format($stats['montant_ht'], 0, ',', ' ') }} F</h3>
                    </div>
                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Montant TTC</h6>
                        <h3 class="mb-0">{{ number_format($stats['montant_ttc'], 0, ',', ' ') }} F</h3>
                    </div>
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-money-check-alt fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Montant Réglé</h6>
                        <h3 class="mb-0">{{ number_format($stats['montant_regle'], 0, ',', ' ') }} F</h3>
                    </div>
                    <div class="stats-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-cash-register fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filter-card card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><i class="far fa-calendar-alt me-1"></i>Période</label>
                    <div class="input-group">
                        <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut->format('Y-m-d') }}">
                        <span class="input-group-text">au</span>
                        <input type="date" name="date_fin" class="form-control" value="{{ $dateFin->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-users me-1"></i>Client</label>
                    <select name="client_id" class="form-select select2">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                                {{ $client->code_client }} - {{ $client->raison_sociale }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-box me-1"></i>Article</label>
                    <select name="article_id" class="form-select select2">
                        <option value="">Tous les articles</option>
                        @foreach($articles as $article)
                            <option value="{{ $article->id }}" {{ $articleId == $article->id ? 'selected' : '' }}>
                                {{ $article->code_article }} - {{ $article->designation }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rapports -->
    <div class="row">
        <!-- Ventes par mois -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Évolution des ventes</h5>
                </div>
                <div class="card-body">
                    <table class="table" id="ventesParMoisTable">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th class="text-end">Nombre de factures</th>
                                <th class="text-end">Total HT</th>
                                <th class="text-end">Total TTC</th>
                                <th class="text-end">Total réglé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventesParMois as $vente)
                            <tr>
                                <td>{{ $vente->mois }}</td>
                                <td class="text-end">{{ number_format($vente->nombre_factures, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($vente->total_ht, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($vente->total_ttc, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($vente->total_regle, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ventes par article -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Détail des ventes par article</h5>
                    <button type="button" class="btn btn-sm btn-success" id="exportExcel">
                        <i class="fas fa-file-excel me-1"></i>Exporter
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="rapportTable">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Article</th>
                                    <th class="text-end">Quantité</th>
                                    <th class="text-end">Montant HT</th>
                                    <th class="text-end">TVA</th>
                                    <th class="text-end">AIB</th>
                                    <th class="text-end">Montant TTC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rapportVentes as $ligne)
                                <tr>
                                    <td>{{ $ligne->code_article }}</td>
                                    <td>{{ $ligne->designation }}</td>
                                    <td class="text-end">{{ number_format($ligne->quantite_vendue, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($ligne->montant_ht, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($ligne->montant_tva, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($ligne->montant_aib, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($ligne->montant_ttc, 0, ',', ' ') }}</td>
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

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    const rapportTable = $('#rapportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i>Excel',
                className: 'btn btn-success btn-sm',
                title: 'Rapport des ventes par article'
            }
        ],
        language: {
            url: '/js/datatables-fr.json'
        },
        pageLength: 25
    });

    const ventesParMoisTable = $('#ventesParMoisTable').DataTable({
        language: {
            url: '/js/datatables-fr.json'
        },
        pageLength: 12
    });

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        window.location.href = `{{ route('rapports.etat-ventes') }}?${new URLSearchParams(formData)}`;
    });
});
</script>
@endpush
