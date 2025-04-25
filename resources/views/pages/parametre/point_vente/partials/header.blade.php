{{-- En-tête de la page --}}
<div class="page-header">
    <div class="container-fluid p-0">
        <div class="d-flex align-items-center justify-content-between">
            {{-- Section gauche --}}
            <div class="d-flex align-items-center gap-3">
                <div class="header-icon">
                    <i class="fas fa-warehouse fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="header-pretitle">{{ $date }}</div>
                    <h6 class="header-title mb-0">Gestion des Points de Ventes</h6>
                </div>
            </div>

            {{-- Section droite --}}
            <button
                type="button"
                class="btn btn-warning btn-sm d-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#addPointVenteModal"
            >
                <i class="fas fa-plus me-2"></i>
                Nouveau Point de Vente
            </button>
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
    background-color: rgba(var(--adjiv-orange-rgb), 0.1);
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

.btn-warning {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    transition: all 0.15s ease-in-out;
    background-color: var(--adjiv-orange);
    border-color: var(--adjiv-orange);
    color: white;
}

.btn-warning:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    background-color: #e68a00;
    border-color: #e68a00;
    color: white;
}

/* Animation subtile pour l'icône */
.header-icon i {
    transition: transform 0.2s ease;
}

.header-icon:hover i {
    transform: scale(1.1);
}

/* Responsive adjustments */
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

    .btn-warning {
        padding: 0.4rem 0.75rem;
    }
}
</style>
