<!-- resources/views/parametrage/tarification/partials/add-modal.blade.php -->

<div class="modal fade" id="addTarificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            {{-- Header du modal --}}
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-tag fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouvelle Tarification</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('tarification.store') }}" method="POST" id="addTarificationForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Article --}}
                        <div class="col-12">
                            <label class="form-label fw-medium required">Article</label>
                            <select class="form-select" name="article_id" required>
                                <option value="">Sélectionner un article</option>
                                @foreach($articles as $article)
                                    <option value="{{ $article->id }}">
                                        {{ $article->code_article }} - {{ $article->libelle_article }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un article
                            </div>
                        </div>

                        {{-- Type de Tarif --}}
                        <div class="col-12">
                            <label class="form-label fw-medium required">Type de Tarif</label>
                            <select class="form-select" name="type_tarif_id" required>
                                <option value="">Sélectionner un type</option>
                                @foreach($typesTarifs as $type)
                                    <option value="{{ $type->id }}">{{ $type->libelle_type_tarif }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un type de tarif
                            </div>
                        </div>

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
                                       id="addStatutTarif"
                                       value="1"
                                       checked>
                                <label class="form-check-label" for="addStatutTarif">
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
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
