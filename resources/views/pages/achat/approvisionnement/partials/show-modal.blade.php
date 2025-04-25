{{-- Modal de visualisation --}}
<div class="modal fade" id="showBonCommandeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ">
        <div class="modal-content border-0 shadow">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-alt fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Bon de Commande</h5>
                        <p class="text-muted small mb-0">Code : <span id="bonCodeShow"></span></p>
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
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium text-muted">Ref programmation</label>
                                        <p class="mb-0" id="refProgrammationShow"></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium text-muted">Date programmation</label>
                                        <p class="mb-0" id="dateProgrammationShow"></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium text-muted">Point de vente</label>
                                        <p class="mb-0" id="pointVenteShow"></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium text-muted">Fournisseur</label>
                                        <p class="mb-0" id="fournisseurShow"></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-medium text-muted">Statut</label>
                                        <p class="mb-0" id="statutShow"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section articles --}}
                    <div class="col-12" id="articlesSectionShow">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-box me-2"></i>Articles
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 15%">Code Article</th>
                                                <th style="width: 40%">Désignation</th>
                                                <th style="width: 15%" class="text-end">Quantité</th>
                                                <th style="width: 15%">Unité</th>
                                                <th style="width: 15%">Observation</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lignesDetails">
                                            <!-- Les lignes seront ajoutées dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section Autres coûts --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-file-invoice me-2"></i>Coûts Supplémentaires
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Coût du transport</label>
                                        <input type="number" class="form-control" id="cout_transport_show" name="cout_transport_show" value="0" readonly>
                                        <div class="invalid-feedback">Le coût du transport est requis</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Coût du Chargement/Déchargement</label>
                                        <input type="number" class="form-control" id="cout_chargement_show" name="cout_chargement_show" value="0" readonly>
                                        <div class="invalid-feedback">Le coût du Chargement/Déchargement est requis</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Autres Coût</label>
                                        <input type="number" class="form-control" id="autre_cout_show" name="autre_cout_show" value="0" readonly>
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
                                <p class="mb-0" id="commentaireShow"></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-light shadow-sm roundered show_reference" id="show_object" hidden>
                        <!-- <form id="exportForm"> -->
                        <div class="modal-body">
                            <input type="hidden" id="bon_id">
                            <textarea name="" required id="bon_object" class="form-control" placeholder="Tapez l'objet ici ...."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" onclick="closeObject()">Close</button>
                            <button type="submit" onclick="exportation()" class="btn btn-dark">Exporter maintenant</button>
                        </div>
                        <!-- </form> -->
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="printProgrammation()">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a onclick="exportPdf()" class="dropdown-item btn">
                                <i class="fas fa-file-pdf me-2"></i>Exporter en PDF
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="exportExcel()">
                                <i class="fas fa-file-excel me-2"></i>Exporter en Excel
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>