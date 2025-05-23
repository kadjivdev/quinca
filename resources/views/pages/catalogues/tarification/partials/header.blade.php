<div class="page-header">
    <div class="container-fluid p-0">
        <div class="row align-items-center">
            {{-- Section gauche --}}
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-tags fs-4 text-primary"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="header-title mb-0">Gestion des Tarifications</h6>
                            <span class="badge bg-soft-success text-success rounded-pill">
                                <i class="fas fa-coins fs-xs me-1"></i>
                                {{ $tarifications->where('statut', true)->count() }} tarif(s) actif(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section droite avec les boutons d'action --}}
            <div class="col-auto d-flex gap-2">
                {{-- Bouton de synchronisation --}}
                <button
                    type="button"
                    class="btn btn-light-secondary btn-sm d-flex align-items-center"
                    onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2 refresh-icon"></i>
                    <span class="refresh-text">Actualiser</span>
                </button>

                {{-- Bouton d'ajout avec modal --}}
                @can("tarification.create")
                <button
                    type="button"
                    class="btn btn-primary btn-sm d-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#addTarificationModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau Tarif
                </button>
                @endcan
            </div>
        </div>

        {{-- Section statistiques rapides --}}
        <div class="row g-3 mt-3">
            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-primary">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Total Tarifs</div>
                            <div class="stat-value">{{ $tarifications->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Tarifs Actifs</div>
                            <div class="stat-value">{{ $tarifications->where('statut', true)->count() }}</div>
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
                            <div class="stat-label">Tarifs Inactifs</div>
                            <div class="stat-value">{{ $tarifications->where('statut', false)->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-info">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Articles Tarifés</div>
                            <div class="stat-value">{{ $tarifications->pluck('article_id')->unique()->count() }}</div>
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
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .header-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .quick-stat-card {
        background: #fff;
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
        min-width: 200px;
    }

    .quick-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.75rem;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .quick-stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(10deg);
    }

    .stat-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
        letter-spacing: -0.02em;
    }

    .btn-light-secondary {
        background: rgba(108, 117, 125, 0.1);
        border: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-light-secondary:hover {
        background: rgba(108, 117, 125, 0.2);
        transform: translateY(-1px);
    }

    .btn-primary {
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(var(--bs-primary-rgb), 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(var(--bs-primary-rgb), 0.3);
    }

    /* Animation pour l'icône de rafraîchissement */
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .refreshing .refresh-icon {
        animation: spin 1s linear infinite;
    }

    /* Background soft colors */
    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    .bg-soft-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .bg-soft-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-soft-info {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .page-header {
            padding: 1rem;
        }

        .quick-stat-card {
            min-width: 100%;
        }
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