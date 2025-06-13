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
                            <h5 class="mb-0 fw-bold">Gestion des Acomptes</h5>
                            <div class="d-flex gap-2 align-items-center ms-3">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    {{ $acomptes->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->count() }} ce mois
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
                        data-bs-target="#addAcompteModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouvel Acompte
                </button>
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="row g-4">
            {{-- Total Acomptes --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded p-3">
                                    <i class="fas fa-receipt fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total Acomptes</h6>
                                <small class="text-muted">Tous les acomptes</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ $acomptes->count() }}</h4>
                            <small class="text-success">
                                <i class="fas fa-calendar-check me-1"></i>
                                {{ $acomptes->where('date', now()->format('Y-m-d'))->count() }} aujourd'hui
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Montant Total --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-success bg-opacity-10 text-success rounded p-3">
                                    <i class="fas fa-money-bill-alt fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Montant Total</h6>
                                <small class="text-muted">Tous les acomptes</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($acomptes->sum('montant'), 0, ',', ' ') }}</h4>
                            <small class="text-muted">FCFA</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Montant du Mois --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-warning bg-opacity-10 text-warning rounded p-3">
                                    <i class="fas fa-calendar-alt fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Montant du Mois</h6>
                                <small class="text-muted">Mois en cours</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">
                                {{ number_format($acomptes->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('montant'), 0, ',', ' ') }}
                            </h4>
                            <small class="text-warning">FCFA</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Montant du Jour --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info bg-opacity-10 text-info rounded p-3">
                                    <i class="fas fa-clock fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Montant du Jour</h6>
                                <small class="text-muted">Aujourd'hui</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">
                                {{ number_format($acomptes->where('date', now()->format('Y-m-d'))->sum('montant'), 0, ',', ' ') }}
                            </h4>
                            <small class="text-info">FCFA</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* On garde les mêmes styles que l'original mais on ajoute quelques spécificités pour les montants */
:root {
    --kadjiv-orange: #FFA500;
    --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
}

/* Style spécifique pour les montants */
.card h4 {
    font-family: 'Consolas', monospace;
    font-size: 1.5rem;
}

/* Animation pour les changements de montants */
@keyframes highlight {
    0% { background-color: var(--kadjiv-orange-light); }
    100% { background-color: transparent; }
}

.amount-change {
    animation: highlight 1s ease-out;
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
