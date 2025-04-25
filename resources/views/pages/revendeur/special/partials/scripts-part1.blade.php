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
        totalAIB: '#totalAIB',
        montantRegle: '#montantRegle',
        montantRestant: '#montantRestant',
        messageRestant: '#messageRestant',
        typeFacture: '#type_facture',
        tvaRow: '#tvaRow',
        aibRow: '#aibRow'
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
        store: 'ventes-speciales/store'
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
        this.isNormalized = false;
    }

    init() {
        if (this.isInitialized) return;

        this.initializeFormValidation();
        this.initEvents();
        this.initTypeFacture();
        this.addLigne();
        this.initMontantRegleEvents(); // Ajoutez cette ligne

        this.isInitialized = true;
    }

    initTypeFacture() {
        const typeSelect = $(FactureConfig.selectors.typeFacture);

        // Initialisation au chargement
        this.isNormalized = typeSelect.val() === 'normaliser';
        this.toggleTaxRows();

        // Cacher les lignes TVA et AIB par défaut
        $(FactureConfig.selectors.totalTVA).closest('tr').hide();
        $(FactureConfig.selectors.totalAIB).closest('tr').hide();

        typeSelect.on('change', (e) => {
            this.isNormalized = e.target.value === 'normaliser';
            this.toggleTaxRows();
            this.updateTotaux();
        });
    }

    // Ajouter cette nouvelle méthode
    toggleTaxRows() {
        const tvaRow = $(FactureConfig.selectors.totalTVA).closest('tr');
        const aibRow = $(FactureConfig.selectors.totalAIB).closest('tr');

        if (this.isNormalized) {
            tvaRow.show();
            aibRow.show();
        } else {
            tvaRow.hide();
            aibRow.hide();
        }
    }

    initMontantRegleEvents() {
        const montantRegleInput = $(FactureConfig.selectors.montantRegle);

        montantRegleInput.on('input', () => {
            this.updateMontantRestant();
        });

        // S'assurer que le montant total est affiché initialement
        this.updateMontantRestant();
    }

    updateMontantRestant(totalTTC) {
        // Si totalTTC n'est pas fourni, le calculer à partir du texte affiché
        if (typeof totalTTC !== 'number') {
            const totalTTCText = $(FactureConfig.selectors.totalTTC).text();
            totalTTC = FactureUtils.parseNumber(totalTTCText);
        }

        const montantRegle = FactureUtils.parseNumber($(FactureConfig.selectors.montantRegle).val()) || 0;
        const montantRestant = FactureUtils.roundNumber(totalTTC - montantRegle);

        // Mettre à jour l'affichage du montant restant
        $(FactureConfig.selectors.montantRestant).text(this.formatMoney(Math.abs(montantRestant)) + ' FCFA');

        // Mettre à jour le message et la couleur en fonction du montant restant
        const messageRestant = $(FactureConfig.selectors.messageRestant);
        const montantRestantElement = $(FactureConfig.selectors.montantRestant);

        if (montantRestant > 0) {
            messageRestant.text('Reste à régler');
            messageRestant.removeClass('text-success text-warning').addClass('text-danger');
            montantRestantElement.removeClass('text-success text-warning').addClass('text-danger');
        } else if (montantRestant < 0) {
            messageRestant.text('Trop perçu à retourner');
            messageRestant.removeClass('text-danger  text-warning').addClass('text-success');
            montantRestantElement.removeClass('text-danger text-warning').addClass('text-success');
        } else {
            messageRestant.text('Intégralement réglé');
            messageRestant.removeClass('text-danger text-warning').addClass('text-success');
            montantRestantElement.removeClass('text-danger text-warning').addClass('text-success');
        }
    }
    async
    initTarifSelect(row, index) {
        const tarifSelect = row.find(`select[name="lignes[${index}][tarification_id]" ]`);
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
            this.handleCalculations({
                target: tarifSelect[0]
            });
        });
    }


    initEvents() {
        const {
            addButton,
            ligneContainer,
            form,
            modal
        } = FactureConfig.selectors;

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
        // Vérifier si la ligne est déjà en cours d'ajout
        if (this.isAddingLine) return;
        this.isAddingLine = true;

        try {
            const template = $(FactureConfig.selectors.template).html();
            const newLine = template.replace(/__INDEX__/g, this.index);
            const $newLine = $(newLine);

            $newLine.hide();
            $(FactureConfig.selectors.ligneContainer).append($newLine);
            $newLine.fadeIn(300);

            // Maintenant nous initialisons uniquement le select d'article
            await this.initArticleSelect($newLine, this.index);

            // Initialiser l'unité de vente
            await this.initUniteSelect($newLine, this.index);

            this.index++;
        } catch (error) {
            console.error('Erreur lors de l\'ajout de ligne:', error);
        } finally {
            this.isAddingLine = false;
        }
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
            {{-- {
                {
                    --placeholder: 'Sélectionner une unité', --
                }
            } --}}
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
        {{-- {
            {
                --uniteSelect.empty().append('<option value="">Sélectionner une unité</option>');
                --
            }
        } --}}

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
        if (!row) return;

        // Ajouter un délai pour éviter les calculs trop fréquents
        clearTimeout(row.data('calculTimeout'));
        row.data('calculTimeout', setTimeout(() => {
            this.calculateRowTotal(row);
            this.updateTotaux();
        }, 300));
    }

    calculateRowTotal(row) {
        try {
            const quantite = FactureUtils.parseNumber(row.find('.quantite-input').val());
            const prix = FactureUtils.parseNumber(row.find('.select2-tarifs').val()); // Maintenant lit directement la valeur de l'input
            const remise = FactureUtils.parseNumber(row.find('.remise-input').val());

            // Vérifier si les valeurs sont valides
            if (quantite <= 0 || prix <= 0) {
                row.find('.total-ligne').val(this.formatMoney(0));
                return 0;
            } // Calcul du total HT avec remise 
            const montantBrut=quantite * prix; const montantRemise=(remise> 0 && remise <= 100) ? montantBrut * (remise / 100) : 0; 
            const totalHT = FactureUtils.roundNumber(montantBrut - montantRemise);
            row.find('.total-ligne').val(this.formatMoney(totalHT));
            return totalHT;
        } catch (error) {
            console.error('Erreur calcul total:', error);
            row.find('.total-ligne').val(this.formatMoney(0));
            return 0;
        }
    }
    updateTotaux() {
        let totalHT = 0;
        let totalTVA = 0;
        let totalAIB = 0; // Récupération du taux AIB du client sélectionné 
        const clientSelect = $('select[name="client_id" ]');
        const
            selectedOption = clientSelect.find(':selected');
        const tauxAib = selectedOption.length ?
            FactureUtils.parseNumber(selectedOption.data('taux-aib')) : 0;
        $(FactureConfig.selectors.ligneContainer).find('tr').each((_, row) => {
            const $row = $(row);
            const montantHT = FactureUtils.parseNumber($row.find('.total-ligne').val());

            totalHT += montantHT;

            // Ne calculer TVA et AIB que si la facture est normalisée
            if (this.isNormalized) {
                totalTVA += FactureUtils.roundNumber(montantHT * (FactureConfig.TVA.rate / 100));
                totalAIB += FactureUtils.roundNumber(montantHT * (tauxAib / 100));
            }
        });

        // Arrondir les totaux
        totalHT = FactureUtils.roundNumber(totalHT);
        totalTVA = FactureUtils.roundNumber(totalTVA);
        totalAIB = FactureUtils.roundNumber(totalAIB);
        const totalTTC = FactureUtils.roundNumber(totalHT + (this.isNormalized ? (totalTVA + totalAIB) : 0));

        // Mise à jour de l'affichage
        $(FactureConfig.selectors.totalHT).text(this.formatMoney(totalHT) + ' FCFA');
        $(FactureConfig.selectors.totalTVA).text(this.formatMoney(totalTVA) + ' FCFA');
        $('#totalAIB').text(this.formatMoney(totalAIB) + ' FCFA');
        $(FactureConfig.selectors.totalTTC).text(this.formatMoney(totalTTC) + ' FCFA');

        // Mettre à jour le montant restant
        this.updateMontantRestant(totalTTC);
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
        this.isNormalized = false;
        this.toggleTaxRows();
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
        return {
            isValid,
            errors
        };
    }
    validateForm() {
        let isValid = true;
        let errors = []; // Validation des champs obligatoires du header const
        dateFacture = $(FactureConfig.selectors.form).find('input[name="date_facture" ]').val();
        const
            client = $(FactureConfig.selectors.form).find('select[name="client_id" ]').val();
        const
            dateEcheance = $(FactureConfig.selectors.form).find('input[name="date_echeance" ]').val();
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
        } //Validation des lignes const
        rows = $(FactureConfig.selectors.ligneContainer).find('tr');
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

        return {
            isValid,
            errors
        };
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
$('<style>').text(styles).appendTo(' head');



// Initialisation unique
$(document).ready(() => {
    if (!window.factureManager) {
        window.factureManager = new FactureManager();
        window.factureManager.init();
    }
});

function deleteFacture(id) {
    Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer!',
        cancelButtonText: 'Annuler'

    }).then((result) => {
        if (result.isConfirmed) {
            // Récupérer le token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // Envoyer la requête de suppression
            fetch(`ventes-speciales/$ {
                                        id
                                    }

                                    `, {

                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }

            }).then(response => response.json()).then(data => {
                if (data.status === 'success') {
                    Swal.fire('Supprimé!',
                        data.message,
                        'success'

                    ).then(() => {
                        // Recharger la page ou mettre à jour la liste
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Erreur!',
                        data.message,
                        'error'
                    );
                }

            }).catch(error => {
                Swal.fire('Erreur!',
                    'Une erreur est survenue lors de la suppression',
                    'error'
                );
                console.error('Erreur:', error);
            });
        }
    });
}