<script>
    // Gestion du formulaire d'import
$('#importClientForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = $(this).find('button[type="submit"]');

    // Vérifier si un fichier a été sélectionné
    const fileInput = $(this).find('input[type="file"]');
    if (fileInput[0].files.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Attention',
            text: 'Veuillez sélectionner un fichier à importer'
        });
        return;
    }

    // Vérifier l'extension du fichier
    const fileName = fileInput[0].files[0].name;
    const fileExt = fileName.split('.').pop().toLowerCase();
    if (!['xlsx', 'xls'].includes(fileExt)) {
        Swal.fire({
            icon: 'error',
            title: 'Format invalide',
            text: 'Seuls les fichiers Excel (.xlsx, .xls) sont acceptés'
        });
        return;
    }

    Swal.fire({
        title: 'Import en cours...',
        html: 'Veuillez patienter pendant le traitement du fichier',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();

            // Désactiver le bouton
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Import en cours...');

            // Envoyer le fichier
            $.ajax({
                url: `${apiUrl}/vente/clients/import`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import réussi !',
                            html: response.message,
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        let errorList = '';
                        if (response.errors && response.errors.length > 0) {
                            errorList = '<ul class="text-start mb-0 ps-3">';
                            response.errors.forEach(error => {
                                errorList += `<li>${error}</li>`;
                            });
                            errorList += '</ul>';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur lors de l\'import',
                            html: errorList || response.message,
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Une erreur est survenue lors de l\'import';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: message
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html('<i class="fas fa-file-import me-2"></i>Importer');
                }
            });
        }
    });
});
</script>
