<script>
    // Gestion du formulaire d'import des familles d'articles
$('#importFamilleForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');
    const modal = $('#importFamilleModal');

    // Vérifier la taille du fichier (5MB max)
    const fileInput = $(this).find('input[type="file"]');
    if (fileInput[0].files.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Attention',
            text: 'Veuillez sélectionner un fichier à importer',
            confirmButtonText: 'OK'
        });
        return;
    }

    const fileSize = fileInput[0].files[0].size;
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (fileSize > maxSize) {
        Swal.fire({
            icon: 'error',
            title: 'Fichier trop volumineux',
            text: 'La taille du fichier ne doit pas dépasser 5 MB',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Désactiver le bouton et changer son texte
    submitBtn.prop('disabled', true);
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Import en cours...');

    // Afficher le loader
    Swal.fire({
        title: 'Import en cours',
        html: 'Veuillez patienter pendant le traitement du fichier...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Envoi de la requête AJAX
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            // Fermer le modal Bootstrap
            modal.modal('hide');

            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Import réussi !',
                    text: response.message,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            } else {
                let errorText = response.message + '\n\n';
                if (response.errors && response.errors.length > 0) {
                    errorText += 'Erreurs détectées :\n';
                    response.errors.forEach(error => {
                        // Nettoyer les messages d'erreur SQL
                        let cleanError = error.replace(/SQLSTATE\[\w+\]:.*?1054/, '')
                                           .replace(/\(Connection:.*?\)/, '')
                                           .trim();
                        errorText += '• ' + cleanError + '\n';
                    });
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erreur lors de l\'import',
                    text: errorText,
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr) {
            modal.modal('hide');

            let errorMessage = 'Une erreur est survenue lors de l\'import';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
        },
        complete: function() {
            // Réactiver le bouton et restaurer son texte
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="fas fa-file-import me-2"></i>Importer');

            // Réinitialiser le formulaire
            $('#importFamilleForm')[0].reset();
        }
    });
});

// Réinitialisation du formulaire à la fermeture du modal
$('#importFamilleModal').on('hidden.bs.modal', function() {
    $('#importFamilleForm')[0].reset();
    const submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', false);
    submitBtn.html('<i class="fas fa-file-import me-2"></i>Importer');
});
</script>
