<script>
    function showLivraison(id) {
        // Afficher un loader dans le modal
        $('#showLivraisonModal').modal('show');
        
        // Charger les données
        $.ajax({
            url: `${apiUrl}/vente/livraisons/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Remplir les informations de base
                    $('#blNumero').text(`Bon de livraison N° ${data.livraison.numero}`);
                    $('#factureNumero').text(data.livraison.facture.numero);
                    $('#factureDate').text(data.livraison.facture.date);
                    
                    // Informations client
                    $('#clientNom').text(data.livraison.facture.client.raison_sociale);
                    $('#clientContact').text(data.livraison.facture.client.telephone);
                    $('#clientAdresse').text(data.livraison.facture.client.adresse);
                    
                    // Lignes de livraison
                    let lignesHtml = '';
                    data.lignes.forEach(ligne => {
                        lignesHtml += `
                            <tr>
                                <td>
                                    <div class="fw-medium">${ligne.article.designation}</div>
                                    <div class="small text-muted">${ligne.article.reference}</div>
                                </td>
                                <td class="text-center">
                                    ${ligne.quantite} ${ligne.unite}
                                </td>
                            </tr>
                        `;
                    });
                    $('#lignesLivraison').html(lignesHtml);
                    
                    // Notes et infos supplémentaires
                    $('#livraisonNotes').text(data.livraison.notes || 'Aucune note');
                    $('#createInfo').text(data.livraison.created_by || '-');
                    $('#validateInfo').text(data.livraison.validated_by || '-');
                }
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des détails'
                });
                $('#showLivraisonModal').modal('hide');
            }
        });
    }
    
    // Fonction utilitaire pour formater les montants
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(montant);
    }
    </script>