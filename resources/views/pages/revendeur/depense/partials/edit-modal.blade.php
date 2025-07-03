<!-- Modal de mise à jour -->
<div class="modal fade" id="updateFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-xxl-down bg-white">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal avec un design similaire à l'ajout --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier la depense <span id="factureNumber"></span></h5>
                        <p class="text-muted small mb-0">Modifiez les informations de la facture</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="updateFactureForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section informations générales --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations Générales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Date facture</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_facture" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Dépôt</label>
                                            <div class="input-group">
                                                <div class="">
                                                    <input type="search" class="form-control" id="input-search">
                                                    <select class="form-select _client-select2" id="content-block" name="client_id" required>
                                                        <option class="first" value="">Selectionnez un dépôt</option>
                                                        @foreach ($depots as $depot)
                                                        <option value="{{ $depot->id }}">
                                                            {{ $depot->libelle_depot }}
                                                        </option>
                                                        @endforeach
                                                    </select>
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
                        <i class="fas fa-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>