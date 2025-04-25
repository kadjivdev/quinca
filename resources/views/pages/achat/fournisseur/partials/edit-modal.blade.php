<div class="modal fade" id="editFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-warehouse fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Modifier le Fournisseur</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="" method="POST" id="editFournisseurForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Code --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Code</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">FRN-</span>
                                <input type="text"
                                       class="form-control"
                                       name="code_fournisseur"
                                       pattern="[A-Z0-9]{6}"
                                       required
                                       maxlength="6"
                                       readonly>
                                <div class="invalid-feedback">
                                    Le code doit contenir 6 caractères alphanumériques.
                                </div>
                            </div>
                            <small class="text-muted">Format: 6 caractères majuscules ou chiffres</small>
                        </div>

                        {{-- Nom --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Nom</label>
                            <input type="text"
                                   class="form-control"
                                   name="nom"
                                   required
                                   minlength="3"
                                   maxlength="100"
                                   placeholder="Ex: SARL EXEMPLE">
                            <div class="invalid-feedback">
                                Le nom est requis (3 à 100 caractères).
                            </div>
                        </div>

                        {{-- Adresse --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Adresse</label>
                            <textarea class="form-control"
                                      name="adresse"
                                      rows="2"
                                      placeholder="Adresse complète du fournisseur"></textarea>
                        </div>

                        {{-- Téléphone --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="tel"
                                   class="form-control"
                                   name="telephone"
                                   placeholder="Ex: 0123456789">
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email"
                                   class="form-control"
                                   name="email"
                                   placeholder="Ex: contact@fournisseur.com">
                            <div class="invalid-feedback">
                                Veuillez entrer une adresse email valide.
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Statut</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="actif"
                                       id="editStatusSwitch"
                                       value="1">
                                <label class="form-check-label" for="editStatusSwitch">
                                    Fournisseur actif
                                </label>
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
