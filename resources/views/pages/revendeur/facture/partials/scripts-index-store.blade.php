<script>
    /**
     * Gestion des factures
     * @version 1.0.0
     * Dépendances requises: jQuery, Select2, SweetAlert2, Bootstrap 5
     */

    'use strict';

    // Configuration globale
    const FactureConfig = {
        routes: {
            searchArticles: `${apiUrl}/api/articles/search`,
            getTarifs: `${apiUrl}/revendeurs/factures/articles/:id/tarifs`,
            getUnites: `${apiUrl}/revendeurs/factures/articles/:id/unites`,
            store: `${apiUrl}/revendeur/factures`,
            validate: `${apiUrl}/revendeurs/factures/:id/validate`,
            cancel: `${apiUrl}/revendeurs/factures/:id/cancel`
        },
        selectors: {
            modal: '#addFactureModal',
            form: '#addFactureForm',
            addButton: '#btnAddLigne',
            container: '#lignesContainer',
            template: '#ligneFactureTemplate',
            totalHT: '#totalHT',
            totalTVA: '#totalTVA',
            totalTTC: '#totalTTC'
        },
        classes: {
            articleSelect: 'select2-articles',
            tarifSelect: 'select2-tarifs',
            uniteSelect: 'unite-select',
            quantiteInput: 'quantite-input',
            remiseInput: 'remise-input',
            tvaInput: 'tva-input',
            totalLigne: 'total-ligne',
            removeLigne: 'remove-ligne'
        }
    };

    // Configuration de la notification Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    class FactureManager {
        constructor() {
            this.ligneIndex = 0;
            this.isProcessing = false;
            console.log('FactureManager initialized'); // Debug
            this.init();
        }

        init() {
            console.log('Initializing...'); // Debug
            this.initializeEventListeners();
            // On attend un court instant pour s'assurer que le DOM est prêt
            setTimeout(() => {
                this.addNewLigne(); // Ajoute une première ligne automatiquement
            }, 100);
        }

        initializeEventListeners() {
            console.log('Setting up event listeners...'); // Debug

            // Gestionnaire pour le bouton d'ajout
            const addButton = document.querySelector(FactureConfig.selectors.addButton);
            console.log('Add button:', addButton); // Debug

            if (addButton) {
                addButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('Add button clicked'); // Debug
                    this.addNewLigne();
                });
            }

            const container = document.querySelector(FactureConfig.selectors.container);
            if (container) {
                container.addEventListener('click', (e) => this.handleLineEvents(e));
                container.addEventListener('change', (e) => this.handleLineChanges(e));
                container.addEventListener('input', (e) => this.handleLineInputs(e));
            }

            const form = document.querySelector(FactureConfig.selectors.form);
            if (form) {
                form.addEventListener('submit', (e) => this.handleFormSubmit(e));
            }
        }

        addNewLigne() {
            try {
                console.log('Adding new line...'); // Debug
                const container = document.querySelector(FactureConfig.selectors.container);
                const template = document.querySelector(FactureConfig.selectors.template);

                console.log('Container:', container); // Debug
                console.log('Template:', template); // Debug

                if (!container || !template) {
                    throw new Error('Container ou template non trouvé');
                }

                const clone = template.content.cloneNode(true);
                this.updateLineIndices(clone);

                // Créer un élément tr temporaire pour contenir la ligne clonée
                const tempTr = document.createElement('tr');
                tempTr.classList.add('ligne-facture');

                // Copier le contenu du clone dans le tr
                while (clone.firstChild) {
                    tempTr.appendChild(clone.firstChild);
                }

                // Ajouter le tr au container
                container.appendChild(tempTr);

                console.log('New line added, initializing components...'); // Debug
                this.initializeLineComponents(this.ligneIndex);
                this.ligneIndex++;

            } catch (error) {
                console.error('Erreur addNewLigne:', error);
                this.showNotification('error', 'Erreur lors de l\'ajout d\'une ligne');
            }
        }

        updateLineIndices(clone) {
            clone.querySelectorAll('[name*="__INDEX__"]').forEach(element => {
                element.name = element.name.replace('__INDEX__', this.ligneIndex);
                element.id = element.name.replace(/\[/g, '_').replace(/\]/g, '');
            });
        }

        initializeLineComponents(index) {
            // Select2 pour les articles
            $(`select[name="lignes[${index}][article_id]"]`).select2({
                theme: 'bootstrap-5',
                placeholder: 'Rechercher un article...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 2,
                ajax: {
                    url: FactureConfig.routes.searchArticles,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                templateResult: this.formatArticle.bind(this),
                templateSelection: this.formatArticleSelection.bind(this)
            }).on('select2:select', (e) => this.handleArticleSelection(index, e.target.value));

            // Select2 pour les tarifs
            $(`select[name="lignes[${index}][tarification_id]"]`).select2({
                theme: 'bootstrap-5',
                placeholder: 'Sélectionner un tarif',
                width: '100%'
            }).on('select2:select', () => this.updateRowCalculations(index));

            // Select2 pour les unités
            $(`select[name="lignes[${index}][unite_vente_id]"]`).select2({
                theme: 'bootstrap-5',
                placeholder: 'Unité',
                width: '100%'
            });
        }

        handleLineEvents(event) {
            const target = event.target;

            // Gestion du bouton de suppression
            if (target.classList.contains(FactureConfig.classes.removeLigne)) {
                const row = target.closest('tr');
                if (row && document.querySelectorAll('.ligne-facture').length > 1) {
                    row.remove();
                    this.updateTotals();
                }
            }
        }

        handleLineChanges(event) {
            const target = event.target;
            const row = target.closest('tr');
            if (!row) return;

            if (target.classList.contains(FactureConfig.classes.tarifSelect)) {
                this.calculateRowTotal(row);
            } else if (target.classList.contains(FactureConfig.classes.uniteSelect)) {
                this.calculateRowTotal(row);
            }
        }

        handleLineInputs(event) {
            const target = event.target;
            const row = target.closest('tr');
            if (!row) return;

            if (
                target.classList.contains(FactureConfig.classes.quantiteInput) ||
                target.classList.contains(FactureConfig.classes.remiseInput) ||
                target.classList.contains(FactureConfig.classes.tvaInput)
            ) {
                this.calculateRowTotal(row);
            }
        }

        formatArticle(article) {
            if (article.loading) return article.text;

            return $(`<div class="select2-result-article">
            <div class="select2-result-article__title">${article.text}</div>
            ${article.prix ?
                `<div class="select2-result-article__price">
                    Prix: ${this.formatMoney(article.prix)} FCFA
                </div>` : ''
            }
        </div>`);
        }

        formatArticleSelection(article) {
            return article.text || article.id;
        }

        async handleArticleSelection(index, articleId) {
            if (!articleId || this.isProcessing) return;

            const row = document.querySelector(`[name="lignes[${index}][article_id]"]`).closest('tr');
            this.showRowLoading(row);
            this.isProcessing = true;

            try {
                const [tarifsResponse, unitesResponse] = await Promise.all([
                    this.fetchData(this.getRoute('getTarifs', {
                        id: articleId
                    })),
                    this.fetchData(this.getRoute('getUnites', {
                        id: articleId
                    }))
                ]);

                this.updateTarifsSelect(index, tarifsResponse.data.tarifs);
                this.updateUnitesSelect(index, unitesResponse.data.unites);

            } catch (error) {
                console.error('Erreur handleArticleSelection:', error);
                this.showNotification('error', 'Erreur lors du chargement des données de l\'article');
            } finally {
                this.hideRowLoading(row);
                this.isProcessing = false;
            }
        }

        updateTarifsSelect(index, tarifs) {
            const select = document.querySelector(`select[name="lignes[${index}][tarification_id]"]`);
            if (!select) return;

            select.innerHTML = '<option value="">Sélectionner un tarif</option>';
            tarifs.forEach(tarif => {
                const option = new Option(
                    `${this.formatMoney(tarif.prix)} FCFA / ${tarif.unite.nom}`,
                    tarif.id,
                    false,
                    false
                );
                option.dataset.prix = tarif.prix;
                select.appendChild(option);
            });
            $(select).trigger('change');
        }

        updateUnitesSelect(index, unites) {
            const select = document.querySelector(`select[name="lignes[${index}][unite_vente_id]"]`);
            if (!select) return;

            select.innerHTML = '<option value="">Unité</option>';
            unites.forEach(unite => {
                const option = new Option(unite.nom, unite.id, false, false);
                select.appendChild(option);
            });
            $(select).trigger('change');
        }

        updateRowCalculations(index) {
            const row = document.querySelector(`[name="lignes[${index}][article_id]"]`).closest('tr');
            this.calculateRowTotal(row);
        }

        calculateRowTotal(row) {
            try {
                const quantite = parseFloat(row.querySelector(`.${FactureConfig.classes.quantiteInput}`).value) ||
                    0;
                const tarifSelect = row.querySelector(`.${FactureConfig.classes.tarifSelect}`);
                const selectedOption = tarifSelect.options[tarifSelect.selectedIndex];
                const prixUnitaire = selectedOption ? parseFloat(selectedOption.dataset.prix) || 0 : 0;
                const remise = parseFloat(row.querySelector(`.${FactureConfig.classes.remiseInput}`).value) || 0;
                const tva = parseFloat(row.querySelector(`.${FactureConfig.classes.tvaInput}`).value) || 0;

                const totalHT = quantite * prixUnitaire * (1 - remise / 100);
                row.querySelector(`.${FactureConfig.classes.totalLigne}`).value = this.formatMoney(totalHT);

                this.updateTotals();
            } catch (error) {
                console.error('Erreur calculateRowTotal:', error);
            }
        }

        updateTotals() {
            let totalHT = 0;
            let totalTVA = 0;

            document.querySelectorAll('.ligne-facture').forEach(row => {
                const totalLigne = parseFloat(row.querySelector(`.${FactureConfig.classes.totalLigne}`)
                    .value.replace(/[^\d.-]/g, '')) || 0;
                const tva = parseFloat(row.querySelector(`.${FactureConfig.classes.tvaInput}`).value) || 0;

                totalHT += totalLigne;
                totalTVA += totalLigne * (tva / 100);
            });

            const totalTTC = totalHT + totalTVA;

            document.querySelector(FactureConfig.selectors.totalHT).textContent =
                `${this.formatMoney(totalHT)} FCFA`;
            document.querySelector(FactureConfig.selectors.totalTVA).textContent =
                `${this.formatMoney(totalTVA)} FCFA`;
            document.querySelector(FactureConfig.selectors.totalTTC).textContent =
                `${this.formatMoney(totalTTC)} FCFA`;
        }

        getRoute(name, params = {}) {
            let url = FactureConfig.routes[name];
            for (let param in params) {
                url = url.replace(`:${param}`, params[param]);
            }
            return url;
        }

        async fetchData(url) {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        }

        formatMoney(amount) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        showRowLoading(row) {
            row.style.opacity = '0.5';
            row.style.pointerEvents = 'none';
        }

        hideRowLoading(row) {
            row.style.opacity = '1';
            row.style.pointerEvents = 'auto';
        }

        showNotification(icon, message) {
            Toast.fire({
                icon: icon,
                title: message
            });
        }

        async handleFormSubmit(event) {
            event.preventDefault();
            if (this.isProcessing) return;

            const form = event.target;
            this.isProcessing = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(FactureConfig.routes.store, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.status === 'success') {
                    this.showNotification('success', 'Facture créée avec succès');
                    this.closeModal();
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Erreur lors de la création de la facture');
                }
            } catch (error) {
                console.error('Erreur handleFormSubmit:', error);
                this.showNotification('error', error.message);
            } finally {
                this.isProcessing = false;
            }
        }

        closeModal() {
            const modal = bootstrap.Modal.getInstance(document.querySelector(FactureConfig.selectors.modal));
            if (modal) {
                modal.hide();
            }
        }
    }

    // Styles pour les résultats Select2
    const style = document.createElement('style');
    style.textContent = `
    .select2-result-article {
        padding: 6px;
    }
    .select2-result-article__title {
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }
    .select2-result-article__price {
        font-size: 0.875em;
        color: #666;
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #eef2ff;
        color: #333;
    }
    .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
        background-color: #e9ecef;
    }
    .ligne-facture {
        transition: opacity 0.3s ease;
    }
    .input-group .select2-container {
        flex: 1 1 auto;
        width: auto !important;
    }
    .select2-container--bootstrap-5 .select2-selection {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    .table td .select2-container {
        width: 100% !important;
    }
`;
    document.head.appendChild(style);

    // Initialisation au chargement de la page
    document.addEventListener('DOMContentLoaded', () => {
        window.factureManager = new FactureManager();

        // Activation de la validation des formulaires Bootstrap
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>
