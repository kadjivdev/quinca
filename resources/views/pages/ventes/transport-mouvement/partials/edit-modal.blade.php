<div class="modal fade" id="editTransportMouvementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-edit fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier le mouvement du transport</h5>
                        <p class="text-muted small mb-0">Modification des informations du mouvement</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editTransportMouvementForm" class="needs-validation" novalidate>
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
                                        <i class="fas fa-info-circle me-2"></i>Informations du mouvement
                                    </h6>
                                    <div class="row g-4">
                                        {{-- Informations du versement --}}
                                        <div class="col-md-12">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-3 text-muted">
                                                        <i class="fas fa-info-circle me-2"></i>Informations du mouvement
                                                    </h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <select class="form-select edit_select2" name="transportation_id" id="edit_transportation_id" required>
                                                                <!--  -->
                                                            </select>
                                                            <div class="invalid-feedback">Le moyen de transport est requis</div>
                                                            @error("transportation_id")
                                                            <div class="text-danger small">{{$message}}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <select class="form-select edit_select2" name="client_id" id="edit_client_id" required>
                                                                <!--  -->
                                                            </select>
                                                            <div class="invalid-feedback">Le client est requis</div>
                                                            @error("client_id")
                                                            <div class="text-danger small">{{$message}}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium required">Date du transport</label>
                                                            <input type="date" class="form-control" name="date" id="edit_date"
                                                                required value="{{ date('Y-m-d') }}">
                                                            <div class="invalid-feedback">La date du transport est requise</div>
                                                            @error("date")
                                                            <div class="text-danger small">{{$message}}</div>
                                                            @enderror
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium required">Montant</label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control text-end" name="montant" id="edit_montant"
                                                                    required min="0" step="0.001" placeholder="0.000">
                                                                <span class="input-group-text">FCFA</span>
                                                            </div>
                                                            <div class="invalid-feedback">Le montant est requis et doit être positif</div>
                                                            @error("montant")
                                                            <div class="text-danger small">{{$message}}</div>
                                                            @enderror
                                                        </div>

                                                        <div class="col-md-6">
                                                            <label class="form-label fw-medium ">Preuve</label>
                                                            <input type="file" class="form-control" name="preuve">
                                                            @error('preuve')
                                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        <div class="col-md-12">
                                                            <label class="form-label fw-medium">Commentaire</label>
                                                            <textarea class="form-control" name="comment" rows="2" id="edit_comment"
                                                                placeholder="Observation ou commentaire concernant le versement"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
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

@push("scripts")
<script>
    $(".edit_select2").select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#editTransportMouvementModal')
    })
</script>
@endpush