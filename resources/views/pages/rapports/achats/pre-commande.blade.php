<!-- resources/views/pages/rapports/achats/rapport-programmation.blade.php -->
@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <!-- En-tête avec filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('rapports.pre-commandes') }}" method="GET" class="row align-items-end g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date début</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date"
                                       name="date_debut"
                                       class="form-control"
                                       value="{{ request('date_debut', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date fin</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date"
                                       name="date_fin"
                                       class="form-control"
                                       value="{{ request('date_fin', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fournisseur</label>
                            <select name="fournisseur_id" class="form-select">
                                <option value="">Tous les fournisseurs</option>
                                @foreach($filtres['fournisseurs'] as $fournisseur)
                                    <option value="{{ $fournisseur->id }}" {{ request('fournisseur_id') == $fournisseur->id ? 'selected' : '' }}>
                                        {{ $fournisseur->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Point de vente</label>
                            <select name="point_de_vente_id" class="form-select">
                                <option value="">Tous les points de vente</option>
                                @foreach($filtres['points_vente'] as $pointVente)
                                    <option value="{{ $pointVente->id }}" {{ request('point_de_vente_id') == $pointVente->id ? 'selected' : '' }}>
                                        {{ $pointVente->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut validation</label>
                            <select name="statut_validation" class="form-select">
                                <option value="tous">Tous les statuts</option>
                                <option value="valide" {{ request('statut_validation') === 'valide' ? 'selected' : '' }}>Validés</option>
                                <option value="non_valide" {{ request('statut_validation') === 'non_valide' ? 'selected' : '' }}>Non validés</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sync-alt me-2"></i>Actualiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Total Programmations</h6>
                            <h2 class="mt-2 mb-0">{{ $statistiques['total_programmations'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Programmations Validées</h6>
                            <h2 class="mt-2 mb-0 text-success">{{ $statistiques['programmations_validees'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Programmations Non Validées</h6>
                            <h2 class="mt-2 mb-0 text-warning">{{ $statistiques['programmations_non_validees'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des programmations -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des programmations d'achat</h5>
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
                                    <th>Date</th>
                                    <th>Code</th>
                                    <th>Point de vente</th>
                                    <th>Fournisseur</th>
                                    <th>Statut</th>
                                    <th>Nb Articles</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programmations as $programmation)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $programmation->date_programmation->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $programmation->code }}</span>
                                    </td>
                                    <td>{{ $programmation->pointVente->libelle }}</td>
                                    <td>{{ $programmation->fournisseur->nom }}</td>
                                    <td>
                                        @if($programmation->validated_at)
                                            <span class="badge bg-success">Validée</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>{{ $programmation->lignes->count() }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal{{ $programmation->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucune programmation trouvée
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $programmations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les détails -->
@foreach($programmations as $programmation)
<div class="modal fade" id="detailsModal{{ $programmation->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la programmation {{ $programmation->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Date:</strong> {{ $programmation->date_programmation->format('d/m/Y') }}</p>
                        <p><strong>Point de vente:</strong> {{ $programmation->pointVente->libelle }}</p>
                        <p><strong>Fournisseur:</strong> {{ $programmation->fournisseur->nom }}</p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Statut:</strong>
                            @if($programmation->validated_at)
                                <span class="badge bg-success">Validée le {{ $programmation->validated_at->format('d/m/Y') }}</span>
                            @else
                                <span class="badge bg-warning">En attente de validation</span>
                            @endif
                        </p>
                        @if($programmation->validated_at)
                            <p><strong>Validé par:</strong> {{ $programmation->validator->name ?? 'N/A' }}</p>
                        @endif
                        <p><strong>Commentaire:</strong> {{ $programmation->commentaire ?? 'Aucun' }}</p>
                    </div>
                </div>

                <!-- Liste des articles -->
                <h6 class="mb-3">Articles programmés</h6>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th class="text-end">Quantité</th>
                            <th>Unité</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($programmation->lignes as $ligne)
                            <tr>
                                <td>{{ $ligne->article->designation }}</td>
                                <td class="text-end">{{ number_format($ligne->quantite, 0, ',', ' ') }}</td>
                                <td>{{ $ligne->uniteMesure->libelle }}</td>
                            </tr>
                        @endforeach
                    </tbody>
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

    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.querySelector('.btn-outline-primary');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const formData = new URLSearchParams(new FormData(document.querySelector('form'))).toString();
            window.location.href = `/rapports/programmations/export?${formData}`;
        });
    }
});
</script>
@endpush
