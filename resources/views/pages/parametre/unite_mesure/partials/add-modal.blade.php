<!-- resources/views/parametrage/unite-mesure/partials/add-modal.blade.php -->

<div class="modal fade" id="addUniteMesureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-ruler-combined fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouvelle Unité de Mesure</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form wire:submit.prevent="store" id="uniteMesureForm" class="needs-validation" novalidate>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Code Unité --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold required">Code Unité</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">UN-</span>
                    <input type="text"
                           class="form-control"
                           name="code_unite"  {{-- Vérifier ce name --}}
                           pattern="[A-Z0-9]{1,3}"
                           required
                           maxlength="3"
                           placeholder="KG">
                    <div class="invalid-feedback">
                        Le code doit contenir 3 caractères majuscules ou chiffres.
                    </div>
                </div>
            </div>

            {{-- Libellé Unité --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold required">Libellé de l'Unité</label>
                <input type="text"
                       class="form-control"
                       name="libelle_unite"  {{-- Vérifier ce name --}}
                       required
                       minlength="2"
                       maxlength="50"
                       placeholder="Ex: Kilogramme">
                <div class="invalid-feedback">
                    Le libellé est requis (2 à 50 caractères).
                </div>
            </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                    wire:model.defer="description"
                                    rows="3"
                                    placeholder="Description optionnelle de l'unité de mesure"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Options --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Options</label>
                            <div>
                                {{-- <div class="form-check mb-2">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           wire:model.defer="unite_base"
                                           id="uniteBaseCheck">
                                    <label class="form-check-label" for="uniteBaseCheck">
                                        Définir comme unité de base
                                    </label>
                                </div> --}}
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           wire:model.defer="statut"
                                           id="uniteActifCheck"
                                           checked>
                                    <label class="form-check-label" for="uniteActifCheck">
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
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
