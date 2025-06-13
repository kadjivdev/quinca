{{-- edit-modal.blade.php --}}
<div class="modal fade" id="editReglementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-warning bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-edit fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier le Règlement</h5>
                        <p class="text-muted small mb-0" id="editFactureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editReglementForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="reglement_id" id="editReglementId">

                <div class="modal-body p-4">
                    {{-- Informations principales --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Facture (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Facture</label>
                                            <div class="form-control bg-white" id="editFactureDisplay">
                                                Chargement...
                                            </div>
                                        </div>

                                        {{-- Client (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Client</label>
                                            <div class="form-control bg-white" id="editClientDisplay">
                                                Chargement...
                                            </div>
                                        </div>

                                        {{-- Type de règlement --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Type de règlement</label>
                                            <select class="form-select" name="type_reglement" id="editTypeReglement" required>
                                                <option value="">Sélectionner un type</option>
                                                <option value="espece">Espèces</option>
                                                <option value="cheque">Chèque</option>
                                                <option value="virement">Virement bancaire</option>
                                                <option value="carte_bancaire">Carte bancaire</option>
                                                <option value="MoMo">Mobile Money</option>
                                                <option value="Flooz">Flooz</option>
                                                <option value="Celtis_Pay">Celtis Pay</option>
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un type de règlement</div>
                                        </div>

                                        {{-- Montant --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-end"
                                                       name="montant" id="editMontant"
                                                       required step="0.001" min="0">
                                                <span class="input-group-text">F CFA</span>
                                            </div>
                                            <div class="form-text text-end" id="editResteAPayer"></div>
                                            <div class="invalid-feedback">Veuillez saisir un montant valide</div>
                                        </div>

                                        {{-- Date de règlement --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date du règlement</label>
                                            <input type="date" class="form-control"
                                                   name="date_reglement"
                                                   id="editDateReglement"
                                                   required>
                                            <div class="invalid-feedback">Veuillez sélectionner une date</div>
                                        </div>

                                        {{-- Banque --}}
                                        <div class="col-md-6" id="editBanqueGroup" style="display: none;">
                                            <label class="form-label fw-medium">Banque</label>
                                            <input type="text" class="form-control"
                                                   name="banque"
                                                   id="editBanque"
                                                   placeholder="Nom de la banque">
                                            <div class="invalid-feedback">Veuillez saisir le nom de la banque</div>
                                        </div>

                                        {{-- Référence --}}
                                        <div class="col-md-6" id="editReferenceGroup" style="display: none;">
                                            <label class="form-label fw-medium" id="editReferenceLabel">Référence</label>
                                            <input type="text" class="form-control"
                                                   name="reference_preuve"
                                                   id="editReference"
                                                   placeholder="N° chèque, transaction...">
                                            <div class="invalid-feedback">Veuillez saisir une référence</div>
                                        </div>

                                        {{-- Date d'échéance --}}
                                        <div class="col-md-6" id="editEcheanceGroup" style="display: none;">
                                            <label class="form-label fw-medium">Date d'échéance</label>
                                            <input type="date" class="form-control"
                                                   name="date_echeance"
                                                   id="editDateEcheance"
                                                   min="{{ date('Y-m-d') }}">
                                            <div class="invalid-feedback">Veuillez sélectionner une date d'échéance</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label fw-medium">Notes / Observations</label>
                                <textarea name="notes" id="editNotes" class="form-control" rows="2"
                                    placeholder="Notes ou observations éventuelles"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-warning px-4" id="btnUpdateReglement">
                        <i class="fas fa-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/modal.css') }}" rel="stylesheet">

