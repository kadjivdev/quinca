<script>
    function validateFacture(id, numero, statut) {
        // Vérifier si la facture est déjà validée
        if (statut === 'validee') {
            Swal.fire({
                title: 'Non autorisé',
                text: 'Cette facture est déjà validée.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Validation de facture',
            // text: `Êtes-vous sûr de vouloir valider la facture ${numero} ?`,
            text: `Êtes-vous sûr de vouloir valider la facture ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Validation en cours...',
                    text: 'Veuillez patienter',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `${apiUrl}/revendeurs/factures/${id}/validate`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Validée !',
                                text: 'La facture a été validée avec succès.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Erreur',
                                text: response.message || 'Une erreur est survenue',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Une erreur est survenue lors de la validation';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: 'Erreur',
                            text: message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }
</script>
