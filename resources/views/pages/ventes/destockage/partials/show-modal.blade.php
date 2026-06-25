{{-- Modal de détails client --}}
<div class="modal fade" id="showDestockageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-user fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="destockageClient">...</h5>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    {{-- Informations principales --}}
                    <div class="col-md-12">
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">
                                    <i class="fas fa-info-circle me-2"></i>Informations principales
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">Reference</label>
                                        <div class="fw-medium" id="destockageReference">...</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small">Date création</label>
                                        <div id="destockageDate" class="fw-medium">...</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div>
                                            <h5 class="modal-title fw-bold mb-0">Code </h5>
                                            <p class="text-muted small mb-0" id="destockageCode">...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  -->
                    {{-- Section articles --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-box me-2"></i>Articles
                                </h6>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="exampleModal" class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Article</th>
                                                <th>Unité</th>
                                                <th>Quantité</th>
                                                <th>Prix unitaire</th>
                                                <th>Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody id="showDestockageLignesContainer">
                                            <!-- Les lignes seront ajoutées ici -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section observations --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-comment-alt me-2"></i>Observations
                                </h6>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control"
                                    id="observation"
                                    name="observation" rows="3" 
                                    placeholder="Observations éventuelles"></textarea>
                            </div>
                        </div>
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
    function showClient(id) {
        // Afficher le loader
        Swal.fire({
            title: 'Chargement...',
            text: 'Récupération des données du client',
            imageUrl: '/assets/img/loader.gif', // Assurez-vous d'avoir une image de loader
            showConfirmButton: false,
            allowOutsideClick: false
        });

        // Charger les données du client
        $.ajax({
            url: `${apiUrl}/vente/clients/${id}`,
            method: 'GET',
            success: function(response) {
                // Fermer le loader
                Swal.close();

                if (response.success) {
                    // Afficher le modal
                    $('#showClientModal').modal('show');

                    const client = response.data.client;
                    const stats = response.data.statistiques;
                    const dernieresFactures = response.data.dernieres_factures;
                    const derniersReglements = response.data.derniers_reglements;

                    // Informations principales
                    $('#clientNom').text(client.raison_sociale);
                    $('#clientCode').text(`Code client: ${client.code_client}`);

                    // Catégorie avec badge
                    let categorieHtml = '';
                    switch (client.categorie) {
                        case 'particulier':
                            categorieHtml = '<span class="badge bg-info bg-opacity-10 text-info"><i class="fas fa-user me-1"></i>Particulier</span>';
                            break;
                        case 'professionnel':
                            categorieHtml = '<span class="badge bg-primary bg-opacity-10 text-primary"><i class="fas fa-briefcase me-1"></i>Professionnel</span>';
                            break;
                        case 'societe':
                            categorieHtml = '<span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-building me-1"></i>Société</span>';
                            break;
                    }
                    $('#clientCategorie').html(categorieHtml);

                    // Date de création
                    $('#clientDateCreation').text(client.created_at);

                    // IFU et RCCM
                    $('#clientIFU').text(client.ifu || '—');
                    $('#clientRCCM').text(client.rccm || '—');
                    $('#clientAIB').text(client.taux_aib || '—');

                    // Contact et adresse
                    $('#clientTelephone').html(`<i class="fas fa-phone me-1"></i>${client.telephone || '—'}`);
                    $('#clientEmail').html(`<i class="fas fa-envelope me-1"></i>${client.email || '—'}`);
                    $('#clientAdresse').html(`<i class="fas fa-map-marker-alt me-1"></i>${client.adresse || '—'}`);
                    $('#clientVille').html(`<i class="fas fa-city me-1"></i>${client.ville || '—'}`);

                    // Crédit et solde
                    $('#clientPlafondCredit').text(
                        client.credit.plafond ?
                        new Intl.NumberFormat('fr-FR').format(client.credit.plafond) + ' FCFA' :
                        '—'
                    );
                    $('#clientDelaiPaiement').text(
                        client.credit.delai_paiement ?
                        `${client.credit.delai_paiement} jours` :
                        '—'
                    );
                    $('#clientSoldeCourant').text(
                        new Intl.NumberFormat('fr-FR').format(client.credit.solde_courant) + ' FCFA'
                    ).toggleClass('text-danger', client.credit.solde_courant > client.credit.plafond);

                    // Dépassement
                    if (client.credit.depassement > 0) {
                        $('#clientDepassementBlock').removeClass('d-none');
                        $('#clientDepassement').text(
                            new Intl.NumberFormat('fr-FR').format(client.credit.depassement) + ' FCFA'
                        );
                    } else {
                        $('#clientDepassementBlock').addClass('d-none');
                    }

                    // Notes
                    $('#clientNotes').text(client.notes || 'Aucune note');

                    // Statistiques
                    if ($('#clientStats').length) {
                        $('#totalFactures').text(stats.total_factures);
                        $('#facturesImpayees').text(stats.factures_impayees);
                        $('#chiffreAffaires').text(new Intl.NumberFormat('fr-FR').format(stats.chiffre_affaires) + ' FCFA');
                        $('#totalReglements').text(new Intl.NumberFormat('fr-FR').format(stats.total_reglements) + ' FCFA');
                    }

                    // Dernières transactions
                    if (dernieresFactures.length > 0) {
                        let facturesHtml = '';
                        dernieresFactures.forEach(facture => {
                            facturesHtml += `
                            <tr>
                                <td>${facture.numero}</td>
                                <td>${facture.date}</td>
                                <td class="text-end">${new Intl.NumberFormat('fr-FR').format(facture.montant)} FCFA</td>
                                <td class="text-center">
                                    <span class="badge bg-${facture.statut_paiement === 'paye' ? 'success' : 'warning'} bg-opacity-10 text-${facture.statut_paiement === 'paye' ? 'success' : 'warning'}">
                                        ${facture.statut_paiement}
                                    </span>
                                </td>
                            </tr>
                        `;
                        });
                        $('#dernieresFactures').html(facturesHtml);
                    }

                    if (derniersReglements.length > 0) {
                        let reglementsHtml = '';
                        derniersReglements.forEach(reglement => {
                            reglementsHtml += `
                            <tr>
                                <td>${reglement.numero}</td>
                                <td>${reglement.date}</td>
                                <td>${reglement.mode_reglement}</td>
                                <td class="text-end">${new Intl.NumberFormat('fr-FR').format(reglement.montant)} FCFA</td>
                            </tr>
                        `;
                        });
                        $('#derniersReglements').html(reglementsHtml);
                    }
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