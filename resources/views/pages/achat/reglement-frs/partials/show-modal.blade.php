{{-- Modal de visualisation --}}
<div class="modal fade" id="detailReglementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg modal-dialog-scrollable" style="overflow-y: scroll!important;">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Détails du Règlement</h5>
                        <p class="text-muted small mb-0" id="reglementCode">Code : </p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    {{-- Section informations de la facture --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-file-invoice me-2"></i>Informations Facture
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium text-muted">Code Facture</label>
                                        <p class="mb-0" id="factureCode"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium text-muted">Fournisseur</label>
                                        <p class="mb-0" id="fournisseurNom"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section informations du règlement --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Détails du Règlement
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Date du règlement</label>
                                        <p class="mb-0" id="dateReglement"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Mode de règlement</label>
                                        <p class="mb-0"><span class="badge bg-soft-info text-info" id="modeReglementShow"></span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Référence</label>
                                        <p class="mb-0" id="referenceReglementShow"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Montant</label>
                                        <p class="mb-0 fw-bold text-primary" id="montantReglementShow"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Statut</label>
                                        <p class="mb-0" id="statutReglement"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Référence Document</label>
                                        <p class="mb-0" id="referenceDocumentShow"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section informations complémentaires --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-clipboard-list me-2"></i>Informations Complémentaires
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Créé par</label>
                                        <p class="mb-0" id="createdBy"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Date création</label>
                                        <p class="mb-0" id="createdAt"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium text-muted">Validé par</label>
                                        <p class="mb-0" id="validatedBy"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section commentaire --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-comment-alt me-2"></i>Commentaire
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0" id="commentaire"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="printReglement()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>


