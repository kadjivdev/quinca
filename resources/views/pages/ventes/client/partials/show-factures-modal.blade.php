{{-- Modal de détails client --}}
<div class="modal fade" id="showClientFacturesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-user fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Les factures(ventes) de :<strong id="factureClientNom">...</strong></h5>
                        <p class="text-muted small mb-0" id="factureClientCode">...</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Numéro</th>
                                    <th>Date</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody id="factures">
                                <!-- Les factures seront injectées ici -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showFactures(id) {
        // Afficher le loader
        Swal.fire({
            title: 'Chargement...',
            text: 'Récupération des données du client',
            // imageUrl: '/assets/img/loader.gif', // Assurez-vous d'avoir une image de loader
            showConfirmButton: false,
            allowOutsideClick: false
        });

        // Charger les données du client
        $.ajax({
            url: `${apiUrl}/vente/clients/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const client = response.data.client;
                    const stats = response.data.statistiques;

                    // Informations principales
                    $('#factureClientNom').text(client.raison_sociale);
                    $('#factureClientCode').text(`Code client: ${client.code_client}`);

                    // Dernières transactions
                    if (client.factures.length > 0) {
                        let facturesHtml = '';
                        client.factures.forEach(facture => {
                            facturesHtml += `
                            <tr>
                                <td>${facture.numero}</td>
                                <td>${facture.date_facture}</td>
                                <td class="text-end">${facture.montant_ttc} FCFA</td>
                                <td class="text-center">
                                    <span class="badge bg-${facture.statut === 'validee' ? 'success' : 'warning'} bg-opacity-10 text-${facture.statut_paiement === 'paye' ? 'success' : 'warning'}">
                                        ${facture.statut}
                                    </span>
                                </td>
                            </tr>
                        `;
                        });
                        $('#factures').html(facturesHtml);
                    }else{
                        $('#factures').html(
                            `
                                <tr>
                                    <td colspan='4'>Aucune facture trouvée</td>
                                </tr>
                            `
                        );
                    }

                    // Fermer le loader
                    Swal.close();
                    // Afficher le modal
                    $('#showClientFacturesModal').modal('show');
                }
            },
            error: function(xhr, status, error) {
                // Fermer le loader et afficher l'erreur
                Swal.close();

                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des détails du client'
                });
            }
        });
    }
</script>