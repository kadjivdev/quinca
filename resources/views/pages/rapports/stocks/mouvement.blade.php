@extends('layouts.rapport.facture')

@section('title', 'Rapport des Mouvements de Stock')
@section('content')
<div class="container-fluid">
    <br>

    <!-- Filtres -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <input type="hidden" name="depot_id" value="{{ $selectedDepot->id }}">
                <div class="col-md-3">
                    <label class="form-label">Type de mouvement</label>
                    <select class="form-select" name="type_mouvement">
                        <option value="">Tous</option>
                        <option value="{{ \App\Models\Stock\StockMouvement::TYPE_ENTREE }}">Entrées</option>
                        <option value="{{ \App\Models\Stock\StockMouvement::TYPE_SORTIE }}">Sorties</option>
                        <option value="{{ \App\Models\Stock\StockMouvement::TYPE_TRANSFERT }}">Transferts</option>
                        <option value="{{ \App\Models\Stock\StockMouvement::TYPE_AJUSTEMENT }}">Ajustements</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Période</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_debut">
                        <span class="input-group-text">au</span>
                        <input type="date" class="form-control" name="date_fin">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Entrées du mois</h6>
                    <h2 class="mb-0">{{ $stats['entrees']['nombre'] }}</h2>
                    <small>Valeur: {{ number_format($stats['entrees']['valeur'], 0, ',', ' ') }} FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Sorties du mois</h6>
                    <h2 class="mb-0">{{ $stats['sorties']['nombre'] }}</h2>
                    <small>Valeur: {{ number_format($stats['sorties']['valeur'], 0, ',', ' ') }} FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Stock Actuel</h6>
                    <h2 class="mb-0">{{ $stats['stock_actuel']['articles'] }} articles</h2>
                    <small>Valeur: {{ number_format($stats['stock_actuel']['valeur_totale'], 0, ',', ' ') }}
                        FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Articles Critiques</h6>
                    <h2 class="mb-0">{{ count($stats['articles_critiques']) }}</h2>
                    <small>En alerte ou sous minimum</small>
                </div>
            </div>
        </div>
    </div>



    <!-- Table des mouvements -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Mouvements de Stock</h5>
            <button class="btn btn-sm btn-primary" onclick="exportMouvements()">
                <i class="fas fa-file-excel me-1"></i> Exporter
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Article</th>
                            <th>Unité</th>
                            <th class="text-end">Quantité</th>
                            <th class="text-end">Prix Unitaire</th>
                            <th>Document</th>
                            <th>Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mouvements as $mouvement)
                        <tr>
                            <td class="text-monospace">{{ $mouvement->code }}</td>
                            <td>{{ $mouvement->date_mouvement->format('d/m/Y') }}</td>
                            <td>
                                @switch($mouvement->type_mouvement)
                                @case('ENTREE')
                                <span class="badge bg-success">Entrée</span>
                                @break

                                @case('SORTIE')
                                <span class="badge bg-warning">Sortie</span>
                                @break

                                @case('TRANSFERT')
                                <span class="badge bg-info">Transfert</span>
                                @break

                                @default
                                <span class="badge bg-secondary">Ajustement</span>
                                @endswitch
                            </td>
                            <td>{{ $mouvement->article->designation }}</td>
                            <td>{{ $mouvement->uniteMesure->code_unite }}</td>
                            <td class="text-end">{{ number_format($mouvement->quantite, 2, ',', ' ') }}</td>
                            <!-- <td class="text-end">{{ number_format($mouvement->prix_unitaire, 0, ',', ' ') }} FCFA -->
                            <td class="text-end">---</td>
                            <td>{{ $mouvement->document_type }} {{ $mouvement->document_id }}</td>
                            <td>{{ $mouvement->user->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $mouvements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-monospace {
        font-family: 'Monaco', 'Consolas', monospace;
    }

    .table-responsive {
        min-height: 300px;
    }

    .badge {
        font-size: 85%;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inputs = form.querySelectorAll('input, select');

        inputs.forEach(input => {
            input.addEventListener('change', function() {
                refreshData();
            });
        });
    });

    function refreshData() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);

        window.location.href = `{{ route('rapports.stock.mouvements') }}?${params.toString()}`;
    }

    function exportStock() {
        const depot_id = document.querySelector('input[name="depot_id"]').value;
        window.location.href = `{{ route('rapports.stock.export') }}?depot_id=${depot_id}`;
    }

    function printStock() {
        const depot_id = document.querySelector('input[name="depot_id"]').value;
        window.open(`{{ route('rapports.stock.print') }}?depot_id=${depot_id}`, '_blank');
    }

    function exportMouvements() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        window.location.href = `{{ route('rapports.stock.export-mouvements') }}?${params.toString()}`;
    }
</script>
@endpush