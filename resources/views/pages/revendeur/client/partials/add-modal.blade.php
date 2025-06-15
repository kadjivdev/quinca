<div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg ">
        <div class="modal-content border-0 shadow-lg modal-dialog-scrollable" style="overflow-y: scroll!important;">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-user-plus fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau Client</h5>
                        <p class="text-muted small mb-0">Remplissez les informations du client</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addClientForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Informations principales --}}
                        <div class="col-md-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>Informations principales
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-medium required">Nom ou Raison sociale</label>
                                            <input type="text" class="form-control" name="raison_sociale"
                                                required placeholder="Nom du client ou de l'entreprise">
                                            <div class="invalid-feedback">La raison sociale est requise</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Catégorie</label>
                                            <select class="form-select" name="categorie" required>
                                                <option value="">Sélectionner une catégorie</option>
                                                <option value="comptoir">Client Comptoir</option>
                                                <option value="particulier">Particulier</option>
                                                <option value="professionnel">Professionnel</option>
                                                <option value="societe">Société</option>
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner une catégorie</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">IFU</label>
                                            <input type="text" class="form-control" name="ifu"
                                                placeholder="Numéro IFU">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">RCCM</label>
                                            <input type="text" class="form-control" name="rccm"
                                                placeholder="Numéro RCCM">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Taux AIB</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="taux_aib"
                                                placeholder="Taux AIB">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Contact et Adresse --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-address-card me-2"></i>Contact et Adresse
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Téléphone</label>
                                            <input type="tel" class="form-control" name="telephone"
                                                placeholder="Numéro de téléphone">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Email</label>
                                            <input type="email" class="form-control" name="email"
                                                placeholder="Adresse email">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Adresse</label>
                                            <textarea class="form-control" name="adresse" rows="2"
                                                placeholder="Adresse complète"></textarea>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Ville</label>
                                            <input type="text" class="form-control" name="ville"
                                                placeholder="Ville">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Paramètres de crédit --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-credit-card me-2"></i>Paramètres de crédit
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Plafond de crédit</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="plafond_credit"
                                                    min="0" step="0.001" value="0" placeholder="0.000">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Délai de paiement (jours)</label>
                                            <input type="number" class="form-control" name="delai_paiement"
                                                min="0" value="0" placeholder="0">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Solde initial</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="solde_initial"
                                                    step="0.001" value="0" placeholder="0.000">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes et Statut --}}
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-medium">Notes / Observations</label>
                                            <textarea class="form-control" name="notes" rows="2"
                                                placeholder="Notes ou observations éventuelles"></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Statut</label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox"
                                                    name="statut"
                                                    value="1"
                                                    id="statutSwitch"
                                                    checked>
                                                <label class="form-check-label" for="statutSwitch">
                                                    Client actif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
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
    :root {
        --kadjiv-orange: #FFA500;
        --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
    }

    /* Modal styles */
    /* .modal-content {
        border-radius: 12px;
        /* overflow: hidden; */
    }

    */ .modal-header {
        background: #fff !important;
    }

    .modal-header .bg-primary {
        background-color: var(--kadjiv-orange-light) !important;
    }

    .modal-header .text-primary {
        color: var(--kadjiv-orange) !important;
    }

    .modal-header .rounded-circle {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Form controls */
    .form-label {
        color: #2c3e50;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .form-label.required:after {
        content: " *";
        color: var(--kadjiv-orange);
    }

    .form-control,
    .form-select {
        border-color: #e9ecef;
        padding: 0.6rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--kadjiv-orange);
        box-shadow: 0 0 0 0.25rem rgba(255, 165, 0, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
    }

    /* Card styles */
    .modal .card {
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .modal .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
    }

    .modal .card.bg-light {
        background-color: #f8f9fa !important;
        border: none;
    }

    .card-subtitle {
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }

    .card-subtitle i {
        color: var(--kadjiv-orange);
    }

    /* Buttons */
    .modal .btn {
        padding: 0.5rem 1.25rem;
        font-weight: 500;
        border-radius: 6px;
    }

    .modal .btn-primary {
        background-color: var(--kadjiv-orange);
        border-color: var(--kadjiv-orange);
    }

    .modal .btn-primary:hover {
        background-color: #e69400;
        border-color: #e69400;
    }

    .modal .btn-light {
        background-color: #f8f9fa;
        border-color: #e9ecef;
    }

    /* Form switch */
    .form-switch .form-check-input {
        background-color: #e9ecef;
        border-color: #dee2e6;
        cursor: pointer;
    }

    .form-switch .form-check-input:checked {
        background-color: var(--kadjiv-orange);
        border-color: var(--kadjiv-orange);
    }

    .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 0.25rem rgba(255, 165, 0, 0.25);
    }

    /* Input validation styles */
    .was-validated .form-control:valid,
    .form-control.is-valid {
        border-color: #198754;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
    }

    .was-validated .form-control:invalid,
    .form-control.is-invalid {
        border-color: #dc3545;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    }

    /* Textarea styles */
    textarea.form-control {
        min-height: 60px;
        resize: vertical;
    }

    /* Number input styles */
    input[type="number"].form-control {
        text-align: right;
    }

    /* Modal footer */
    .modal-footer {
        border-top: 1px solid #e9ecef;
        background-color: #f8f9fa;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-body {
            padding: 1rem;
        }

        .row.g-4 {
            --bs-gutter-y: 1rem;
        }
    }

    /* Animation */
    .modal.show .modal-dialog {
        animation: modal-slide-down 0.3s ease-out;
    }

    @keyframes modal-slide-down {
        from {
            transform: translateY(-100px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>