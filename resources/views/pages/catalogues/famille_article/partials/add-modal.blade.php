<div class="modal fade" id="addFamilleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-box-open fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouvelle Famille d'Articles</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('famille-article.store') }}" method="POST" id="familleArticleForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Code Famille --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Code Famille</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">FAM-</span>
                                <input type="text"
                                       class="form-control"
                                       name="code_famille"
                                       pattern="[A-Z0-9]{6}"
                                       required
                                       maxlength="6"
                                       placeholder="AUTO123">
                                <div class="invalid-feedback">
                                    Le code doit contenir 6 caractères alphanumériques.
                                </div>
                            </div>
                            <small class="text-muted">Format: 6 caractères majuscules ou chiffres</small>
                        </div>

                        {{-- Libellé Famille --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Libellé de la Famille</label>
                            <input type="text"
                                   class="form-control"
                                   name="libelle_famille"
                                   required
                                   minlength="3"
                                   maxlength="100"
                                   placeholder="Ex: Pièces Automobiles">
                            <div class="invalid-feedback">
                                Le libellé de la famille est requis (3 à 100 caractères).
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control"
                                      name="description"
                                      rows="3"
                                      placeholder="Description détaillée de la famille d'articles"></textarea>
                        </div>




                        {{-- Options --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Options</label>
                            <div>
                                <div class="form-check">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           name="statut"
                                           id="familleActifCheck"
                                           value="1"
                                           checked>
                                    <label class="form-check-label" for="familleActifCheck">
                                        Famille active
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
