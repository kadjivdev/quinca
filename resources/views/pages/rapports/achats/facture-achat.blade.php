<br>
@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <!-- En-tête avec filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('rapports.facture-achats') }}" method="GET" class="row align-items-end g-3">
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
                            <label class="form-label">Statut validation</label>
                            <select name="statut_validation" class="form-select">
                                <option value="tous">Tous les statuts</option>
                                <option value="valide" {{ $params['statut_validation'] === 'valide' ? 'selected' : '' }}>Validés</option>
                                <option value="non_valide" {{ $params['statut_validation'] === 'non_valide' ? 'selected' : '' }}>Non validés</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut paiement</label>
                            <select name="statut_paiement" class="form-select">
                                <option value="tous">Tous les statuts</option>
                                @foreach($filtres['statuts_paiement'] as $key => $value)
                                    <option value="{{ $key }}" {{ $params['statut_paiement'] === $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
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
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Total Factures</h6>
                            <h3 class="mt-2 mb-0">{{ $statistiques['total_factures'] }}</h3>
                            <p class="text-muted mb-0">{{ number_format($statistiques['montant_total'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Non Payé</h6>
                            <h3 class="mt-2 mb-0 text-danger">{{ number_format($statistiques['montant_non_paye'], 0, ',', ' ') }}</h3>
                            <p class="text-danger mb-0">FCFA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Partiellement Payé</h6>
                            <h3 class="mt-2 mb-0 text-warning">{{ number_format($statistiques['montant_partiel'], 0, ',', ' ') }}</h3>
                            <p class="text-warning mb-0">FCFA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Payé</h6>
                            <h3 class="mt-2 mb-0 text-success">{{ number_format($statistiques['montant_paye'], 0, ',', ' ') }}</h3>
                            <p class="text-success mb-0">FCFA</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des factures -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des factures fournisseurs</h5>
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
                                    <th class="text-end">Montant TTC</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($factures as $facture)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $facture->code }}</span>
                                    </td>
                                    <td>{{ $facture->pointVente->libelle }}</td>
                                    <td>{{ $facture->fournisseur->raison_sociale }}</td>
                                    <td>
                                        @if($facture->validated_at)
                                            <span class="badge bg-success">{{ $facture->statut_paiement }}</span>
                                        @else
                                            <span class="badge bg-warning">Non validée</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal{{ $facture->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucune facture trouvée
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $factures->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les détails -->
@foreach($factures as $facture)
<div class="modal fade" id="detailsModal{{ $facture->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la facture {{ $facture->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Date:</strong> {{ $facture->date_facture->format('d/m/Y') }}</p>
                        <p><strong>Point de vente:</strong> {{ $facture->pointVente->libelle }}</p>
                        <p><strong>Fournisseur:</strong> {{ $facture->fournisseur->raison_sociale }}</p>
                        <p><strong>Bon de commande:</strong> {{ $facture->bonCommande->code ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Statut validation:</strong>
                            @if($facture->validated_at)
                                <span class="badge bg-success">Validée le {{ $facture->validated_at->format('d/m/Y') }}</span>
                            @else
                                <span class="badge bg-warning">En attente de validation</span>
                            @endif
                        </p>
                        <p>
                            <strong>Statut paiement:</strong>
                            @switch($facture->statut_paiement)
                                @case('NON_PAYE')
                                    <span class="badge bg-danger">Non payé</span>
                                    @break
                                @case('PARTIELLEMENT_PAYE')
                                    <span class="badge bg-warning">Partiellement payé</span>
                                    @break
                                @case('PAYE')
                                    <span class="badge bg-success">Payé</span>
                                    @break
                            @endswitch
                        </p>
                        @if($facture->validated_at)
                            <p><strong>Validé par:</strong> {{ $facture->validator->name ?? 'N/A' }}</p>
                        @endif
                        <p><strong>Commentaire:</strong> {{ $facture->commentaire ?? 'Aucun' }}</p>
                    </div>
                </div>

                <!-- Liste des articles -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Articles facturés</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th class="text-end">Quantité</th>
                                    <th class="text-end">Prix unitaire</th>
                                    <th class="text-end">TVA</th>
                                    <th class="text-end">AIB</th>
                                    <th class="text-end">Total TTC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facture->lignes as $ligne)
                                    <tr>
                                        <td>{{ $ligne->article->designation }}</td>
                                        <td class="text-end">{{ number_format($ligne->quantite, 0, ',', ' ') }} {{ $ligne->uniteMesure->libelle }}</td>
                                        <td class="text-end">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">{{ number_format($ligne->montant_tva, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">{{ number_format($ligne->montant_aib, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">{{ number_format($ligne->montant_ttc, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">Total HT:</td>
                                    <td class="text-end">{{ number_format($facture->montant_ht, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end">Total TVA:</td>
                                    <td class="text-end">{{ number_format($facture->montant_tva, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end">Total AIB:</td>
                                    <td class="text-end">{{ number_format($facture->montant_aib, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end">Total TTC:</td>
                                    <td class="text-end">{{ number_format($facture->montant_ttc, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Règlements associés -->
                @if($facture->reglements->isNotEmpty())
                <div>
                    <h6 class="fw-bold mb-3">Règlements effectués</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Mode</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($facture->reglements as $reglement)
                                    <tr>
                                        <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                                        <td>{{ $reglement->reference }}</td>
                                        <td>{{ $reglement->mode_reglement }}</td>
                                        <td class="text-end">{{ number_format($reglement->montant, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                @if($facture->validated_at)
                    <a href="{{ route('factures.print', $facture->id) }}" class="btn btn-primary" target="_blank">
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

    .table-sm td, .table-sm th {
        padding: 0.5rem;
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
            window.location.href = `/rapports/factures/export?${formData}`;
        });
    }
});
</script>
@endpush
