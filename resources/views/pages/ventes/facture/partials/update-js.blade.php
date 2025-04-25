<script>
    'use strict';
    
    // Configuration pour la mise à jour
    const UpdateConfig = {
        selectors: {
            modal: '#updateFactureModal',
            form: '#updateFactureForm',
            ligneContainer: '#updateLinesContainer',
            template: '#updateLineTemplate',
            addButton: '#addLineUpdate',
            totalHT: '#updateTotalHT',
            totalTVA: '#updateTotalTVA',
            totalTTC: '#updateTotalTTC',
            totalAIB: '#updateTotalAIB',
            typeFacture: '#update_type_facture',
            montantRegle: '#updateMontantRegle',
            montantRestant: '#updateMontantRestant',
            messageRestant: '#updateMessageRestant',
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

    class UpdateFactureManager {
        constructor() {
            this.modal = $(UpdateConfig.selectors.modal);
            this.form = $(UpdateConfig.selectors.form);
            this.index = 0;
            this.isProcessing = false;
            this.isNormalized = false;
        }

        init() {
            this.initEvents();
            this.initTypeFacture();
            this.initMontantRegleEvents();
        }
        
        async handleArticleSelect(e, $line) {
            const articleId = e.target.value;
            if (!articleId) return;

            try {
                // Charger les tarifs et unités simultanément
                const [tarifsRes, unitesRes] = await Promise.all([
                    fetch(`${apiUrl}/vente/factures/articles/${articleId}/tarifs`),
                    fetch(`${apiUrl}/vente/factures/articles/${articleId}/unites`)
                ]);

                const tarifs = await tarifsRes.json();
                const unites = await unitesRes.json();

                // Mettre à jour les unités
                const uniteSelect = $line.find('.unite-select');
                uniteSelect.empty().append('<option value="">Sélectionner une unité</option>');

                if (unites.status === 'success' && unites.data.unites) {
                    unites.data.unites.forEach(unite => {
                        uniteSelect.append(new Option(unite.text, unite.id));
                    });
                    // Sélectionner la première unité par défaut si disponible
                    if (unites.data.unites.length > 0) {
                        uniteSelect.val(unites.data.unites[0].id).trigger('change');
                    }
                }

                // Mettre à jour les prix
                if (tarifs.status === 'success' && tarifs.data.tarifs.length > 0) {
                    const prix = tarifs.data.tarifs[0].prix;
                    $line.find('.select2-tarifs').val(prix).trigger('change');
                }

                this.calculateLineTotal($line);
                this.updateTotaux();

            } catch (error) {
                console.error('Erreur:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors du chargement des données de l\'article'
                });
            }
        }

        async loadUnites(articleId, $line, selectedUniteId = null) {
            try {
                const response = await fetch(`${apiUrl}/vente/factures/articles/${articleId}/unites`);
                const data = await response.json();

                const uniteSelect = $line.find('.unite-select');
                uniteSelect.empty().append('<option value="">Sélectionner une unité</option>');

                if (data.status === 'success' && data.data.unites) {
                    data.data.unites.forEach(unite => {
                        const option = new Option(unite.text, unite.id,
                            unite.id === selectedUniteId,
                            unite.id === selectedUniteId
                        );
                        uniteSelect.append(option);
                    });
                }

                // Si aucune unité n'est sélectionnée mais qu'il y en a de disponibles,
                // sélectionner la première par défaut
                if (!selectedUniteId && data.status === 'success' && data.data.unites.length > 0) {
                    uniteSelect.val(data.data.unites[0].id).trigger('change');
                }

            } catch (error) {
                console.error('Erreur lors du chargement des unités:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les unités de mesure'
                });
            }
        }

        async fillLine($line, data) {
            // Article
            const articleSelect = $line.find('.select2-articles');
            const articleOption = new Option(
                `${data.article.code_article} - ${data.article.designation}`,
                data.article_id,
                true,
                true
            );
            articleSelect.append(articleOption).trigger('change');

            // Charger les unités avant de définir les autres valeurs
            await this.loadUnites(data.article_id, $line, data.unite_vente_id);

            // Autres champs
            $line.find('.quantite-input').val(data.quantite);
            $line.find('.select2-tarifs').val(data.prix_unitaire_ht);
            $line.find('.remise-input').val(data.taux_remise || 0);

            this.calculateLineTotal($line);
            this.updateTotaux();
        }

        // Mise à jour de la méthode addLigne pour initialiser correctement l'unité
        async addLigne(data = null) {
            const template = $(UpdateConfig.selectors.template).html();
            const newLine = template.replace(/__INDEX__/g, this.index);
            const $line = $(newLine);

            $(UpdateConfig.selectors.ligneContainer).append($line);

            // Initialiser Select2 pour l'article
            const articleSelect = $line.find('.select2-articles');
            articleSelect.select2({
                theme: 'bootstrap-5',
                dropdownParent: this.modal,
                width: '100%',
                ajax: {
                    url: `${apiUrl}/vente/factures/api/articles/search`,
                    dataType: 'json',
                    delay: 250,
                    data: (params) => ({
                        q: params.term,
                        page: params.page || 1
                    }),
                    processResults: (data) => ({
                        results: data.results.map(item => ({
                            id: item.id,
                            text: `${item.code_article} - ${item.text}`,
                            code_article: item.code_article
                        }))
                    })
                }
            }).on('select2:select', (e) => this.handleArticleSelect(e, $line));

            if (data) {
                await this.fillLine($line, data);
            }

            this.index++;
            return $line;
        }


        initTypeFacture() {
            const typeSelect = $(UpdateConfig.selectors.typeFacture);

            this.isNormalized = typeSelect.val() === 'normaliser';
            this.toggleTaxRows();

            // Cacher les lignes TVA et AIB par défaut
            $('#updateTvaRow, #updateAibRow').hide();

            typeSelect.on('change', (e) => {
                this.isNormalized = e.target.value === 'normaliser';
                this.toggleTaxRows();
                this.updateTotaux();
            });
        }

        toggleTaxRows() {
            const rows = $('#updateTvaRow, #updateAibRow');
            this.isNormalized ? rows.show() : rows.hide();
        }

        initEvents() {
            $(UpdateConfig.selectors.addButton).on('click', () => this.addLigne());

            this.form.on('submit', (e) => this.handleSubmit(e));

            $(UpdateConfig.selectors.ligneContainer)
                .on('click', '.remove-ligne', (e) => this.deleteLigne(e))
                .on('change keyup', '.quantite-input, .select2-tarifs, .remise-input',
                    (e) => this.handleCalculations(e));
        }

        initMontantRegleEvents() {
            const montantRegleInput = $(UpdateConfig.selectors.montantRegle);

            montantRegleInput.on('input', () => {
                try {
                    console.log('Montant réglé:', montantRegleInput.val());
                    this.updateMontantRestant();
                } catch (error) {
                    console.error('Erreur dans updateMontantRestant:', error);
                }
            });

            // S'assurer que le montant total est affiché initialement
            this.updateMontantRestant();
        }

        async fillLigne($line, data) {
            // Article
            const articleSelect = $line.find('.select2-articles');
            const articleOption = new Option(
                `${data.article.code_article} - ${data.article.designation}`,
                data.article_id,
                true,
                true
            );
            articleSelect.append(articleOption).trigger('change');

            // Autres champs
            $line.find('.quantite-input').val(data.quantite);
            $line.find('.select2-tarifs').val(data.prix_unitaire_ht);
            $line.find('.remise-input').val(data.taux_remise || 0);

            // Charger les unités
            if (data.article_id) {
                const unitesRes = await fetch(`${apiUrl}/vente/factures/articles/${data.article_id}/unites`);
                const unites = await unitesRes.json();

                const uniteSelect = $line.find('.unite-select');
                uniteSelect.empty();

                if (unites.status === 'success') {
                    unites.data.unites.forEach(unite => {
                        const option = new Option(unite.text, unite.id,
                            unite.id === data.unite_vente_id,
                            unite.id === data.unite_vente_id
                        );
                        uniteSelect.append(option);
                    });
                }
            }

            this.calculateLineTotal($line);
        }

        deleteLigne(event) {
            const $button = $(event.target).closest('.remove-ligne');
            const $line = $button.closest('tr');

            if ($('#updateLinesContainer tr').length > 1) {
                $line.fadeOut(300, () => {
                    $line.remove();
                    this.updateTotaux();
                });
            }
        }

        handleCalculations(event) {
            const $line = $(event.target).closest('tr');
            this.calculateLineTotal($line);
            this.updateTotaux();
        }

        calculateLineTotal($line) {
            try {
                const quantite = this.parseMoney($line.find('.quantite-input').val());
                const prix = this.parseMoney($line.find('.select2-tarifs').val());
                const remise = this.parseMoney($line.find('.remise-input').val());

                // Vérifier si les valeurs sont valides
                if (quantite <= 0 || prix <= 0) {
                    $line.find('.total-ligne').val(this.formatMoney(0));
                    return 0;
                }

                // Calcul du total HT avec remise
                const montantBrut = quantite * prix;
                const montantRemise = (remise > 0 && remise <= 100) ? montantBrut * (remise / 100) : 0;
                const totalHT = montantBrut - montantRemise;

                $line.find('.total-ligne').val(this.formatMoney(totalHT));
                return totalHT;

            } catch (error) {
                console.error('Erreur calcul total:', error);
                $line.find('.total-ligne').val(this.formatMoney(0));
                return 0;
            }
        }

        updateTotaux() {
            let totalHT = 0;
            let totalTVA = 0;
            let totalAIB = 0;

            // Récupération du taux AIB du client sélectionné
            const clientSelect = this.form.find('[name="client_id"]');
            const selectedOption = clientSelect.find(':selected');
            const tauxAib = selectedOption.length ? this.parseMoney(selectedOption.data('taux-aib')) : 0;

            $(UpdateConfig.selectors.ligneContainer).find('tr').each((_, row) => {
                const $row = $(row);
                console.log('Row:', $row.find('.total-ligne').val());
                const montantHT = this.parseMoney($row.find('.total-ligne').val());
                console.log('Montant HT:', montantHT);

                totalHT += montantHT;

                // Ne calculer TVA et AIB que si la facture est normalisée
                if (this.isNormalized) {
                    totalTVA += this.roundNumber(montantHT * (0.18)); // TVA 18%
                    totalAIB += this.roundNumber(montantHT * (tauxAib / 100));
                }
            });

            console.log('Total HT:', totalHT);


            // Arrondir les totaux
            totalHT = this.roundNumber(totalHT);
            totalTVA = this.roundNumber(totalTVA);
            totalAIB = this.roundNumber(totalAIB);
            const totalTTC = this.roundNumber(totalHT + (this.isNormalized ? (totalTVA + totalAIB) : 0));

            // Mise à jour de l'affichage
            $(UpdateConfig.selectors.totalHT).text(this.formatMoney(totalHT) + ' FCFA');
            $(UpdateConfig.selectors.totalTVA).text(this.formatMoney(totalTVA) + ' FCFA');
            $(UpdateConfig.selectors.totalAIB).text(this.formatMoney(totalAIB) + ' FCFA');
            $(UpdateConfig.selectors.totalTTC).text(this.formatMoney(totalTTC) + ' FCFA');

            // Mettre à jour le montant restant
            this.updateMontantRestant(totalTTC);
        }

        roundNumber(number, decimals = 2) {
            return Number(Math.round(number + 'e' + decimals) + 'e-' + decimals);
        }
        async handleSubmit(event) {
            event.preventDefault();
            if (this.isProcessing) return;

            try {
                this.isProcessing = true;
                const formData = new FormData(this.form[0]);
                formData.append('_method', 'PUT');

                const response = await fetch(this.form.attr('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                const data = await response.json();

                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Facture mise à jour avec succès',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        this.modal.modal('hide');
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Erreur lors de la mise à jour');
                }
            } catch (error) {
                console.error('Erreur:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.message
                });
            } finally {
                this.isProcessing = false;
            }
        }

        formatMoney(amount) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        parseMoney(value) {
            if (!value) return 0;
            return parseFloat(value.replace(/[^\d,.-]/g, '')) || 0;
        }
        

        reset() {
            this.form[0].reset();
            $(UpdateConfig.selectors.ligneContainer).empty();
            this.index = 0;
            this.isNormalized = false;
            this.toggleTaxRows();
            this.updateTotaux();
        }

        updateMontantRestant(totalTTC) {
            console.log('Total TTC:', totalTTC);
            // Si totalTTC n'est pas fourni, le calculer à partir du texte affiché
            if (typeof totalTTC !== 'number') {
                const totalTTCText = $(UpdateConfig.selectors.totalTTC).text();
                totalTTC = FactureUtils.parseNumber(totalTTCText);
            }

            const montantRegle = FactureUtils.parseNumber($(UpdateConfig.selectors.montantRegle).val()) || 0;
            const montantRestant = FactureUtils.roundNumber(totalTTC - montantRegle);

            // Mettre à jour l'affichage du montant restant
            $(UpdateConfig.selectors.montantRestant).text(this.formatMoney(Math.abs(montantRestant)) + ' FCFA');

            // Mettre à jour le message et la couleur en fonction du montant restant
            const messageRestant = $(UpdateConfig.selectors.messageRestant);
            const montantRestantElement = $(UpdateConfig.selectors.montantRestant);

            if (montantRestant > 0) {
                messageRestant.text('Reste à régler');
                messageRestant.removeClass('text-success text-warning').addClass('text-danger');
                montantRestantElement.removeClass('text-success text-warning').addClass('text-danger');
            } else if (montantRestant < 0) {
                messageRestant.text('Trop perçu à retourner');
                messageRestant.removeClass('text-danger text-warning').addClass('text-success');
                montantRestantElement.removeClass('text-danger text-warning').addClass('text-success');
            } else {
                messageRestant.text('Intégralement réglé');
                messageRestant.removeClass('text-danger text-warning').addClass('text-success');
                montantRestantElement.removeClass('text-danger text-warning').addClass('text-success');
            }
        }

    }

    // Initialisation
    let updateManager;
    $(document).ready(() => {
        updateManager = new UpdateFactureManager();
        updateManager.init();
    });

    // Fonction d'édition
    function editFactures(id) {
        Swal.fire({
            title: 'Chargement...',
            text: 'Récupération des données',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`${apiUrl}/vente/factures/${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();

                if (data.status === 'success') {
                    if (data.data.facture.statut === 'validee') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Modification impossible',
                            text: 'Cette facture a déjà été validée'
                        });
                        return;
                    }

                    const facture = data.data.facture;

                    // Remplir le formulaire
                    $('#updateFactureForm').attr('action', `${apiUrl}/vente/factures/update/${facture.id}`);
                    $('#factureNumber').text(facture.numero);
                    $('[name="date_facture"]').val(facture.date_facture.split('T')[0]);
                    $('[name="date_echeance"]').val(facture.date_echeance.split('T')[0]);
                    $('[name="client_id"]').val(facture.client_id);
                    $('[name="observations"]').val(facture.observations || '');
                    $('#update_type_facture').val(facture.taux_tva > 0 ? 'normaliser' : 'simple').trigger('change');
                    $('#updateMontantRegle').val(facture.montant_regle).trigger('input');

                    // Vider et remplir les lignes
                    $('#updateLinesContainer').empty();
                    if (facture.lignes && facture.lignes.length > 0) {
                        facture.lignes.forEach(ligne => updateManager.addLigne(ligne));
                    } else {
                        updateManager.addLigne();
                    }

                    // Afficher le modal
                    $('#updateFactureModal').modal('show');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors du chargement de la facture'
                });
            });
    }
</script>
