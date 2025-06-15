<script>
    // Fonction de suppression du client
function deleteClient(clientId, raisonSociale) {
    Swal.fire({
        title: 'Confirmation de suppression',
        html: `Voulez-vous vraiment supprimer le client <br><strong>${raisonSociale}</strong> ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Supprimer',
        cancelButtonText: 'Annuler',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Appel AJAX pour la suppression
            $.ajax({
                url: `${apiUrl}/vente/clients/${clientId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Suppression en cours...',
                        html: 'Veuillez patienter',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès !',
                            text: response.message || 'Client supprimé avec succès',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: response.message || 'Impossible de supprimer ce client'
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Une erreur est survenue lors de la suppression';
                    if (xhr.status === 422) {
                        message = xhr.responseJSON.message || 'Impossible de supprimer ce client';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: message
                    });
                }
            });
        }
    });
}
</script>
