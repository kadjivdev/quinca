@extends('layouts.rapport.facture')

@section('title', 'Rapport du Stock Disponible')
@section('content')
<br><br>
    <div class="container-fluid">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Magasin actuel : {{ $selectedDepot->libelle_depot }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rapports.stock.changeDepot') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <select class="form-select" name="depot_id" onchange="this.form.submit()">
                            @foreach ($depots as $depot)
                                <option value="{{ $depot->id }}" {{ $selectedDepot->id == $depot->id ? 'selected' : '' }}>
                                    {{ $depot->libelle_depot }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Stock Disponible</h5>
                <div>
                    <button class="btn btn-sm btn-primary" onclick="exportStock()">
                        <i class="fas fa-file-excel me-1"></i> Exporter
                    </button>
                    <button class="btn btn-sm btn-info ms-2" onclick="printStock()">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Article</th>
                                <th>Unité</th>
                                <th class="text-end">Qté Réelle</th>
                                <th class="text-end">Qté Réservée</th>
                                <th class="text-end">Qté Disponible</th>
                                <th class="text-end">Prix Moyen</th>
                                <th class="text-end">Valeur Stock</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stocks as $stock)
                                <tr @if ($stock['statut'] === 'Alerte') class="table-danger" @endif>
                                    <td>{{ $stock['article']['code'] }}</td>
                                    <td>{{ $stock['article']['designation'] }}</td>
                                    <td>{{ $stock['article']['unite'] }}</td>
                                    <td class="text-end">{{ number_format($stock['quantite_reelle'], 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($stock['quantite_reservee'], 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($stock['quantite_disponible'], 2, ',', ' ') }}</td>
                                    <!-- <td class="text-end">{{ number_format($stock['prix_moyen'], 0, ',', ' ') }} FCFA</td> -->
                                    <td class="text-end">---</td>
                                    <td class="text-end">{{ number_format($stock['valeur_stock'], 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        @switch($stock['statut'])
                                            @case('Alerte')
                                                <span class="badge bg-danger">Alerte</span>
                                                @break
                                            @case('Minimum')
                                                <span class="badge bg-warning">Minimum</span>
                                                @break
                                            @case('Maximum')
                                                <span class="badge bg-info">Maximum</span>
                                                @break
                                            @default
                                                <span class="badge bg-success">Normal</span>
                                        @endswitch
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        function exportStock() {
            const depot_id = document.querySelector('select[name="depot_id"]').value;
            window.location.href = `{{ route('rapports.stock.export') }}?depot_id=${depot_id}`;
        }

        function printStock() {
            const depot_id = document.querySelector('select[name="depot_id"]').value;
            window.open(`{{ route('rapports.stock.print') }}?depot_id=${depot_id}`, '_blank');
        }
    </script>
@endpush
