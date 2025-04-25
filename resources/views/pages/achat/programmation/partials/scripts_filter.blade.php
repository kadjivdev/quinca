<script>
    $(document).ready(function() {
        // Configuration AJAX globale
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Gestionnaire des Programmations
        const ProgrammationManager = {
            init: function() {
                this.initSelectors();
                this.initEvents();
                this.initViewState();
                this.loadArticles();
            },

            // Initialisation des sélecteurs
            initSelectors: function() {
                this.selectors = {
                    addModal: '#addProgrammationModal',
                    addForm: '#programmationForm',
                    codeInput: '#codeProgrammation',
                    regenerateBtn: '#regenerateCode',
                    addArticleBtn: '#addArticleRow',
                    articlesContainer: '#articlesContainer',
                    fournisseurSelect: 'select[name="fournisseur_id"]',
                    submitBtn: '#submitProgrammationBtn'
                };
            },

            // Initialisation des événements
            initEvents: function() {
                $(this.selectors.addModal).on('show.bs.modal', () => this.generateNewCode());
                $(this.selectors.addModal).on('hidden.bs.modal', () => this.resetForm());
                $(this.selectors.regenerateBtn).on('click', () => this.generateNewCode());
                $(this.selectors.addArticleBtn).on('click', () => this.addArticleRow());
                $(this.selectors.addForm).on('submit', (e) => this.handleSubmit(e));
                $(this.selectors.fournisseurSelect).on('change', (e) => this.handleFournisseurChange(
                e));

                // Délégation d'événements pour les boutons dynamiques
                $(this.selectors.articlesContainer).on('click', '.remove-article', (e) => {
                    $(e.target).closest('.article-row').remove();
                });

                $(this.selectors.articlesContainer).on('change', '.article-select', (e) => {
                    this.updateUnites($(e.target));
                });
            },

            // État initial
            initViewState: function() {
                this.addArticleRow(); // Ajoute une première ligne d'article
            },

            // Génération du code
            generateNewCode: function() {
                $.get("{{ route('programmations.generate-code') }}")
                    .done((response) => {
                        $(this.selectors.codeInput).val(response.code);
                    })
                    .fail(() => {
                        Toast.fire({
                            icon: 'error',
                            title: "Erreur lors de la génération du code"
                        });
                    });
            },

            // Chargement des articles
            loadArticles: function() {
                $.get("{{ route('articles.list') }}")
                    .done((response) => {
                        this.articles = response;
                        this.updateArticleSelects();
                    })
                    .fail(() => {
                        Toast.fire({
                            icon: 'error',
                            title: "Erreur lors du chargement des articles"
                        });
                    });
            },

            // Mise à jour des sélecteurs d'articles
            updateArticleSelects: function() {
                const options = this.articles.map(article =>
                    `<option value="${article.id}" data-unites='${JSON.stringify(article.unites)}'>
                        ${article.code_article} - ${article.designation}
                    </option>`
                ).join('');

                $('.article-select').each(function() {
                    const currentVal = $(this).val();
                    $(this).html(`<option value="">Sélectionner un article</option>${options}`);
                    $(this).val(currentVal);
                });
            },

            // Ajout d'une ligne d'article
            addArticleRow: function() {
                const template = `
                    <div class="article-row mb-3">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <select class="form-select article-select" name="articles[]" required>
                                    <option value="">Sélectionner un article</option>
                                    ${this.articles ? this.articles.map(article =>
                                        `<option value="${article.id}" data-unites='${JSON.stringify(article.unites)}'>
                                            ${article.code_article} - ${article.designation}
                                        </option>`
                                    ).join('') : ''}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="quantites[]"
                                           min="0.01" step="0.01" required placeholder="Quantité">
                                    <select class="form-select unite-select" name="unites[]" required
                                            style="max-width: 100px;">
                                        <option value="">Unité</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-article">
                                    <i class="fas fa-times"></i>Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $(this.selectors.articlesContainer).append(template);
            },

            // Mise à jour des unités de mesure
            updateUnites: function(articleSelect) {
                const selectedOption = articleSelect.find('option:selected');
                const unites = JSON.parse(selectedOption.data('unites') || '[]');
                const uniteSelect = articleSelect.closest('.article-row').find('.unite-select');

                uniteSelect.html('<option value="">Unité</option>' +
                    unites.map(unite =>
                        `<option value="${unite.id}">${unite.symbole}</option>`
                    ).join('')
                );
            },

            // Gestion du changement de fournisseur
            handleFournisseurChange: function(e) {
                const fournisseurId = $(e.target).val();
                if (fournisseurId) {
                    $.get(`/fournisseurs/${fournisseurId}/articles`)
                        .done((response) => {
                            this.articles = response;
                            this.updateArticleSelects();
                        })
                        .fail(() => {
                            Toast.fire({
                                icon: 'error',
                                title: "Erreur lors du chargement des articles"
                            });
                        });
                }
            },

            // Soumission du formulaire
            handleSubmit: function(e) {
                e.preventDefault();
                const form = $(this.selectors.addForm);

                if (!form[0].checkValidity()) {
                    e.stopPropagation();
                    form.addClass('was-validated');
                    return;
                }

                const submitBtn = $(this.selectors.submitBtn);
                const formData = new FormData(form[0]);

                submitBtn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-2"></i>Traitement...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message ||
                                    'Précommande créée avec succès'
                            });
                            $(this.selectors.addModal).modal('hide');
                            window.location.reload();
                        }
                    },
                    error: (xhr) => {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message ||
                                'Une erreur est survenue'
                        });
                    },
                    complete: () => {
                        submitBtn.prop('disabled', false)
                            .html('<i class="fas fa-save me-2"></i>Enregistrer');
                    }
                });
            },

            // Réinitialisation du formulaire
            resetForm: function() {
                const form = $(this.selectors.addForm);
                form[0].reset();
                form.removeClass('was-validated');
                $(this.selectors.articlesContainer).empty();
                this.addArticleRow();
            }
        };

        // Initialisation au chargement
        ProgrammationManager.init();

        // Fonctions globales
        window.editProgrammation = function(id) {
            // Implémentation de la modification
        };

        window.validateProgrammation = function(id) {
            // Implémentation de la validation
        };

        window.deleteProgrammation = function(id) {
            // Implémentation de la suppression
        };

        window.viewDetails = function(id) {
            // Implémentation de l'affichage des détails
        };
    });
</script>
