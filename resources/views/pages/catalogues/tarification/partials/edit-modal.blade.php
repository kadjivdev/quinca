<!-- resources/views/parametrage/tarification/partials/edit-modal.blade.php -->

<div class="modal fade" id="editTarificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            {{-- Header du modal --}}
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-edit fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Modifier la Tarification</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="" method="POST" id="editTarificationForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    {{-- Informations de l'article --}}
                    <div class="article-info mb-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Code Article</label>
                                <div class="fw-medium" id="editCodeArticle"></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Type de Tarif</label>
                                <div class="fw-medium" id="editTypeTarif"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Prix --}}
                        <div class="col-12">
                            <label class="form-label fw-medium required">Prix</label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control"
                                       name="prix"
                                       step="0.01"
                                       min="0"
                                       required
                                       placeholder="0.00">
                                <span class="input-group-text">FCFA</span>
                                <div class="invalid-feedback">
                                    Le prix est requis et doit être supérieur à 0
                                </div>
                            </div>
                        </div>

                        {{-- Statut --}}
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       name="statut"
                                       id="editStatutTarif"
                                       value="1">
                                <label class="form-check-label" for="editStatutTarif">
                                    Tarification active
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
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
