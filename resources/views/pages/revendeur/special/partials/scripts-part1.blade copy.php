
    'use strict';

// Configuration centralisée
const FactureConfig = {
    selectors: {
        modal: '#addFactureModal',
        form: '#addFactureForm',
        ligneContainer: '#lignesContainer',
        template: '#ligneFactureTemplate',
        addButton: '#btnAddLigne',
        totalHT: '#totalHT',
        totalTVA: '#totalTVA',
        totalTTC: '#totalTTC',
        totalAIB: '#totalAIB'
    },
    classes: {
        ligne: 'ligne-facture',
        quantiteInput: 'quantite-input',
        tarifSelect: 'select2-tarifs',
        uniteSelect: 'unite-select',
        remiseInput: 'remise-input',
        tvaInput: 'tva-input',
        totalLigne: 'total-ligne'
    },
    routes: {
        articlesSearch: 'ventes-speciales/api/articles/search',
        getTarifs: (id) => `ventes-speciales/articles/${id}/tarifs`,
        getUnites: (id) => `ventes-speciales/articles/${id}/unites`,
        store: 'ventes-speciales'
    },
    select2Options: {
        theme: 'bootstrap-5',
        width: '100%',
        language: 'fr'
    },
    TVA: {
        rate: 18 // Taux fixe de 18%
    }
};

const FactureUtils = {
    roundNumber(number, decimals = 2) {
        return Number(Math.round(number + 'e' + decimals) + 'e-' + decimals);
    },

    parseNumber(value) {
        if (!value) return 0;
        // Supprime les espaces et remplace la virgule par un point
        const cleanValue = value.toString().replace(/\s/g, '').replace(',', '.');
        return parseFloat(cleanValue) || 0;
    }
};

// Messages et configurations
const FactureMessages = {
    errors: {
        articleLoad: "Impossible de charger les détails de l'article",
        tarifLoad: "Impossible de charger les tarifs",
        uniteLoad: "Impossible de charger les unités de mesure",
        submission: "Erreur lors de l'enregistrement de la facture",
        validation: "Veuillez vérifier les champs du formulaire",
        network: "Erreur de connexion au serveur"
    },
    success: {
        created: "Facture créée avec succès",
        updated: "Facture mise à jour avec succès"
    },
    confirmations: {
        delete: "Êtes-vous sûr de vouloir supprimer cette ligne ?",
        cancel: "Êtes-vous sûr de vouloir annuler la saisie ?"
    }
};

// Cache pour les données
const ArticleCache = {
    _cache: new Map(),

    async get(type, id) {
        const key = `${type}_${id}`;
        return this._cache.get(key) || null;
    },

    set(type, id, data) {
        const key = `${type}_${id}`;
        this._cache.set(key, data);
    },

    clear() {
        this._cache.clear();
    }
};

class FactureManager {
    constructor() {
        this.index = 0;
        this.isInitialized = false;
        this.isProcessing = false;
    }

    init() {
        if (this.isInitialized) return;

        this.initializeFormValidation();
        this.initEvents();
        this.addLigne();

        this.isInitialized = true;
    }

    async initTarifSelect(row, index) {
        const tarifSelect = row.find(`select[name="lignes[${index}][tarification_id]"]`);

        if (tarifSelect.hasClass('select2-hidden-accessible')) {
            tarifSelect.select2('destroy');
        }

        tarifSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addFactureModal'),
            placeholder: 'Sélectionner un tarif',
            allowClear: true
        }).on('change', () => {
            // Utiliser handleCalculations au lieu de updateRowCalculations
            this.handleCalculations({ target: tarifSelect[0] });
        });
    }


    initEvents() {
        const {addButton, ligneContainer, form, modal} = FactureConfig.selectors;

        // Nettoyage et réinitialisation des événements
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

        // Événements pour les calculs
        $(ligneContainer).off('change keyup', '.quantite-input, .remise-input, .tva-input, .select2-tarifs')
            .on('change keyup', '.quantite-input, .remise-input, .tva-input, .select2-tarifs',
                (e) => this.handleCalculations(e));

        // Gestion du formulaire
        $(form).off('submit').on('submit', (e) => this.handleSubmit(e));

        // Réinitialisation lors de la fermeture du modal
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
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addFactureModal'),
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
              document.querySelector('.select2-search__field').focus();
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

    async initTarifSelect(row, index) {
        const tarifSelect = row.find(`select[name="lignes[${index}][tarification_id]"]`);

        if (tarifSelect.hasClass('select2-hidden-accessible')) {
            tarifSelect.select2('destroy');
        }

        tarifSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addFactureModal'),
            placeholder: 'Sélectionner un tarif',
            allowClear: true
        }).on('change', () => this.handleCalculations(row));
    }

    async initUniteSelect(row, index) {
        const uniteSelect = row.find(`select[name="lignes[${index}][unite_vente_id]"]`);

        if (uniteSelect.hasClass('select2-hidden-accessible')) {
            uniteSelect.select2('destroy');
        }

        uniteSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addFactureModal'),
            placeholder: 'Sélectionner une unité',
            allowClear: true
        });
    }

    async loadArticleDetails(index, articleId) {
        const row = $(`select[name="lignes[${index}][article_id]"]`).closest('tr');

        try {
            this.showRowLoading(row);

            // Vérifier le cache
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
        // Mise à jour des tarifs
        const tarifSelect = row.find(`select[name="lignes[${index}][tarification_id]"]`);
        tarifSelect.empty().append('<option value="">Sélectionner un tarif</option>');

        if (tarifsData?.data?.tarifs) {
            tarifsData.data.tarifs.forEach(tarif => {
                const option = new Option(tarif.text, tarif.id);
                $(option).data('prix', tarif.prix);
                tarifSelect.append(option);
            });
        }

        // Mise à jour des unités
        const uniteSelect = row.find(`select[name="lignes[${index}][unite_vente_id]"]`);
        uniteSelect.empty().append('<option value="">Sélectionner une unité</option>');

        if (unitesData?.data?.unites) {
            unitesData.data.unites.forEach(unite => {
                uniteSelect.append(new Option(unite.text, unite.id));
            });
        }

        // Rafraîchir Select2
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
                // Calcul du total HT avec remise
                const montantBrut = quantite * prix;
                const montantRemise = montantBrut * (remise / 100);
                const totalHT = FactureUtils.roundNumber(montantBrut - montantRemise);

                row.find('.total-ligne').val(this.formatMoney(totalHT));
            } else {
                row.find('.total-ligne').val(this.formatMoney(0));
            }

            // Mise à jour des totaux généraux
            this.updateTotaux();
        } catch (error) {
            console.error('Erreur calcul total:', error);
            row.find('.total-ligne').val(this.formatMoney(0));
        }
    }

    
    updateTotaux() {
        let totalHT = 0;
        let totalTVA = 0;
        let totalAIB = 0;
        
        // Récupération du taux AIB du client sélectionné
        const clientSelect = $('select[name="client_id"]');
        const selectedOption = clientSelect.find(':selected');
        const tauxAib = selectedOption.length ? FactureUtils.parseNumber(selectedOption.data('taux-aib')) : 0;
    
        console.log('Taux AIB récupéré:', tauxAib); // Debug
    
        $(FactureConfig.selectors.ligneContainer).find('tr').each((_, row) => {
            const $row = $(row);
            const montantHT = FactureUtils.parseNumber($row.find('.total-ligne').val());
    
            totalHT += montantHT;
            totalTVA += FactureUtils.roundNumber(montantHT * (FactureConfig.TVA.rate / 100));
            totalAIB += FactureUtils.roundNumber(montantHT * (tauxAib / 100));
    
            console.log('Calculs pour la ligne:', { // Debug
                montantHT,
                tva: montantHT * (FactureConfig.TVA.rate / 100),
                aib: montantHT * (tauxAib / 100)
            });
        });
    
        // Arrondir les totaux
        totalHT = FactureUtils.roundNumber(totalHT);
        totalTVA = FactureUtils.roundNumber(totalTVA);
        totalAIB = FactureUtils.roundNumber(totalAIB);
        const totalTTC = FactureUtils.roundNumber(totalHT + totalTVA + totalAIB);
    
        // Mise à jour de l'affichage
        $(FactureConfig.selectors.totalHT).text(this.formatMoney(totalHT) + ' FCFA');
        $(FactureConfig.selectors.totalTVA).text(this.formatMoney(totalTVA) + ' FCFA');
        $('#totalAIB').text(this.formatMoney(totalAIB) + ' FCFA');
        $(FactureConfig.selectors.totalTTC).text(this.formatMoney(totalTTC) + ' FCFA');
    
        console.log('Totaux finaux:', { // Debug
            totalHT,
            tauxTVA: FactureConfig.TVA.rate,
            totalTVA,
            tauxAIB: tauxAib,
            totalAIB,
            totalTTC
        });
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

    // Méthodes utilitaires
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

    formatMoney(amount) {
        // S'assurer que le montant est un nombre et arrondi à 2 décimales
        const roundedAmount = FactureUtils.roundNumber(amount);
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(roundedAmount);
    }

    parseMoney(value) {
        if (!value) return 0;
        // Nettoie la chaîne et convertit en nombre
        return FactureUtils.parseNumber(value);
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

        // Réinitialiser les validations
        form.removeClass('was-validated');

        // Réinitialiser les champs
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

        // Désactiver la validation HTML5 par défaut
        form.setAttribute('novalidate', '');

        // Ajouter nos propres validations
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);

        // Validation en temps réel des champs
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

    validateRow(row) {
        const quantite = parseFloat(row.find('.quantite-input').val());
        const tarif = row.find('.select2-tarifs').val();
        const unite = row.find('.unite-select').val();

        let isValid = true;
        let errors = [];

        if (!quantite || quantite <= 0) {
            errors.push("La quantité doit être supérieure à 0");
            isValid = false;
        }

        if (!tarif) {
            errors.push("Veuillez sélectionner un tarif");
            isValid = false;
        }

        if (!unite) {
            errors.push("Veuillez sélectionner une unité");
            isValid = false;
        }

        return { isValid, errors };
    }

    validateForm() {
        let isValid = true;
        let errors = [];

        // Validation des champs obligatoires du header
        const dateFacture = $(FactureConfig.selectors.form).find('input[name="date_facture"]').val();
        const client = $(FactureConfig.selectors.form).find('select[name="client_id"]').val();
        const dateEcheance = $(FactureConfig.selectors.form).find('input[name="date_echeance"]').val();

        if (!dateFacture) {
            errors.push("La date de facture est obligatoire");
            isValid = false;
        }

        if (!client) {
            errors.push("Le client est obligatoire");
            isValid = false;
        }

        if (!dateEcheance) {
            errors.push("La date d'échéance est obligatoire");
            isValid = false;
        }

        // Validation des lignes
        const rows = $(FactureConfig.selectors.ligneContainer).find('tr');
        if (rows.length === 0) {
            errors.push("Ajoutez au moins une ligne à la facture");
            isValid = false;
        }

        rows.each((_, row) => {
            const rowValidation = this.validateRow($(row));
            if (!rowValidation.isValid) {
                errors = [...errors, ...rowValidation.errors];
                isValid = false;
            }
        });

        return { isValid, errors };
    }
}

// Style supplémentaires pour l'UI
const styles = `
    .ligne-facture {
        transition: all 0.3s ease;
    }

    .ligne-facture.loading {
        opacity: 0.6;
        pointer-events: none;
        position: relative;
    }

    .ligne-facture.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7) url('data:image/svg+xml,...') center no-repeat;
        background-size: 30px;
    }

    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.375rem;
        border-color: #ced4da;
    }

    .was-validated .select2-container--bootstrap-5 .select2-selection--single {
        border-color: #dc3545;
    }

    .was-validated .valid.select2-container--bootstrap-5 .select2-selection--single {
        border-color: #198754;
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #e9ecef;
        color: #1e2125;
    }

    .select2-result-article {
        padding: 8px;
    }

    .select2-result-article__code {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .select2-result-article__title {
        font-weight: 500;
        margin: 4px 0;
    }

    .select2-result-article__stock {
        font-size: 0.875rem;
        color: #198754;
    }
`;

// Injection des styles
$('<style>').text(styles).appendTo('head');



// Initialisation unique
$(document).ready(() => {
    if (!window.factureManager) {
        window.factureManager = new FactureManager();
        window.factureManager.init();
    }
});

