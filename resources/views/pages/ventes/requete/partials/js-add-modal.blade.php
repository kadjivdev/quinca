<script>
    $(document).ready(function() {
        $(".select2").select2({
            dropdownParent: $('#addRequeteModal .modal-content'),
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner un client',
            width: '100%',
        })

        function refreshList() {
            location.reload();
        }

        $("#motif").on("change", function(e) {
            if (e.target.value=='Articles') {
                $("#art_div").removeClass('d-none')
            }else{
                $("#art_div").addClass('d-none')
            }
        })

        // Soumission du formulaire
        $('#addRequeteForm').on('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            const submitBtn = $('#btnSaveRequete');
            submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');
            $.ajax({
                url: `${apiUrl}/vente/requetes`,
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#addRequeteModal').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        // Réinitialiser le formulaire
                        resetForm();
                        // Rafraîchir la liste
                        location.reload()
                        // refreshList();
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