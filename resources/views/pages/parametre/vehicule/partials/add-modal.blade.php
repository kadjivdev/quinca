<div class="modal fade" id="addVehiculeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-car fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouveau Vehicule</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('vehicule.store') }}" method="POST" id="addVehiculeForm"
                class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Nom --}}
                        <div class="col-md-9">
                            <label class="form-label fw-semibold required">Matricule</label>
                            <input type="text" class="form-control" name="matricule" required minlength="6"
                                maxlength="100" placeholder="Ex: AB 0000">
                            <div class="invalid-feedback">
                                Le matricule est requis (3 à 100 caractères).
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Statut</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif" id="statusSwitch"
                                    value="1" checked>
                                <label class="form-check-label" for="statusSwitch">
                                    Vehicule actif
                                </label>
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
