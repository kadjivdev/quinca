<script>
    function showMarchand(id) {
        // Afficher un loader dans le modal
        $('#showMarchandModal').modal('show');

        // Charger les données
        $.ajax({
            url: `${apiUrl}/vente/marchand-back/${id}/show`,
            method: 'GET',
            success: function(response) {
                console.log(response)
                if (response.success) {
                    const marchandise = response.data.marchand;

                    console.log(marchandise)

                    // Remplir les informations de base
                    $('#marchandNumero').text(`Retour de marchandise N° ${marchandise.numero}`);
                    $('#marchandNum').text(marchandise.numero);
                    $('#marchandDate').text(marchandise.date);

                    // Lignes de livraison
                    let lignesHtml = '';
                    marchandise.lignes.forEach(ligne => {
                        lignesHtml += `
                            <tr>
                                <td>
                                    <div class="fw-medium">${ligne.article.designation}</div>
                                    <div class="small text-muted">${ligne.article.code_article}</div>
                                </td>
                                <td class="text-center">
                                    ${ligne.quantite_formatted} ${ligne.unite_vente.libelle_unite}
                                </td>
                                <td class="text-center">
                                    ${ligne.prix_formatted}
                                </td>
                            </tr>
                        `;
                    });
                    $('#marchandLignesLivraison').html(lignesHtml);
                }
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des détails'
                });
                $('#showMarchandModal').modal('hide');
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