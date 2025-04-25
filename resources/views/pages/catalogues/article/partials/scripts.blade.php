<script>
    $(document).ready(function() {
        // Configuration AJAX globale
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Gestionnaire des Articles
        const ArticleManager = {
            init: function() {
                this.initSelectors();
                this.initEvents();
                this.initViewState();
            },

            // Initialisation des sélecteurs
            initSelectors: function() {
                this.selectors = {
                    // Sélecteurs du modal d'ajout
                    addModal: '#addArticleModal',
                    addForm: '#articleForm',
                    codeInput: '#codeArticle',
                    regenerateBtn: '#regenerateCode',
                    stockableCheck: '#stockableCheck',
                    submitBtn: '#submitArticleBtn',

                    // Sélecteurs du modal d'édition
                    editModal: '#editArticleModal',
                    editForm: '#editArticleForm',
                    editStockableCheck: '#editStockableCheck',
                    editSubmitBtn: '#editSubmitBtn',
                    currentPhoto: '#currentPhoto',

                    // Sélecteurs communs
                    stockFields: '.stock-fields',
                    photoInput: 'input[name="photo"]',
                    photoPreview: '#photoPreview'
                };
            },

            // Initialisation des événements
            initEvents: function() {
                // Événements du modal d'ajout
                $(this.selectors.addModal).on('show.bs.modal', () => this.generateNewCode());
                $(this.selectors.addModal).on('hidden.bs.modal', () => this.resetForm(this.selectors.addForm));
                $(this.selectors.regenerateBtn).on('click', () => this.generateNewCode());
                $(this.selectors.stockableCheck).on('change', () => this.toggleStockFields(this.selectors.addForm));
                $(this.selectors.addForm).on('submit', (e) => this.handleSubmit(e));

                // Événements du modal d'édition
                $(this.selectors.editStockableCheck).on('change', () => this.toggleStockFields(this.selectors.editForm));
                $(this.selectors.editForm).on('submit', (e) => this.handleEditSubmit(e));
                $(this.selectors.photoInput).on('change', (e) => this.handlePhotoUpload(e));
            },

            // État initial
            initViewState: function() {
                this.toggleStockFields(this.selectors.addForm);
                this.toggleStockFields(this.selectors.editForm);
            },

            // Génération du code article
            generateNewCode: function() {
                $.get("{{ route('articles.generate-code') }}")
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

            // Gestion de l'ajout d'un article
            handleSubmit: function(e) {
                e.preventDefault();
                const form = e.currentTarget;
                const submitBtn = $(form).find('button[type="submit"]');
                const formData = new FormData(form);

                // Gestion du checkbox stockable
                formData.set('stockable', $(this.selectors.stockableCheck).is(':checked') ? '1' : '0');

                submitBtn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-2"></i>Traitement...');

                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message || 'Article ajouté avec succès'
                            });
                            $(this.selectors.addModal).modal('hide');
                            window.location.reload();
                        }
                    },
                    error: (xhr) => {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Une erreur est survenue'
                        });
                    },
                    complete: () => {
                        submitBtn.prop('disabled', false)
                            .html('<i class="fas fa-save me-2"></i>Enregistrer');
                    }
                });
            },

            // Chargement d'un article pour modification
            loadArticle: function(id) {
                $.ajax({
                    url: `${apiUrl}/catalogue/articles/${id}/edit`,
                    method: 'GET',
                    success: (response) => {
                        if (response.success) {
                            this.fillEditForm(response.data);
                            $(this.selectors.editModal).modal('show');
                        }
                    },
                    error: () => {
                        Toast.fire({
                            icon: 'error',
                            title: 'Erreur lors du chargement de l\'article'
                        });
                    }
                });
            },

            // Remplissage du formulaire de modification
            fillEditForm: function(article) {
                const form = $(this.selectors.editForm);

                form.attr('action', `${apiUrl}/catalogue/articles/${article.id}`);

                // Remplissage des champs
                form.find('[name="code_article"]').val(article.code_article);
                form.find('[name="designation"]').val(article.designation);
                form.find('[name="famille_id"]').val(article.famille_id);
                form.find(this.selectors.editStockableCheck).prop('checked', article.stockable);
                form.find('[name="stock_minimum"]').val(article.stock_minimum);
                form.find('[name="stock_maximum"]').val(article.stock_maximum);
                form.find('[name="stock_securite"]').val(article.stock_securite);
                form.find('[name="stock_actuel"]').val(article.stock_actuel);
                form.find('[name="emplacement_stock"]').val(article.emplacement_stock);

                // Marquer le mode de reglement
                const uniteList = $("[name='unite_mesure_id']");
                const uniteOption = uniteList.find(`option[value="${article.unite_mesure_id}"]`);
                if (uniteOption.length > 0) {
                    uniteOption.prop("selected", true);
                }

                // Gestion de la photo
                $(this.selectors.currentPhoto).empty();
                if (article.photo) {
                    $(this.selectors.currentPhoto).html(`
                        <div class="position-relative d-inline-block">
                            <img src="${article.photo}" class="img-thumbnail" style="max-height: 150px;">
                            <button type="button"
                                    class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                    onclick="ArticleManager.deletePhoto(${article.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `);
                }

                this.toggleStockFields(this.selectors.editForm);

            },

            // Gestion de la modification
            handleEditSubmit: function(e) {
                e.preventDefault();

                const form = e.currentTarget;
                const submitBtn = $(this.selectors.editSubmitBtn);
                const formData = new FormData(form);

                formData.set('stockable', $(this.selectors.editStockableCheck).is(':checked') ? '1' : '0');

                submitBtn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-2"></i>Traitement...');

                $.ajax({
                    url: form.action,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: (response) => {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message || 'Article modifié avec succès'
                            });
                            $(this.selectors.editModal).modal('hide');
                            window.location.reload();
                        }
                    },
                    error: (xhr) => {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erreur lors de la modification'
                        });
                    },
                    complete: () => {
                        submitBtn.prop('disabled', false)
                            .html('<i class="fas fa-save me-2"></i>Enregistrer');
                    }
                });
            },

            // Gestion des champs de stock
            toggleStockFields: function(formSelector) {
                const form = $(formSelector);
                const isStockable = form.find('[name="stockable"]').is(':checked');
                const stockFields = form.find(this.selectors.stockFields);
                const stockInputs = stockFields.find('input').not('[name="stock_actuel"]');

                stockFields.toggle(isStockable);
                stockInputs.prop('required', isStockable);
            },

            // Réinitialisation du formulaire
            resetForm: function(formSelector) {
                const form = $(formSelector);
                if (form.length) {
                    form[0].reset();
                    form.removeClass('was-validated');
                    $(this.selectors.photoPreview).empty();
                    this.toggleStockFields(formSelector);
                }
            },

            // Suppression de la photo
            deletePhoto: function(articleId) {
                Swal.fire({
                    title: 'Confirmer la suppression',
                    text: "Voulez-vous vraiment supprimer la photo ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${apiUrl}/catalogue/articles/${articleId}/delete-photo`,
                            method: 'POST',
                            success: (response) => {
                                if (response.success) {
                                    $(this.selectors.currentPhoto).empty();
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Photo supprimée avec succès'
                                    });
                                }
                            },
                            error: () => {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Erreur lors de la suppression de la photo'
                                });
                            }
                        });
                    }
                });
            },

            // Gestion de l'upload de photo
            handlePhotoUpload: function(e) {
                const file = e.target.files[0];
                const maxSize = 2 * 1024 * 1024; // 2MB
                const photoPreview = $(this.selectors.photoPreview);

                if (file) {
                    if (file.size > maxSize) {
                        Toast.fire({
                            icon: 'error',
                            title: 'La taille de l\'image ne doit pas dépasser 2MB'
                        });
                        e.target.value = '';
                        photoPreview.empty();
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        photoPreview.html(`
                            <div class="position-relative d-inline-block">
                                <img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        `);
                    };
                    reader.readAsDataURL(file);
                } else {
                    photoPreview.empty();
                }
            }
        };

        // Fonction globale pour éditer un article
        window.editArticle = function(id) {
            ArticleManager.loadArticle(id);
        };

        // Initialisation au chargement
        ArticleManager.init();
    });
</script>