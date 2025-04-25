<div class="modal fade" id="addApprovisionnementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg ">
        <div class="modal-content border-0 shadow-lg modal-dialog-scrollable" style="overflow-y: scroll!important;">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-shopping-cart fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvel Approvisionnement</h5>
                        <p class="text-muted small mb-0">Créez un Nouvel Approvisionnement</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('approvisionnements.store') }}" method="POST" id="_addApprovisionnementForm"
                class="needs-validation" novalidate enctype="multipart/form-data">
                @csrf
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
                                        <select class="form-select select2" name="fournisseur_id">
                                            <option value="">Sélectionner un fournisseur</option>
                                            @foreach ($fournisseurs as $fournisseur)
                                            <option value="{{ $fournisseur->id }}">
                                                {{ $fournisseur->raison_sociale }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Veuillez sélectionner un founisseur</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="">Montant</label>
                                        <input type="number" name="montant" class="form-control" min="1" id="" placeholder="Example: 100000">
                                        <div class="invalid-feedback">Veuillez sélectionner un founisseur</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <select class="form-select select2" name="source">
                                            <option value="">Une source( Qui a effectué le paiement?? )</option>
                                            <option value="DIRECTION">DIRECTION</option>
                                            <option value="AGENT">AGENT</option>
                                        </select>
                                        <div class="invalid-feedback">Veuillez sélectionner une source</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <input type="date" name="date" class="form-control" id="">
                                        <div class="invalid-feedback">Veuillez choisir une date</div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <input type="file" name="document" class="form-control" id="">
                                        <div class="invalid-feedback">Veuillez sélectionner une preuve</div>
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