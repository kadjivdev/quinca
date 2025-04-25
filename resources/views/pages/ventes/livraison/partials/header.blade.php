<div class="page-header">
    <div class="container-fluid p-0">
        <div class="row align-items-center">
            {{-- Section gauche --}}
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-truck fs-4 text-warning"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="header-title mb-0">Gestion des Bons de Livraison</h6>
                            <span class="badge bg-soft-success text-success rounded-pill">
                                <i class="fas fa-check fs-xs me-1"></i>
                                {{ $livraisons->where('statut', 'valide')->count() }} livraison(s) validée(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section droite avec les boutons d'action --}}
            <div class="col-auto d-flex gap-2">
                {{-- Bouton de synchronisation --}}
                <button type="button" class="btn btn-light-secondary btn-sm d-flex align-items-center"
                    onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2 refresh-icon"></i>
                    <span class="refresh-text">Actualiser</span>
                </button>

                {{-- Bouton d'ajout avec modal --}}
                <button type="button" class="btn btn-warning btn-sm d-flex align-items-center" data-bs-toggle="modal"
                    data-bs-target="#addLivraisonModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau Bon de Livraison
                </button>
            </div>
        </div>

        {{-- Section statistiques rapides --}}
        <div class="row g-3 mt-3">
            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-primary">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Total Livraisons</div>
                            <div class="stat-value">{{ $livraisons->count() }}</div>
                            <div class="stat-trend text-primary">
                                <span class="fw-medium">{{ $livraisons->where('statut', 'brouillon')->count() }}</span>
                                en attente
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-success">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Articles Livrés</div>
                            <div class="stat-value">{{ $totalArticlesLivres ?? 0 }}</div>
                            <div class="stat-trend text-muted">
                                Ce mois
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">En Attente</div>
                            <div class="stat-value">{{ $livraisons->where('statut', 'brouillon')->count() }}</div>
                            <div class="stat-trend text-warning">
                                À traiter aujourd'hui
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-info">
                            <i class="fas fa-warehouse"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Magasins Actifs</div>
                            <div class="stat-value">{{ $depots->count() }}</div>
                            <div class="stat-trend text-info">
                                Points de livraison
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        background: #fff;
        padding: 1.25rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        background: linear-gradient(145deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-primary-rgb), 0.05));
        position: relative;
    }

    .icon-wrapper {
        position: relative;
        z-index: 2;
        transition: transform 0.3s ease;
    }

    .header-icon:hover .icon-wrapper {
        transform: scale(1.15) rotate(15deg);
    }

    .icon-pulse {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0) 70%);
        border-radius: inherit;
        animation: pulse 2s infinite;
        z-index: 1;
    }

    .stat-trend {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-size: 1.5rem;
    }

    .bg-soft-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }

    .bg-soft-success {
        background-color: rgba(var(--bs-success-rgb), 0.1);
        color: var(--bs-success);
    }

    .bg-soft-warning {
        background-color: rgba(var(--bs-warning-rgb), 0.1);
        color: var(--bs-warning);
    }

    .bg-soft-info {
        background-color: rgba(var(--bs-info-rgb), 0.1);
        color: var(--bs-info);
    }

    .quick-stat-card {
        background: #fff;
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
        min-width: 240px;
    }

    .quick-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    @keyframes pulse {
        0% {
            transform: translate(-50%, -50%) scale(0.95);
            opacity: 1;
        }

        100% {
            transform: translate(-50%, -50%) scale(1.5);
            opacity: 0;
        }
    }

    .header-pretitle {
        font-size: 0.8125rem;
        color: var(--bs-gray-600);
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--bs-gray-800);
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--bs-gray-600);
        margin-bottom: 0.25rem;
    }
</style>

<script>
    function refreshPage() {
        const refreshBtn = document.querySelector('.btn-light-secondary');
        refreshBtn.classList.add('refreshing');
        refreshBtn.disabled = true;

        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
</script>
