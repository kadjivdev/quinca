<div class="modal fade" id="addTransportationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-car fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouveau moyen de transport</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('transportation.store') }}" method="POST" id="addTransportationForm"
                class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        {{-- Matricule --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Matricule</label>
                            <input type="text" class="form-control" name="matricule" required placeholder="Ex: AB 0000">
                        </div>

                        {{-- Libelle --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Libellé</label>
                            <input type="text" class="form-control" name="libelle" required
                                placeholder="Ex: CAMIONNETTE">
                        </div>
                    </div>
                    <!--  -->
                    <div class="row">
                        {{-- Matricule --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold required">Type de transport</label>
                            <div class="input-group">
                                <select class="form-select select2" name="type" id="type_transport"
                                    required>
                                    <option value="">Selectionner un type de moyen de transport</option>
                                    <option value="TRICYCLE">TRICYCLE</option>
                                    <option value="CAMIONNETTE">CAMIONNETTE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
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