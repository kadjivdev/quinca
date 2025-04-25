<div class="modal fade" id="addReferenceRecuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-cash-register fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Encaissement de vente</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addReferenceRecuForm" action="#" method="POST" class="needs-validation" novalidate>
                @csrf

                <div class="modal-body p-4">
                    {{-- Informations principales --}}
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="row g-3">

                                {{-- Reference Reçu --}}
                                <div class="col-12">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                        Reference Reçu
                                    </label>
                                    <div class="input-group">
                                        <input type="text"
                                               class="form-control text-end"
                                               name="reference_recu"
                                               id="reference_recu"
                                               placeholder="Saisissez la référence reçu">
                                        {{-- <span class="input-group-text">Reference</span> --}}
                                        <input id="facture_id" name="facture_id" type="hidden">
                                    </div>
                                    <div class="invalid-feedback">Veuillez saisir une reference</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4" id="saveReferenceRecuBtn">
                        <i class="fas fa-check me-2"></i>Valider
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
.modal-content {
    border-radius: 12px;
    overflow: hidden;
}

.modal-header {
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

.form-control {
    border-color: #e9ecef;
    padding: 0.6rem 0.875rem;
    font-size: 0.875rem;
    border-radius: 6px;
}

.form-control:focus {
    border-color: var(--kadjiv-orange);
    box-shadow: 0 0 0 0.25rem rgba(255, 165, 0, 0.25);
}

.form-control[readonly], .form-control:disabled {
    background-color: #f8f9fa;
}

/* Card in modal */
.modal .card {
    border-radius: 8px;
}

.modal .card.bg-light {
    background-color: #f8f9fa !important;
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

/* Icons in form labels */
.form-label i {
    color: var(--kadjiv-orange) !important;
    width: 20px;
    text-align: center;
}
</style>
