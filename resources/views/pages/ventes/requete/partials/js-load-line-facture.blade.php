<script>
    // Au changement de facture
    $('#factureSelect').on('change', function() {
        const factureId = $(this).val();
        $('#factureClientId').val(factureId);

        if (!factureId) {
            $('#lignesFacture').html(`
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        Veuillez sélectionner une facture
                    </td>
                </tr>
            `);
            return;
        }

        // Afficher le loader
        $('#lignesFacture').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </td>
            </tr>
        `);

        // Charger les lignes de la facture
        $.ajax({
            url: `${apiUrl}/vente/livraisons/facture/${factureId}/lignes-disponibles`,
            method: 'GET',
            success: function(response) {
                $('#factureInfo').text(
                    `Facture N° ${response.facture.numero} du ${response.facture.date_facture}`);
                $('#clientName').text(response.facture.client.raison_sociale);

                if (response.lignes.length === 0) {
                    $('#lignesFacture').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Toutes les lignes de cette facture ont déjà été livrées
                            </td>
                        </tr>
                    `);
                    return;
                }

                let html = '';
                response.lignes.forEach(ligne => {
                    html += `
                        <tr>
                            <td>
                                <div class="fw-medium">${ligne.article.designation}</div>
                                <div class="small text-muted">${ligne.article.reference}</div>
                            </td>
                            <td class="text-center">
                                ${ligne.quantite_facturee} ${ligne.unite_vente.libelle}
                            </td>
                            <td class="text-center">
                                ${ligne.quantite_livree} ${ligne.unite_vente.libelle}
                            </td>
                            <td class="text-center">
                                ${ligne.reste_a_livrer} ${ligne.unite_vente.libelle}
                            </td>
                            <td class="text-center">
                                <input type="number"
                                       class="form-control form-control-sm quantite-input text-end"
                                       name="lignes[${ligne.id}][quantite]"
                                       min="0"
                                       max="${ligne.reste_a_livrer}"
                                       step="0.001"
                                       data-ligne-id="${ligne.article.id}"
                                       data-max="${ligne.reste_a_livrer}"
                                       value="0">
                                <input type="hidden" name="lignes[${ligne.id}][ligne_facture_id]" value="${ligne.id}">
                                <input type="hidden" name="lignes[${ligne.id}][article_id]" value="${ligne.article.id}">
                                <input type="hidden" name="lignes[${ligne.id}][unite_vente_id]" value="${ligne.unite_vente.id}">
                                <input type="hidden" name="lignes[${ligne.id}][prix_unitaire]" value="${ligne.prix_unitaire}">
                            </td>
                            <td class="text-center">
                                <span class="stock-dispo" id="stock-${ligne.article.id}">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </td>
                        </tr>
                    `;
                });

                $('#lignesFacture').html(html);

                // Activer les inputs par défaut
                $('.quantite-input').prop('disabled', false);

                // Vérifier le stock si un magasin est sélectionné
                const depotId = $('select[name="depot_id"]').val();
                if (depotId) {
                    response.lignes.forEach(ligne => {
                        verifierStock(ligne.article.id, depotId);
                    });
                }

                // S'assurer que le bouton est dans le bon état
                updateSaveButton();
            },
            error: function() {
                $('#lignesFacture').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Erreur lors du chargement des lignes
                        </td>
                    </tr>
                `);
            }
        });
    });


    // Fonction pour vérifier le stock d'un article
    function verifierStock(articleId, depotId) {
        if (!depotId) {
            $(`#stock-${articleId}`).html(`
            <span class="text-warning">
                <i class="fas fa-exclamation-triangle"></i> Sélectionnez un magasin
            </span>
        `);
            return;
        }

        $(`#stock-${articleId}`).html(`
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Chargement...</span>
        </div>
    `);

        // Correction de l'URL
        $.ajax({
            url: `${apiUrl}/vente/livraisons/verifier-stock`, // URL corrigée
            method: 'GET',
            data: {
                article_id: articleId,
                depot_id: depotId
            },
            success: function(response) {
                if (response.success) {
                    const stockElement = $(`#stock-${articleId}`);
                    const quantite = parseFloat(response.quantite);

                    if (quantite > 0) {
                        stockElement.html(`
                        <span class="badge bg-success">
                            ${response.quantite} disponible(s)
                        </span>
                    `);
                    } else {
                        stockElement.html(`
                        <span class="badge bg-danger">
                            Stock épuisé
                        </span>
                    `);
                    }
                } else {
                    $(`#stock-${articleId}`).html(`
                    <span class="badge bg-danger">
                        Erreur de vérification
                    </span>
                `);
                }
            },
            error: function() {
                $(`#stock-${articleId}`).html(`
                <span class="badge bg-danger">
                    Erreur de vérification
                </span>
            `);
            }
        });
    }

    // Mettre à jour le gestionnaire d'événements du magasin
    $('select[name="depot_id"]').on('change', function() {
        const depotId = $(this).val();
        if (depotId) {
            $('.stock-dispo').each(function() {
                const articleId = $(this).attr('id').replace('stock-', '');
                verifierStock(articleId, depotId);
            });
        } else {
            $('.stock-dispo').html(`
            <span class="text-warning">
                <i class="fas fa-exclamation-triangle"></i> Sélectionnez un magasin
            </span>
        `);
        }
    });
</script>

<script>
    // Fonction pour vérifier le stock d'un article
    function verifierStock(articleId, depotId) {
        if (!depotId) {
            $(`#stock-${articleId}`).html(`
                <span class="text-warning">
                    <i class="fas fa-exclamation-triangle"></i> Sélectionnez un magasin
                </span>
            `);
            return;
        }

        $(`#stock-${articleId}`).html(`
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        `);

        $.ajax({
            url: `${apiUrl}/vente/livraisons/verifier-stock`,
            method: 'GET',
            data: {
                article_id: articleId,
                depot_id: depotId
            },
            success: function(response) {
                const stockElement = $(`#stock-${articleId}`);
                const quantite = parseFloat(response.quantite);
                const resteALivrer = parseFloat($(`input[data-ligne-id="${articleId}"]`).data('max'));

                if (quantite >= resteALivrer) {
                    // Stock suffisant
                    stockElement.html(`
                        <span class="badge bg-success">
                            ${response.quantite}
                        </span>
                    `);
                    // Activer l'input
                    $(`input[data-ligne-id="${articleId}"]`).prop('disabled', false);
                } else if (quantite > 0) {
                    // Stock partiel
                    stockElement.html(`
                        <span class="badge bg-warning" data-bs-toggle="tooltip" title="Stock insuffisant">
                            ${response.quantite}
                        </span>
                    `);
                    // Limiter l'input au stock disponible
                    const input = $(`input[data-ligne-id="${articleId}"]`);
                    input.prop('disabled', false);
                    input.attr('max', quantite);
                } else {
                    // Pas de stock
                    stockElement.html(`
                        <span class="badge bg-danger">
                            Indisponible
                        </span>
                    `);
                    // Désactiver l'input
                    $(`input[data-ligne-id="${articleId}"]`).prop('disabled', true);
                }

                // Réinitialiser les tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function() {
                $(`#stock-${articleId}`).html(`
                    <span class="badge bg-danger" data-bs-toggle="tooltip" title="Erreur lors de la vérification">
                        <i class="fas fa-exclamation-circle"></i>
                    </span>
                `);
                // Désactiver l'input par sécurité
                $(`input[data-ligne-id="${articleId}"]`).prop('disabled', true);
            }
        });
    }

    // Mise à jour du gestionnaire d'événements pour le changement de magasin
    $('select[name="depot_id"]').on('change', function() {
        const depotId = $(this).val();

        // Réinitialiser tous les inputs
        $('.quantite-input').val('').prop('disabled', true);

        // Vérifier le stock pour chaque article
        $('.stock-dispo').each(function() {
            const articleId = $(this).attr('id').replace('stock-', '');
            verifierStock(articleId, depotId);
        });
    });

    // Ajouter une validation sur les inputs de quantité
    $(document).on('input', '.quantite-input', function() {
        const input = $(this);
        const max = parseFloat(input.attr('max'));
        const value = parseFloat(input.val());

        if (value > max) {
            input.val(max);
            // Optionnel : afficher un message d'avertissement
            toastr.warning('Quantité ajustée au maximum disponible');
        }
    });


    // Fonction de vérification du stock
    function verifierStock(articleId, depotId) {
        if (!depotId) {
            $(`#stock-${articleId}`).html(`
                <span class="badge bg-warning">
                    <i class="fas fa-exclamation-triangle"></i> Sélectionnez un magasin
                </span>
            `);
            // Ne pas désactiver l'input si pas de magasin sélectionné
            $(`input[data-ligne-id="${articleId}"]`).prop('disabled', false);
            return;
        }

        $(`#stock-${articleId}`).html(`
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        `);

        $.ajax({
            url: `${apiUrl}/vente/livraisons/verifier-stock`,
            method: 'GET',
            data: {
                article_id: articleId,
                depot_id: depotId
            },
            success: function(response) {
                const stockElement = $(`#stock-${articleId}`);
                const quantite = parseFloat(response.quantite);
                const input = $(`input[data-ligne-id="${articleId}"]`);
                const resteALivrer = parseFloat(input.data('max'));

                // Ne jamais désactiver l'input, juste mettre des warnings visuels
                input.prop('disabled', false);

                if (quantite >= resteALivrer) {
                    // Stock suffisant
                    stockElement.html(`
                        <span class="badge bg-success">
                            ${response.quantite} disponible(s)
                        </span>
                    `);
                } else if (quantite > 0) {
                    // Stock partiel
                    stockElement.html(`
                        <span class="badge bg-warning" data-bs-toggle="tooltip"
                              title="Stock disponible limité">
                            ${response.quantite} disponible(s)
                        </span>
                    `);
                } else {
                    // Pas de stock
                    stockElement.html(`
                        <span class="badge bg-danger">
                            Stock épuisé
                        </span>
                    `);
                }
            },
            error: function() {
                $(`#stock-${articleId}`).html(`
                    <span class="badge bg-danger">
                        Erreur de vérification
                    </span>
                `);
                // En cas d'erreur, ne pas bloquer la saisie
                $(`input[data-ligne-id="${articleId}"]`).prop('disabled', false);
            }
        });
    }

    // Au changement de magasin
    $('select[name="depot_id"]').on('change', function() {
        const depotId = $(this).val();
        $('.stock-dispo').each(function() {
            const articleId = $(this).attr('id').replace('stock-', '');
            verifierStock(articleId, depotId);
        });
    });

    // Mise à jour du bouton de sauvegarde
    function updateSaveButton() {
        let hasValidQuantity = false;
        $('.quantite-input').each(function() {
            const value = parseFloat($(this).val()) || 0;
            if (value > 0) {
                hasValidQuantity = true;
                return false;
            }
        });
        $('#btnSaveLivraison').prop('disabled', !hasValidQuantity);
    }

    // Validation des quantités
    $(document).on('input', '.quantite-input', function() {
        const input = $(this);
        const value = parseFloat(input.val()) || 0;
        const max = parseFloat(input.data('max'));

        if (value > max) {
            input.val(max);
            toastr.warning('La quantité a été ajustée au maximum disponible');
        }

        updateSaveButton();
    });


    // Fonction pour rafraîchir la liste


    // Fonction pour mettre à jour les stats
    function updateStats(stats) {
        if (stats.total) $('#totalLivraisons').text(stats.total);
        if (stats.validees) $('#livraisonsValidees').text(stats.validees);
        if (stats.en_attente) $('#livraisonsEnAttente').text(stats.en_attente);
    }
</script>
