<div class="modal fade" id="addChauffeurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-plus fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouveau Chauffeur</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('chauffeur.store') }}" method="POST" id="addChauffeurForm"
                class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Nom --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Nom</label>
                            <input type="text" class="form-control" name="nom" required minlength="3"
                                maxlength="100" placeholder="Ex: JOHN DOE">
                            <div class="invalid-feedback">
                                Le nom est requis (3 à 100 caractères).
                            </div>
                        </div>

                        {{-- Téléphone --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="tel" class="form-control" name="telephone" placeholder="Ex: 0123456789">
                        </div>

                        {{-- Permis --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Numéro Permis</label>
                            <textarea class="form-control" name="num_permis" rows="2" placeholder="Numéro Permis"></textarea>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Statut</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif" id="statusSwitch"
                                    value="1" checked>
                                <label class="form-check-label" for="statusSwitch">
                                    Chauffeur actif
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
