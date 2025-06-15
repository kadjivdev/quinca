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
            $.ajax({
                url: `${apiUrl}/vente/livraisons/${id}/validate`,
                type: 'POST',
                beforeSend: function() {
                    // Désactiver le bouton et montrer le loading
                    const button = $(`button[onclick="validateLivraison(${id})"]`);
                    button.prop('disabled', true);
                    button.html('<i class="fas fa-spinner fa-spin"></i>');
                },
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        refreshList(); // Rafraîchir la liste
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
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
                },
                complete: function() {
                    // Réactiver le bouton
                    const button = $(`button[onclick="validateLivraison(${id})"]`);
                    button.prop('disabled', false);
                    button.html('<i class="fas fa-check"></i>');
                }
            });
        }
    });
}
</script>