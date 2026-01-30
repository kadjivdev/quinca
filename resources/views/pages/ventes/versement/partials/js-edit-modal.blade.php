<script>
    // Fonction pour modifier un acompte

    const formatDate = (date) => {
        // Exemple de la date de versement (assurez-vous qu'elle est au format "yyyy-mm-dd" ou dans un format compatible)
        var dateOp = new Date(date);

        // Formater la date au format 'yyyy-mm-dd'
        return dateOp.getFullYear() + '-' +
            ('0' + (dateOp.getMonth() + 1)).slice(-2) + '-' +
            ('0' + dateOp.getDate()).slice(-2);
    }

    function editAcompte(id) {
        $.ajax({
            url: `${apiUrl}/vente/versements/${id}`,
            type: 'GET',
            success: function(response) {
                console.log(`Les data recupérés: ${JSON.stringify(response.data)}`)

                if (response.success) {
                    const versement = response.data.versement;
                    console.log(`Le versement: ${JSON.stringify(versement)}`)
                    console.log(`Le versement banque: ${versement.banque}`)

                    // Mettre à jour l'URL du formulaire
                    $('#editAcompteForm').attr('action', `${apiUrl}/vente/versements/${id}`);

                    // Remplir les champs avec les bons IDs
                    $('#edit_id').val(versement.id);
                    $('#edit_reference_op').val(versement.reference_op);
                    $('#edit_date_op').val(formatDate(versement.date_op));
                    $('#edit_montant').val(versement.montant);
                    $('#edit_date_valeur').val(formatDate(versement.date_valeur));
                    $('#edit_comment').val(versement.comment);
                    $('#edit_banque').val(versement.banque);

                    const preuveHtml = versement.preuve ? `
                                <a href="${versement.preuve}" target="_blank" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Voir la preuve">
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

                    // Créer les options par défaut
                    let clientsOptions = `<option value="">Sélectionner un client</option>`;

                    // Ajouter les options pour chaque client
                    clients.forEach(client => {
                        if (client.id == versement.client?.id) {
                            clientsOptions += `<option value="${client.id}" selected>${client.raison_sociale}</option>`;
                        } else {
                            clientsOptions += `<option value="${client.id}">${client.raison_sociale}</option>`;
                        }
                    });

                    // Insérer les options dans le select
                    $('#edit_client_id').html(clientsOptions);

                    // show modal
                    $('#editAcompteModal').modal('show');
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
    $('#editAcompteForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = $(this).attr('action');

        $.ajax({
            url: url,
            type: 'POST', // 👈 important (see note below)
            data: formData,
            processData: false, // ✅ required for FormData
            contentType: false, // ✅ required for FormData
            headers: {
                'X-HTTP-Method-Override': 'PUT' // 👈 Laravel PUT support
            },
            success: function(response) {
                if (response.success) {
                    $('#editAcompteModal').modal('hide');
                    window.location.reload();

                    Toast.fire({
                        icon: 'success',
                        title: 'Versement modifié avec succès'
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


    // Réinitialisation du modal
    $('#editAcompteModal').on('hidden.bs.modal', function() {
        $('#editAcompteForm')[0].reset();
        $('#editAcompteForm').removeClass('was-validated');
        $('.select2-edit').val('').trigger('change');
    });

    // Initialisation au chargement de la page
    $(document).ready(function() {
        // Initialisation des tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Initialisation de Select2 pour le modal d'édition
        $('.select2-edit').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editAcompteModal'),
            placeholder: 'Sélectionner un client'
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