<!-- Modal d'ajout de point de vente -->
<div class="modal fade" id="addPointVenteModal" tabindex="-1" aria-labelledby="addPointVenteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <!-- En-tête du modal -->
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-store fs-4 text-warning me-2"></i>
                    <h5 class="modal-title fw-bold" id="addPointVenteModalLabel">Nouveau Point de Vente</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Corps du modal -->
            <form action="{{ route('point-vente.store') }}" method="POST" id="pointVenteForm" class="needs-validation"
                novalidate>
                @csrf
                <div class="modal-body p-4">
                    <!-- Informations principales -->
                    <div class="row g-4">
                        <!-- Code PV -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Code Point de Vente</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">PV-</span>
                                <input type="text" class="form-control" name="code_pv" pattern="[A-Z0-9]{6}"
                                    placeholder="AUTO123" required maxlength="6">
                                <div class="invalid-feedback">
                                    Le code doit contenir 6 caractères alphanumériques.
                                </div>
                            </div>
                            <small class="text-muted">Format: 6 caractères majuscules ou chiffres</small>
                        </div>

                        <!-- Nom PV -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Nom du Point de Vente</label>
                            <input type="text" class="form-control" name="nom_pv" required minlength="3"
                                maxlength="100" placeholder="Ex: Point de Vente Central">
                            <div class="invalid-feedback">
                                Le nom du point de vente est requis (3 à 100 caractères).
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div class="col-12">
                            <label class="form-label fw-semibold required">Adresse</label>
                            <textarea class="form-control" name="adresse_pv" rows="3"
                                placeholder="Entrez l'adresse complète du point de vente" required></textarea>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Statut</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif" id="statusSwitch"
                                    checked>
                                <label class="form-check-label" for="statusSwitch">
                                    Point de vente actif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pied du modal -->
                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    :root {
        --adjiv-orange: #FF9B00;
    }

    /* Styles personnalisés pour le modal */
    .modal-content {
        border-radius: 1rem;
    }

    .modal-header,
    .modal-footer {
        padding: 1rem 1.5rem;
    }

    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }

    .form-control,
    .form-select {
        padding: 0.6rem 1rem;
        border-radius: 0.5rem;
        border-color: #dee2e6;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--adjiv-orange);
        box-shadow: 0 0 0 0.25rem rgba(255, 155, 0, 0.25);
    }

    .input-group-text {
        border-top-left-radius: 0.5rem;
        border-bottom-left-radius: 0.5rem;
    }

    .form-check-input {
        width: 3em;
        height: 1.5em;
        margin-top: 0.25em;
        cursor: pointer;
    }

    .form-check-input:checked {
        background-color: var(--adjiv-orange);
        border-color: var(--adjiv-orange);
    }

    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .btn-warning {
        background-color: var(--adjiv-orange);
        border-color: var(--adjiv-orange);
        color: white;
    }

    .btn-warning:hover {
        background-color: #e68a00;
        border-color: #e68a00;
        color: white;
    }

    .invalid-feedback {
        font-size: 0.812rem;
    }

    /* Animation du modal */
    .modal.fade .modal-dialog {
        transform: scale(0.95);
        transition: transform 0.3s ease-out;
    }

    .modal.show .modal-dialog {
        transform: scale(1);
    }
</style>
