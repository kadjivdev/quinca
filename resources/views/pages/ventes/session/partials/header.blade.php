<div class="page-header mb-4">
    <div class="container-fluid p-0">
        {{-- En-tête principal --}}
        <div class="row align-items-center mb-4">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-cash-register fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Gestion des Sessions de Caisse</h5>
                            <div class="d-flex gap-2 align-items-center ms-3">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                    <i class="fas fa-door-open me-1"></i>
                                    {{ $sessions->where('statut', 'ouverte')->count() }} session(s) ouverte(s)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-light px-3 d-inline-flex align-items-center" onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2"></i>
                    Actualiser
                </button>

                <button type="button"
                        class="btn btn-primary px-3 d-inline-flex align-items-center"
                        data-bs-toggle="modal"
                        data-bs-target="#addSessionCaisseModal"
                        {{ $hasSessionOuverte ? 'disabled' : '' }}>
                    <i class="fas fa-plus me-2"></i>
                    Ouvrir une Session
                </button>
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="row g-4">
            {{-- Total des sessions --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded p-3">
                                    <i class="fas fa-cash-register fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total Sessions</h6>
                                <small class="text-muted">Aujourd'hui</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ $sessions->count() }}</h4>
                            <small class="text-muted">sessions</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total encaissements --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-success bg-opacity-10 text-success rounded p-3">
                                    <i class="fas fa-money-bill-wave fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total Encaissements</h6>
                                <small class="text-muted">Ce mois</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($totalEncaissements, 0, ',', ' ') }} F</h4>
                            <small class="text-success">
                                <i class="fas fa-chart-line me-1"></i>
                                Mensuel
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sessions ouvertes --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-warning bg-opacity-10 text-warning rounded p-3">
                                    <i class="fas fa-door-open fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Sessions Ouvertes</h6>
                                <small class="text-muted">En cours</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ $sessions->where('statut', 'ouverte')->count() }}</h4>
                            <small class="text-warning">
                                <i class="fas fa-clock me-1"></i>
                                Active(s)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Écart moyen --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info bg-opacity-10 text-info rounded p-3">
                                    <i class="fas fa-balance-scale fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Écart Moyen</h6>
                                <small class="text-muted">Moyenne mensuelle</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($ecartMoyen, 0, ',', ' ') }} F</h4>
                            <small class="text-info">
                                <i class="fas fa-calculator me-1"></i>
                                Mensuel
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --kadjiv-orange: #FFA500;
    --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
}

.page-header {
    margin-bottom: 2rem;
}

/* Icônes et badges */
.stats-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon .icon-wrapper {
    background-color: var(--kadjiv-orange-light) !important;
    transition: transform 0.3s ease;
}

.header-icon .icon-wrapper i {
    color: var(--kadjiv-orange) !important;
}

.header-icon:hover .icon-wrapper {
    transform: scale(1.1);
}

/* Cartes */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
}

/* Stats icons couleurs */
.stats-icon.bg-primary {
    background-color: var(--kadjiv-orange-light) !important;
}

.stats-icon.text-primary {
    color: var(--kadjiv-orange) !important;
}

/* Boutons */
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

.btn-primary {
    background-color: var(--kadjiv-orange) !important;
    border-color: var(--kadjiv-orange) !important;
}

.btn-primary:hover {
    background-color: #e69400 !important;
    border-color: #e69400 !important;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Badges */
.badge {
    padding: 0.5rem 0.75rem;
}

.badge.bg-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
    color: #198754 !important;
}

/* Animation de rafraîchissement */
@keyframes spin {
    100% {
        transform: rotate(360deg);
    }
}

.refresh-spinner {
    animation: spin 1s linear infinite;
}
</style>

<script>
function refreshPage() {
    const icon = document.querySelector('.fa-sync-alt');
    icon.classList.add('refresh-spinner');

    setTimeout(() => {
        window.location.reload();
    }, 500);
}
</script>
