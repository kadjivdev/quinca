<div class="modal fade" id="addReglementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau Règlement</h5>
                        <p class="text-muted small mb-0">Enregistrer un règlement pour la facture <span class="fw-medium" id="numeroFacture"></span></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addReglementForm" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="facture_client_id" id="factureClientId">

                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Informations de la facture --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="small text-muted mb-1">Client</p>
                                            <p class="fw-medium mb-0" id="clientName"></p>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <p class="small text-muted mb-1">Montant restant</p>
                                            <p class="fw-medium text-danger mb-0" id="montantRestant"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Date et montant --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations du règlement
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date règlement</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_reglement" required value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-money-bill text-primary"></i>
                                                </span>
                                                <input type="number" class="form-control" name="montant" required min="0" step="0.001">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                            <div class="invalid-feedback">Le montant est requis et doit être positif</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Mode de règlement --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-credit-card me-2"></i>Mode de règlement
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Type de règlement</label>
                                            <select class="form-select" name="type_reglement" id="typeReglement" required>
                                                <option value="">Sélectionner</option>
                                                <option value="espece">Espèces</option>
                                                <option value="cheque">Chèque</option>
                                                <option value="virement">Virement</option>
                                                <option value="carte_bancaire">Carte bancaire</option>
                                                <option value="MoMo">Mobile Money</option>
                                                <option value="Flooz">Flooz</option>
                                                <option value="Celtis_Pay">Celtis Pay</option>
                                                <option value="Effet">Effet</option>
                                                <option value="Avoir">Avoir</option>
                                            </select>
                                            <div class="invalid-feedback">Le type de règlement est requis</div>
                                        </div>

                                        {{-- Champs conditionnels selon le type --}}
                                        <div class="col-md-6" id="banqueField" style="display: none;">
                                            <label class="form-label fw-medium required">Banque</label>
                                            <input type="text" class="form-control" name="banque">
                                            <div class="invalid-feedback">La banque est requise pour ce type de règlement</div>
                                        </div>

                                        <div class="col-md-6" id="referenceField" style="display: none;">
                                            <label class="form-label fw-medium required">Référence</label>
                                            <input type="text" class="form-control" name="reference_preuve">
                                            <div class="invalid-feedback">La référence est requise pour ce type de règlement</div>
                                        </div>

                                        <div class="col-md-6" id="dateEcheanceField" style="display: none;">
                                            <label class="form-label fw-medium required">Date d'échéance</label>
                                            <input type="date" class="form-control" name="date_echeance">
                                            <div class="invalid-feedback">La date d'échéance est requise pour ce type de règlement</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Notes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="notes" rows="2" placeholder="Notes éventuelles sur le règlement"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <span class="text-muted">Nouveau solde après règlement</span>
                                <h3 class="mb-0 mt-2" id="nouveauSolde">0 FCFA</h3>
                                <div id="soldeMessage" class="small text-muted mt-1"></div>
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
    .required:after {
        content: " *";
        color: #dc3545;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }

    .card {
        border-radius: 0.5rem;
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .input-group-text {
        border-color: #dee2e6;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@include('pages.ventes.facture.partials.js-reg-modal')
