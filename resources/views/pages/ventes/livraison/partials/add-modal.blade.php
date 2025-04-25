<!-- Modal de création de livraison -->
<div class="modal fade" id="addLivraisonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-truck fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau Bon de Livraison</h5>
                        <p class="text-muted small mb-0" id="factureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addLivraisonForm" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="facture_client_id" id="factureClientId">

                <div class="modal-body p-4">
                    {{-- Informations de la facture --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-client rounded-3 me-3">
                                                    <i class="fas fa-user fs-4"></i>
                                                </div>
                                                <div>
                                                    <span class="text-muted small">Client</span>
                                                    <h6 class="mb-0" id="clientName"></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <label class="form-label fw-medium required">Facture</label>
                                            <select class="form-select" name="facture_id" id="factureSelect" required>
                                                <option value="">Sélectionner une facture</option>
                                                @foreach ($factures as $facture)
                                                    <option value="{{ $facture->id }}">{{ $facture->numero }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner une facture</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Magasin source</label>
                                            <select class="form-select" name="depot_id" required>
                                                <option value="">Sélectionner un magasin</option>
                                                @foreach ($depots as $depot)
                                                    <option value="{{ $depot->id }}">{{ $depot->libelle_depot }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un magasin</div>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Magasin Destination Interne (pour une livraison sur un autre point de vente)</label>
                                            <select class="form-select" name="depot_dest_id" >
                                                <option value="">Sélectionner un magasin de destination</option>
                                                @foreach ($depots as $depot)
                                                    <option value="{{ $depot->id }}">{{ $depot->libelle_depot }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un magasin de destination interne</div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Articles à livrer --}}
                    <div class="card border border-light-subtle mb-4">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-box me-2"></i>Articles à livrer
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="articlesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30%">Article</th>
                                            <th class="text-center">Quantité facturée</th>
                                            <th class="text-center">Déjà livrée</th>
                                            <th class="text-center">Reste à livrer</th>
                                            <th class="text-center" style="width: 150px;">Quantité à livrer</th>
                                            <th class="text-center">Stock disponible</th>
                                            {{-- <th class="text-center">Prix moyen (CUMP)</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody id="lignesFacture">
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                Veuillez sélectionner une facture
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                    <button type="submit" class="btn btn-primary px-4" id="btnSaveLivraison" disabled>
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .modal-xl {
        max-width: 1200px;
    }

    .avatar-client {
        width: 40px;
        height: 40px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .required:after {
        content: " *";
        color: #dc3545;
    }

    .modal-footer {
        position: sticky;
        bottom: 0;
        z-index: 1020;
    }

    .quantite-input {
        width: 100px;
        text-align: right;
    }

    .table> :not(caption)>*>* {
        padding: 0.75rem 1rem;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: rgba(var(--bs-primary-rgb), 0.5);
        box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1);
    }

    /* Style pour Select2 dans le modal */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #dee2e6;
        min-height: 31px;
        padding: 2px 8px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding: 0;
        line-height: 24px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 29px;
    }

    /* Style pour les indicateurs de stock */
    .stock-badge {
        min-width: 80px;
        font-size: 0.85rem;
    }

    .stock-warning {
        color: #ffc107;
    }

    .stock-danger {
        color: #dc3545;
    }

    .prix-moyen {
        font-weight: 500;
        color: #198754;
    }
</style>
