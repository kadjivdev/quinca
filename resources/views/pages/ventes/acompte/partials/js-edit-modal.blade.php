<script>
    // Fonction pour modifier un acompte

    function refreshList() {
    const filters = {
        client_id: $('#clientFilter').val(),
        type_paiement: $('#typePaiementFilter').val(),
        date_debut: $('#dateDebut').val(),
        date_fin: $('#dateFin').val(),
        search: $('#searchFilter').val()
    };

    $.ajax({
        url: `${apiUrl}/vente/acomptes/refresh-list`,
        method: 'GET',
        data: filters,
        success: function(response) {
            if (response.html) {
                $('#acomptesTable tbody').html($(response.html).find('#acomptesTable tbody').html());
                if ($('.card-footer').length) {
                    $('.card-footer').html($(response.html).find('.card-footer').html());
                }
            }
            if (response.stats) {
                updateStats(response.stats);
            }
            // Réinitialiser les tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            window.location.reload();
        },
        error: function(xhr) {
            Toast.fire({
                icon: 'error',
                title: 'Erreur lors du rafraîchissement de la liste'
            });
        }
    });
}

    function editAcompte(id) {
    $.ajax({
        url: `${apiUrl}/vente/acomptes/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const acompte = response.data.acompte;

                // Mettre à jour l'URL du formulaire
                $('#editAcompteForm').attr('action', `${apiUrl}/vente/acomptes/${id}`);

                // Remplir les champs avec les bons IDs
                $('#edit_id').val(acompte.id);
                $('#edit_date').val(acompte.date);
                $('#edit_client_id').val(acompte.client.id).trigger('change');  // Correction ici
                $('#edit_type_paiement').val(acompte.type_paiement);
                $('#edit_montant').val(acompte.montant);
                $('#edit_observation').val(acompte.observation);

                // Afficher le modal
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
   // Gestion de la soumission du formulaire
$('#editAcompteForm').on('submit', function(e) {
    e.preventDefault();

    // Récupérer les données du formulaire
    const formData = new FormData(this);
    const url = $(this).attr('action');

    // Convertir FormData en objet JSON
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    $.ajax({
        url: url,
        type: 'PUT',
        // Envoyer les données en JSON
        data: data,
        // Supprimer ces deux lignes car on n'utilise plus FormData
        // processData: false,
        // contentType: false,
        success: function(response) {
    if (response.success) {
        $('#editAcompteModal').modal('hide');

        // Rafraîchir la liste
        window.location.reload();

        Toast.fire({
            icon: 'success',
            title: 'Acompte modifié avec succès'
        });

        // Attendre que le modal soit complètement fermé avant de réinitialiser
        $('#editAcompteModal').on('hidden.bs.modal', function () {
            // Réinitialiser le formulaire
            $('#editAcompteForm')[0].reset();
            $('#editAcompteForm').removeClass('was-validated');
            $('.select2-edit').val('').trigger('change');

            // Supprimer l'event handler pour éviter la duplication
            $(this).off('hidden.bs.modal');
        });
    }
},
        error: function(xhr) {
            let errorMessage = 'Erreur lors de la modification';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
                // Si vous voulez afficher toutes les erreurs de validation
                if (xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).join('\n');
                }
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

    // Formatage des montants pour les champs de type nombre
    // $('input[type="number"]').on('input', function() {
    //     if (this.value.length > 0) {
    //         let number = parseFloat(this.value);
    //         if (!isNaN(number)) {
    //             this.value = new Intl.NumberFormat('fr-FR', {
    //                 minimumFractionDigits: 3,
    //                 maximumFractionDigits: 3,
    //                 useGrouping: false
    //             }).format(number).replace(',', '.');
    //         }
    //     }
    // });

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

        // Formater les montants existants au chargement
        // $('input[type="number"]').each(function() {
        //     if (this.value.length > 0) {
        //         let number = parseFloat(this.value);
        //         if (!isNaN(number)) {
        //             this.value = new Intl.NumberFormat('fr-FR', {
        //                 minimumFractionDigits: 3,
        //                 maximumFractionDigits: 3,
        //                 useGrouping: false
        //             }).format(number).replace(',', '.');
        //         }
        //     }
        // });
    });

    // Fonction pour rafraîchir la liste
    function refreshList() {
        const filters = {
            client_id: $('#clientFilter').val(),
            type_paiement: $('#typePaiementFilter').val(),
            date_debut: $('#dateDebut').val(),
            date_fin: $('#dateFin').val(),
            search: $('#searchFilter').val()
        };

        $.ajax({
            url: `${apiUrl}/vente/acomptes/refresh-list`,
            method: 'GET',
            data: filters,
            success: function(response) {
                if (response.html) {
                    $('#acomptesTable tbody').html($(response.html).find('#acomptesTable tbody').html());
                    if ($('.card-footer').length) {
                        $('.card-footer').html($(response.html).find('.card-footer').html());
                    }
                }
                if (response.stats) {
                    updateStats(response.stats);
                }
                // Réinitialiser les tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
                window.location.reload();
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du rafraîchissement de la liste'
                });
            }
        });
    }

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
