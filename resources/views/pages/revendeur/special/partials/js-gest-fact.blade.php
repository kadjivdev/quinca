// facture-manager.js

'use strict';

class FactureManager {
    constructor() {
        this.index = 0;
        this.isInitialized = false;
        this.isProcessing = false;
    }

    init() {
        if (this.isInitialized) return;

        this.initializeFormValidation();
        this.initClientSelect();
        this.initEvents();
        this.addLigne();

        this.isInitialized = true;
    }

    initClientSelect() {
        const clientSelect = $(FactureConfig.selectors.clientSelect);
        if (clientSelect.length) {
            clientSelect.select2({
                ...FactureConfig.select2Options,
                dropdownParent: $(FactureConfig.selectors.modal),
                placeholder: 'Sélectionner un client',
                allowClear: true
            }).on('change', () => this.updateTotaux());
        }
    }

    async initTarifSelect(row, index) {
        const tarifSelect = row.find(`select[name="lignes[${index}][tarification_id]"]`);

        if (tarifSelect.hasClass('select2-hidden-accessible')) {
            tarifSelect.select2('destroy');
        }

        tarifSelect.select2({
            ...FactureConfig.select2Options,
            dropdownParent: $(FactureConfig.selectors.modal),
            placeholder: 'Sélectionner un tarif',
            allowClear: true
        }).on('change', () => this.handleCalculations({ target: tarifSelect[0] }));
    }

    initEvents() {
        const {addButton, ligneContainer, form, modal} = FactureConfig.selectors;

        $(document).off('click', addButton).on('click', addButton, () => this.addLigne());

        $(ligneContainer).off('click', '.remove-ligne').on('click', '.remove-ligne', (e) => {
            const row = $(e.target).closest('tr');
            if ($(ligneContainer).find('tr').length > 1) {
                if (confirm(FactureMessages.confirmations.delete)) {
                    row.fadeOut(300, () => {
                        row.remove();
                        this.updateTotaux();
                    });
                }
            }
        });

        $(ligneContainer).off('change keyup', '.quantite-input, .remise-input, .select2-tarifs')
            .on('change keyup', '.quantite-input, .remise-input, .select2-tarifs',
                (e) => this.handleCalculations(e));

        $(form).off('submit').on('submit', (e) => this.handleSubmit(e));

        $(modal).off('hidden.bs.modal').on('hidden.bs.modal', () => {
            if (confirm(FactureMessages.confirmations.cancel)) {
                this.resetForm();
            }
        });
    }

    async addLigne() {
        const template = $(FactureConfig.selectors.template).html();
        const newLine = template.replace(/__INDEX__/g, this.index);
        const $newLine = $(newLine);

        $newLine.hide();
        $(FactureConfig.selectors.ligneContainer).append($newLine);
        $newLine.fadeIn(300);

        await this.initSelect2ForRow(this.index);
        this.index++;
    }

    async initSelect2ForRow(index) {
        const row = $(`select[name="lignes[${index}][article_id]"]`).closest('tr');

        await this.initArticleSelect(row, index);
        await this.initTarifSelect(row, index);
        await this.initUniteSelect(row, index);
    }

    async initArticleSelect(row, index) {
        const articleSelect = row.find(`select[name="lignes[${index}][article_id]"]`);

        if (articleSelect.hasClass('select2-hidden-accessible')) {
            articleSelect.select2('destroy');
        }

        articleSelect.select2({
            ...FactureConfig.select2Options,
            dropdownParent: $(FactureConfig.selectors.modal),
            placeholder: 'Rechercher un article...',
            allowClear: true,
            ajax: {
                url: FactureConfig.routes.articlesSearch,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination?.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            templateResult: this.formatArticle,
            templateSelection: this.formatArticleSelection,
            escapeMarkup: markup => markup
        }).on('select2:select', (e) => this.loadArticleDetails(index, e.target.value))
          .on('select2:open', function() {
              setTimeout(() => {
                  $('.select2-search__field').focus();
              }, 0);
          });
    }

    formatArticle(article) {
        if (article.loading) return article.text;
        return `<div class="select2-result-article">
                    <div class="select2-result-article__code">${article.code_article || ''}</div>
                    <div class="select2-result-article__title">${article.text || ''}</div>
                    <div class="select2-result-article__stock">Stock: ${article.stock || 0}</div>
                </div>`;
    }

    formatArticleSelection(article) {
        if (!article.id) return article.text;
        return `${article.code_article} - ${article.text}`;
    }

    async initUniteSelect(row, index) {
        const uniteSelect = row.find(`select[name="lignes[${index}][unite_vente_id]"]`);

        if (uniteSelect.hasClass('select2-hidden-accessible')) {
            uniteSelect.select2('destroy');
        }

        uniteSelect.select2({
            ...FactureConfig.select2Options,
            dropdownParent: $(FactureConfig.selectors.modal),
            placeholder: 'Sélectionner une unité',
            allowClear: true
        });
    }

    async loadArticleDetails(index, articleId) {
        const row = $(`select[name="lignes[${index}][article_id]"]`).closest('tr');

        try {
            this.showRowLoading(row);

            let tarifsData = await ArticleCache.get('tarifs', articleId);
            let unitesData = await ArticleCache.get('unites', articleId);

            if (!tarifsData) {
                const tarifResponse = await this.fetchWithHeaders(
                    FactureConfig.routes.getTarifs(articleId)
                );
                tarifsData = await tarifResponse.json();
                if (tarifsData.status === 'success') {
                    ArticleCache.set('tarifs', articleId, tarifsData);
                }
            }

            if (!unitesData) {
                const uniteResponse = await this.fetchWithHeaders(
                    FactureConfig.routes.getUnites(articleId)
                );
                unitesData = await uniteResponse.json();
                if (unitesData.status === 'success') {
                    ArticleCache.set('unites', articleId, unitesData);
                }
            }

            await this.updateSelects(row, index, tarifsData, unitesData);

        } catch (error) {
            console.error('Erreur loadArticleDetails:', error);
            this.showNotification('error', FactureMessages.errors.articleLoad);
        } finally {
            this.hideRowLoading(row);
        }
    }

    async updateSelects(row, index, tarifsData, unitesData) {
        const tarifSelect = row.find(`select[name="lignes[${index}][tarification_id]"]`);
        const uniteSelect = row.find(`select[name="lignes[${index}][unite_vente_id]"]`);

        // Mise à jour des tarifs
        tarifSelect.empty().append('<option value="">Sélectionner un tarif</option>');
        if (tarifsData?.data?.tarifs) {
            tarifsData.data.tarifs.forEach(tarif => {
                const option = new Option(tarif.text, tarif.id);
                $(option).data('prix', tarif.prix);
                tarifSelect.append(option);
            });
        }

        // Mise à jour des unités
        uniteSelect.empty().append('<option value="">Sélectionner une unité</option>');
        if (unitesData?.data?.unites) {
            unitesData.data.unites.forEach(unite => {
                uniteSelect.append(new Option(unite.text, unite.id));
            });
        }

        tarifSelect.trigger('change');
        uniteSelect.trigger('change');
    }

    handleCalculations(event) {
        const row = $(event.target).closest('tr');
        this.calculateRowTotal(row);
        this.updateTotaux();
    }

    calculateRowTotal(row) {
        try {
            const quantite = FactureUtils.parseNumber(row.find('.quantite-input').val());
            const tarifSelect = row.find('.select2-tarifs');
            const prix = FactureUtils.parseNumber(tarifSelect.find(':selected').data('prix'));
            const remise = FactureUtils.parseNumber(row.find('.remise-input').val());

            if (quantite && prix) {
                const montantBrut = quantite * prix;
                const montantRemise = montantBrut * (remise / 100);
                const totalHT = FactureUtils.roundNumber(montantBrut - montantRemise);

                row.find('.total-ligne').val(FactureUtils.formatMoney(totalHT));
            } else {
                row.find('.total-ligne').val(FactureUtils.formatMoney(0));
            }
        } catch (error) {
            console.error('Erreur calcul total:', error);
            row.find('.total-ligne').val(FactureUtils.formatMoney(0));
        }
    }

    updateTotaux() {
        let totalHT = 0;

        // Calcul du total HT
        $(FactureConfig.selectors.ligneContainer).find('tr').each((_, row) => {
            const montantLigne = FactureUtils.parseNumber($(row).find('.total-ligne').val());
            totalHT += montantLigne;
        });

        // Récupération des taux
        const clientSelect = $('select[name="client_id"]');
        const selectedOption = clientSelect.find(':selected');
        let tauxAib = 0;

        if (selectedOption.length && selectedOption.val()) {
            tauxAib = parseFloat(selectedOption.data('taux-aib')) || 0;
        }

        // Calculs des montants
        const tauxTva = FactureConfig.TVA.rate;
        const montantTva = FactureUtils.roundNumber((totalHT * tauxTva) / 100);
        const montantAib = FactureUtils.roundNumber((totalHT * tauxAib) / 100);
        const totalTTC = FactureUtils.roundNumber(totalHT + montantTva + montantAib);

        // Debug des calculs
        console.log('Détails des calculs:', {
            totalHT,
            tauxTva,
            montantTva,
            tauxAib,
            montantAib,
            totalTTC
        });

        // Mise à jour de l'affichage
        $(FactureConfig.selectors.totalHT).text(FactureUtils.formatMoney(totalHT) + ' FCFA');
        $(FactureConfig.selectors.totalTVA).text(FactureUtils.formatMoney(montantTva) + ' FCFA');
        $(FactureConfig.selectors.totalAIB).text(FactureUtils.formatMoney(montantAib) + ' FCFA');
        $(FactureConfig.selectors.totalTTC).text(FactureUtils.formatMoney(totalTTC) + ' FCFA');

        // Mise à jour du montant restant si nécessaire
        this.updateMontantRestant();
    }

    updateMontantRestant() {
        const totalTTC = FactureUtils.parseNumber($(FactureConfig.selectors.totalTTC).text());
        const montantRegle = FactureUtils.parseNumber($('#montantRegle').val());
        const restant = montantRegle - totalTTC;

        const montantRestantElement = $('#montantRestant');
        const messageRestantElement = $('#messageRestant');

        if (montantRestantElement.length) {
            montantRestantElement.val(FactureUtils.formatMoney(Math.abs(restant)));
        }

        if (messageRestantElement.length) {
            if (restant > 0) {
                messageRestantElement.text("À rendre au client").removeClass().addClass("text-success");
            } else if (restant < 0) {
                messageRestantElement.text("Dû par le client").removeClass().addClass("text-danger");
            } else {
                messageRestantElement.text("Compte exact").removeClass().addClass("text-primary");
            }
        }
    }

    async handleSubmit(event) {
        event.preventDefault();
        if (this.isProcessing) return;

        const form = event.target;
        if (!form.checkValidity()) {
            event.stopPropagation();
            $(form).addClass('was-validated');
            this.showNotification('error', FactureMessages.errors.validation);
            return;
        }

        try {
            this.isProcessing = true;
            this.showFormLoading();

            const response = await this.fetchWithHeaders(FactureConfig.routes.store, {
                method: 'POST',
                body: new FormData(form)
            });

            const data = await response.json();

            if (data.status === 'success') {
                this.showNotification('success', FactureMessages.success.created);
                this.resetForm();
                window.location.reload();
            } else {
                throw new Error(data.message || FactureMessages.errors.submission);
            }

        } catch (error) {
            console.error('Erreur soumission:', error);
            this.showNotification('error', error.message);
        } finally {
            this.isProcessing = false;
            this.hideFormLoading();
        }
    }

    fetchWithHeaders(url, options = {}) {
        return fetch(url, {
            ...options,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                ...(options.headers || {})
            }
        });
    }

    showRowLoading(row) {
        row.addClass('loading');
        row.find('select, input').prop('disabled', true);
    }

    hideRowLoading(row) {
        row.removeClass('loading');
        row.find('select, input').prop('disabled', false);
    }

    showFormLoading() {
        const submitBtn = $(FactureConfig.selectors.form).find('button[type="submit"]');
        submitBtn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...');
        $(FactureConfig.selectors.form).addClass('form-loading');
    }

    hideFormLoading() {
        const submitBtn = $(FactureConfig.selectors.form).find('button[type="submit"]');
        submitBtn.prop('disabled', false)
            .html('<i class="fas fa-save me-2"></i>Enregistrer');
        $(FactureConfig.selectors.form).removeClass('form-loading');
    }

    showNotification(type, message) {
        if (window.Swal) {
            Swal.fire({
                icon: type,
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        } else {
            alert(message);
        }
    }

    resetForm() {
        const form = $(FactureConfig.selectors.form);
        form.removeClass('was-validated');
        form[0].reset();

        // Réinitialiser les select2
        form.find('select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).val(null).trigger('change');
            }
        });

        // Vider le conteneur de lignes
        $(FactureConfig.selectors.ligneContainer).empty();

        // Réinitialiser les totaux
        this.updateTotaux();

        // Réinitialiser l'index et ajouter une nouvelle ligne
        this.index = 0;
        this.addLigne();

        // Vider le cache
        ArticleCache.clear();
    }

    initializeFormValidation() {
        const form = document.querySelector(FactureConfig.selectors.form);
        if (!form) return;

        form.setAttribute('novalidate', '');

        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        form.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', () => {
                if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            });
        });
    }
}

// Initialisation unique
$(document).ready(() => {
    if (!window.factureManager) {
        window.factureManager = new FactureManager();
        window.factureManager.init();
    }
});
