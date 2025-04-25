<div class="modal fade" id="editArticleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-edit fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Modifier l'Article</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" id="editArticleForm" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Code Article --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Code Article</label>
                            <input type="text" class="form-control" name="code_article" readonly required>
                        </div>

                        {{-- Désignation --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold required">Désignation</label>
                            <input type="text" class="form-control" name="designation" required maxlength="255"
                                placeholder="Nom ou désignation de l'article">
                            <div class="invalid-feedback">
                                La désignation est requise
                            </div>
                        </div>

                        {{-- Famille --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Famille</label>
                            <select class="form-select" name="famille_id" required>
                                <option value="">Sélectionner une famille</option>
                                @foreach ($familles as $famille)
                                <option value="{{ $famille->id }}">{{ $famille->libelle_famille }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner une famille d'articles
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Options</label>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="stockable" id="editStockableCheck"
                                    value="1">
                                <input type="hidden" name="stockable" value="0">
                                <label class="form-check-label" for="editStockableCheck">
                                    Article stockable
                                </label>
                            </div>
                        </div>

                        {{-- Informations de stock --}}
                        <div class="stock-fields">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold required">Stock Minimum</label>
                                    <input type="number" class="form-control" name="stock_minimum" step="0.01"
                                        min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold required">Stock Maximum</label>
                                    <input type="number" class="form-control" name="stock_maximum" step="0.01"
                                        min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold required">Stock Sécurité</label>
                                    <input type="number" class="form-control" name="stock_securite" step="0.01"
                                        min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Stock Actuel</label>
                                    <input type="number" class="form-control" name="stock_actuel" step="0.01"
                                        min="0" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Emplacement Stock</label>
                                    <input type="text" class="form-control" name="emplacement_stock"
                                        placeholder="Ex: Allée A, Rayon 3, Position 12">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold required">Unité de mésure</label>
                                    <select class="form-select" name="unite_mesure_id" required>
                                        <option value="">Sélectionner une unité</option>
                                        @foreach ($unites as $unite)
                                        <option value="{{ $unite->id }}">{{ $unite->libelle_unite }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Veuillez sélectionner une unité de mésure
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Photo --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Photo actuelle</label>
                            <div id="currentPhoto" class="mb-3"></div>

                            <label class="form-label fw-semibold">Nouvelle photo</label>
                            <input type="file" class="form-control" name="photo" accept="image/*">
                            <small class="text-muted">Formats acceptés: JPG, PNG. Max: 2MB</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4" id="editSubmitBtn">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>