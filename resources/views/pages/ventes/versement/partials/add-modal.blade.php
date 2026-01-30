<div class="modal fade" id="addAcompteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau versement</h5>
                        <p class="text-muted small mb-0">Enregistrement d'un versement</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addAcompteForm" class="needs-validation" novalidate enctype="multipart/form-data">
                @csrf
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
                                                required value="{{ date('Y-m-d') }}">
                                            <div class="invalid-feedback">La date d'opération est requise</div>
                                            @error("date_op")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date valeur</label>
                                            <input type="date" class="form-control" name="date_valeur"
                                                required value="{{ date('Y-m-d') }}">
                                            <div class="invalid-feedback">La date valeur est requise</div>
                                            @error("date_valeur")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Réference</label>
                                            <input type="text" class="form-control" name="reference_op" placeholder="***********" required>
                                            <div class="invalid-feedback">La réference de l'opération est requise</div>
                                            @error("reference_op")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Client</label>
                                            <select class="form-select select2" name="client_id" required>
                                                <option value="">Sélectionner un client</option>
                                                @foreach ($clients as $client)
                                                <option value="{{ $client->id }}">{{ $client->raison_sociale }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Veuillez sélectionner un client</div>
                                            @error("client_id")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Type de paiement</label>
                                            <select class="form-select" name="type_op" required>
                                                <option value="">Sélectionner un type</option>
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
                                            <input type="text" class="form-control" name="banque" placeholder="Ex: BOA" required>
                                            <div class="invalid-feedback">La banque est requise</div>
                                            @error("banque")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-end" name="montant"
                                                    required min="0" step="0.001" placeholder="0.000">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                            <div class="invalid-feedback">Le montant est requis et doit être positif</div>
                                            @error("montant")
                                            <div class="text-danger small">{{$message}}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Preuve</label>
                                            <input type="file" class="form-control" name="preuve" required>
                                            @error('preuve')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-12">
                                            <label class="form-label fw-medium">Commentaire</label>
                                            <textarea class="form-control" name="comment" rows="2"
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

<style>
    /* Les mêmes styles que pour le modal client, pas besoin de les modifier */
    :root {
        --kadjiv-orange: #FFA500;
        --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
    }

    /* Styles spécifiques pour la select2 */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #e9ecef;
        padding: 0.6rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 6px;
        min-height: 40px;
    }

    .select2-container--bootstrap-5 .select2-selection--single {
        background-color: #fff;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #212529;
        line-height: 1.5;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: var(--kadjiv-orange);
        border-radius: 6px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
        background-color: var(--kadjiv-orange);
    }
</style>

@push("scripts")
<script>
    $(".select2").select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#addAcompteModal')
    })
</script>
@endpush