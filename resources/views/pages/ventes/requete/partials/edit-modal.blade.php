{{-- edit-modal.blade.php --}}
<div class="modal fade" id="editRequeteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-warning bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-edit fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier la requête</h5>
                        <p class="text-muted small mb-0" id="editFactureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editRequeteForm" class="needs-validation" novalidate>
                @csrf
                @method('PATCH')
                <!-- <input type="hidden" name="reglement_id" id="editReglementId"> -->

                <div class="modal-body p-4">
                    {{-- Informations principales --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- NUM demande (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Numéro de demande</label>
                                            <input type="text" class="form-control" name="num_demande" id="edit_num_demande">
                                        </div>

                                        {{-- Montant --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-end"
                                                    name="montant" id="edit_montant"
                                                    required step="0.001" min="0">
                                                <span class="input-group-text">F CFA</span>
                                            </div>
                                            <div class="invalid-feedback">Veuillez saisir un montant valide</div>
                                        </div>

                                        {{-- Date de demande --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date du règlement</label>
                                            <input type="date" class="form-control"
                                                name="date_demande"
                                                id="edit_date_demande">
                                            <div class="invalid-feedback">Veuillez sélectionner une date</div>
                                        </div>

                                        {{-- NATURE (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Nature</label>
                                            <textarea rows="1" name="nature" id="edit_nature" class="form-control"></textarea>
                                        </div>

                                        {{-- Mention (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Mention</label>
                                            <textarea rows="1" name="mention" id="edit_mention" class="form-control"></textarea>
                                        </div>

                                        {{-- Formulation (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Formulation</label>
                                            <textarea rows="1" name="formulation" id="edit_formulation" class="form-control"></textarea>
                                        </div>

                                        {{-- Client (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Client</label>
                                            <select name="client_id" id="edit_client_id" class="form-control form-select">
                                                <!-- gere par du js -->
                                            </select>
                                        </div>

                                        {{-- Article (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Articles</label>
                                            <select name="client_id" id="edit_article_id" class="form-control form-select">
                                                <!-- gere par du js -->
                                            </select>
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
                    <button type="submit" class="btn btn-warning px-4" id="btnUpdateReglement">
                        <i class="fas fa-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/modal.css') }}" rel="stylesheet">
