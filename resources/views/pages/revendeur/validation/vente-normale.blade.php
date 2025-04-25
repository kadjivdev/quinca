@extends('layouts.rapport.facture')
@section('title', 'Rapport de Session')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- En-tête avec filtres -->
    <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
        <h2 class="font-weight-bold text-dark">
            <i class="fas fa-cash-register me-2"></i>Rapport journalier
        </h2>
    </div>

    <!-- Sélecteur de session -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">Date
                        <i class="far fa-calendar-alt me-1"></i>Période
                    </label>
                    <div class="input-group">
                        <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
                        {{-- <span class="input-group-text bg-light">au</span>
                        <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}"> --}}
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($ventes->count() > 0)
        {{-- Cartes de statistiques --}}
        <div class="row g-3 mb-4">

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-stats bg-info bg-opacity-10">
                                    <i class="fas fa-file-invoice text-info"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title mb-0">Factures</h6>
                                <span class="text-muted small">Nombre total de ventes</span>
                            </div>
                        </div>
                        <h3 class="mb-0">{{count($ventes) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-stats bg-primary bg-opacity-10">
                                    <i class="fas fa-money-bill-wave text-success"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title mb-0">Total Ventes</h6>
                                <span class="text-muted small">Chiffre d'affaires global</span>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($ventes->sum('montant_ttc'), 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="avatar-stats bg-success bg-opacity-10">
                                    <i class="fas fa-shopping-cart text-primary"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="card-title mb-0">Nombre d'articles</h6>
                                <span class="text-muted small">Nombre total d'articles</span>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($totalLignes, 0, ',', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Détail des encaissements -->
    <div class="row">

        <!-- Liste des factures -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="row w-100">
                        <div class="col-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-invoice me-2"></i>Factures ({{ $ventes->count() }})
                            </h5>
                        </div>
                        @if ($ventes->count() > 0)
                            <div class="col-3">
                                <label class="form-label fw-medium required">Revendeur</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-user text-primary"></i>
                                    </span>
                                    <select class="form-select" name="client_id" required>
                                        <option value="">Sélectionner un revendeur</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                data-taux-aib="{{ $client->taux_aib }}">
                                                {{ $client->raison_sociale }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="invalid-feedback">Le client est requis</div>
                            </div>
                            <div class="col-3">
                                <label class="form-label fw-medium required">Type de règlement</label>
                                <select class="form-select" name="type_reglement" id="typeReglement" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="espece">Espèces</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="virement">Virement bancaire</option>
                                    <option value="carte_bancaire">Carte bancaire</option>
                                    <option value="MoMo">Mobile Money</option>
                                    <option value="Flooz">Flooz</option>
                                    <option value="Celtis_Pay">Celtis Pay</option>
                                </select>
                                <div class="invalid-feedback">Veuillez sélectionner un type de règlement</div>
                            </div>
                            <div class="col-3 text-end">
                                <button class="btn btn-sm btn-success" id="exportBtn" onclick="validateVente()">
                                    <i class="fas fa-check me-1"></i>Valider
                                </button>
                            </div>
                        @endif
                    </div>
                </div>                
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="facturesTable">
                            <thead>
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Date Facture</th>
                                    <th class="text-end">Montant TTC</th>
                                    {{-- <th class="text-end">Réglé</th>
                                    <th class="text-center">Statut</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventes as $vente)
                                <tr>
                                    <td>{{ $vente->numero }}</td>
                                    <td>{{ \Carbon\Carbon::parse($vente->date_facture)->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</td>
                                    <td class="text-end">{{ number_format($vente->montant_ttc, 0, ',', ' ') }} F</td>
                                    {{-- <td class="text-end">{{ number_format($vente->montant_regle, 0, ',', ' ') }} F</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $vente->est_solde ? 'success' : 'warning' }}">
                                            {{ $vente->est_solde ? 'Soldée' : 'En cours' }}
                                        </span>
                                    </td> --}}
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    $('#sessionForm').on('submit', function(e) {
        e.preventDefault();
        const sessionId = $('#session_id').val();
        window.location.href = sessionId
            ? `{{ route('vente.sessions.rapport.show', '') }}/${sessionId}`
            : `{{ route('vente.sessions.rapport') }}`;
    });
});

async function validateVente(){
    if($("[name='client_id']").val() == ''){
        Toast.fire({
            icon: 'error',
            title: 'Veuillez sélectionner un client'
        });
    }else if($("[name='type_reglement']").val() == ''){
        Toast.fire({
            icon: 'error',
            title: 'Veuillez sélectionner un type de règlement'
        });
    }else{
        try {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Voulez-vous valider les ventes de cette journée ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF9B00',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, modifier',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const payload = {
                            _method: 'PUT',
                            date_debut: document.querySelector('[name="date_debut"]').value,
                            client_id: document.querySelector('[name="client_id"]').value,
                            type_reglement: document.querySelector('[name="type_reglement"]').value,
                        };

                        const response = await fetch(`make-validation`, {
                            method: 'POST',
                            body: JSON.stringify(payload),
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        if (!data.success) {
                            throw new Error(data.message || 'Erreur lors de la modification');
                        }
                        return data;
                    } catch (error) {
                        Swal.showValidationMessage(
                            `Erreur: ${error.message}`
                        );
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed) {
                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.value.message || 'Statut modifié avec succès'
                });

                // Recharger la page après un court délai
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Une erreur est survenue lors de la validation du statut'
            });
        }
    }
}
</script>
@endpush
