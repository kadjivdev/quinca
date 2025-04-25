{{-- header.blade.php --}}

{{-- Variables couleurs --}}
<style>
    :root {
        --kadjiv-primary: #FFA500;
        --kadjiv-dark: #000000;
        --kadjiv-light: #FFFFFF;
        --kadjiv-gray: #6c757d;
        --kadjiv-border: #e9ecef;
    }

    .page-header {
        padding: 1.5rem 0;
        background-color: var(--kadjiv-light);
        border-bottom: 1px solid var(--kadjiv-border);
        margin-bottom: 1.5rem;
    }

    .header-icon {
        position: relative;
        width: 48px;
        height: 48px;
    }

    .icon-wrapper {
        position: absolute;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 165, 0, 0.1);
        border-radius: 12px;
        z-index: 1;
    }

    .icon-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 165, 0, 0.1);
        border-radius: 12px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0.8;
        }

        70% {
            transform: scale(1.1);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 0;
        }
    }

    .header-pretitle {
        font-size: 0.875rem;
        color: var(--kadjiv-gray);
        margin-bottom: 0.25rem;
    }

    .header-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--kadjiv-dark);
    }

    .quick-stat-card {
        background-color: var(--kadjiv-light);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        min-width: 240px;
        border-left: 4px solid var(--kadjiv-primary);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.25rem;
        background-color: rgba(255, 165, 0, 0.1);
        color: var(--kadjiv-primary);
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--kadjiv-gray);
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--kadjiv-dark);
        margin-bottom: 0.25rem;
    }

    .stat-trend {
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .btn-kadjiv {
        background-color: var(--kadjiv-primary);
        border-color: var(--kadjiv-primary);
        color: var(--kadjiv-light);
    }

    .btn-kadjiv:hover {
        background-color: #e69400;
        border-color: #e69400;
        color: var(--kadjiv-light);
    }

    .btn-light-kadjiv {
        background-color: rgba(255, 165, 0, 0.1);
        border: none;
        color: var(--kadjiv-primary);
    }

    .btn-light-kadjiv:hover {
        background-color: rgba(255, 165, 0, 0.2);
        color: var(--kadjiv-primary);
    }

    .dropdown-menu {
        border-radius: 8px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: none;
        padding: 0.5rem;
    }

    .dropdown-item {
        border-radius: 6px;
        padding: 0.5rem 1rem;
    }

    .dropdown-item:hover {
        background-color: rgba(255, 165, 0, 0.1);
        color: var(--kadjiv-primary);
    }

    .badge {
        padding: 0.5em 0.9em;
        font-weight: 500;
    }

    .fs-xs {
        font-size: 0.75rem;
    }

    .refresh-icon {
        transition: transform 0.3s ease;
    }

    .btn:hover .refresh-icon {
        transform: rotate(180deg);
    }
</style>

{{-- Header Content --}}
<div class="page-header">
    <div class="container-fluid p-2">
        <div class="row align-items-center">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-truck fs-4" style="color: var(--kadjiv-primary)"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ now()->format('d/m/Y') }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="header-title mb-0">Bons de Livraison Fournisseurs</h6>
                            <span class="badge"
                                style="background-color: rgba(255, 165, 0, 0.1); color: var(--kadjiv-primary)">
                                <i class="fas fa-clipboard-list fs-xs me-1"></i>
                                {{ count($livraisons)}} bon(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-kadjiv btn-sm d-flex align-items-center" data-bs-toggle="modal"
                    data-bs-target="#addLivraisonFournisseurModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau bon de livraison
                </button>

                <button type="button" class="btn btn-light-kadjiv btn-sm d-flex align-items-center"
                    onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2 refresh-icon"></i>
                    <span class="refresh-text">Actualiser</span>
                </button>

                <div class="dropdown">
                    <button class="btn btn-light-kadjiv btn-sm dropdown-toggle d-flex align-items-center" type="button"
                        data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>
                        Filtrer par
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('all')">Tous les bons</a>
                        </li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('pending')">En attente</a>
                        </li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('validated')">Validés</a>
                        </li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('rejected')">Rejetés</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('today')">Aujourd'hui</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('week')">Cette semaine</a>
                        </li>
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('month')">Ce mois</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Total Livraisons</div>
                            <div class="stat-value">{{ count($livraisons) }}</div>
                            <div class="stat-trend" style="color: var(--kadjiv-primary)">
                                <i class="fas fa-calendar"></i> Global
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Livraisons Validées</div>
                            <div class="stat-value">{{ $livraisons->where('validated_at', '!=', null)->count() }}</div>
                            <div class="stat-trend" style="color: var(--kadjiv-primary)">
                                <i class="fas fa-chart-line"></i> En stock
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">En Attente</div>
                            <div class="stat-value">
                                {{ $livraisons->whereNull('validated_at')->whereNull('rejected_at')->count() }}
                            </div>
                            <div class="stat-trend" style="color: var(--kadjiv-primary)">
                                <i class="fas fa-hourglass-half"></i> À traiter
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Magasins Actifs</div>
                            <div class="stat-value">{{ $depots->count() }}</div>
                            <div class="stat-trend" style="color: var(--kadjiv-primary)">
                                <i class="fas fa-warehouse"></i> Disponibles
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>