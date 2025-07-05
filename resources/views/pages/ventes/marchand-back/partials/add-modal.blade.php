<div class="modal fade" id="addMarchandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg bg-white">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal avec un nouveau design --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle Marchandise</h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer une nouvelle
                            dépense</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('vente.marchand-back.store')}}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                @method("POST")

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
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date de retour</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date" required>
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Livraison</label>
                                            <select class="form-select" id="livraison_select" name="livraison_id" required>
                                                @foreach ($livraisons as $livraison)
                                                <option value="{{ $livraison->id }}">
                                                    {{ $livraison->numero }} -- {{$livraison->facture?->client->raison_sociale}}
                                                </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">La livraison est requise</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Observation</label>
                                            <textarea name="observation" class="form-control"></textarea>
                                            <div class="invalid-feedback">L'observation est requise</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Documents</label>
                                            <input type="file" name="documents" class="form-control">
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

@push('scripts')
<script type="text/javascript">
    $("#livraison_select").select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#addMarchandModal .modal-content'),
        placeholder: $(this).attr('placeholder') || 'Sélectionner...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Aucun résultat trouvé";
            },
            searching: function() {
                return "Recherche...";
            }
        }
    })
</script>
@endpush