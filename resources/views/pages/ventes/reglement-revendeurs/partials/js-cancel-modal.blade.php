<script>
    // Ajouter ce code JavaScript

// Fonction pour annuler un règlement
window.cancelReglement = function(reglementId) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action annulera le règlement. Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, annuler',
        cancelButtonText: 'Non, retour'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `${apiUrl}/vente/reglement/${reglementId}/cancel`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        // Rafraîchir la liste
                        refreshList();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Une erreur est survenue';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Toast.fire({
                        icon: 'error',
                        title: message
                    });
                }
            });
        }
    });
};

// Ajouter l'événement sur le bouton d'annulation
$(document).on('click', '.btn-cancel-reglement', function() {
    const reglementId = $(this).data('reglement-id');
    cancelReglement(reglementId);
});
</script>
