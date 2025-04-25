<!-- Modal des détails du règlement -->
<div class="modal fade" id="showReglementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="fas fa-receipt text-primary me-2"></i>
                    Détails du règlement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4">
                <div id="loaderContainer" class="py-5 text-center">
                    <div class="loader-container">
                        <div class="spinner-pulse"></div>
                        <div class="mt-3 text-muted">Chargement des données...</div>
                    </div>
                </div>

                <div id="reglementDetails" class="d-none">
                    <!-- Numéro et Date -->
                    <div class="text-center mb-4">
                        <div class="numero-recu mb-1" id="showNumero"></div>
                        <div class="text-muted small" id="showDate"></div>
                    </div>

                    <!-- Client et Facture -->
                    <div class="client-info mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="client-avatar me-3" id="showClientAvatar"></div>
                            <div>
                                <div class="fw-bold" id="showClient"></div>
                                <div class="text-muted small" id="showContact"></div>
                            </div>
                        </div>
                        <div class="facture-info bg-light rounded p-2 small">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Facture N°:</span>
                                <span class="fw-medium" id="showFacture"></span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted">Date facture:</span>
                                <span id="showDateFacture"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Détails Paiement -->
                    <div class="payment-details bg-white rounded shadow-sm p-3 mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-money-bill-wave text-success me-2"></i>
                            Détails du paiement
                        </h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Mode de paiement</div>
                                <div class="fw-medium" id="showMode"></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Montant</div>
                                <div class="fw-bold text-success" id="showMontant"></div>
                            </div>
                            <div class="col-12" id="referenceBlock">
                                <div class="text-muted small">Référence</div>
                                <div class="fw-medium" id="showReference"></div>
                            </div>
                            <div class="col-12" id="banqueBlock">
                                <div class="text-muted small">Banque</div>
                                <div class="fw-medium" id="showBanque"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Statut et Infos -->
                    <div class="status-info bg-light rounded p-3">
                        <div class="mb-2">
                            <div class="text-muted small mb-1">Statut</div>
                            <div id="showStatut"></div>
                        </div>
                        <div class="border-top pt-2 mt-2">
                            <div class="text-muted small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Créé par:</span>
                                    <span class="fw-medium" id="showCreatedBy"></span>
                                </div>
                                <div class="d-flex justify-content-between" id="validatedByBlock">
                                    <span>Validé par:</span>
                                    <span class="fw-medium" id="showValidatedBy"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="button" class="btn btn-primary" onclick="printReglement(reglementId)">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/modal.css') }}" rel="stylesheet">

<style>
/* Styles pour le modal */
.numero-recu {
    font-family: 'Monaco', 'Consolas', monospace;
    color: var(--bs-primary);
    font-size: 1.2rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    border-radius: 0.5rem;
    display: inline-block;
}

.client-avatar {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background-color: var(--bs-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
}

.payment-details {
    border: 1px solid rgba(0, 0, 0, 0.05);
}

/* Loader styles */
.spinner-pulse {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--bs-primary);
    margin: 0 auto;
    animation: pulse 1.2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(0);
        opacity: 1;
    }
    100% {
        transform: scale(1);
        opacity: 0;
    }
}
</style>
