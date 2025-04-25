<div class="modal fade" id="editProgrammationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" >
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-clipboard-list fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modification de Précommande</h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer une nouvelle
                            precommande</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="editProgrammationForm" class="needs-validation" novalidate>
                @method('PUT')
                @csrf
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
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Code</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-hashtag text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control" name="code" id="code"
                                                    required readonly>
                                            </div>
                                            <div class="invalid-feedback">Le code est requis</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Date precommande</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_programmation"
                                                    required value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Fournisseur</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-truck text-primary"></i>
                                                </span>
                                                <select class="form-select" name="fournisseur_id" id="fournisseurSelect"
                                                    required>
                                                    <option value="">Sélectionner un fournisseur</option>
                                                    @foreach ($fournisseurs as $fournisseur)
                                                        <option value="{{ $fournisseur->id }}">
                                                            {{ $fournisseur->raison_sociale }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le fournisseur est requis</div>
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
                                        <i class="fas fa-box me-2"></i>Articles
                                    </h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnAddLigneEdit">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50%">Article</th>
                                                    <th style="width: 25%">Quantité</th>
                                                    <th style="width: 20%">Unité</th>
                                                    <th style="width: 5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="lignesContainerMod">
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section commentaire --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Commentaire
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="commentaire" rows="3" placeholder="Commentaire éventuel"></textarea>
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

{{-- Template pour une nouvelle ligne --}}
<!-- <template id="ligneProgrammationTemplate">
    <tr class="ligne-programmation hover:bg-gray-50 transition-colors duration-200">
        <td class="p-2">
            <select class="form-select select2-articles" name="articles[]" required>
                <option value="">Sélectionner un article</option>
                @foreach ($articles as $article)
                    <option value="{{ $article->id }}">
                        {{ $article->code_article }} - {{ $article->designation }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback">L'article est requis</div>
        </td>
        <td class="p-2">
            <input type="number" class="form-control text-end" name="quantites[]" placeholder="0.00" required
                min="0.01" step="0.01">
            <div class="invalid-feedback">La quantité est requise</div>
        </td>
        <td class="p-2">
            <select class="form-select" name="unites[]" required>
                <option value="">Sélectionner une unité</option>
                @foreach ($unitesMesure as $unite)
                    <option value="{{ $unite->id }}">
                        {{ $unite->code_unite }} - {{ $unite->libelle_unite }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback">L'unité est requise</div>
        </td>
        <td class="p-2 text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-ligne">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template> -->


@push('scripts')
    <script>
        $(document).ready(function() {
            // Générer le code au chargement
            // generateCode();

            // Initialize Select2 for fournisseur
            $('#fournisseurSelect').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Charger les articles quand le fournisseur change
            $('#fournisseurSelect').on('change', function() {
                const fournisseurId = $(this).val();
                if (fournisseurId) {
                    loadArticles(fournisseurId);
                }
            });

            // Ajouter une nouvelle ligne
            $('#btnAddLigneEdit').on('click', function() {
                addNewLineMod();
            });

            // Supprimer une ligne
            $(document).on('click', '.remove-ligne', function() {
                $(this).closest('tr').remove();
            });

            // Soumission du formulaire
            $('#editProgrammationForm').on('submit', function(e) {
                e.preventDefault();
                if (this.checkValidity()) {
                    editProgrammationMod($(this), this.action);
                }
                $(this).addClass('was-validated');
            });
        });

        function generateCode() {
            $.ajax({
                url: '/achat/programmation/generate-code',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#code').val(response.code);
                    }
                }
            });
        }

        function loadArticles(fournisseurId) {
            $.ajax({
                url: `/achat/programmation/articles/${fournisseurId}`,
                method: 'GET',
                success: function(response) {
                    const articles = response;
                    updateArticlesOptions(articles);
                }
            });
        }

        function updateArticlesOptions(articles) {
            let options = '<option value="">Sélectionner un article</option>';
            articles.forEach(article => {
                options += `<option value="${article.id}" data-unites='${JSON.stringify(article.unites)}'>
                    ${article.designation}
                </option>`;
            });
            $('.select2-articles').html(options);
        }

        function addNewLineMod() {
            const template = document.getElementById('ligneProgrammationTemplate');
            const clone = template.content.cloneNode(true);
            $('#lignesContainerMod').append(clone);

            const newLine = $('#lignesContainerMod tr:last');

            // Initialize Select2 for the new line
            newLine.find('.select2-articles').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }

        function editProgrammationMod($form, action) {
            const formData = $form.serialize();
            console.log(action);

            $.ajax({
                url: action,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Fermer le modal
                        $('#editProgrammationForm').modal('hide');

                        // Afficher le message de succès
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        // Recharger la page après un court délai
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors de l\'enregistrement'
                    });
                }
            });
        }
    </script>
@endpush
