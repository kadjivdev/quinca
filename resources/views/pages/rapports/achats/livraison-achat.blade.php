<!-- resources/views/pages/rapports/achats/rapport-livraison.blade.php -->
@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <!-- Filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('rapports.livraison-achats') }}" method="GET" class="row align-items-end g-3">
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
                            <label class="form-label">Dépôt</label>
                            <select name="depot_id" class="form-select">
                                <option value="">Tous les dépôts</option>
                                @foreach($filtres['depots'] as $depot)
                                    <option value="{{ $depot->id }}" {{ $params['depot_id'] == $depot->id ? 'selected' : '' }}>
                                        {{ $depot->nom }}
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
                            <h6 class="card-title text-muted mb-0">Total Livraisons</h6>
                            <h2 class="mt-2 mb-0">{{ $statistiques['total_livraisons'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Livraisons Validées</h6>
                            <h2 class="mt-2 mb-0 text-success">{{ $statistiques['livraisons_validees'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Livraisons Non Validées</h6>
                            <h2 class="mt-2 mb-0 text-warning">{{ $statistiques['livraisons_non_validees'] }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des livraisons -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des bons de livraison</h5>
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
                                    <th>Dépôt</th>
                                    <th>Statut</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($livraisons as $livraison)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $livraison->date_livraison->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $livraison->code }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $livraison->facture->code ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ $livraison->fournisseur->raison_sociale }}</td>
                                    <td>{{ $livraison->depot->nom_depot }}</td>
                                    <td>
                                        @if($livraison->validated_at)
                                            <span class="badge bg-success">Validé</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal{{ $livraison->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucune livraison trouvée
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $livraisons->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les détails -->
@foreach($livraisons as $livraison)
<div class="modal fade" id="detailsModal{{ $livraison->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du bon de livraison {{ $livraison->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Date:</strong> {{ $livraison->date_livraison->format('d/m/Y') }}</p>
                        <p><strong>Point de vente:</strong> {{ $livraison->pointDeVente->libelle }}</p>
                        <p><strong>Fournisseur:</strong> {{ $livraison->fournisseur->raison_sociale }}</p>
                        <p><strong>Dépôt:</strong> {{ $livraison->depot->nom_depot }}</p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Statut:</strong>
                            @if($livraison->validated_at)
                                <span class="badge bg-success">Validé le {{ $livraison->validated_at->format('d/m/Y') }}</span>
                            @else
                                <span class="badge bg-warning">En attente de validation</span>
                            @endif
                        </p>
                        @if($livraison->vehicule)
                            <p><strong>Véhicule:</strong> {{ $livraison->vehicule->immatriculation }}</p>
                        @endif
                        @if($livraison->chauffeur)
                            <p><strong>Chauffeur:</strong> {{ $livraison->chauffeur->nom_complet }}</p>
                        @endif
                        <p><strong>Facture associée:</strong> {{ $livraison->facture->code ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Liste des articles -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Articles livrés</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th class="text-end">Quantité</th>
                                    <th>Unité</th>
                                    <th class="text-end">Quantité Supp.</th>
                                    <th>Unité Supp.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($livraison->lignes as $ligne)
                                    <tr>
                                        <td>{{ $ligne->article->designation }}</td>
                                        <td class="text-end">{{ number_format($ligne->quantite, 0, ',', ' ') }}</td>
                                        <td>{{ $ligne->uniteMesure->libelle }}</td>
                                        <td class="text-end">
                                            @if($ligne->quantite_supplementaire > 0)
                                                {{ number_format($ligne->quantite_supplementaire, 0, ',', ' ') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($ligne->uniteSupplementaire)
                                                {{ $ligne->uniteSupplementaire->libelle }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($livraison->commentaire)
                <div>
                    <h6 class="fw-bold mb-2">Commentaire</h6>
                    <p class="mb-0">{{ $livraison->commentaire }}</p>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>

            </div>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<style>
    .modal-header, .modal-footer {
        padding: 1rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }

    .table > thead {
        background-color: #f8f9fa;
    }

    .badge {
        padding: 0.5em 0.75em;
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
            window.location.href = `/rapports/livraisons/export?${formData}`;
        });
    }
});
</script>
@endpush
