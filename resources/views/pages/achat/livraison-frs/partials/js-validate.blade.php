<script>
    function validateLivraisonFournisseur(id) {
        Swal.fire({
            title: 'Confirmation de validation',
            text: 'Êtes-vous sûr de vouloir valider ce bon de livraison ? Cette action va mettre à jour les stocks et ne pourra pas être annulée.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                        url: `${apiUrl}/achat/livraisons/${id}/validate`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    .then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Une erreur est survenue lors de la validation');
                        }
                        return response;
                    })
                    .catch(error => {
                        console.error('Erreur validation:', error);
                        Swal.showValidationMessage(
                            error.responseJSON?.message || error.message || 'Une erreur est survenue lors de la validation'
                        );
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                // Afficher le message de succès avec les détails
                Swal.fire({
                    title: 'Validé !',
                    text: 'Le bon de livraison a été validé avec succès',
                    icon: 'success',
                    html: generateValidationMessage(result.value)
                }).then(() => {
                    // Recharger la page
                    window.location.reload();
                });
            }
        });
    }

    // Fonction pour générer le message de validation
    function generateValidationMessage(response) {
        let message = 'Le bon de livraison a été validé avec succès.<br><br>';

        // Ajouter les détails des mouvements de stock
        if (response.details && response.details.mouvements) {
            message += '<strong>Détails des mouvements :</strong><br>';
            response.details.mouvements.forEach(mvt => {
                message += `- ${mvt.quantite_origine} ${mvt.unite_origine} `;
                if (mvt.quantite_origine !== mvt.quantite_base) {
                    message += `(converti en ${mvt.quantite_base} ${mvt.unite_base}) `;
                }
                message += `<br>`;
            });
        }

        return message;
    }

    // Initialisation au chargement du document (si nécessaire)
    $(document).ready(function() {
        // Si des boutons de validation sont présents avec des data-attributes
        $('[data-validate-livraison]').on('click', function() {
            const id = $(this).data('validate-livraison');
            validateLivraisonFournisseur(id);
        });
    });
</script>