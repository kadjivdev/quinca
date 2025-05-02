<script>
   function validateLivraison(id) {
    Swal.fire({
        title: 'Valider la livraison ?',
        text: "Cette action générera les mouvements de stock et ne pourra pas être annulée",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, valider',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Stocker une référence au bouton avant l'appel AJAX
            const button = $(`button[onclick="validateLivraison(${id})"]`);
            button.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: `${apiUrl}/vente/livraisons/${id}/validate`,
                type: 'POST',
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        // Rafraîchir la liste sans attendre le complete
                        refreshList();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
                        // En cas d'erreur, restaurer le bouton
                        button.prop('disabled', false)
                              .html('<i class="fas fa-check"></i>');
                    }
                },
                error: function(xhr) {
                    let message = 'Erreur lors de la validation';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Toast.fire({
                        icon: 'error',
                        title: message
                    });
                    // En cas d'erreur, restaurer le bouton
                    button.prop('disabled', false)
                          .html('<i class="fas fa-check"></i>');
                }
            });
        }
    });
}

// Modifier la fonction refreshList pour gérer le rafraîchissement complet
function refreshList() {
    $.ajax({
        url: window.location.href,
        type: 'GET',
        success: function(response) {
            // Mettre à jour le contenu de la table
            if (typeof response === 'string') {
                $('#livraisonsTable').html($(response).find('#livraisonsTable').html());
            } else if (response.html) {
                $('#livraisonsTable').html(response.html);
            }

            // Réinitialiser les tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        },
        error: function() {
            Toast.fire({
                icon: 'error',
                title: 'Erreur lors du rafraîchissement de la liste'
            });
        }
    });
}
</script>
