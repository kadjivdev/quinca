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
                url: `${apiUrl}/revendeurs/livraisons/${livraisonId}/edit`,
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
                        // $("#currentDepot").html()
                        const depotSelect = $('#depot_id');
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
                            console.log("Les lignes :", response.lignes)

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
                                        <input type="hidden" required name="lignes[${ligne.id}][unite_vente_id]" value="${ligne.unite_mesure.id}" class="quantite-input">
                                        <input type="hidden" required name="lignes[${ligne.id}][prix_unitaire]" value="${ligne.prix_unitaire}" class="quantite-input">
                                        <input type="hidden" required name="lignes[${ligne.id}][ligne_facture_id]" value="${ligne.ligne_facture_id}" class="quantite-input">
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
                                                step="0.001"
                                                data-ligne-id="${ligne.id}"
                                                data-article-id="${ligne.article.id}">
                                        </div>
                                        <input type="hidden" name="lignes[${ligne.id}][article_id]" value="${ligne.article.id}">
                                        <input type="hidden" name="lignes[${ligne.id}][prix_unitaire]" value="${prixUnitaire}">
                                        <input type="hidden" name="lignes[${ligne.id}][ligne_facture_id]" value="${ligne.ligne_facture_id}">
                                    </td>
                                    <td class="text-center">
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                class="form-control"
                                                name="lignes[${ligne.id}][quantite_supplementaire]"
                                                value="${ligne.quantite_supplementaire}"
                                                min="0"
                                                step="0.001">
                                        </div>
                                    </td>
                                </tr>
                            `;
                            });
                        }

                        $('#editLignesFacture').html(html ||
                            '<tr><td colspan="8" class="text-center">Aucune ligne trouvée</td></tr>'
                        );

                        
                        console.log("livraisonId :", livraisonId)
                        $('#editLivraisonForm').attr("action", `${apiUrl}/revendeurs/livraisons/${livraisonId}/update`)


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

        // Récupérer tous les inputs du formulaire sous forme d'objet imbriqué
        function getFormData(formSelector) {
            const arr = $(formSelector).serializeArray();
            const data = {};

            arr.forEach(({
                name,
                value
            }) => {
                // transformer "lignes[123][quantite]" en ['lignes','123','quantite']
                const keys = name.replace(/\]/g, '').split('[');
                let cur = data;

                keys.forEach((k, idx) => {
                    if (idx === keys.length - 1) {
                        // dernier niveau -> assigner
                        if (cur[k] !== undefined) {
                            // convertir en tableau si nécessaire
                            if (!Array.isArray(cur[k])) cur[k] = [cur[k]];
                            cur[k].push(value);
                        } else {
                            cur[k] = value;
                        }
                    } else {
                        if (!cur[k]) cur[k] = {};
                        cur = cur[k];
                    }
                });
            });

            return data;
        }

        // $('#_editLivraisonForm').on('submit', function(e) {
        //     e.preventDefault();

        //     if (!this.checkValidity()) {
        //         e.stopPropagation();
        //         $(this).addClass('was-validated');
        //         return;
        //     }

        //     const depotId = $('#depot_id').val();
        //     if (!depotId) {
        //         Toast.fire({
        //             icon: 'warning',
        //             title: 'Veuillez sélectionner un magasin'
        //         });
        //         return;
        //     }

        //     // Récupérer toutes les données du formulaire et filtrer les lignes
        //     const allData = getFormData('#editLivraisonForm');
        //     let lignes = allData.lignes || {};
        //     const filteredLignes = {};
        //     let hasQuantity = false;

        //     Object.keys(lignes).forEach((ligneId) => {
        //         const l = lignes[ligneId];
        //         const quantite = parseFloat(l.quantite) || 0;
        //         if (quantite > 0) {
        //             hasQuantity = true;
        //             filteredLignes[ligneId] = l;
        //             filteredLignes[ligneId].quantite = quantite;
        //         }
        //     });

        //     lignes = filteredLignes;

        //     if (!hasQuantity) {
        //         Toast.fire({
        //             icon: 'warning',
        //             title: 'Veuillez saisir au moins une quantité à livrer'
        //         });
        //         return;
        //     }


        //     const submitBtn = $('#btnUpdateLivraison');
        //     submitBtn.prop('disabled', true)
        //         .html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

        //     console.log("les lignes generées :", lignes)

        //     const data = {
        //         _token: $('meta[name="csrf-token"]').attr('content'),
        //         _method: 'PUT',
        //         depot_id: depotId,
        //         notes: $('#editNotes').val(),
        //         lignes: lignes
        //     };

        //     const livraisonId = $('#editLivraisonId').val();

        //     $.ajax({
        //         url: `${apiUrl}/revendeurs/livraisons/${livraisonId}`,
        //         method: 'POST',
        //         data: data,
        //         success: function(response) {
        //             if (response.success) {
        //                 $('#editLivraisonModal').modal('hide');
        //                 Toast.fire({
        //                     icon: 'success',
        //                     title: response.message ||
        //                         'Livraison modifiée avec succès'
        //                 });
        //                 window.location.reload()
        //                 // refreshLivraisonsList();
        //                 // refreshList();
        //             } else {
        //                 Toast.fire({
        //                     icon: 'error',
        //                     title: response.message ||
        //                         'Erreur lors de la modification'
        //                 });
        //             }
        //         },
        //         error: function(xhr) {
        //             Toast.fire({
        //                 icon: 'error',
        //                 title: xhr.responseJSON?.message ||
        //                     'Erreur lors de la modification'
        //             });
        //         },
        //         complete: function() {
        //             submitBtn.prop('disabled', false)
        //                 .html('<i class="fas fa-save me-2"></i>Enregistrer');
        //         }
        //     });
        // });

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