<div class="modal fade" id="addDepotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-warehouse fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouveau Magasin</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('depot.store') }}" method="POST" id="depotForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Code Magasin --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Code Magasin</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">DEP-</span>
                                <input type="text" class="form-control" name="code_depot" pattern="[A-Z0-9]{6}"
                                    required maxlength="6" placeholder="AUTO123">
                                <div class="invalid-feedback">
                                    Le code doit contenir 6 caractères alphanumériques.
                                </div>
                            </div>
                            <small class="text-muted">Format: 6 caractères majuscules ou chiffres</small>
                        </div>

                        {{-- Libellé Magasin --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Libellé du Magasin</label>
                            <input type="text" class="form-control" name="libelle_depot" required minlength="3"
                                maxlength="100" placeholder="Ex: Magasin Central">
                            <div class="invalid-feedback">
                                Le libellé du magasin est requis (3 à 100 caractères).
                            </div>
                        </div>

                        {{-- Type de Magasin --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Type de Magasin</label>
                            <select class="form-select" name="type_depot_id" required>
                                <option value="">Sélectionner un type</option>
                                @foreach ($typesDepot as $typeDepot)
                                    <option value="{{ $typeDepot->id }}">{{ $typeDepot->libelle_type_depot }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un type de magasin.
                            </div>
                        </div>

                        {{-- Adresse --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Adresse</label>
                            <textarea class="form-control" name="adresse_depot" rows="3" placeholder="Entrez l'adresse complète du magasin"></textarea>
                        </div>

                        {{-- Téléphone --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="tel" class="form-control" name="tel_depot"
                                placeholder="Ex: +225 0101020304">
                        </div>

                        <!-- Magasin associé -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Point de vente</label>
                            <select class="form-select" name="point_de_vente_id" required>
                                <option value="">Sélectionnez un point de vente</option>
                                @foreach ($pvs ?? [] as $pv)
                                    <option value="{{ $pv->id }}">{{ $pv->nom_pv }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Veuillez sélectionner un point de vente.
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Options</label>
                            <div>
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" name="depot_principal"
                                        id="depotPrincipalCheck" value="1">
                                    <label class="form-check-label" for="depotPrincipalCheck">
                                        Définir comme magasin principal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="actif" id="depotActifCheck"
                                        value="1" checked>
                                    <label class="form-check-label" for="depotActifCheck">
                                        Magasin actif
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
