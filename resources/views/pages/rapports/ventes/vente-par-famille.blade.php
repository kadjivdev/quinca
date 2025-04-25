@extends('layouts.rapport.facture')

@section('title', 'Rapport des ventes par client')

@section('styles')
<style>
    .stats-card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-3px);
    }
    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold">
            <i class="fas fa-layer-group me-2"></i>Rapport des ventes par famille
        </h2>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row mb-3">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>Période
                                </label>
                                <div class="input-group">
                                    <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
                                    <span class="input-group-text bg-light">au</span>
                                    <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="fas fa-layer-group me-1"></i>Famille
                                </label>
                                <select name="famille_id" class="form-select select2">
                                    <option value="">Toutes les familles</option>
                                    @foreach($familles as $famille)
                                        <option value="{{ $famille->id }}" {{ $familleId == $famille->id ? 'selected' : '' }}>
                                            {{ $famille->code_famille }} - {{ $famille->libelle_famille }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques globales -->
 <!-- Cartes statistiques -->
<div class="row mb-4">
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="stats-card bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">CA Total</h6>
                    <h3 class="mb-0">{{ number_format($rapportVentes->sum('montant_ttc'), 0, ',', ' ') }} F</h3>
                </div>
                <div class="stats-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-money-bill-wave fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="stats-card bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Familles actives</h6>
                    <h3 class="mb-0">{{ $rapportVentes->count() }}</h3>
                </div>
                <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-layer-group fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="stats-card bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Articles vendus</h6>
                    <h3 class="mb-0">{{ $rapportVentes->sum('nombre_articles') }}</h3>
                </div>
                <div class="stats-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-box fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="stats-card bg-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-2">Quantités vendues</h6>
                    <h3 class="mb-0">{{ number_format($rapportVentes->sum('quantite_vendue'), 0, ',', ' ') }}</h3>
                </div>
                <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>


    <!-- Filtres -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">
                        <i class="far fa-calendar-alt me-1"></i>Période
                    </label>
                    <div class="input-group">
                        <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
                        <span class="input-group-text bg-light">au</span>
                        <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">
                        <i class="fas fa-layer-group me-1"></i>Famille
                    </label>
                    <select name="famille_id" class="form-select select2">
                        <option value="">Toutes les familles</option>
                        @foreach($familles as $famille)
                            <option value="{{ $famille->id }}" {{ $familleId == $famille->id ? 'selected' : '' }}>
                                {{ $famille->code_famille }} - {{ $famille->libelle_famille }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des résultats -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Détails par famille</h5>
                <button type="button" class="btn btn-success" id="exportExcel">
                    <i class="fas fa-file-excel me-1"></i>Exporter
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="rapportTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Famille</th>
                            <th class="text-end">Articles</th>
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
                            <td>{{ $ligne->code_famille }}</td>
                            <td>{{ $ligne->libelle_famille }}</td>
                            <td class="text-end">{{ number_format($ligne->nombre_articles, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->quantite_vendue, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->montant_ht, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->montant_tva, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->montant_aib, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne->montant_ttc, 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2">Total</td>
                            <td class="text-end">{{ number_format($rapportVentes->sum('nombre_articles'), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($rapportVentes->sum('quantite_vendue'), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($rapportVentes->sum('montant_ht'), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($rapportVentes->sum('montant_tva'), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($rapportVentes->sum('montant_aib'), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($rapportVentes->sum('montant_ttc'), 0, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
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

    const table = $('#rapportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Rapport des ventes par famille'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Rapport des ventes par famille'
            }
        ],
        language: {
            url: '/js/datatables-fr.json'
        }
    });

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        window.location.href = `{{ route('rapports.ventes-familles') }}?${new URLSearchParams(formData)}`;
    });
});
</script>
@endpush
