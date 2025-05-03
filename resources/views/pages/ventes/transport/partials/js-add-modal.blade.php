<script>
    $(document).ready(function() {
        $(".select2").select2({
            dropdownParent: $('#addTransportModal .modal-content'),
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner un client',
            width: '100%',
        })


        // Soumission du formulaire
        $('#addTransportForm').on('submit', function(e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            const submitBtn = $('#btnSaveTransport');
            submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

            $.ajax({
                url: `${apiUrl}/vente/transports`,
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {

                        $('#addTransportModal').modal('hide');
                        
                        window.location.href = "/vente/transports"

                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        // Réinitialiser le formulaire
                        // resetForm();
                        // Rafraîchir la liste
                        // window.reload()

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
                },
                complete: function() {
                    submitBtn.prop('disabled', false)
                        .html('<i class="fas fa-save me-2"></i>Enregistrer');
                }
            });
        });
    });
</script>