@extends('layouts.rapport.facture')
<br><br>
@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- En-tête avec logo et titre -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-kadjiv fw-bold mb-0">Comptes Clients</h2>
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
                <form action="{{ route('rapports.compte-client') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small text-uppercase">Client</label>
                        <select name="client_id" class="form-select form-select-lg">
                            <option value="">Tous les clients</option>
                            @foreach ($filtres['clients'] as $client)
                                <option value="{{ $client->id }}"
                                    {{ $params['client_id'] == $client->id ? 'selected' : '' }}>
                                    {{ $client->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small text-uppercase">Point de Vente</label>
                        <select name="point_de_vente_id" class="form-select form-select-lg">
                            <option value="">Tous les points de vente</option>
                            @foreach ($filtres['points_vente'] as $pdv)
                                <option value="{{ $pdv->id }}"
                                    {{ $params['point_de_vente_id'] == $pdv->id ? 'selected' : '' }}>
                                    {{ $pdv->libelle }}
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
            <!-- Solde initial si un client est sélectionné -->
            @if ($params['client_id'] && isset($solde_initial))
                <div class="col-12">
                    <div class="card shadow-smooth border-0 bg-gradient-light">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="badge bg-kadjiv mb-2">Solde Initial au
                                        {{ $solde_initial->date_solde->format('d/m/Y') }}</span>
                                    <h3
                                        class="mb-1 {{ $solde_initial->type === 'CREDITEUR' ? 'text-danger' : 'text-success' }}">
                                        {{ number_format(abs($solde_initial->montant), 0, ',', ' ') }} FCFA
                                        <small>({{ $solde_initial->type === 'CREDITEUR' ? 'Doit nous payer' : 'En sa faveur' }})</small>
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
                                <h6 class="text-muted text-uppercase small mb-1">Total Clients</h6>
                                <h3 class="mb-0 fw-bold">{{ $statistiques['total_clients'] }}</h3>
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
                                <h3
                                    class="mb-0 fw-bold {{ $statistiques['solde_global'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format(abs($statistiques['solde_global']), 0, ',', ' ') }}
                                </h3>
                                <span
                                    class="small">{{ $statistiques['solde_global'] > 0 ? 'Nous doivent' : 'Nous leur devons' }}</span>
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
                                <h6 class="text-muted text-uppercase small mb-1">Clients Débiteurs</h6>
                                <h3 class="mb-0 fw-bold text-danger">{{ $statistiques['clients_debiteurs'] }}</h3>
                                <span
                                    class="small text-danger">{{ number_format($statistiques['montant_debiteur'], 0, ',', ' ') }}
                                    FCFA</span>
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
                                <h6 class="text-muted text-uppercase small mb-1">Clients Créditeurs</h6>
                                <h3 class="mb-0 fw-bold text-success">{{ $statistiques['clients_crediteurs'] }}</h3>
                                <span
                                    class="small text-success">{{ number_format($statistiques['montant_crediteur'], 0, ',', ' ') }}
                                    FCFA</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
 <!-- Liste des clients -->
 @if (!$params['client_id'])
 <div class="card shadow-smooth border-0">
     <div class="card-header bg-light py-3">
         <h5 class="text-kadjiv mb-0 fw-bold">Situation des comptes clients</h5>
     </div>
     <div class="card-body p-0">
         <div class="table-responsive">
             <table class="table table-hover align-middle mb-0">
                 <thead class="bg-light">
                     <tr>
                         <th class="border-0">Client</th>
                         <th class="border-0">Code Client</th>
                         <th class="border-0">Catégorie</th>
                         <th class="border-0 text-end">Solde Initial</th>
                         <th class="border-0 text-end">Total Factures</th>
                         <th class="border-0 text-end">Total Règlements</th>
                         <th class="border-0 text-end">Total Acomptes</th>
                         <th class="border-0 text-end">Solde</th>
                         <th class="border-0">Actions</th>
                     </tr>
                 </thead>
                 <tbody class="border-top-0">
                     @forelse($clients as $client)
                         <tr>
                             <td class="fw-medium">{{ $client->raison_sociale }}</td>
                             <td>{{ $client->code_client }}</td>
                             <td><span class="badge bg-light text-dark">{{ ucfirst($client->categorie) }}</span></td>
                             <td class="text-end">
                                 @if ($client->soldeInitial)
                                     <span class="{{ $client->soldeInitial->type === 'CREDITEUR' ? 'text-danger' : 'text-success' }}">
                                         {{ number_format(abs($client->soldeInitial->montant), 0, ',', ' ') }}
                                         <br>
                                         <small>{{ $client->soldeInitial->type === 'CREDITEUR' ? 'Doit' : 'Avoir' }}</small>
                                     </span>
                                 @else
                                     -
                                 @endif
                             </td>
                             <td class="text-end">{{ number_format($client->total_factures, 0, ',', ' ') }}</td>
                             <td class="text-end">{{ number_format($client->total_reglements, 0, ',', ' ') }}</td>
                             <td class="text-end">{{ number_format($client->total_acomptes, 0, ',', ' ') }}</td>
                             <td class="text-end">
                                 <span class="badge {{ $client->solde > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill">
                                     {{ number_format(abs($client->solde), 0, ',', ' ') }} FCFA
                                 </span>
                                 <br>
                                 <small>{{ $client->solde > 0 ? 'Doit nous payer' : 'En sa faveur' }}</small>
                             </td>
                             <td>
                                 <a href="{{ route('rapports.compte-client', ['client_id' => $client->id]) }}"
                                     class="btn btn-sm btn-light">
                                     <i class="fas fa-eye text-kadjiv"></i>
                                 </a>
                             </td>
                         </tr>
                     @empty
                         <tr>
                             <td colspan="9" class="text-center py-4">
                                 <p class="text-muted mb-0">Aucun client trouvé</p>
                             </td>
                         </tr>
                     @endforelse
                 </tbody>
             </table>
         </div>
     </div>
 </div>
@endif

<!-- Détail des mouvements pour un client spécifique -->
@if ($params['client_id'])
 <div class="row mb-4">
     <!-- Statistiques des modes de règlement et acomptes -->
     <div class="col-12">
         <div class="card shadow-smooth border-0">
             <div class="card-header bg-light py-3">
                 <h5 class="text-kadjiv mb-0 fw-bold">Répartition des règlements et acomptes par mode</h5>
             </div>
             <div class="card-body">
                 <div class="row g-4">
                     <!-- Règlements -->
                     <div class="col-md-6">
                         <h6 class="text-muted mb-3">Règlements</h6>
                         @foreach ($statistiques['par_mode'] as $mode => $montant)
                             @if ($mode !== 'acomptes' && $montant > 0)
                                 <div class="d-flex justify-content-between align-items-center mb-2">
                                     <span class="text-muted">{{ ucfirst($mode) }}</span>
                                     <span class="fw-bold">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
                                 </div>
                             @endif
                         @endforeach
                     </div>

                     <!-- Acomptes -->
                     <div class="col-md-6">
                         <h6 class="text-muted mb-3">Acomptes</h6>
                         @foreach ($statistiques['par_mode']['acomptes'] as $mode => $montant)
                             @if ($montant > 0)
                                 <div class="d-flex justify-content-between align-items-center mb-2">
                                     <span class="text-muted">{{ ucfirst($mode) }}</span>
                                     <span class="fw-bold">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
                                 </div>
                             @endif
                         @endforeach
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <div class="card shadow-smooth border-0">
     <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
         <h5 class="text-kadjiv mb-0 fw-bold">Détail des mouvements</h5>
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
                                 @switch($mouvement['type'])
                                     @case('SOLDE_INITIAL')
                                         <span class="badge bg-secondary">SOLDE INITIAL</span>
                                         @break
                                     @case('FACTURE')
                                         <span class="badge bg-danger">FACTURE</span>
                                         @break
                                     @case('REGLEMENT')
                                         <span class="badge bg-success">RÈGLEMENT</span>
                                         @break
                                     @case('ACOMPTE')
                                         <span class="badge bg-info">ACOMPTE</span>
                                         @break
                                 @endswitch
                             </td>
                             <td>
                                 {{ $mouvement['reference'] }}
                                 @if (isset($mouvement['mode']))
                                     <br><small class="text-muted">
                                         @switch($mouvement['mode'])
                                             @case('espece')
                                                 <i class="fas fa-money-bill-wave me-1"></i>Espèces
                                                 @break
                                             @case('cheque')
                                                 <i class="fas fa-money-check me-1"></i>Chèque
                                                 @break
                                             @case('virement')
                                                 <i class="fas fa-exchange-alt me-1"></i>Virement
                                                 @break
                                         @endswitch
                                         @if(isset($mouvement['observation']))
                                             <br>{{ $mouvement['observation'] }}
                                         @endif
                                     </small>
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
                                 <br>
                                 <small>{{ $solde > 0 ? 'Doit' : 'Avoir' }}</small>
                             </td>
                             <td>
                                 @if ($mouvement['type'] !== 'SOLDE_INITIAL')
                                     @switch($mouvement['type'])
                                         @case('FACTURE')
                                             <button type="button" class="btn btn-sm btn-light"
                                                 onclick="showFactureDetails({{ $mouvement['id'] }})">
                                                 <i class="fas fa-eye text-kadjiv"></i>
                                             </button>
                                             @break
                                         @case('REGLEMENT')
                                             <button type="button" class="btn btn-sm btn-light"
                                                 onclick="showReglementDetails({{ $mouvement['id'] }})">
                                                 <i class="fas fa-eye text-kadjiv"></i>
                                             </button>
                                             @break
                                         @case('ACOMPTE')
                                             <button type="button" class="btn btn-sm btn-light"
                                                 onclick="showAcompteDetails({{ $mouvement['id'] }})">
                                                 <i class="fas fa-eye text-kadjiv"></i>
                                             </button>
                                             @break
                                     @endswitch
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

<!-- Le reste du code reste inchangé -->

@endsection

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

/* Styles spécifiques pour les acomptes */
.badge.bg-info {
 background-color: #17a2b8 !important;
}

/* Styles pour l'impression */
@media print {
 .btn,
 .modal,
 .card-header {
     display: none !important;
 }

 .card {
     border: none !important;
     box-shadow: none !important;
 }

 .table-responsive {
     overflow: visible !important;
 }

 .no-print {
     display: none !important;
 }
}
</style>
@endpush

@push('scripts')
<script>
// ... Les fonctions existantes showFactureDetails et showReglementDetails restent inchangées ...

async function showAcompteDetails(id) {
 try {
     const response = await fetch(`${apiUrl}/api/acomptes-client/${id}`);
     const acompte = await response.json();

     const modalContent = `
         <div class="modal-header">
             <h5 class="modal-title">Détails Acompte ${acompte.reference}</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
             <div class="row">
                 <div class="col-md-6">
                     <h6>Informations Générales</h6>
                     <p>
                         Date: ${acompte.date}<br>
                         Client: ${acompte.client.raison_sociale}<br>
                         Statut: <span class="badge bg-${acompte.statut === 'valide' ? 'success' : 'warning'}">${acompte.statut.toUpperCase()}</span>
                     </p>
                 </div>
                 <div class="col-md-6 text-end">
                     <h6>Paiement</h6>
                     <p>
                         Mode: ${acompte.type_paiement}<br>
                         Montant: ${acompte.montant} FCFA
                     </p>
                 </div>
             </div>
             <div class="row">
                 <div class="col-12">
                     <h6>Observation</h6>
                     <p>${acompte.observation || '-'}</p>
                 </div>
             </div>
         </div>`;

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
