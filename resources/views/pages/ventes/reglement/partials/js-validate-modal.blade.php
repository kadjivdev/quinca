<script>
$(document).ready(function() {
    // Gestionnaire pour le bouton de validation
    $(document).on('click', '.btn-validate-reglement', function(e) {
        e.preventDefault();
        const reglementId = $(this).data('reglement-id');
        const row = $(this).closest('tr');

        // Confirmation avant validation
        Swal.fire({
            title: 'Confirmer la validation',
            text: "Voulez-vous vraiment valider ce règlement ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                validateReglement(reglementId, row);
            }
        });
    });

    // Fonction pour valider le règlement
    function validateReglement(reglementId, row) {
        $.ajax({
            url: `${apiUrl}/vente/reglement/${reglementId}/validate`,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                // Désactiver le bouton et montrer le chargement
                row.find('.btn-validate-reglement')
                   .prop('disabled', true)
                   .html('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if (response.success) {
                    // Notification de succès
                    Toast.fire({
                        icon: 'success',
                        title: 'Règlement validé avec succès'
                    });

                    // Rafraîchir la liste des règlements
                    refreshList();
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Erreur lors de la validation'
                    });
                }
            },
            error: function(xhr) {
                // Gérer les différents types d'erreurs
                let errorMessage = 'Une erreur est survenue';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Toast.fire({
                    icon: 'error',
                    title: errorMessage
                });

                // Réactiver le bouton
                row.find('.btn-validate-reglement')
                   .prop('disabled', false)
                   .html('<i class="fas fa-check"></i> Valider');
            }
        });
    }
});
</script>
