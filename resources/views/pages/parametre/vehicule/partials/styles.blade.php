<style>
    /* Styles pour les cartes */
    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* États de fond */
    .bg-soft-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .bg-soft-danger {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    /* Icône de magasin */
    .depot-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(13, 110, 253, 0.1);
        border-radius: 10px;
    }

    /* Style pour les statistiques */
    .stat-item {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background-color: rgba(0, 0, 0, 0.03);
        transition: background-color 0.3s ease;
    }

    .stat-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    /* Styles pour les formulaires */
    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }

    .form-control,
    .form-select {
        padding: 0.6rem 1rem;
        border-radius: 0.5rem;
        border-color: #dee2e6;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Styles pour les modals */
    .modal-content {
        border-radius: 1rem;
        overflow: hidden;
    }

    .modal-header,
    .modal-footer {
        padding: 1rem 1.5rem;
    }

    /* Boutons et contrôles */
    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* Animation du modal */
    .modal.fade .modal-dialog {
        transform: scale(0.95);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: scale(1);
    }

    /* État vide */
    .empty-state {
        padding: 2rem;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.03);
        border-radius: 50%;
    }

    /* Styles pour le menu déroulant */
    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .dropdown-item i {
        width: 16px;
    }

    /* Utilitaires */
    .fs-xs {
        font-size: 0.65rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .stat-item {
            padding: 0.4rem 0.8rem;
        }
    }
</style>
