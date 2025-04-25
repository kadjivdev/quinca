<script>
function deleteFacture(id) {
    Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer!',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Récupérer le token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // Envoyer la requête de suppression
            fetch(`${apiUrl}/vente/factures/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire(
                        'Supprimé!',
                        data.message,
                        'success'
                    ).then(() => {
                        // Recharger la page ou mettre à jour la liste
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Erreur!',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Erreur!',
                    'Une erreur est survenue lors de la suppression',
                    'error'
                );
                console.error('Erreur:', error);
            });
        }
    });
}
</script>
