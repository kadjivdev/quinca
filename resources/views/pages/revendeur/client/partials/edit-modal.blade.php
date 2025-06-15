<div class="modal fade" id="editClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-user-edit fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier le Client</h5>
                        <p class="text-muted small mb-0">Modifier les informations du client</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editClientForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="client_id" id="edit_client_id">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Informations principales --}}
                        <div class="col-md-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>Informations principales
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-medium required">Raison sociale</label>
                                            <input type="text" class="form-control" name="raison_sociale" id="edit_raison_sociale"
                                                required placeholder="Nom du client ou de l'entreprise">
                                            <div class="invalid-feedback">La raison sociale est requise</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Catégorie</label>
                                            <select class="form-select" name="categorie" id="edit_categorie" required>
                                                <option value="">Sélectionner une catégorie</option>
                                                <option value="comptoir">Client Comptoir</option>
                                                <option value="particulier">Particulier</option>
                                                <option value="professionnel">Professionnel</option>
                                                <option value="societe">Société</option>
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner une catégorie</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">IFU</label>
                                            <input type="text" class="form-control" name="ifu" id="edit_ifu"
                                                placeholder="Numéro IFU">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">RCCM</label>
                                            <input type="text" class="form-control" name="rccm" id="edit_rccm"
                                                placeholder="Numéro RCCM">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Taux AIB</label>
                                            <input type="number" step="0.01" min="0" class="form-control" name="taux_aibMob" id="edit_aib"
                                                placeholder="Taux AIB">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Contact et Adresse --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-address-card me-2"></i>Contact et Adresse
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Téléphone</label>
                                            <input type="tel" class="form-control" name="telephone" id="edit_telephone"
                                                placeholder="Numéro de téléphone">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Email</label>
                                            <input type="email" class="form-control" name="email" id="edit_email"
                                                placeholder="Adresse email">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Adresse</label>
                                            <textarea class="form-control" name="adresse" id="edit_adresse" rows="2"
                                                placeholder="Adresse complète"></textarea>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Ville</label>
                                            <input type="text" class="form-control" name="ville" id="edit_ville"
                                                placeholder="Ville">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Paramètres de crédit --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-credit-card me-2"></i>Paramètres de crédit
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Plafond de crédit</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="plafond_credit" id="edit_plafond_credit"
                                                    min="0" step="0.001" placeholder="0.000">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Délai de paiement (jours)</label>
                                            <input type="number" class="form-control" name="delai_paiement" id="edit_delai_paiement"
                                                min="0" placeholder="0">
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Solde initial</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="solde_initial" id="edit_solde_initial"
                                                    step="0.001" placeholder="0.000">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                        </div>

                                        {{-- <div class="col-md-12">
                                            <label class="form-label fw-medium">Taux AIB (%)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="taux_aib" id="edit_taux_aib"
                                                    step="0.01" min="0" max="100" placeholder="0.00">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <div class="form-text">Taux de l'Acompte sur Impôt sur le Bénéfice</div>
                                        </div> --}}

                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes et Statut --}}
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-medium">Notes / Observations</label>
                                            <textarea class="form-control" name="notes" id="edit_notes" rows="2"
                                                placeholder="Notes ou observations éventuelles"></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Statut</label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox"
                                                    name="statut"
                                                    value="1"
                                                    id="editStatutSwitch">
                                                <label class="form-check-label" for="editStatutSwitch">
                                                    Client actif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
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
