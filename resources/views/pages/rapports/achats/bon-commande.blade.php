<!-- resources/views/pages/rapports/achats/rapport-bon-commande.blade.php -->
@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <!-- En-tête avec filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('rapports.bon-commandes') }}" method="GET" class="row align-items-end g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date début</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date"
                                       name="date_debut"
                                       class="form-control"
                                       value="{{ $params['date_debut'] }}">
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
                                       value="{{ $params['date_fin'] }}">
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
                            <h6 class="card-title text-muted mb-0">Total Commandes</h6>
                            <h2 class="mt-2 mb-0">{{ $statistiques['total_commandes'] }}</h2>
                            <p class="text-muted mb-0">{{ number_format($statistiques['montant_total'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Commandes Validées</h6>
                            <h2 class="mt-2 mb-0 text-success">{{ $statistiques['commandes_validees'] }}</h2>
                            <p class="text-success mb-0">{{ number_format($statistiques['montant_valide'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-0">Commandes Non Validées</h6>
                            <h2 class="mt-2 mb-0 text-warning">{{ $statistiques['commandes_non_validees'] }}</h2>
                            <p class="text-warning mb-0">{{ number_format($statistiques['montant_total'] - $statistiques['montant_valide'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des bons de commande -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des bons de commande</h5>
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
                                    <th class="text-end">Montant</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bonCommandes as $bonCommande)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $bonCommande->date_commande->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $bonCommande->code }}</span>
                                    </td>
                                    <td>{{ $bonCommande->pointVente->libelle }}</td>
                                    <td>{{ $bonCommande->fournisseur->raison_sociale }}</td>
                                    <td>
                                        @if($bonCommande->validated_at)
                                            <span class="badge bg-success">Validée</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($bonCommande->montant_total, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal{{ $bonCommande->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucun bon de commande trouvé
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $bonCommandes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les détails -->
<!-- Modals pour les détails -->
@foreach($bonCommandes as $bonCommande)
<div class="modal fade" id="detailsModal{{ $bonCommande->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails du bon de commande {{ $bonCommande->code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informations générales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Date:</strong> {{ $bonCommande->date_commande->format('d/m/Y') }}</p>
                        <p><strong>Point de vente:</strong> {{ $bonCommande->pointVente->libelle }}</p>
                        <p><strong>Fournisseur:</strong> {{ $bonCommande->fournisseur->raison_sociale }}</p>
                        <p><strong>Montant total:</strong> {{ number_format($bonCommande->montant_total, 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong>Statut:</strong>
                            @if($bonCommande->validated_at)
                                <span class="badge bg-success">Validée le {{ $bonCommande->validated_at->format('d/m/Y') }}</span>
                            @else
                                <span class="badge bg-warning">En attente de validation</span>
                            @endif
                        </p>
                        @if($bonCommande->validated_at)
                            <p><strong>Validé par:</strong> {{ $bonCommande->validator->name ?? 'N/A' }}</p>
                        @endif
                        <p><strong>Programmation:</strong> {{ $bonCommande->programmation->code ?? 'N/A' }}</p>
                        <p><strong>Commentaire:</strong> {{ $bonCommande->commentaire ?? 'Aucun' }}</p>
                    </div>
                </div>

                <!-- Liste des articles -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Articles commandés</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th class="text-end">Quantité</th>
                                    <th class="text-end">Prix unitaire</th>
                                    <th class="text-end">Remise</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bonCommande->lignes as $ligne)
                                    <tr>
                                        <td>{{ $ligne->article->designation }}</td>
                                        <td class="text-end">{{ number_format($ligne->quantite, 0, ',', ' ') }} {{ $ligne->uniteMesure->libelle }}</td>
                                        <td class="text-end">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">{{ number_format($ligne->taux_remise, 1, ',', ' ') }}%</td>
                                        <td class="text-end">{{ number_format($ligne->montant_ligne, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end">Total:</td>
                                    <td class="text-end">{{ number_format($bonCommande->montant_total, 0, ',', ' ') }} FCFA</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Informations sur les factures liées -->
                @if($bonCommande->factures->isNotEmpty())
                <div>
                    <h6 class="fw-bold mb-3">Factures associées</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Date</th>
                                    <th class="text-end">Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bonCommande->factures as $facture)
                                    <tr>
                                        <td>{{ $facture->reference }}</td>
                                        <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                        <td class="text-end">{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            @if($facture->validated_at)
                                                <span class="badge bg-success">Validée</span>
                                            @else
                                                <span class="badge bg-warning">En attente</span>
                                            @endif
                                        </td>
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

            </div>
        </div>
    </div>
</div>
@endforeach

@push('styles')
<style>
    .table > :not(caption) > * > * {
        padding: 0.75rem 1rem;
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
            window.location.href = `/rapports/bon-commandes/export?${formData}`;
        });
    }
});
</script>
@endpush
