<script>
    // js-delete.blade.php

// Configuration globale pour SweetAlert2
const DeleteLivraisonFournisseur = {
    swalConfig: {
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    },

    // Toast pour les notifications
    toast: Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    }),

    /**
     * Fonction pour supprimer un bon de livraison
     * @param {number} id - L'ID du bon de livraison à supprimer
     */
    delete: function(id) {
        Swal.fire({
            ...this.swalConfig,
            text: "Voulez-vous vraiment supprimer ce bon de livraison ?"
        }).then((result) => {
            if (result.isConfirmed) {
                // Envoi de la requête AJAX de suppression
                $.ajax({
                    url: `${apiUrl}/achat/livraisons/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (response) => {
                        if (response.success) {
                            // Notification de succès
                            this.toast.fire({
                                icon: 'success',
                                title: response.message || 'Bon de livraison supprimé avec succès'
                            });

                            // Rafraîchir la page après suppression
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: response.message || 'Une erreur est survenue lors de la suppression',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: (xhr) => {
                        console.error('Erreur lors de la suppression:', xhr);

                        let message = 'Une erreur est survenue lors de la suppression';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: message,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    },

    /**
     * Suppression en lot de bons de livraison
     * @param {Array} ids - Tableau des IDs à supprimer
     */
    bulkDelete: function(ids) {
        if (!ids || ids.length === 0) {
            this.toast.fire({
                icon: 'warning',
                title: 'Veuillez sélectionner au moins un bon de livraison'
            });
            return;
        }

        Swal.fire({
            ...this.swalConfig,
            text: `Voulez-vous vraiment supprimer ces ${ids.length} bons de livraison ?`
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '${apiUrl}/achat/livraisons/bulk-delete',
                    type: 'POST',
                    data: { ids },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (response) => {
                        if (response.success) {
                            this.toast.fire({
                                icon: 'success',
                                title: response.message || 'Bons de livraison supprimés avec succès'
                            });

                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: response.message || 'Une erreur est survenue lors de la suppression',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: (xhr) => {
                        console.error('Erreur lors de la suppression en lot:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: 'Une erreur est survenue lors de la suppression en lot',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }
};

// Initialisation des gestionnaires d'événements
$(document).ready(function() {
    // Gestionnaire pour le bouton de suppression individuelle
    $('.btn-delete-livraison').on('click', function() {
        const id = $(this).data('id');
        DeleteLivraisonFournisseur.delete(id);
    });

    // Gestionnaire pour le bouton de suppression en lot
    $('#btnBulkDelete').on('click', function() {
        const selectedIds = $('.livraison-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        DeleteLivraisonFournisseur.bulkDelete(selectedIds);
    });
});
</script>
