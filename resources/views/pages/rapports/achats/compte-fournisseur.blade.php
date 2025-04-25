@extends('layouts.rapport.facture')
<br><br>
@section('content')
<div class="container-fluid px-4 py-4">
    <!-- En-tête avec logo et titre -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-kadjiv fw-bold mb-0">Comptes Fournisseurs</h2>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-kadjiv" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-2"></i>Importer Soldes
            </button>
            <button type="button" class="btn btn-kadjiv" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow-smooth mb-4 border-0">
        <div class="card-body bg-light rounded">
            <form action="{{ route('rapports.compte-fournisseur') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted small text-uppercase">Fournisseur</label>
                    <select name="fournisseur_id" class="form-select form-select-lg">
                        <option value="">Tous les fournisseurs</option>
                        @foreach($filtres['fournisseurs'] as $fournisseur)
                            <option value="{{ $fournisseur->id }}" {{ $params['fournisseur_id'] == $fournisseur->id ? 'selected' : '' }}>
                                {{ $fournisseur->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-kadjiv btn-lg w-100">
                        <i class="fas fa-sync-alt me-2"></i>Actualiser
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4 g-4">
        <!-- Solde initial si un fournisseur est sélectionné -->
        @if($params['fournisseur_id'] && isset($solde_initial))
        <div class="col-12">
            <div class="card shadow-smooth border-0 bg-gradient-light">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <span class="badge bg-kadjiv mb-2">Solde Initial au {{ $solde_initial->date_solde->format('d/m/Y') }}</span>
                            <h3 class="mb-1 {{ $solde_initial->type === 'CREDITEUR' ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($solde_initial->montant), 0, ',', ' ') }} FCFA
                                <small>({{ $solde_initial->type === 'CREDITEUR' ? 'Dû au fournisseur' : 'En notre faveur' }})</small>
                            </h3>
                            <p class="text-muted mb-0 small">{{ $solde_initial->commentaire }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Cartes statistiques -->
        <div class="col-md-3">
            <div class="card shadow-smooth border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-3 me-3">
                            <i class="fas fa-users fa-lg text-kadjiv"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Total Fournisseurs</h6>
                            <h3 class="mb-0 fw-bold">{{ $statistiques['total_fournisseurs'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-smooth border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-3 me-3">
                            <i class="fas fa-balance-scale fa-lg text-kadjiv"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Solde Global</h6>
                            <h3 class="mb-0 fw-bold {{ $statistiques['solde_global'] > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(abs($statistiques['solde_global']), 0, ',', ' ') }}
                            </h3>
                            <span class="small">{{ $statistiques['solde_global'] > 0 ? 'Dû aux fournisseurs' : 'En notre faveur' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-smooth border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                            <i class="fas fa-arrow-up fa-lg text-danger"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Créditeurs</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $statistiques['fournisseurs_crediteurs'] }}</h3>
                            <span class="small text-danger">{{ number_format($statistiques['montant_crediteur'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-smooth border-0 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="fas fa-arrow-down fa-lg text-success"></i>
                        </div>
                        <div>
                            <h6 class="text-muted text-uppercase small mb-1">Débiteurs</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $statistiques['fournisseurs_debiteurs'] }}</h3>
                            <span class="small text-success">{{ number_format($statistiques['montant_debiteur'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des fournisseurs -->
    @if(!$params['fournisseur_id'])
    <div class="card shadow-smooth border-0">
        <div class="card-header bg-light py-3">
            <h5 class="text-kadjiv mb-0 fw-bold">Situation des comptes fournisseurs</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Fournisseur</th>
                            <th class="border-0 text-end">Solde Initial</th>
                            <th class="border-0 text-end">Total Factures</th>
                            <th class="border-0 text-end">Total Règlements</th>
                            <th class="border-0 text-end">Solde</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($fournisseurs as $fournisseur)
                        <tr>
                            <td class="fw-medium">{{ $fournisseur->raison_sociale }}</td>
                            <td class="text-end">
                                @if($fournisseur->soldeInitial)
                                    <span class="{{ $fournisseur->soldeInitial->type === 'CREDITEUR' ? 'text-danger' : 'text-success' }}">
                                        {{ number_format(abs($fournisseur->soldeInitial->montant), 0, ',', ' ') }}
                                        <br>
                                        <small>{{ $fournisseur->soldeInitial->type === 'CREDITEUR' ? 'Dû' : 'Avoir' }}</small>
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($fournisseur->total_factures, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($fournisseur->total_reglements, 0, ',', ' ') }}</td>
                            <td class="text-end">
                                <span class="badge {{ $fournisseur->solde > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill">
                                    {{ number_format(abs($fournisseur->solde), 0, ',', ' ') }} FCFA
                                </span>
                                <br>
                                <small>{{ $fournisseur->solde > 0 ? 'Dû au fournisseur' : 'En notre faveur' }}</small>
                            </td>
                            <td>
                                <a href="{{ route('rapports.compte-fournisseur', ['fournisseur_id' => $fournisseur->id]) }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-eye text-kadjiv"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted mb-0">Aucun fournisseur trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Détail des mouvements pour un fournisseur spécifique -->
    @if($params['fournisseur_id'])
    <div class="card shadow-smooth border-0">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
            <h5 class="text-kadjiv mb-0 fw-bold">Détail des mouvements</h5>
            <div>
                <button type="button" class="btn btn-outline-kadjiv" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Date</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Référence</th>
                            <th class="border-0 text-end">Débit</th>
                            <th class="border-0 text-end">Crédit</th>
                            <th class="border-0 text-end">Solde</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @php $solde = isset($solde_initial) ? $solde_initial->montant : 0; @endphp
                        @forelse($mouvements as $mouvement)
                            @php
                                if ($mouvement['type'] !== 'SOLDE_INITIAL') {
                                    $solde += $mouvement['debit'] - $mouvement['credit'];
                                }
                            @endphp
                            <tr>
                                <td>{{ $mouvement['date']->format('d/m/Y') }}</td>
                                <td>
                                    @if($mouvement['type'] === 'SOLDE_INITIAL')
                                        <span class="badge bg-secondary">SOLDE INITIAL</span>
                                    @elseif($mouvement['type'] === 'FACTURE')
                                        <span class="badge bg-danger">FACTURE</span>
                                    @else
                                        <span class="badge bg-success">RÈGLEMENT</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $mouvement['reference'] }}
                                    @if(isset($mouvement['bon_commande']))
                                        <br><small class="text-muted">BC: {{ $mouvement['bon_commande'] }}</small>
                                    @endif
                                    @if(isset($mouvement['mode']))
                                        <br><small class="text-muted">{{ $mouvement['mode'] }} {{ $mouvement['reference_paiement'] }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ $mouvement['debit'] > 0 ? number_format($mouvement['debit'], 0, ',', ' ') : '-' }}
                                </td>
                                <td class="text-end">
                                    {{ $mouvement['credit'] > 0 ? number_format($mouvement['credit'], 0, ',', ' ') : '-' }}
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $solde > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill">
                                        {{ number_format(abs($solde), 0, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td>
                                    @if($mouvement['type'] !== 'SOLDE_INITIAL')
                                        @if($mouvement['type'] === 'FACTURE')
                                            <button type="button" class="btn btn-sm btn-light" onclick="showFactureDetails({{ $mouvement['id'] }})">
                                                <i class="fas fa-eye text-kadjiv"></i>
                                            </button>
                                        @elseif($mouvement['type'] === 'REGLEMENT')
                                            <button type="button" class="btn btn-sm btn-light" onclick="showReglementDetails({{ $mouvement['id'] }})">
                                                <i class="fas fa-eye text-kadjiv"></i>
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p class="text-muted mb-0">Aucun mouvement trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Import -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-kadjiv">Importer les soldes fournisseurs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('soldes.import')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Fichier Excel</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                            <div class="form-text">Format attendu : XLS, XLSX</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date des soldes</label>
                            <input type="date" name="date_solde" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-kadjiv">
                                <i class="fas fa-upload me-2"></i>Importer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les détails -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    :root {
        --bs-kadjiv: #FFA500;
        --bs-kadjiv-rgb: 255, 165, 0;
    }

    .text-kadjiv {
        color: var(--bs-kadjiv) !important;
    }

    .bg-kadjiv {
        background-color: var(--bs-kadjiv) !important;
    }

    .btn-kadjiv {
        color: #fff;
        background-color: var(--bs-kadjiv);
        border-color: var(--bs-kadjiv);
    }

    .btn-kadjiv:hover {
        color: #fff;
        background-color: #e69500;
        border-color: #d98c00;
    }

    .btn-outline-kadjiv {
        color: var(--bs-kadjiv);
        border-color: var(--bs-kadjiv);
    }

    .btn-outline-kadjiv:hover {
        color: #fff;
        background-color: var(--bs-kadjiv);
        border-color: var(--bs-kadjiv);
    }

    .shadow-smooth {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }

    .bg-gradient-light {
        background: linear-gradient(to right, #f8f9fa, #fff);
    }

    .table > :not(caption) > * > * {
        padding: 1rem;
        border-bottom-width: 1px;
    }

    .badge {
        padding: 0.6em 1em;
    }

    @media print {
        .btn, .modal, .card-header {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }

    .modal-xl {
        max-width: 90%;
    }

    /* Animations */
    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .statistics-card {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
async function showFactureDetails(id) {
    try {
        const response = await fetch(`/api/factures/${id}`);
        const facture = await response.json();

        let lignesHtml = '';
        facture.lignes.forEach(ligne => {
            lignesHtml += `
                <tr>
                    <td>${ligne.article.designation}</td>
                    <td class="text-end">${ligne.quantite}</td>
                    <td class="text-end">${ligne.prix_unitaire}</td>
                    <td class="text-end">${ligne.montant_total}</td>
                </tr>
            `;
        });

        const modalContent = `
            <div class="modal-header">
                <h5 class="modal-title">Détails Facture ${facture.code}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Informations Générales</h6>
                        <p>Date: ${facture.date_facture}<br>
                           Fournisseur: ${facture.fournisseur.raison_sociale}<br>
                           Point de Vente: ${facture.point_vente.nom_pv}<br>
                           Référence: ${facture.reference || '-'}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6>Montants</h6>
                        <p>Total HT: ${facture.montant_ht}<br>
                           TVA: ${facture.montant_tva}<br>
                           Total TTC: ${facture.montant_ttc}</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th class="text-end">Quantité</th>
                                <th class="text-end">Prix Unit.</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${lignesHtml}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        document.querySelector('#detailsModal .modal-content').innerHTML = modalContent;
        modal.show();
    } catch (error) {
        console.error('Erreur lors du chargement des détails:', error);
        alert('Erreur lors du chargement des détails');
    }
}

async function showReglementDetails(id) {
    try {
        const response = await fetch(`/api/reglements/${id}`);
        const reglement = await response.json();

        const modalContent = `
            <div class="modal-header">
                <h5 class="modal-title">Détails Règlement ${reglement.code}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations Générales</h6>
                        <p>Date: ${reglement.date_reglement}<br>
                           Facture: ${reglement.facture.code}<br>
                           Fournisseur: ${reglement.facture.fournisseur.raison_sociale}<br>
                           Point de Vente: ${reglement.facture.point_vente.nom_pv}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6>Paiement</h6>
                        <p>Mode: ${reglement.mode_reglement}<br>
                           Référence: ${reglement.reference_reglement || '-'}<br>
                           Montant: ${reglement.montant_reglement}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>Commentaire</h6>
                        <p>${reglement.commentaire || '-'}</p>
                    </div>
                </div>
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        document.querySelector('#detailsModal .modal-content').innerHTML = modalContent;
        modal.show();
    } catch (error) {
        console.error('Erreur lors du chargement des détails:', error);
        alert('Erreur lors du chargement des détails');
    }
}
</script>
@endpush

@endsection
