<div class="page-header">
    <div class="container-fluid p-0">
        <div class="row align-items-center">
            {{-- Section gauche --}}
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-exchange-alt fs-4 text-warning"></i>
                        </div>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="header-title mb-0">Gestion des Conversions</h6>
                            <span class="badge bg-soft-warning text-warning rounded-pill">
                                <i class="fas fa-calculator me-1"></i>
                                {{ $conversions->count() }} conversion(s)
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section droite avec les boutons d'action --}}
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-light-secondary btn-sm d-flex align-items-center" onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2 refresh-icon"></i>
                    <span class="refresh-text">Actualiser</span>
                </button>

                <button type="button" class="btn btn-warning btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addConversionModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle Conversion
                </button>
            </div>
        </div>

        {{-- Section statistiques rapides --}}
        <div class="row g-3 mt-3">
            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Conversions Actives</div>
                            <div class="stat-value">{{ $conversionsActives }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-warning">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Par Article</div>
                            <div class="stat-value">{{ $conversionsParArticle }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="quick-stat-card">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-soft-info">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="ms-3">
                            <div class="stat-label">Générales</div>
                            <div class="stat-value">{{ $conversionsGenerales }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --adjiv-orange: #FF9B00;
    --adjiv-orange-rgb: 255, 155, 0;
}

.page-header {
    background: #fff;
    padding: 1.25rem 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.page-header:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.header-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background: linear-gradient(145deg, rgba(var(--adjiv-orange-rgb), 0.1), rgba(var(--adjiv-orange-rgb), 0.05));
    position: relative;
    overflow: hidden;
}

/* ... [Reste du CSS inchangé] ... */
</style>

<script>
// ... [Scripts inchangés] ...
</script>
