<script>
    $(document).ready(function() {
        
        // Fonctions utilitaires
        function displayErrors(errors) {
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');

            Object.keys(errors).forEach(field => {
                const input = $(`[name="${field}"]`);
                input.addClass('is-invalid');
                const errorDiv = $('<div>')
                    .addClass('invalid-feedback')
                    .text(errors[field][0]);
                input.after(errorDiv);
            });

            Toast.fire({
                icon: 'warning',
                title: 'Veuillez corriger les erreurs suivantes',
                text: Object.values(errors).flat().join('\n'),
                timer: 5000
            });
        }

        // Validation du formulaire
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Soumission du formulaire
        $('#addAcompteForm').on('submit', function(e) {
            e.preventDefault();
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

            const formData = new FormData(this);

            $.ajax({
                url: `${apiUrl}/vente/versements`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Toast.fire({
                            icon: 'success',
                            title: response.message || 'Versement enregistré avec succès',
                            timer: 2000
                        });

                        // Recharger la page après un court délai
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        console.log(`les erreurs : ${response.errors}`)
                        displayErrors(response.errors);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    if (xhr.status === 422) {
                        if (response.errors) {
                            displayErrors(response.errors);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Erreur de validation',
                                timer: 5000
                            });
                        }
                    } else {
                        // alert("erreur")
                        console.log(`Erreure ${JSON.stringify(response)}`)
                        Toast.fire({
                            icon: 'error',
                            title: response.message || 'Une erreur est survenue lors de l\'enregistrement de l\'acompte',
                            timer: 5000
                        });
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html('<i class="fas fa-save me-2"></i>Enregistrer');
                }
            });
        });

        // Réinitialisation du modal
        $('#addAcompteModal').on('show.bs.modal', function() {
            $('#addAcompteForm')[0].reset();
            $('#addAcompteForm').removeClass('was-validated');
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');
            $('.select2').val(null).trigger('change');
            $('input[name="date"]').val(new Date().toISOString().split('T')[0]);
        });
    });
</script>