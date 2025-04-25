<div class="page-header mb-4">
    <div class="container-fluid p-0">
        {{-- En-tête principal --}}
        <div class="row align-items-center mb-4">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-file-invoice fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Gestion des Factures</h5>
                            <div class="d-flex gap-2 align-items-center ms-3">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i>
                                    {{ $factures->where('statut_reel', 'payee')->count() }} payées
                                </span>
                                @if($factures->where('statut', 'en_attente')->count() > 0)
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $factures->where('statut', 'en_attente')->count() }} en attente
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <a href="{{route('vente.facture.index')}}" type="button" class="btn btn-light px-3 d-inline-flex align-items-center" onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2"></i>
                    Actualiser
                </a>

                <button type="button"
                    class="btn btn-primary px-3 d-inline-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#addFactureModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle Facture
                </button>
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="row g-4">
            {{-- Total des factures --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-primary bg-opacity-10 text-primary rounded p-3">
                                    <i class="fas fa-file-invoice fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Total du Mois</h6>
                                <small class="text-muted">Factures émises</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($statsFactures['total_mois'] ?? 0, 0, ',', ' ') }} F</h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                {{ number_format($statsFactures['progression_mois'] ?? 0, 1) }}%
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total encaissé --}}
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
                                <h6 class="mb-0">Total Encaissé ({{$statsFactures['nombre_encaisse']}} / {{count($factures)}})</h6>
                                <small class="text-muted">Toutes factures</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($statsFactures['total_encaisse'] ?? 0, 0, ',', ' ') }} F</h4>
                            <small class="text-muted">Total cumulé</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Montants en attente --}}
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
                                <small class="text-muted">Non payées</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($statsFactures['montant_en_attente'] ?? 0, 0, ',', ' ') }} F</h4>
                            <small class="text-warning">
                                {{ count($statsFactures['factures_en_attente']) ?? 0 }} facture(s)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Performance --}}
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="stats-icon bg-info bg-opacity-10 text-info rounded p-3">
                                    <i class="fas fa-chart-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">Performance</h6>
                                <small class="text-muted">Taux de recouvrement</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-baseline">
                            <h4 class="mb-0 me-2">{{ number_format($statsFactures['taux_recouvrement'] ?? 0, 1) }}%</h4>
                            <small class="text-info">
                                Ce mois
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/header.css') }}" rel="stylesheet">

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

    /* Badges */
    .badge {
        padding: 0.5rem 0.75rem;
    }

    .badge.bg-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .badge.bg-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    /* Stats icons couleurs */
    .stats-icon.bg-primary {
        background-color: var(--kadjiv-orange-light) !important;
    }

    .stats-icon.text-primary {
        color: var(--kadjiv-orange) !important;
    }

    .stats-icon.bg-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .stats-icon.bg-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .stats-icon.bg-info {
        background-color: rgba(13, 202, 240, 0.1) !important;
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
    .refresh-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        100% {
            transform: rotate(360deg);
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

    // Fonction pour mettre à jour les statistiques
    function updateStats(stats) {
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