<div class="page-header">
    <div class="container-fluid p-0">
        <div class="row align-items-center">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-money-bill-wave fs-4 text-warning"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="header-title mb-0">Règlements Fournisseurs</h6>
                            <span class="badge bg-soft-warning text-warning rounded-pill">
                                <i class="fas fa-money-check fs-xs me-1"></i>
                                {{ $nombreReglements }} règlement(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-warning btn-sm d-flex align-items-center" data-bs-toggle="modal"
                    data-bs-target="#addReglementModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau règlement
                </button>

                <button type="button" class="btn btn-light-secondary btn-sm d-flex align-items-center"
                    onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2 refresh-icon"></i>
                    <span class="refresh-text">Actualiser</span>
                </button>

                <div class="dropdown">
                    <button class="btn btn-light-warning btn-sm dropdown-toggle d-flex align-items-center"
                        type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>
                        Filtrer par
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('today')">Aujourd'hui</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('week')">Cette semaine</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByDate('month')">Ce mois</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByMode('ESPECE')">Espèces</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByMode('CHEQUE')">Chèques</a></li>
                        <li><a class="dropdown-item" href="#" onclick="filterByMode('VIREMENT')">Virements</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-3">
            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-primary">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Total Règlements</div>
                            <div class="stat-value">{{ $nombreReglements }}</div>
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
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Montant Total</div>
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
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Factures Payées</div>
                            <div class="stat-value">{{ $facturesPayees }}</div>
                            <div class="stat-trend text-warning">
                                <i class="fas fa-check-circle"></i> Total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
