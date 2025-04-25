<script>
    function deleteLivraison(id) {
        Swal.fire({
            title: 'Supprimer la livraison ?',
            text: "Cette action ne peut pas être annulée",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                // Stocker une référence au bouton
                const button = $(`button[onclick="deleteLivraison(${id})"]`);
                
                // Désactiver le bouton et afficher le loader
                button.prop('disabled', true)
                      .html('<i class="fas fa-spinner fa-spin"></i>');
    
                $.ajax({
                    url: `${apiUrl}/vente/livraisons/${id}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            refreshList();
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message
                            });
                            // Restaurer le bouton en cas d'erreur
                            button.prop('disabled', false)
                                  .html('<i class="fas fa-trash"></i>');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Erreur lors de la suppression';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Toast.fire({
                            icon: 'error',
                            title: message
                        });
                        // Restaurer le bouton en cas d'erreur
                        button.prop('disabled', false)
                              .html('<i class="fas fa-trash"></i>');
                    }
                });
            }
        });
    }
    </script>