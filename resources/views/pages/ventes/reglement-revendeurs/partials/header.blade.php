<div class="page-header mb-4">
    <div class="container-fluid p-0">
        {{-- En-tête principal --}}
        <div class="row align-items-center mb-4">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Gestion des Règlements</h5>
                            <div class="d-flex gap-2 align-items-center ms-3">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $statsReglements['reglements_valides'] ?? $reglements->where('statut', 'valide')->count() }} validés
                                </span>
                                @if($statsReglements['reglements_en_attente'] ?? $reglements->where('statut', 'brouillon')->count() > 0)
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $statsReglements['reglements_en_attente'] ?? $reglements->where('statut', 'brouillon')->count() }} en attente
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-light px-3 d-inline-flex align-items-center" onclick="refreshList()">
                    <i class="fas fa-sync-alt me-2"></i>
                    Actualiser
                </button>

                <button type="button"
                        class="btn btn-primary px-3 d-inline-flex align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target="#addReglementModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau Règlement
                </button>
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="row g-4">
            {{-- Total des règlements du mois --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded p-3">
                                    <i class="fas fa-calendar-alt fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total du Mois</h6>
                                <small class="text-muted">Règlements validés</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($statsReglements['total_mois'] ?? 0, 0, ',', ' ') }} F</h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                {{ number_format($statsReglements['progression_mois'] ?? 0, 1) }}%
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Montant total des règlements --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-success bg-opacity-10 text-success rounded p-3">
                                    <i class="fas fa-hand-holding-usd fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total Encaissé</h6>
                                <small class="text-muted">Tous règlements</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($statsReglements['total_reglements'] ?? 0, 0, ',', ' ') }} F</h4>
                            <small class="text-muted">Total cumulé</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Règlements en attente --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-warning bg-opacity-10 text-warning rounded p-3">
                                    <i class="fas fa-clock fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">En Attente</h6>
                                <small class="text-muted">À valider</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($statsReglements['montant_en_attente'] ?? 0, 0, ',', ' ') }} F</h4>
                            <small class="text-warning">
                                {{ $statsReglements['reglements_en_attente'] ?? 0 }} règlement(s)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Répartition des modes de paiement --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info bg-opacity-10 text-info rounded p-3">
                                    <i class="fas fa-chart-pie fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Mode Principal</h6>
                                <small class="text-muted">Plus utilisé</small>
                            </div>
                        </div>
                        @php
                            $modesPaiement = $statsReglements['repartition_modes'] ?? collect();
                            $modePrincipal = $modesPaiement->sortByDesc('total')->first();
                        @endphp
                        @if($modePrincipal)
                            <div class="d-flex align-items-baseline">
                                <h4 class="mb-0 me-2 text-capitalize">{{ str_replace('_', ' ', $modePrincipal->type_reglement) }}</h4>
                                <small class="text-info">
                                    {{ number_format(($modePrincipal->total / $statsReglements['total_reglements'] * 100), 1) }}% du total
                                </small>
                            </div>
                        @else
                            <div class="text-muted">Aucune donnée</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/header.css') }}" rel="stylesheet">

<style>
.page-header {
    margin-bottom: 2rem;
}

.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon .icon-wrapper {
    transition: transform 0.3s ease;
}

.header-icon:hover .icon-wrapper {
    transform: scale(1.1);
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
}

.badge {
    padding: 0.5rem 0.75rem;
}

.btn {
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn i {
    transition: transform 0.3s ease;
}

.btn:active i {
    transform: scale(0.9);
}
</style>

<script>
// Animation de rafraîchissement
function refreshList() {
    const icon = document.querySelector('.fa-sync-alt');
    icon.classList.add('fa-spin');

    // Appel AJAX pour rafraîchir les données
    $.ajax({
        url: '{{ route("vente.reglement.refresh") }}',
        type: 'GET',
        success: function(response) {
            // Mettre à jour le contenu
            $('#reglementsList').html(response.html);

            // Mettre à jour les statistiques
            updateStats(response.stats);

            // Notification
            Toast.fire({
                icon: 'success',
                title: 'Liste actualisée'
            });
        },
        error: function() {
            Toast.fire({
                icon: 'error',
                title: 'Erreur lors de l\'actualisation'
            });
        },
        complete: function() {
            // Arrêter l'animation après un délai
            setTimeout(() => {
                icon.classList.remove('fa-spin');
            }, 500);
        }
    });
}

// Fonction pour mettre à jour les statistiques
function updateStats(stats) {
    // Mettre à jour chaque statistique
    Object.keys(stats).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            if (key.includes('montant')) {
                element.textContent = new Intl.NumberFormat('fr-FR').format(stats[key]) + ' F';
            } else {
                element.textContent = stats[key];
            }
        }
    });
}
</script>
