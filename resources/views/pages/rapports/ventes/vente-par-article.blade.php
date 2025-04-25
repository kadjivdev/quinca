@extends('layouts.rapport.facture')
@section('title', 'Rapport des ventes par article')

@section('styles')
<style>
    .filter-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        background: linear-gradient(to right, #ffffff, #f8f9fa);
    }

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

    .table-container {
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        padding: 1.5rem;
    }

    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        color: #495057;
        font-weight: 600;
    }

    .btn-filter {
        background: linear-gradient(45deg, #4CAF50, #45a049);
        border: none;
        color: white;
        padding: 0.5rem 1.5rem;
    }

    .btn-export {
        background: linear-gradient(45deg, #2196F3, #1976D2);
        border: none;
        color: white;
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
        <h2 class="font-weight-bold text-dark">
            <i class="fas fa-chart-line me-2"></i>Rapport des ventes par article
        </h2>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total des ventes</h6>
                        <h3 class="mb-0">{{ number_format($rapportVentes->sum('montant_ttc'), 0, ',', ' ') }} F</h3>
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
                        <h6 class="text-muted mb-2">Articles vendus</h6>
                        <h3 class="mb-0">{{ number_format($rapportVentes->sum('quantite_vendue'), 0, ',', ' ') }}</h3>
                    </div>
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-box fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total TVA</h6>
                        <h3 class="mb-0">{{ number_format($rapportVentes->sum('montant_tva'), 0, ',', ' ') }} F</h3>
                    </div>
                    <div class="stats-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-percent fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total AIB</h6>
                        <h3 class="mb-0">{{ number_format($rapportVentes->sum('montant_aib'), 0, ',', ' ') }} F</h3>
                    </div>
                    <div class="stats-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-calculator fa-lg"></i>
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
                        <i class="fas fa-box me-1"></i>Article
                    </label>
                    <select name="article_id" class="form-select select2">
                        <option value="">Tous les articles</option>
                        @foreach($articles as $article)
                            <option value="{{ $article->id }}" {{ $articleId == $article->id ? 'selected' : '' }}>
                                {{ $article->code_article }} - {{ $article->designation }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des résultats -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Détails des ventes</h5>
            <button type="button" class="btn btn-export" id="exportExcel">
                <i class="fas fa-file-excel me-1"></i>Exporter
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" id="rapportTable">
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
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="2">Total</td>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Sélectionner un article',
        allowClear: true,
        width: '100%'
    });

    const table = $('#rapportTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Rapport des ventes par article'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Rapport des ventes par article'
            }
        ],
        language: {
            url: '/js/datatables-fr.json'
        },
        pageLength: 25
    });

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        window.location.href = `{{ route('rapports.ventes-articles') }}?${new URLSearchParams(formData)}`;
    });
});
</script>
@endpush
