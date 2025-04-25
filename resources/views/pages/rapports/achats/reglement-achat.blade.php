@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('rapports.reglement-achats') }}" method="GET" class="row align-items-end g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date début</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date" name="date_debut" class="form-control" value="{{ $params['date_debut'] }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date fin</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date" name="date_fin" class="form-control" value="{{ $params['date_fin'] }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fournisseur</label>
                            <select name="fournisseur_id" class="form-select">
                                <option value="">Tous les fournisseurs</option>
                                @foreach($filtres['fournisseurs'] as $fournisseur)
                                    <option value="{{ $fournisseur->id }}" {{ $params['fournisseur_id'] == $fournisseur->id ? 'selected' : '' }}>
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
                                    <option value="{{ $pointVente->id }}" {{ $params['point_de_vente_id'] == $pointVente->id ? 'selected' : '' }}>
                                        {{ $pointVente->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mode de règlement</label>
                            <select name="mode_reglement" class="form-select">
                                <option value="tous">Tous les modes</option>
                                @foreach($filtres['modes_reglement'] as $key => $value)
                                    <option value="{{ $key }}" {{ $params['mode_reglement'] === $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut validation</label>
                            <select name="statut_validation" class="form-select">
                                <option value="tous">Tous les statuts</option>
                                <option value="valide" {{ $params['statut_validation'] === 'valide' ? 'selected' : '' }}>Validés</option>
                                <option value="non_valide" {{ $params['statut_validation'] === 'non_valide' ? 'selected' : '' }}>Non validés</option>
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
                            <h6 class="card-title text-muted mb-0">Total Règlements</h6>
                            <h3 class="mt-2 mb-0">{{ $statistiques['total_reglements'] }}</h3>
                            <p class="text-muted mb-0">{{ number_format($statistiques['montant_total'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Répartition par mode</h6>
                            <div class="row mt-3">
                                @foreach($statistiques['par_mode'] as $mode => $montant)
                                    <div class="col">
                                        <p class="text-muted mb-0">{{ $filtres['modes_reglement'][$mode] }}</p>
                                        <h5 class="mb-0">{{ number_format($montant, 0, ',', ' ') }}</h5>
                                        <small class="text-muted">FCFA</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des règlements -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des règlements fournisseurs</h5>
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
                                    <th>Facture</th>
                                    <th>Fournisseur</th>
                                    <th>Mode</th>
                                    <th>Référence</th>
                                    <th class="text-end">Montant</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reglements as $reglement)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $reglement->code }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $reglement->facture->code }}</span>
                                    </td>
                                    <td>{{ $reglement->facture->fournisseur->raison_sociale }}</td>
                                    <td>
                                        @switch($reglement->mode_reglement)
                                            @case('ESPECE')
                                                <span class="badge bg-success">Espèces</span>
                                                @break
                                            @case('CHEQUE')
                                                <span class="badge bg-info">Chèque</span>
                                                @break
                                            @case('VIREMENT')
                                                <span class="badge bg-primary">Virement</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $reglement->mode_reglement }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $reglement->reference_reglement ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($reglement->montant_reglement, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal{{ $reglement->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucun règlement trouvé
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $reglements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les détails -->
@foreach($reglements as $reglement)
<div class="modal fade" id="detailsModal{{ $reglement->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du règlement {{ $reglement->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Informations générales</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> {{ $reglement->date_reglement->format('d/m/Y') }}</p>
                            <p><strong>Mode:</strong> {{ $filtres['modes_reglement'][$reglement->mode_reglement] }}</p>
                            <p><strong>Référence:</strong> {{ $reglement->reference_reglement ?? 'N/A' }}</p>
                            <p><strong>Montant:</strong> {{ number_format($reglement->montant_reglement, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>N° Facture:</strong> {{ $reglement->facture->code }}</p>
                            <p><strong>Fournisseur:</strong> {{ $reglement->facture->fournisseur->raison_sociale }}</p>
                            <p><strong>Point de vente:</strong> {{ $reglement->facture->pointVente->libelle }}</p>
                            @if($reglement->reference_document)
                                <p><strong>Document:</strong> {{ $reglement->reference_document }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Validation</h6>
                    <p>
                        <strong>Statut:</strong>
                        @if($reglement->validated_at)
                            <span class="badge bg-success">Validé le {{ $reglement->validated_at->format('d/m/Y') }}</span>
                        @else
                            <span class="badge bg-warning">En attente de validation</span>
                        @endif
                    </p>
                    @if($reglement->validated_at)
                        <p><strong>Validé par:</strong> {{ $reglement->validator->name ?? 'N/A' }}</p>
                    @endif
                </div>

                @if($reglement->commentaire)
                    <div>
                        <h6 class="fw-bold mb-2">Commentaire</h6>
                        <p class="mb-0">{{ $reglement->commentaire }}</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                @if($reglement->validated_at)
                    <a href="{{ route('reglements.print', $reglement->id) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<style>
    .table > :not(caption) > * > * {
        padding: 0.75rem 1rem;
    }

    .table > thead {
        background-color: #f8f9fa;
    }

    .badge {
        padding: 0.5em 0.75em;
    }

    .modal-header, .modal-footer {
        padding: 1rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
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
            window.location.href = `/rapports/reglements/export?${formData}`;
        });
    }
});
</script>
@endpush
