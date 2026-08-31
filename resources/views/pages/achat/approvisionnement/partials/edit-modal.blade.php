<div class="modal fade" id="editApprovisionnementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-shopping-cart fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modification de l'approvisionnement <span id="codeAppro"></span></h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" id="editApprovisionnementForm"
                class="needs-validation" novalidate enctype="multipart/form-data">
                @csrf
                @method("PATCH")
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section sélection fournisseurs --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-list-check me-2"></i>Sélection Fournisseur
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <select class="form-select select2" name="fournisseur_id" id="fournisseur_id">
                                           <!-- js -->
                                        </select>
                                        <div class="invalid-feedback">Veuillez sélectionner un founisseur</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="">Montant</label>
                                        <input type="number" required name="montant" class="form-control" min="1" id="montant" placeholder="Example: 100000">
                                        <div class="invalid-feedback">Veuillez sélectionner un founisseur</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <select class="form-select select2" required name="source" id="source">
                                            <option value="">Une source( Qui a effectué le paiement?? )</option>
                                            <option value="DIRECTION">DIRECTION</option>
                                            <option value="AGENT">AGENT</option>
                                        </select>
                                        <div class="invalid-feedback">Veuillez sélectionner une source</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <input type="date" required name="date" class="form-control" id="date">
                                        <div class="invalid-feedback">Veuillez choisir une date</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <input type="file" name="document" class="form-control" id="">
                                        <div class="invalid-feedback">Veuillez sélectionner une preuve</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <input type="text" name="reference" class="form-control" id="reference" placeholder="Inserez une reference ici">
                                        <div class="invalid-feedback">Veuillez sélectionner une reference</div>
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
                    <button type="submit" class="btn btn-primary px-4" id="btnSave">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .modal-dialog {
        max-width: 95%;
        margin: 1.75rem auto;
    }

    .invalid-feedback {
        font-size: 80%;
    }

    .prix-unitaire {
        min-width: 100px;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(1.5em + 0.75rem + 2px);
    }

    .table> :not(caption)>*>* {
        padding: 0.5rem;
    }

    .form-control-sm {
        min-height: calc(1.5em + 0.5rem + 2px);
    }

    .card {
        margin-bottom: 0;
    }

    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header {
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .btn-icon {
        padding: 0.25rem 0.5rem;
    }

    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
</style>
@endpush