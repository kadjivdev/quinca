<div class="modal fade" id="showLivraisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-truck fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Détails du Bon de Livraison</h5>
                        <p class="text-muted small mb-0" id="blNumero"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- En-tête des informations -->
                <div class="row g-4 mb-4">
                    <!-- Informations de la facture -->
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-file-invoice me-2"></i>Informations Facture
                                </h6>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="text-muted small mb-1">N° Facture</label>
                                        <div class="fw-medium" id="factureNumero"></div>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small mb-1">Date Facture</label>
                                        <div class="fw-medium" id="factureDate"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations client -->
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-user me-2"></i>Client
                                </h6>
                                <div class="fw-medium" id="clientNom"></div>
                                <div class="text-muted small" id="clientContact"></div>
                                <div class="text-muted small" id="clientAdresse"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Articles livrés -->
                <div class="card border border-light-subtle mb-4">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-box me-2"></i>Articles Livrés
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Article</th>
                                    <th class="text-center">Quantité</th>
                                    {{-- <th class="text-end">Prix Unitaire</th>
                                    <th class="text-end">Montant Total</th> --}}
                                </tr>
                            </thead>
                            <tbody id="lignesLivraison">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Notes et informations supplémentaires -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Notes</label>
                        <p class="form-text" id="livraisonNotes"></p>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Créé par</span>
                            <span class="fw-medium" id="createInfo"></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Validé par</span>
                            <span class="fw-medium" id="validateInfo"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="printLivraison()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>