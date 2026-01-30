<div class="modal fade" id="editAcompteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-edit fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier le versement</h5>
                        <p class="text-muted small mb-0">Modification des informations du versement</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editAcompteForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Informations du versement --}}
                        <div class="col-md-12">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted">
                                        <i class="fas fa-info-circle me-2"></i>Informations du versement
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date de l'opération</label>
                                            <input type="date" class="form-control" name="date_op"
                                                required id="edit_date_op">
                                            <div class="invalid-feedback">La date d'opération est requise</div>
                                            @error("date_op")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date valeur</label>
                                            <input type="date" class="form-control" name="date_valeur"
                                                required id="edit_date_valeur">
                                            <div class="invalid-feedback">La date valeur est requise</div>
                                            @error("date_valeur")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Réference</label>
                                            <input type="text" class="form-control" name="reference_op" placeholder="***********"
                                                required id="edit_reference_op">
                                            <div class="invalid-feedback">La réference de l'opération est requise</div>
                                            @error("reference_op")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Client</label>
                                            <select class="form-select select2" name="client_id" required id="edit_client_id">
                                                <!-- JS -->
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un client</div>
                                            @error("client_id")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Type d'opération</label>
                                            <select class="form-select" name="type_op" required id="type_op">
                                                <option value="">***Selectionner un type d'opération***</option>
                                                <option value="MoMo">Momo</option>
                                                <option value="Chèque">Chèque</option>
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un type de paiement</div>
                                            @error("type_op")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Banque</label>
                                            <input type="text" class="form-control" name="banque" placeholder="Ex: BOA" required id="edit_banque">
                                            <div class="invalid-feedback">La banque est requise</div>
                                            @error("banque")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-end" name="montant"
                                                    required min="0" step="0.001" placeholder="0.000" id="edit_montant">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                            <div class="invalid-feedback">Le montant est requis et doit être positif</div>
                                            @error("montant")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Preuve</label>
                                            <div id="preuveFile">
                                                <!-- js -->
                                            </div>
                                            <input type="file" class="form-control" name="preuve" >
                                            @error('preuve')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Commentaire</label>
                                            <textarea class="form-control" name="comment" rows="2"
                                                id="edit_comment"
                                                placeholder="Observation ou commentaire concernant le versement"></textarea>
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