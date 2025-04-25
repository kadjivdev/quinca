<div class="page-header mb-4">
    <div class="container-fluid p-0">
        {{-- En-tête principal --}}
        <div class="row align-items-center mb-4">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-users fs-4 text-warning"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Gestion des Clients</h5>
                            <div class="d-flex gap-2 align-items-center ms-3">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                    <i class="fas fa-check me-1"></i>
                                    {{ $clients->where('statut', true)->count() }} actifs
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="button"
                    class="btn btn-dark px-3 d-inline-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#importClientModal">
                    <i class="fas fa-file-import me-2"></i>
                    Importer
                </button>

                <button type="button" class="btn btn-light px-3 d-inline-flex align-items-center" onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2"></i>
                    Actualiser
                </button>
                @if(auth()->user()->can("vente.clients.create") || auth()->user()->can("revendeur.clients.create"))
                <button type="button"
                    class="btn btn-primary px-3 d-inline-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#addClientModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau Client
                </button>
                @endif
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="row g-4">
            {{-- Total Clients --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded p-3">
                                    <i class="fas fa-users fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total Clients</h6>
                                <small class="text-muted">Base clients</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ $clients->count() }}</h4>
                            <small class="text-success">
                                <i class="fas fa-plus me-1"></i>
                                {{ $clients->where('created_at', '>=', now()->startOfMonth())->count() }} ce mois
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Entreprises --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-success bg-opacity-10 text-success rounded p-3">
                                    <i class="fas fa-building fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Entreprises</h6>
                                <small class="text-muted">Professionnels</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ $clients->where('categorie', 'societe')->count() }}</h4>
                            <small class="text-muted">clients</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clients avec crédit --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-warning bg-opacity-10 text-warning rounded p-3">
                                    <i class="fas fa-credit-card fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Avec Crédit</h6>
                                <small class="text-muted">Plafond défini</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ $clients->where('plafond_credit', '>', 0)->count() }}</h4>
                            <small class="text-warning">clients</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Particuliers --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info bg-opacity-10 text-info rounded p-3">
                                    <i class="fas fa-user fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Particuliers</h6>
                                <small class="text-muted">Clients individuels</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ $clients->where('categorie', 'particulier')->count() }}</h4>
                            <small class="text-info">clients</small>
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
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
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

    /* Animation de rafraîchissement */
    @keyframes spin {
        100% {
            transform: rotate(360deg);
        }
    }

    .refresh-spinner {
        animation: spin 1s linear infinite;
    }

    /* Badges */
    .badge {
        padding: 0.5rem 0.75rem;
    }

    .badge.bg-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
        color: #198754 !important;
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