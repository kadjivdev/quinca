<script>
    // Au changement de facture
    // Au changement de facture
    $('#factureSelect').on('change', function() {
        const factureId = $(this).val();
        const depotId = $('select[name="depot_id"]').val();
        $('#factureClientId').val(factureId);

        if (!factureId) {
            $('#lignesFacture').html(`
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    Veuillez sélectionner une facture
                </td>
            </tr>
        `);
            return;
        }

        // Afficher le loader
        $('#lignesFacture').html(`
        <tr>
            <td colspan="7" class="text-center py-4">
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
            data: {
                depot_id: depotId
            },
            success: function(response) {
                $('#factureInfo').text(
                    `Facture N° ${response.facture.numero} du ${response.facture.date_facture}`);
                $('#clientName').text(response.facture.client.raison_sociale);

                if (response.lignes.length === 0) {
                    $('#lignesFacture').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
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
                ${formatNumber(ligne.quantite_facturee)} ${ligne.unite_vente.libelle}
            </td>
            <td class="text-center">
                ${formatNumber(ligne.quantite_livree)} ${ligne.unite_vente.libelle}
            </td>
            <td class="text-center">
                ${formatNumber(ligne.reste_a_livrer)} ${ligne.unite_vente.libelle}
            </td>
            <td class="text-center">
                <input type="number"
                       class="form-control form-control-sm quantite-input text-end"
                       name="lignes[${ligne.id}][quantite]"
                       min="0"
                       max="${ligne.reste_a_livrer}"
                       data-ligne-id="${ligne.article.id}"
                       data-max="${ligne.reste_a_livrer}"
                       value="0,000">

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

                // Initialiser Select2 pour les lots
                $('.lot-select').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Sélection automatique'
                });

                // Gérer le changement de lot
                $('.lot-select').on('change', function() {
                    const selectedOption = $(this).find(':selected');
                    const quantiteDisponible = selectedOption.data('quantite');
                    const row = $(this).closest('tr');
                    const quantiteInput = row.find('.quantite-input');

                    if (selectedOption.val()) {
                        // Si un lot est sélectionné, ajuster la quantité max
                        quantiteInput.attr('max', Math.min(quantiteDisponible, quantiteInput
                            .data('max-initial')));
                    } else {
                        // Si "Sélection automatique", remettre la limite initiale
                        quantiteInput.attr('max', quantiteInput.data('max-initial'));
                    }

                    // Ajuster la quantité si elle dépasse le nouveau maximum
                    const currentValue = parseFloat(quantiteInput.val()) || 0;
                    const maxValue = parseFloat(quantiteInput.attr('max'));
                    if (currentValue > maxValue) {
                        quantiteInput.val(maxValue);
                    }
                });

                // Vérifier le stock si un magasin est sélectionné
                if (depotId) {
                    response.lignes.forEach(ligne => {
                        verifierStock(ligne.article.id, depotId);
                    });
                }

                // Activer les tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();

                // S'assurer que le bouton est dans le bon état
                updateSaveButton();
            },
            error: function() {
                $('#lignesFacture').html(`
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Erreur lors du chargement des lignes
                    </td>
                </tr>
            `);
            }
        });
    });

    // Mettre à jour la sélection des lots quand le magasin change
    $('select[name="depot_id"]').on('change', function() {
        const factureId = $('#factureSelect').val();
        if (factureId) {
            $('#factureSelect').trigger('change');
        }
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
                            ${response.quantite} ${response.unite}
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
    // $(document).on('input', '.quantite-input', function() {
    //     const input = $(this);
    //     const max = parseFloat(input.attr('max'));
    //     const value = parseFloat(input.val());

    //     if (value > max) {
    //         input.val(max);
    //         // Optionnel : afficher un message d'avertissement
    //         toastr.warning('Quantité ajustée au maximum disponible');
    //     }
    // });


    // Fonction de vérification du stock


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

    function formatNumber(number) {
        // S'assurer que le nombre est traité comme un float
        const num = typeof number === 'string' ? parseFloat(number.replace(',', '.')) : number;

        // Ne formater que si c'est un nombre valide
        if (isNaN(num)) return '';

        // Formatter avec 3 décimales maximum et les séparateurs de milliers
        return num.toLocaleString('fr-FR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
            useGrouping: true
        });
    }

    // Pour la validation des quantités
    $(document).on('input', '.quantite-input', function() {
        const input = $(this);
        // Récupérer la valeur saisie brute, sans formatage
        let value = input.val().replace(/\s/g, '').replace(',', '.');

        // Ne traiter que si une valeur est saisie
        if (value !== '') {
            value = parseFloat(value);
            const max = parseFloat(input.attr('max').replace(/\s/g, '').replace(',', '.'));
            const min = parseFloat(input.attr('min')) || 0;

            if (!isNaN(value)) {
                if (value > max) {
                    input.val(formatNumber(max));
                    toastr.warning('La quantité a été ajustée au maximum disponible');
                } else if (value < min) {
                    input.val(formatNumber(min));
                } else {
                    // Ne formater que si la saisie est complète
                    if (value.toString().split('.')[1]?.length >= 3 || !input.is(':focus')) {
                        input.val(formatNumber(value));
                    }
                }
            }
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
