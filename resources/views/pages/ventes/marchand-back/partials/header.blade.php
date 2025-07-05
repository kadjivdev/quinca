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
                            <h5 class="mb-0 fw-bold">Gestion des retour de marchandises</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <a href="{{route('vente.marchand-back.index')}}" class="btn btn-light px-3 d-inline-flex align-items-center">
                    <i class="fas fa-sync-alt me-2"></i>
                    Actualiser
                </a>

                <button type="button"
                    class="btn btn-primary px-3 d-inline-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#addMarchandModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouveau retour de marchandise
                </button>
            </div>
        </div>

        {{-- Cartes de statistiques --}}
        <div class="row g-4">
            <div class="col-xl-12 col-md-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <form action="{{route('vente.marchand-back.index')}}">
                            @csrf
                            <div class="d-flex align-items-baseline">
                                <input type="date" value="{{$date}}" required name="date" id="date" class="form-control">
                                <button type="submit" class="form-control w-50 btn btn-sm btn-primary mx-2"> <i class="fas fa-sync-alt me-2"></i> Filtrer par date</button>
                            </div>
                        </form>
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