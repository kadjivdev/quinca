<div class="modal fade" id="addMarchandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-xxl-down bg-white">
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

                        {{-- Section articles --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i>Les articles concernés
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Article</th>
                                                    <th>Quantité</th>
                                                    <!-- <th>Prix</th> -->
                                                    <!-- <th>Total TTC</th> -->
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="lignesLivraisonContainer">
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                        </table>
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
    }).on('select2:select', function(e) {
        loadArticles(e);
    });

    function loadArticles(e) {
        const livraisonId = e.target.value;

        // Charger les données
        $.ajax({
            url: `${apiUrl}/vente/livraisons/${livraisonId}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    console.log(data)
                    // Lignes de livraison
                    let lignesHtml = '';
                    if(data.lignes.length>0){
                        data.lignes.forEach(ligne => {
                            lignesHtml += `
                                <tr>
                                    <td>
                                        <input type="hidden" name="lignes[${ligne.id}][article_id]" value="${ligne.article.id}">
                                        <div class="fw-medium">${ligne.article.designation}</div>
                                        <div class="small text-muted">${ligne.article.reference}</div>
                                    </td>
                                    <td class="text-center">
                                        <input type="number" name="lignes[${ligne.id}][quantite]" value="${ligne.quantite}">
                                        <input type="hidden" name="lignes[${ligne.id}][unite_vente_id]" value="${ligne.unite_id}">
                                        ${ligne.unite}

                                        <input type="hidden" name="lignes[${ligne.id}][prix_unitaire]" value="${ligne.prix_unitaire}">
                                    </td>
                                   
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-ligne">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }else{
                        lignesHtml+=`<p class="">Aucun article trouvé</p>`
                    }

                    $('#lignesLivraisonContainer').html(lignesHtml);
                }
            },

            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des détails'
                });
                $('#showLivraisonModal').modal('hide');
            }
        });
    }

    // Utiliser la délégation d'événements pour les éléments ajoutés dynamiquement
    $(document).on('click', '.remove-ligne', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
    });
</script>
@endpush