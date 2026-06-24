<div class="modal fade" id="editDestockageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-edit fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier le destockage</h5>
                        <p class="text-muted small mb-0">Modification des informations du destockage</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editDestockageForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>Informations du destockage
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date</label>
                                            <input type="date" class="form-control" name="date_op" id="edit_date" required>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Dépôt</label>
                                            <select class="form-select select2-edit" name="depot_id" id="edit_depot_id" required>
                                                <option value="">Sélectionner un dépôt</option>
                                                @foreach ($depots as $depot)
                                                    <option value="{{ $depot->id }}">{{ $depot->libelle_depot }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un dépôt</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Client</label>
                                            <select class="form-select select2-edit" name="client_id" id="edit_client_id" required>
                                                <option value="">Sélectionner un client</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}">{{ $client->raison_sociale }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un client</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Reference</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control text-end" name="reference"
                                                       id="edit_reference">
                                            </div>
                                            <div class="invalid-feedback">Le montant est requis</div>
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Observation</label>
                                            <textarea class="form-control" name="observation" id="edit_observation" rows="2"
                                                placeholder="Observation ou note concernant l'acompte"></textarea>
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
