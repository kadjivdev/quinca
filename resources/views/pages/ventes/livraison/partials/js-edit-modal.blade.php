<script>
    $(document).ready(function() {
        // Fonction pour formater les nombres
        function formatNumber(number) {
            return Number(number).toLocaleString('fr-FR', {
                minimumFractionDigits: 3,
                maximumFractionDigits: 3
            });
        }

        // Fonction pour charger les données de la livraison
        function loadLivraisonData(livraisonId) {
            $.ajax({
                url: `${apiUrl}/vente/livraisons/${livraisonId}/edit`,
                method: 'GET',
                success: function(response) {
                    if (response?.success && response?.livraison?.facture?.client) {
                        // Remplir les champs du formulaire
                        $('#editLivraisonId').val(livraisonId);
                        $('#editClientName').text(response.livraison.facture.client.raison_sociale);
                        $('#editNumeroFacture').text(response.livraison.facture.numero);
                        $('#editDateFacture').text(response.livraison.facture.date_facture);
                        $('#editFactureId').val(response.livraison.facture.id);
                        $('#editNotes').val(response.livraison.notes);

                        // Remplir le select des dépôts
                        const depotSelect = $('#editDepotId');
                        depotSelect.empty();
                        depotSelect.append('<option value="">Sélectionner un magasin</option>');
                        if (Array.isArray(response.depots)) {
                            response.depots.forEach(depot => {
                                depotSelect.append(`
                                <option value="${depot.id}"
                                    ${depot.id == response.livraison.depot_id ? 'selected' : ''}>
                                    ${depot.libelle_depot}
                                </option>
                            `);
                            });
                        }

                        // Générer les lignes du tableau
                        let html = '';
                        if (Array.isArray(response.lignes)) {
                            response.lignes.forEach(ligne => {
                                if (!ligne?.article || !ligne?.unite_mesure) return;

                                const stockDisponible = parseFloat(ligne
                                    .stock_disponible) || 0;
                                const resteALivrer = parseFloat(ligne.reste_a_livrer) || 0;
                                const stockClass = stockDisponible < resteALivrer ?
                                    'stock-danger' : '';
                                const quantite = parseFloat(ligne.quantite) || 0;
                                const prixUnitaire = parseFloat(ligne.prix_unitaire) || 0;

                                html += `
                                <tr>
                                    <td>
                                        <div class="fw-medium">${ligne.article.designation}</div>
                                        <small class="text-muted">${ligne.article.reference}</small>
                                    </td>
                                    <td class="text-center">${ligne.unite_mesure.libelle}</td>
                                    <td class="text-center">
                                        ${formatNumber(ligne.quantite_facturee)}
                                        <small class="text-muted">${ligne.unite_mesure.libelle}</small>
                                    </td>
                                    <td class="text-center">
                                        ${formatNumber(ligne.quantite_livree)}
                                        <small class="text-muted">${ligne.unite_mesure.libelle}</small>
                                    </td>
                                    <td class="text-center">
                                        ${formatNumber(resteALivrer)}
                                        <small class="text-muted">${ligne.unite_mesure.libelle}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                class="form-control quantite-input"
                                                name="lignes[${ligne.id}][quantite]"
                                                value="${quantite}"
                                                min="0"
                                                max="${Math.min(resteALivrer, stockDisponible)}"
                                                step="0.001"
                                                data-ligne-id="${ligne.id}"
                                                data-article-id="${ligne.article.id}">
                                        </div>
                                        <input type="hidden" name="lignes[${ligne.id}][article_id]" value="${ligne.article.id}">
                                        <input type="hidden" name="lignes[${ligne.id}][prix_unitaire]" value="${prixUnitaire}">
                                        <input type="hidden" name="lignes[${ligne.id}][ligne_facture_id]" value="${ligne.ligne_facture_id}">
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-${stockDisponible > 0 ? 'success' : 'danger'} stock-badge">
                                            ${formatNumber(stockDisponible)}
                                            <small>${ligne.unite_mesure.libelle}</small>
                                        </span>
                                    </td>
                                </tr>
                            `;
                            });
                        }

                        $('#editLignesFacture').html(html ||
                            '<tr><td colspan="8" class="text-center">Aucune ligne trouvée</td></tr>'
                            );

                        // Initialiser les composants
                        initializeEditComponents();
                        updateSaveButton();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Structure de données invalide'
                        });
                    }
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message ||
                            'Erreur lors du chargement des données'
                    });
                    $('#editLignesFacture').html(
                        '<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des données</td></tr>'
                        );
                }
            });
        }

        // Initialisation des composants
        function initializeEditComponents() {
            // Nettoyer les anciens listeners avant d'en ajouter de nouveaux
            $('.quantite-input').off('input').on('input', function() {
                const val = this.value.replace(',', '.');
                if (!/^\d*\.?\d*$/.test(val)) {
                    this.value = 0;
                    return;
                }

                const max = parseFloat($(this).attr('max'));
                let quantity = parseFloat(val) || 0;

                if (quantity > max) {
                    quantity = max;
                    this.value = quantity;
                    Toast.fire({
                        icon: 'warning',
                        title: 'Quantité ajustée au maximum disponible'
                    });
                }

                updateSaveButton();
            });
        }

        // Mise à jour du bouton de sauvegarde
        function updateSaveButton() {
            let hasQuantity = false;
            $('.quantite-input').each(function() {
                if (parseFloat($(this).val()) > 0) {
                    hasQuantity = true;
                    return false;
                }
            });

            $('#btnUpdateLivraison').prop('disabled', !hasQuantity);
        }

        // Soumission du formulaire
        $('#editLivraisonForm').on('submit', function(e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            const depotId = $('#editDepotId').val();
            if (!depotId) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Veuillez sélectionner un magasin'
                });
                return;
            }

            // Vérifier si au moins une quantité est saisie
            const lignes = {};
            let hasQuantity = false;

            $('.quantite-input').each(function() {
                const quantite = parseFloat($(this).val()) || 0;
                if (quantite > 0) {
                    hasQuantity = true;
                    const ligneId = $(this).data('ligne-id');
                    lignes[ligneId] = {
                        ligne_facture_id: $(
                            `input[name="lignes[${ligneId}][ligne_facture_id]"]`).val(),
                        article_id: $(`input[name="lignes[${ligneId}][article_id]"]`).val(),
                        prix_unitaire: $(`input[name="lignes[${ligneId}][prix_unitaire]"]`)
                            .val(),
                        quantite: quantite
                    };
                }
            });

            if (!hasQuantity) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Veuillez saisir au moins une quantité à livrer'
                });
                return;
            }

            const submitBtn = $('#btnUpdateLivraison');
            submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

            const data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                depot_id: depotId,
                notes: $('#editNotes').val(),
                lignes: lignes
            };

            const livraisonId = $('#editLivraisonId').val();

            $.ajax({
                url: `${apiUrl}/vente/livraisons/${livraisonId}`,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#editLivraisonModal').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: response.message ||
                                'Livraison modifiée avec succès'
                        });
                        // refreshLivraisonsList();
                        refreshList();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message ||
                                'Erreur lors de la modification'
                        });
                    }
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message ||
                            'Erreur lors de la modification'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false)
                        .html('<i class="fas fa-save me-2"></i>Enregistrer');
                }
            });
        });

        // Rafraîchissement de la liste des livraisons
        // function refreshLivraisonsList() {
        //     const filtres = {
        //         client_id: $('#filterClient').val(),
        //         depot_id: $('#filterDepot').val(),
        //         statut: $('#filterStatut').val(),
        //         date_debut: $('#filterDateDebut').val(),
        //         date_fin: $('#filterDateFin').val()
        //     };

        //     $.get('/vente/livraisons/refresh', filtres, function(response) {
        //         $('#livraisons-table-container').html(response.html);
        //         updateStats(response.stats);
        //     });
        // }

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

        // Réinitialisation du modal
        $('#editLivraisonModal').on('hidden.bs.modal', function() {
            $('#editLivraisonForm')[0].reset();
            $('#editLignesFacture').html('');
            $('#editClientName').text('');
            $('#editNumeroFacture').text('');
            $('#editDateFacture').text('');
            $('#btnUpdateLivraison').prop('disabled', true);
        });

        // Pour ouvrir le modal
        window.editLivraison = function(livraisonId) {
            $('#editLivraisonModal').modal('show');
            loadLivraisonData(livraisonId);
        }
    });
</script>
