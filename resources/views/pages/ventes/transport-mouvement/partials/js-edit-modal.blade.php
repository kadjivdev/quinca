<script>
    // Fonction pour modifier un acompte

    const baseUrl = "{{env('APP_URL')}}"

    const formatDate = (date) => {
        // Exemple de la date de versement (assurez-vous qu'elle est au format "yyyy-mm-dd" ou dans un format compatible)
        var dateOp = new Date(date);

        // Formater la date au format 'yyyy-mm-dd'
        return dateOp.getFullYear() + '-' +
            ('0' + (dateOp.getMonth() + 1)).slice(-2) + '-' +
            ('0' + dateOp.getDate()).slice(-2);
    }

    function editMouvement(id) {
        $.ajax({
            url: `${apiUrl}/vente/transport-mouvements/${id}`,
            type: 'GET',
            success: function(response) {
                console.log(`Les data recupérés: ${JSON.stringify(response.data)}`)

                if (response.success) {
                    const mouvement = response.data.transport_mouvement;
                    console.log(`Le mouvement: ${JSON.stringify(mouvement)}`)

                    // Mettre à jour l'URL du formulaire
                    $('#editTransportMouvementForm').attr('action', `${baseUrl}/vente/transport-mouvements/${id}`);

                    // 'transportation_id',
                    // 'client_id',
                    // 'date',
                    // 'montant',
                    // 'comment',
                    // 'preuve',

                    // Remplir les champs avec les bons IDs
                    $('#edit_id').val(mouvement.id);
                    $('#edit_date').val(formatDate(mouvement.date));
                    $('#edit_montant').val(mouvement.montant);
                    $('#edit_comment').val(mouvement.comment);

                    const preuveHtml = mouvement.preuve ? `
                                <a href="${mouvement.preuve}" target="_blank" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Voir la preuve">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                                <span>Une preuve existe déjà</span>
                    ` : null;

                    $('#preuveFile').html(preuveHtml);
                    // Afficher le modal

                    // Handle clients
                    $('#edit_client_id').empty();

                    // Assurez-vous que clients est bien un tableau d'objets
                    let clients = @json($clients);
                    let transportations = @json($transportations);

                    // Créer les options par défaut
                    let clientsOptions = `<option value="">Sélectionner un client</option>`;

                    // Ajouter les options pour chaque client
                    clients.forEach(client => {
                        if (client.id == mouvement.client?.id) {
                            clientsOptions += `<option value="${client.id}" selected>${client.raison_sociale}</option>`;
                        } else {
                            clientsOptions += `<option value="${client.id}">${client.raison_sociale}</option>`;
                        }
                    });

                    // Créer les options par défaut
                    let transportationsOptions = `<option value="">Sélectionner un moyen de transport</option>`;

                    // Ajouter les options pour chaque transporation
                    transportations.forEach(transportation => {
                        if (transportation.id == mouvement.transportation?.id) {
                            transportationsOptions += `<option value="${transportation.id}" selected>${transportation.matricule}</option>`;
                        } else {
                            transportationsOptions += `<option value="${transportation.id}">${transportation.matricule}</option>`;
                        }
                    });

                    // vider les selects
                    $('#edit_client_id').empty();
                    $('#edit_transportation_id').empty();

                    // Insérer les options dans le select
                    $('#edit_client_id').html(clientsOptions);
                    $('#edit_transportation_id').html(transportationsOptions);

                    // show modal
                    $('#editTransportMouvementModal').modal('show');
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du chargement des données'
                    });
                }
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des données'
                });
            }
        });
    }

    // Gestion de la soumission du formulaire
    $('#editTransportMouvementForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const formUrl = $(this).attr('action');

        console.log("formUrl :", formUrl)

        $.ajax({
            url: formUrl,
            type: 'POST', // 👈 important (see note below)
            data: formData,
            processData: false, // ✅ required for FormData
            contentType: false, // ✅ required for FormData
            headers: {
                'X-HTTP-Method-Override': 'PATCH' // 👈 Laravel PUT support
            },
            success: function(response) {
                if (response.success) {
                    $('#editTransportMouvementModal').modal('hide');
                    window.location.reload();

                    Toast.fire({
                        icon: 'success',
                        title: 'Mouvement modifié avec succès'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Erreur lors de la modification';
                if (xhr.responseJSON?.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                Toast.fire({
                    icon: 'error',
                    title: errorMessage
                });
            }
        });
    });

    // Fonction pour mettre à jour les statistiques
    function updateStats(stats) {
        if (stats) {
            $('.stat-total').text(formatNumber(stats.total));
            $('.stat-montant').text(formatMontant(stats.montant_total));
            $('.stat-mois').text(formatNumber(stats.acomptes_mois));
            $('.stat-montant-mois').text(formatMontant(stats.montant_mois));
        }
    }

    // Fonction pour formater les nombres
    function formatNumber(number) {
        return new Intl.NumberFormat('fr-FR').format(number);
    }

    // Fonction pour formater les montants
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(montant) + ' FCFA';
    }
</script>