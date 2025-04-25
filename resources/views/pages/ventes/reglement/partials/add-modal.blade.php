<div class="modal fade" id="addReglementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau Règlement</h5>
                        <p class="text-muted small mb-0" id="factureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addReglementForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    {{-- Informations principales --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- Client (en lecture seule) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Client</label>
                                            <select class="form-select" name="clientId" id="clientDisplay">
                                                <option value="">Sélectionnez un client</option>
                                                @foreach ($clients as $client)
                                                <option value="{{ $client->id }}"
                                                    data-factures="{{ $client->facturesClient }}">
                                                    {{ $client->raison_sociale }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un client</div>
                                        </div>

                                        {{-- Sélection Facture --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Facture</label>
                                            <select class="form-control" name="facture_id" id="factureSelect" required>
                                                <option value="">Sélectionner une facture</option>
                                            </select>
                                            <!-- <div class="invalid-feedback">Veuillez sélectionner une facture</div> -->
                                        </div>

                                        {{-- Type de règlement --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Type de règlement</label>
                                            <select class="form-select" name="type_reglement" id="typeReglement"
                                                required>
                                                <option value="">Sélectionner un type</option>
                                                <option value="espece">Espèces</option>
                                                <option value="cheque">Chèque</option>
                                                <option value="virement">Virement bancaire</option>
                                                <option value="carte_bancaire">Carte bancaire</option>
                                                <option value="MoMo">Mobile Money</option>
                                                <option value="Flooz">Flooz</option>
                                                <option value="Celtis_Pay">Celtis Pay</option>
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un type de règlement
                                            </div>
                                        </div>

                                        {{-- Montant --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-end" name="montant"
                                                    id="montant" required step="0.001" min="0">
                                                <span class="input-group-text">F CFA</span>
                                            </div>
                                            <div class="form-text text-end" id="resteAPayer"></div>
                                            <div class="invalid-feedback">Veuillez saisir un montant valide</div>
                                        </div>

                                        {{-- Date de règlement --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date du règlement</label>
                                            <input type="date" class="form-control" name="date_reglement" required
                                                value="{{ date('Y-m-d') }}">
                                            <div class="invalid-feedback">Veuillez sélectionner une date</div>
                                        </div>

                                        {{-- Banque --}}
                                        <div class="col-md-6" id="banqueGroup" style="display: none;">
                                            <label class="form-label fw-medium">Banque</label>
                                            <input type="text" class="form-control" name="banque"
                                                placeholder="Nom de la banque">
                                            <div class="invalid-feedback">Veuillez saisir le nom de la banque</div>
                                        </div>

                                        {{-- Référence --}}
                                        <div class="col-md-6" id="referenceGroup" style="display: none;">
                                            <label class="form-label fw-medium" id="referenceLabel">Référence</label>
                                            <input type="text" class="form-control" name="reference_preuve"
                                                id="reference" placeholder="N° chèque, transaction...">
                                            <div class="invalid-feedback">Veuillez saisir une référence</div>
                                        </div>

                                        {{-- Date d'échéance --}}
                                        <div class="col-md-6" id="echeanceGroup" style="display: none;">
                                            <label class="form-label fw-medium">Date d'échéance</label>
                                            <input type="date" class="form-control" name="date_echeance"
                                                min="{{ date('Y-m-d') }}">
                                            <div class="invalid-feedback">Veuillez sélectionner une date d'échéance
                                            </div>
                                        </div>

                                        {{-- Reference --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Reference</label>
                                            <input type="text" placeholder="########" class="form-control" name="reference_preuve">
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
                                <textarea name="notes" class="form-control" rows="2" placeholder="Notes ou observations éventuelles"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4" id="btnSaveReglement" disabled>
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/modal.css') }}" rel="stylesheet">
<style>
    .required:after {
        content: " *";
        color: red;
    }

    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f8f9fa;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
</style>