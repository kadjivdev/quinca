<script>
    // Définition des fonctions globales
    const LivraisonFournisseur = {
        // Configuration de Sweet Alert
        swalConfig: {
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        },

        // Configuration du toast
        toast: Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }),

        // Initialisation
        init: function() {
            this.initializeSelect2();
            this.initializeEventListeners();
            this.initializeTooltips();
        },

        // Initialisation de Select2
        initializeSelect2: function() {
            // $('#factureSelect, #factureSelectMod, select[name="depot_id"], select[name="vehicule_id"], select[name="chauffeur_id"]')
            //     .select2({
            //         theme: 'bootstrap-5',
            //         width: '100%',
            //         dropdownParent: $('#addLivraisonFournisseurModal'),
            //         dropdownParent: $('#editLivraisonFournisseurModal'),
            //         placeholder: 'Sélectionner une option'
            //     });

            try {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#addLivraisonFournisseurModal'),
                    // dropdownParent: $('#editLivraisonFournisseurModal'),
                });

            } catch (e) {
                console.error('Erreur initialisation Select2:', e);
            }
        },

        // Initialisation des écouteurs d'événements
        initializeEventListeners: function() {
            $('#factureSelect').on('change', this.handleFactureChange.bind(this));
            $('#addLivraisonFournisseurForm').on('submit', this.handleFormSubmit.bind(this));
        },

        // Gestion du changement de facture
        handleFactureChange: function(e) {
            const factureId = e.target.value;
            if (!factureId) {
                this.resetForm();
                return;
            }

            // Ajout du loader pendant le chargement
            $('#modalLignesFacture').html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Chargement des données de la facture...</p>
                </td>
            </tr>
        `);

            $.get(`${apiUrl}/achat/factures/${factureId}`, this.handleFactureData.bind(this))
                .fail(this.handleAjaxError.bind(this));
        },

        // Traitement des données de la facture
        handleFactureData: async function(response) {
            if (!response.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors du chargement des données de la facture',
                    ...this.swalConfig
                });
                this.resetForm();
                return;
            }

            const facture = response.data;

            // Mise à jour des informations de la facture
            $('#fournisseurName').text(facture.fournisseur.raison_sociale);
            $('#factureInfo').text(
                `Facture N° ${facture.code} du ${moment(facture.date_facture).format('DD/MM/YYYY')}`);

            // Récupérez les options des unités
            const unites = await this.getUnitesOptions();

            // Génération des lignes
            let html = this.generateLignesHtml(facture.lignes, unites);
            $('#modalLignesFacture').html(html);

            this.initializeInputs();
            this.updateSaveButton();
        },

        // Gestion de la soumission du formulaire
        handleFormSubmit: function(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = $('#btnSaveLivraison');

            // Vérification de la validité du formulaire
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }

            // Log des données avant envoi pour debugging
            const formData = new FormData(form);
            console.log('Données à envoyer:', Object.fromEntries(formData));

            // Désactiver le bouton et afficher le loader
            submitBtn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...');

            $.ajax({
                url: `${apiUrl}/achat/livraisons`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {
                    console.log('Réponse succès:', response); // Log de la réponse

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message || 'Bon de livraison créé avec succès',
                            ...this.swalConfig
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: response.message ||
                                'Une erreur est survenue lors de la création',
                            ...this.swalConfig
                        });
                    }
                },
                error: (xhr) => {
                    console.error('Détails de l\'erreur:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });

                    let message = 'Une erreur est survenue lors de l\'enregistrement';
                    let details = '';

                    if (xhr.responseJSON) {
                        // Cas d'erreur de validation
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            message = 'Erreurs de validation :';
                            details = Object.values(xhr.responseJSON.errors)
                                .flat()
                                .join('\n');
                        }
                        // Cas d'erreur serveur avec debug
                        else if (xhr.responseJSON.debug) {
                            message = xhr.responseJSON.message || message;
                            details = `Erreur: ${xhr.responseJSON.debug.error}\n` +
                                `Fichier: ${xhr.responseJSON.debug.file}\n` +
                                `Ligne: ${xhr.responseJSON.debug.line}`;
                        }
                        // Cas d'erreur simple
                        else if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        html: details ?
                            `${message}<br><pre class="mt-3 text-start bg-light p-3 small">${details}</pre>` : message,
                        ...this.swalConfig,
                        width: details ? '600px' : undefined
                    });
                },
                complete: () => {
                    // Réactiver le bouton et restaurer son état initial
                    submitBtn.prop('disabled', false)
                        .html('<i class="fas fa-save me-2"></i>Enregistrer');
                }
            });
        },

        getUnitesOptions: async function() {
            try {
                const responseUnites = await fetch(`${apiUrl}/parametres/unites-mesure/list`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const unitesGot = await responseUnites.json();
                // Vérifiez si un tableau est contenu dans la réponse
                const unitesArray = Array.isArray(unitesGot) ? unitesGot : unitesGot.data;
                if (!Array.isArray(unitesArray)) {
                    throw new Error('La réponse des unités n’est pas un tableau valide.');
                }

                // Créez les options HTML pour le select
                return unitesArray
                    .map(unite => `<option value="${unite.id}">${unite.code_unite} - ${unite.libelle_unite}</option>`)
                    .join('');
            } catch (error) {
                console.error('Erreur lors de la récupération des unités:', error);
                return '<option value="">Erreur lors du chargement des unités</option>';
            }
        },


        // Génération du HTML des lignes
        generateLignesHtml: function(lignes, unites) {
            if (!lignes || lignes.length === 0) return '';

            return lignes.map(ligne => {
                // S'assurer que la quantité livrée est un nombre
                const quantiteLivree = parseFloat(ligne.quantite_livree) || 0;
                const quantiteTotale = parseFloat(ligne.quantite) || 0;
                const resteALivrer = Math.max(0, quantiteTotale - quantiteLivree);

                // Formater les nombres pour l'affichage
                const quantiteLivreeFormatted = this.formatNumber(quantiteLivree);
                const quantiteTotaleFormatted = this.formatNumber(quantiteTotale);
                const resteALivrerFormatted = this.formatNumber(resteALivrer);

                return `
                    <tr>
                        <td>
                            <div class="fw-medium">${ligne.article.designation}</div>
                            <div class="small text-muted">${ligne.article.code_article}</div>
                            <input type="hidden" name="lignes[${ligne.article_id}][article_id]" value="${ligne.article_id}">
                        </td>
                        <td class="text-center">
                            <span>${ligne.unite_mesure.libelle_unite}</span>
                            <input type="hidden" name="lignes[${ligne.article_id}][unite_mesure_id]" value="${ligne.unite_mesure.id}">
                        </td>
                        <td class="text-center">${quantiteTotaleFormatted}</td>
                        <td class="text-center">${quantiteLivreeFormatted}</td>
                        <td class="text-center">${resteALivrerFormatted}</td>
                        <td class="text-center">
                            <input type="number"
                                class="form-control form-control-sm quantite-input text-end"
                                name="lignes[${ligne.article_id}][quantite]"
                                value="0"
                                min="0"
                                step="0.01"
                                max="${resteALivrer}"
                                ${resteALivrer <= 0 ? 'readonly' : ''}
                                required>
                        </td>
                        <td class="text-center">
                            <input type="number"
                                class="form-control form-control-sm quantite-supp-input text-end"
                                name="lignes[${ligne.article_id}][quantite_supplementaire]"
                                value="0"
                                min="0"
                                ${resteALivrer <= 0 ? 'readonly' : ''}>
                            <select class="form-select" name="lignes[${ligne.article_id}][unite_id]">
                                <option value="">Sélectionner...</option>
                                ${unites}
                            </select>
                        </td>
                    </tr>
                `;
            }).join('');
        },
        // Réinitialisation du formulaire
        resetForm: function() {
            $('#addLivraisonFournisseurForm')[0].reset();
            $('#fournisseurName').text('');
            $('#factureInfo').text('');
            $('#modalLignesFacture').html(`
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    Veuillez sélectionner une facture
                </td>
            </tr>
        `);
            $('#factureSelect').val('').trigger('change');
            this.updateSaveButton();
        },

        // Gestion des erreurs Ajax
        handleAjaxError: function(xhr) {
            console.error('XHR Error:', xhr);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue lors du chargement des données',
                ...this.swalConfig
            });
            this.resetForm();
        },

        // Formatage des nombres
        formatNumber: function(number) {
            return new Intl.NumberFormat('fr-FR').format(number);
        },

        // Initialisation des inputs
        initializeInputs: function() {
            $('.quantite-input, .quantite-supp-input').on('input', this.updateSaveButton.bind(this));
        },

        // Mise à jour du bouton de sauvegarde
        updateSaveButton: function() {
            const quantiteInputs = $('.quantite-input');
            const canSubmit = quantiteInputs.length > 0 &&
                Array.from(quantiteInputs).some(input => parseFloat(input.value) > 0);

            $('#btnSaveLivraison').prop('disabled', !canSubmit);
        },

        // Initialisation des tooltips
        initializeTooltips: function() {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(t => new bootstrap.Tooltip(t));
        }
    };

    // Initialisation au chargement du document
    $(document).ready(function() {
        LivraisonFournisseur.init();
    });

    async function showLivraisonFournisseur(id) {
        // Afficher l'indicateur de chargement
        Swal.fire({
            title: 'Chargement...',
            text: 'Veuillez patienter...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Charger les données
        $.ajax({
            url: `${apiUrl}/achat/livraisons/${id}`,
            method: 'GET',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    console.log(response)
                    fillLivraisonDetails(response);
                    $('#showLivraisonFournisseurModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.close();
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des données'
                });
            }
        });
    }

    function fillLivraisonDetails(data) {
        console.log('Données à afficher:', data);

        // Réinitialiser le contenu précédent
        $('#modalLignesFactureShow').empty();

        $('#codeBon').text(data.livraison.code);
        $('#fournisseurNameShow').text(data.livraison.fournisseur.raison_sociale);
        $('#factureCode').text(data.livraison.facture.code);
        $("[name='date_livraison']").text(data.livraison.date_livraison.split('T')[0]);
        $('#depotId').text(data.livraison.depot.libelle_depot);
        $('#vehiculeId').text(data.livraison.vehicule.matricule);
        $('#chauffeurId').text(data.livraison.chauffeur.nom_chauf);

        // Vérification des articles
        if (data.livraison.lignes && data.livraison.lignes.length > 0) {
            let articlesHtml = ``;

            data.livraison.lignes.forEach((ligne, index) => {
                const article = ligne.article;
                articlesHtml += `
                <tr>
                    <td>
                        <div class="fw-medium">${article.designation}</div>
                        <div class="small text-muted">${article.code_article}</div>
                    </td>
                    <td class="text-center">
                        <span>${ligne.unite_mesure.libelle_unite}</span>
                    </td>
                    <td class="text-center">
                        <input type="number"
                            class="form-control form-control-sm quantite-input text-end"
                            value="${ligne.quantite}"
                            readonly>
                    </td>
                    <td class="text-center">
                        <label>${ligne.quantite_supplementaire} ${ligne?.unite_supplementaire?.libelle_unite ?? ''}</label>
                    </td>
                </tr>`;
            });

            $('#modalLignesFactureShow').html(articlesHtml);

            $("[name='commentaire']").val(data.livraison.commentaire)
        } else {
            $('#articlesSection').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucun article trouvé dans cette programmation
                </div>
            `);
        }
    }

    async function editLivraisonFournisseur(id) {
        try {
            Swal.fire({
                title: 'Chargement...',
                text: 'Veuillez patienter...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`${apiUrl}/achat/livraisons/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des données');
            }

            const result = await response.json();

            console.log(result)

            if (result.success) {
                const editModal = document.getElementById('editLivraisonFournisseurModal');
                const editForm = document.getElementById('editLivraisonFournisseurForm');

                // Mettre à jour l'action du formulaire
                editForm.action = `${apiUrl}/achat/livraisons/${id}`; // Changement ici

                $("#fournisseurNameMod").text(result.livraison.fournisseur.raison_sociale);
                $("[name='date_livraison']").val(result.livraison.date_livraison.split('T')[0]);
                $("[name='point_de_vente_id']").val(result.livraison.point_de_vente_id);

                // Marquer la facture sélectionnée
                const selectFactures = $("#factureSelectMod");
                const factureOption = selectFactures.find(`option[value="${result.livraison.facture_id}"]`);
                if (factureOption.length > 0) {
                    factureOption.prop("selected", true);
                }

                // Marquer le depot sélectionné
                const selectDepot = $("[name='depot_id']");
                const depotOption = selectDepot.find(`option[value="${result.livraison.depot_id}"]`);
                if (depotOption.length > 0) {
                    depotOption.prop("selected", true);
                }

                // Marquer le véhicule sélectionné
                const selectVehicule = $("[name='vehicule_id']");
                const vehiculeOption = selectVehicule.find(`option[value="${result.livraison.vehicule_id}"]`);
                if (vehiculeOption.length > 0) {
                    vehiculeOption.prop("selected", true);
                }

                // Marquer le chauffeur sélectionné
                const selectChauffeur = $("[name='chauffeur_id']");
                const chauffeurOption = selectChauffeur.find(`option[value="${result.livraison.chauffeur_id}"]`);
                if (chauffeurOption.length > 0) {
                    chauffeurOption.prop("selected", true);
                }

                const livraison = result.livraison;

                let articles = ``;
                const unites = await LivraisonFournisseur.getUnitesOptions();
                livraison.facture.lignes.forEach((ligne, index) => {
                    const quantiteLivree = parseFloat(ligne.quantite_livree) || 0;
                    const quantiteTotale = parseFloat(ligne.quantite) || 0;
                    const resteALivrer = Math.max(0, quantiteTotale - quantiteLivree);

                    // Formater les nombres pour l'affichage
                    const quantiteLivreeFormatted = formatNumber(quantiteLivree);
                    const quantiteTotaleFormatted = formatNumber(quantiteTotale);
                    const resteALivrerFormatted = formatNumber(resteALivrer);
                    articles += `
                        <tr>
                            <td>
                                <div class="fw-medium">${ligne.article.designation}</div>
                                <div class="small text-muted">${ligne.article.code_article}</div>
                                <input type="hidden" name="lignes[${ligne.article_id}][article_id]" value="${ligne.article_id}">
                            </td>
                            <td class="text-center">
                                <span></span>
                                <input type="hidden" name="lignes[${ligne.article_id}][unite_mesure_id]" value="${ligne.article_id}">
                            </td>
                            <td class="text-center">${quantiteTotaleFormatted}</td>
                            <td class="text-center">${quantiteLivreeFormatted}</td>
                            <td class="text-center">${resteALivrerFormatted}</td>
                            <td class="text-center">
                                <input type="number"
                                    class="form-control form-control-sm quantite-input text-end"
                                    name="lignes[${ligne.article_id}][quantite]"
                                    value="${livraison.lignes[index]?.quantite ?? 0}"
                                    min="0"
                                    step="0.01"
                                    max="${resteALivrer}"
                                    ${resteALivrer <= 0 ? 'readonly' : ''}
                                    required>
                            </td>
                            <td class="text-center">
                                <input type="number"
                                    class="form-control form-control-sm quantite-supp-input text-end"
                                    name="lignes[${ligne.article_id}][quantite_supplementaire]"
                                    value="${livraison.lignes[index]?.quantite_supplementaire ?? 0}"
                                    min="0"
                                    ${resteALivrer <= 0 ? 'readonly' : ''}>
                                <select class="form-select" name="lignes[${ligne.article_id}][unite_id]">
                                    <option value="">Sélectionner...</option>
                                    ${unites}
                                </select>
                            </td>
                        </tr>
                    `
                });

                $("#modalLignesFactureMod").html(articles);

                livraison.facture.lignes.forEach((ligne, index) => {
                    const selectUnite = $(`[name='lignes[${ligne.article_id}][unite_id]']`);
                    console.log(selectUnite)
                    const uniteOption = selectUnite.find(`option[value="${livraison.lignes[index]?.unite_supplementaire_id}"]`);
                    if (uniteOption.length > 0) {
                        uniteOption.prop("selected", true);
                    }
                });

                editForm.querySelector('[name="commentaire"]').value = result.livraison.commentaire;

                // editForm.querySelector('[name="nom"]').value = result.data.raison_sociale;
                // editForm.querySelector('[name="adresse"]').value = result.data.adresse || '';
                // editForm.querySelector('[name="telephone"]').value = result.data.telephone || '';
                // editForm.querySelector('[name="email"]').value = result.data.email || '';
                // editForm.querySelector('[name="actif"]').checked = result.data.statut;

                const modal = new bootstrap.Modal(editModal);
                Swal.close();
                modal.show();
            } else {
                throw new Error(result.message || 'Erreur lors du chargement des données');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des données',
                timer: 3000
            });
        }
    }

    function formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }

    document.getElementById('editLivraisonFournisseurForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mise à jour...';

        try {
            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Fermer le modal
                const editModal = document.getElementById('editLivraisonFournisseurModal');
                bootstrap.Modal.getInstance(editModal).hide();

                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Bon de livraison mis à jour avec succès'
                });

                // Recharger la page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(result.message || 'Erreur lors de la mise à jour');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors de la mise à jour de la caisse'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer les modifications';
        }
    });
</script>