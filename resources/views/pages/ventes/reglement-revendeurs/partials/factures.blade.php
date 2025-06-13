<div class="modal fade" id="reglementFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 reglement-title"></h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    {{-- Section informations générales --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Informations Générales
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                <div class="col-md-6">
                                        <label class="form-label fw-medium required">Facture</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                            </span>
                                            <input type="text" disabled class="form-control facture-number">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-medium required">Date facture</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                            </span>
                                            <input type="text" disabled class="form-control date_facture">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-medium required">Client</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-user text-primary"></i>
                                            </span>
                                            <input type="text" disabled class="form-control  facture-client">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-medium required">Échéance</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-clock text-primary"></i>
                                            </span>
                                            <input type="text" disabled class="form-control date-echeance">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-medium required">Type de Facture</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-file-invoice text-primary"></i>
                                            </span>
                                            <input type="text" class="form-select type-facture" disabled></input>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section articles --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-box me-2"></i>Articles
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Article</th>
                                                <th style="width: 20%">Dépôts</th>
                                                <th>Qte</th>
                                                <th>Prix</th>
                                                <th>Montant Remise</th>
                                                <th>Total HT</th>
                                            </tr>
                                        </thead>
                                        <tbody class="factures-articles">
                                            <!-- Les lignes seront ajoutées ici -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
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