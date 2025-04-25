<div class="modal fade" id="showLivraisonFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4">
            {{-- Header du modal --}}
            <div class="modal-header border-0 bg-gradient-light py-4">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-truck-loading text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-semibold mb-1">Detail du Bon de Livraison <span id="codeBon"></span>
                        </h5>
                        <p class="text-muted small mb-0" id="factureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addLivraisonFournisseurForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    {{-- Informations de la facture --}}
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card bg-light border-0 rounded-3">
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-3 bg-warning bg-opacity-10 p-3 me-3">
                                                    <i class="fas fa-building text-warning"></i>
                                                </div>
                                                <div>
                                                    <span class="text-muted small">Fournisseur</span>
                                                    <h6 class="fw-semibold mb-0" id="fournisseurNameShow"></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-2 required">Facture</label>
                                            <h6 id="factureCode"></h6>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-2 required">Date de
                                                livraison</label>
                                            <h6 name="date_livraison"></h6>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-2 required">Magasin</label>
                                            <h6 id="depotId"></h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Informations de transport --}}
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-2 required">Véhicule</label>
                            <h6 id="vehiculeId"></h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-2 required">Chauffeur</label>
                            <h6 id="chauffeurId"></h6>
                        </div>
                    </div>

                    {{-- Articles à livrer --}}
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-gradient-light border-0 py-3">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-boxes text-warning me-2"></i>Articles à réceptionner
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" id="modalArticlesTable">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="py-3 px-4" style="width: 30%">Article</th>
                                        <th class="py-3 text-center">Unité</th>
                                        {{-- <th class="py-3 text-center">Quantité facturée</th>
                                        <th class="py-3 text-center">Déjà reçue</th>
                                        <th class="py-3 text-center">À recevoir</th> --}}
                                        <th class="py-3 text-center" style="width: 150px;">Quantité</th>
                                        <th class="py-3 text-center">Qté supplémentaire</th>
                                    </tr>
                                </thead>
                                <tbody id="modalLignesFactureShow">
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state">
                                                <div
                                                    class="rounded-circle bg-warning bg-opacity-10 p-4 mx-auto mb-3 d-inline-flex">
                                                    <i class="fas fa-file-invoice text-warning fa-2x"></i>
                                                </div>
                                                <p class="text-muted mb-0">Veuillez sélectionner une facture</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label small text-muted mb-2">Commentaire</label>
                            <textarea name="commentaire" class="form-control rounded-3" rows="2"
                                placeholder="Saisissez un commentaire éventuel..." readonly></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 py-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-gradient-light {
        background: linear-gradient(to right, #fff, #fff8e1);
    }

    .form-control,
    .form-select {
        border-color: #dee2e6;
        padding: 0.6rem 1rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #ffa000;
        box-shadow: 0 0 0 0.25rem rgba(255, 160, 0, 0.1);
    }

    .required:after {
        content: " *";
        color: #dc3545;
    }

    .btn-warning {
        background-color: #ffa000;
        border-color: #ffa000;
        color: white;
    }

    .btn-warning:hover {
        background-color: #ff8f00;
        border-color: #ff8f00;
        color: white;
    }

    .btn-warning:disabled {
        background-color: #ffa000;
        border-color: #ffa000;
        opacity: 0.65;
    }

    .table> :not(caption)>*>* {
        padding: 1rem 0.75rem;
        border-bottom-color: #f1f1f1;
    }

    .table> :not(caption)>*>* {
        background-color: transparent;
    }

    .empty-state {
        padding: 2rem;
    }

    .modal-content {
        border-radius: 1rem;
    }

    .card {
        border-radius: 0.75rem;
    }

    .invalid-feedback {
        font-size: 0.75rem;
    }
</style>
