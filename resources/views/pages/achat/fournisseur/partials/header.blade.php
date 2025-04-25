{{-- pages/fournisseurs/partials/header.blade.php --}}
<div class="page-header">
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between">
            {{-- Section gauche --}}
            <div class="d-flex align-items-center gap-3">
                <div class="header-icon">
                    <i class="fas fa-users fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="header-pretitle">{{ $date }}</div>
                    <h6 class="header-title mb-0">Gestion des Fournisseurs</h6>
                </div>
            </div>

            {{-- Section droite --}}
            <div class="d-flex gap-2">
                <button type="button"
                    class="btn btn-dark btn-sm d-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#importFournisseurModal">
                    <i class="fas fa-file-import me-2"></i>
                    Importer
                </button>

                <div class="">
                    <input type="search" placeholder="Recherche de fournisseur ..." name="" class="form-control" id="searchFournisseur">
                </div>

                <button
                    type="button"
                    class="btn btn-primary btn-sm d-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#addFournisseurModal"
                >
                    <i class="fas fa-plus me-2"></i>
                    Nouveau Fournisseur
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    background: #fff;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.header-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 0.5rem;
}

.header-pretitle {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.header-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.btn-primary, .btn-dark {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    transition: all 0.15s ease-in-out;
}

.btn-primary:hover, .btn-dark:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-icon i {
    transition: transform 0.2s ease;
}

.header-icon:hover i {
    transform: scale(1.1);
}

@media (max-width: 576px) {
    .page-header {
        padding: 0.75rem 1rem;
    }

    .header-icon {
        width: 35px;
        height: 35px;
    }

    .header-title {
        font-size: 1rem;
    }

    .btn-primary, .btn-dark {
        padding: 0.4rem 0.75rem;
    }
}
</style>
