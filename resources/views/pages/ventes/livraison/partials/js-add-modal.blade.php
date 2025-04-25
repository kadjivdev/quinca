<script>
    $(document).ready(function() {
        // Gestion du changement de facture
        $('#factureSelect').on('change', function() {
            const factureId = $(this).val();
            const depotId = $('select[name="depot_id"]').val();

            if (factureId && depotId) {
                chargerLignesFacture(factureId, depotId);
            } else {
                $('#lignesFacture').html(`
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        Veuillez sélectionner une facture et un magasin
                    </td>
                </tr>
            `);
            }
            updateSaveButton();
        });

        // Gestion du changement de magasin
        $('select[name="depot_id"]').on('change', function() {
            const factureId = $('#factureSelect').val();
            const depotId = $(this).val();

            if (factureId && depotId) {
                chargerLignesFacture(factureId, depotId);
            }
            updateSaveButton();
        });

        // Fonction pour charger les lignes de facture
        function chargerLignesFacture(factureId, depotId) {
            $.ajax({
                url: `${apiUrl}/vente/livraisons/facture/${factureId}/lignes-disponibles`,
                data: {
                    depot_id: depotId
                },
                success: function(response) {
                    if (response.success) {
                        // Mise à jour des informations client et facture
                        $('#clientName').text(response.facture.client.raison_sociale);
                        $('#factureInfo').text(
                            `Facture ${response.facture.numero} du ${response.facture.date_facture}`
                            );

                        // Génération des lignes
                        let html = '';
                        response.lignes.forEach(function(ligne) {
                            const stockClass = ligne.stock_disponible < ligne
                                .reste_a_livrer ? 'stock-danger' : '';

                            html += `
                            <tr>
                                <td>
                                    <div class="fw-medium">${ligne.article.designation}</div>
                                    <small class="text-muted">${ligne.article.reference}</small>
                                </td>
                                <td class="text-center">
                                    ${ligne.quantite_facturee}
                                    <small class="text-muted">${ligne.unite_vente.libelle}</small>
                                </td>
                                <td class="text-center">
                                    ${ligne.quantite_livree}
                                    <small class="text-muted">${ligne.unite_vente.libelle}</small>
                                </td>
                                <td class="text-center">
                                    ${ligne.reste_a_livrer}
                                    <small class="text-muted">${ligne.unite_vente.libelle}</small>
                                </td>
                                <td>
                                    <input type="hidden" name="lignes[${ligne.id}][ligne_facture_id]" value="${ligne.id}">
                                    <input type="hidden" name="lignes[${ligne.id}][article_id]" value="${ligne.article.id}">
                                    <input type="hidden" name="lignes[${ligne.id}][unite_vente_id]" value="${ligne.unite_vente.id}">
                                    <input type="hidden" name="lignes[${ligne.id}][prix_unitaire]" value="${ligne.prix_unitaire}">
                                    <div class="input-group input-group-sm">
                                        <input type="number"
                                            class="form-control quantite-input"
                                            name="lignes[${ligne.id}][quantite]"
                                            min="0"
                                            max="${ligne.reste_a_livrer}"
                                            step="0.001"
                                            value="0">
                                        <span class="input-group-text">${ligne.unite_vente.libelle}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark stock-badge ${stockClass}">
                                        ${ligne.stock_disponible}
                                        <small>${ligne.unite_vente.libelle}</small>
                                    </span>
                                </td>
                                // <td class="text-center">
                                //     <span class="prix-moyen">
                                //         ${ligne.prix_moyen} FCFA
                                //     </span>
                                // </td>
                            </tr>
                        `;
                        });

                        $('#lignesFacture').html(html);

                        // Initialisation des gestionnaires d'événements sur les inputs
                        initQuantiteInputs();
                    } else {
                        // Toast.fire({
                        //     icon: 'error',
                        //     title: response.message
                        // });
                    }
                },
                error: function() {
                    // Toast.fire({
                    //     icon: 'error',
                    //     title: 'Erreur lors du chargement des lignes'
                    // });
                }
            });
        }

        // Fonction pour initialiser les gestionnaires sur les inputs de quantité
        function initQuantiteInputs() {
            $('.quantite-input').on('input', function() {
                const max = parseFloat($(this).attr('max'));
                let val = parseFloat($(this).val()) || 0;

                if (val > max) {
                    $(this).val(max);
                    Toast.fire({
                        icon: 'warning',
                        title: 'La quantité saisie dépasse le reste à livrer'
                    });
                }

                updateSaveButton();
            });
        }

        // Fonction pour activer/désactiver le bouton d'enregistrement
        function updateSaveButton() {
            const factureSelected = $('#factureSelect').val() !== '';
            const depotSelected = $('select[name="depot_id"]').val() !== '';
            let hasQuantity = false;

            $('.quantite-input').each(function() {
                if (parseFloat($(this).val()) > 0) {
                    hasQuantity = true;
                    return false; // Break the loop
                }
            });

            $('#btnSaveLivraison').prop('disabled', !(factureSelected && depotSelected && hasQuantity));
        }

        // Soumission du formulaire
        $('#addLivraisonForm').on('submit', function(e) {
            e.preventDefault();

            // Vérifier si au moins une quantité est saisie
            let hasQuantity = false;
            $('.quantite-input').each(function() {
                if (parseFloat($(this).val()) > 0) {
                    hasQuantity = true;
                    return false;
                }
            });

            if (!hasQuantity) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Veuillez saisir au moins une quantité à livrer'
                });
                return;
            }

            const submitBtn = $('#btnSaveLivraison');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

            // Préparation des données
            const formData = new FormData(this);

            $.ajax({
                url: `${apiUrl}/vente/livraisons`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#addLivraisonModal').modal('hide');

                        Toast.fire({
                            icon: 'success',
                            title: response.message ||
                                'Bon de livraison créé avec succès'
                        });

                        // Réinitialisation du formulaire
                        $('#addLivraisonForm')[0].reset();
                        $('#factureSelect').val('').trigger('change');

                        // Rafraîchissement de la liste
                        refreshList();
                    } else {
                        Toast.fire({
                            icon: response.type || 'warning',
                            title: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Une erreur est survenue';
                    let icon = 'error';

                    if (xhr.responseJSON) {
                        if (xhr.status === 422) {
                            message = xhr.responseJSON.message;
                            icon = 'warning';
                        } else {
                            message = xhr.responseJSON.message;
                        }
                    }

                    Toast.fire({
                        icon: icon,
                        title: message,
                        timer: 5000
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html('<i class="fas fa-save me-2"></i>Enregistrer');
                }
            });
        });

        // Rafraîchissement de la liste
        function refreshList() {
            $.ajax({
                url: window.location.href,
                type: 'GET',
                success: function(response) {
                    if (typeof response === 'string') {
                        const newContent = $(response).find('#livraisonsTable').html();
                        $('#livraisonsTable').html(newContent);

                        const newStats = $(response).find('#statsContainer').html();
                        if (newStats) {
                            $('#statsContainer').html(newStats);
                        }
                    } else if (response.html) {
                        $('#livraisonsTable').html(response.html);
                        if (response.stats) {
                            updateStats(response.stats);
                        }
                    }

                    initTooltips();
                },
                error: function() {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du rafraîchissement de la liste'
                    });
                }
            });
        }

        // Initialisation des tooltips
        function initTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        // Réinitialisation du modal
        $('#addLivraisonModal').on('show.bs.modal', function() {
            $(this).find('form')[0].reset();
            $('#factureInfo').text('');
            $('#clientName').text('');
            $('#lignesFacture').html(`
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    Veuillez sélectionner une facture
                </td>
            </tr>
        `);
            $('#factureSelect').val('').trigger('change');
            $('select[name="depot_id"]').val('');
            updateSaveButton();
        });

        // Initialisation de Select2
        $('#factureSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Sélectionner une facture'
        });

        // Vérification dynamique des stocks
        function verifierStock(articleId, depotId, quantite, callback) {
            $.ajax({
                url: `${apiUrl}/vente/livraisons/verifier-stock`,
                type: 'GET',
                data: {
                    article_id: articleId,
                    depot_id: depotId
                },
                success: function(response) {
                    if (response.success) {
                        const stockDisponible = parseFloat(response.quantite);
                        callback(stockDisponible >= quantite, stockDisponible, response.prix_moyen);
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
                    }
                },
                error: function() {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors de la vérification du stock'
                    });
                }
            });
        }
    });
</script>
