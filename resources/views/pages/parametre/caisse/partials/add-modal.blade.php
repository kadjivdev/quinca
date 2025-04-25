<!-- Modal d'ajout de caisse -->
<div class="modal fade" id="addCaisseModal" tabindex="-1" aria-labelledby="addCaisseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <!-- En-tête du modal -->
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cash-register fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold" id="addCaisseModalLabel">Nouvelle Caisse</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Corps du modal -->
            <form action="{{ route('caisse.store') }}" method="POST" id="caisseForm" class="needs-validation"
                novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Code Caisse -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Code Caisse</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">CA-</span>
                                <input type="text" class="form-control bg-light" name="code_caisse" readonly>
                            </div>
                            <small class="text-muted">Le code ne peut pas être modifié</small>
                        </div>

                        <!-- Libellé -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Libellé</label>
                            <input type="text" class="form-control" name="libelle" required minlength="3"
                                maxlength="100" placeholder="Ex: Caisse Principale">
                            <div class="invalid-feedback">
                                Le libellé est requis (3 à 100 caractères).
                            </div>
                        </div>

                        <!-- Point de vente associé -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Point de Vente</label>
                            <select class="form-select" name="point_de_vente_id" required>
                                <option value="">Sélectionnez un point de vente</option>
                                @foreach ($pointsVente ?? [] as $pv)
                                    <option value="{{ $pv->id }}">{{ $pv->nom_pv }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un point de vente.
                            </div>
                        </div>

                        <!-- Statut -->
                        <!-- Statut -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Statut</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif" id="statusSwitch"
                                    value="1" checked>
                                <label class="form-check-label" for="statusSwitch">
                                    Caisse active
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
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
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

    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .invalid-feedback {
        font-size: 0.812rem;
    }
</style>
