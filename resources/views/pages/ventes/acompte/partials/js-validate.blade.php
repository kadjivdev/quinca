<script>
    // Fonction pour valider un acompte
function validateAcompte(id) {
    Swal.fire({
        title: 'Confirmer la validation',
        text: 'Êtes-vous sûr de vouloir valider cet acompte ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, valider',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `${apiUrl}/vente/acomptes/${id}/validate`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Erreur lors de la validation'
                    });
                }
            });
        }
    });
}

// Fonction pour rejeter un acompte
function rejectAcompte(id) {
    Swal.fire({
        title: 'Motif du rejet',
        text: 'Veuillez indiquer le motif du rejet',
        input: 'text',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Rejeter',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#dc3545',
        inputValidator: (value) => {
            if (!value) {
                return 'Le motif du rejet est requis';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `${apiUrl}/vente/acomptes/${id}/reject`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    motif_rejet: result.value
                },
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Erreur lors du rejet'
                    });
                }
            });
        }
    });
}

// Fonction pour afficher le statut avec badge
function getStatusBadge(statut) {
    const badges = {
        en_attente: '<span class="badge bg-warning">En attente</span>',
        valide: '<span class="badge bg-success">Validé</span>',
        rejete: '<span class="badge bg-danger">Rejeté</span>'
    };
    return badges[statut] || '';
}
</script>
