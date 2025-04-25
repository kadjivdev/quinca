<div class="page-header">
    <div class="container-fluid p-0">
        <div class="row align-items-center">
            {{-- Section gauche --}}
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-file-invoice fs-4 text-primary"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="header-title mb-0">Factures Fournisseurs</h6>
                            <span class="badge bg-soft-primary text-primary rounded-pill">
                                <i class="fas fa-file-invoice fs-xs me-1"></i>
                                {{ $nombreFactures }} facture(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section droite avec les boutons d'action --}}
            <div class="col-auto d-flex gap-2">
                {{-- Bouton Nouvelle facture --}}
                <button type="button" class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal"
                    data-bs-target="#addFactureModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle facture
                </button>

                {{-- Bouton de synchronisation --}}
                <button type="button" class="btn btn-light-secondary btn-sm d-flex align-items-center"
                    onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2 refresh-icon"></i>
                    <span class="refresh-text">Actualiser</span>
                </button>

                {{-- Filtre dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-light-primary btn-sm dropdown-toggle d-flex align-items-center"
                        type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>
                        Filtrer par
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('today')">Aujourd'hui</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('week')">Cette semaine</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('month')">Ce mois</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('NON_LIVRE')">Non livrées</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('PARTIELLEMENT_LIVRE')">Partiellement livrées</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('LIVRE')">Livrées</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByPayment('NON_PAYE')">Non payées</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByPayment('PARTIELLEMENT_PAYE')">Partiellement payées</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByPayment('PAYE')">Payées</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Section statistiques rapides --}}
        <div class="row g-3 mt-3">
            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-primary">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Total Factures</div>
                            <div class="stat-value">{{ $nombreFactures }}</div>
                            <div class="stat-trend text-success">
                                <i class="fas fa-calendar"></i> Ce mois
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Montant Total TTC</div>
                            <div class="stat-value">{{ number_format($montantTotal, 2) }} FCFA</div>
                            <div class="stat-trend text-success">
                                <i class="fas fa-chart-line"></i> Cumulé
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-info">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Montant Moyen</div>
                            <div class="stat-value">{{ number_format($montantMoyen, 2) }} FCFA</div>
                            <div class="stat-trend text-info">
                                Par facture
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-danger">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Non Payées</div>
                            <div class="stat-value">{{ $facturesNonPayees }}</div>
                            <div class="stat-trend text-danger">
                                En attente
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Les mêmes styles que précédemment */
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

    .stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-size: 1.25rem;
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 600;
        line-height: 1.2;
        margin: 0.25rem 0;
    }

    .stat-label {
        font-size: 0.875rem;
        color: var(--bs-gray-600);
    }

    .stat-trend {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
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

    function filterByDate(period) {
        window.location.href = `/achat/factures?period=${period}`;
    }

    function filterByStatus(status) {
        window.location.href = `/achat/factures?status=${status}`;
    }

    function filterByPayment(payment) {
        window.location.href = `/achat/factures?payment=${payment}`;
    }
</script>
