<!-- resources/views/parametrage/unite-mesure/partials/edit-modal.blade.php -->

<div class="modal fade" id="editUniteMesureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-ruler-combined fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Modifier l'Unité de Mesure</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form wire:submit.prevent="update" id="editUniteMesureForm" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Code Unité (lecture seule) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Code Unité</label>
                            <input type="text" class="form-control" name="code_unite" readonly>
                            <small class="text-muted">Le code ne peut pas être modifié</small>
                        </div>

                        {{-- Libellé Unité --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Libellé de l'Unité</label>
                            <input type="text" class="form-control @error('libelle_unite') is-invalid @enderror"
                                name="libelle_unite" required minlength="2" maxlength="50">
                            @error('libelle_unite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Options --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Options</label>
                            <div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="unite_base"
                                        id="editUniteBase">
                                    <label class="form-check-label" for="editUniteBase">
                                        Définir comme unité de base
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="statut"
                                        id="editUniteActif">
                                    <label class="form-check-label" for="editUniteActif">
                                        Unité active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
