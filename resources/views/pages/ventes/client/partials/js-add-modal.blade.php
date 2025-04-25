<script>
    $(document).ready(function() {
        // Fonctions utilitaires
        function getFilterValues() {
            return {
                categorie: $('#categorieFilter').val(),
                ville: $('#villeFilter').val(),
                statut: $('#statutFilter').val(),
                credit: $('#creditFilter').val(),
                search: $('#searchFilter').val()
            };
        }

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

        function updateStats(stats) {
            if (stats.total !== undefined) $('#totalClients').text(stats.total);
            if (stats.actifs !== undefined) $('#clientsActifs').text(stats.actifs);
            if (stats.professionnels !== undefined) $('#clientsProfessionnels').text(stats.professionnels);
            if (stats.avec_credit !== undefined) $('#clientsAvecCredit').text(stats.avec_credit);
        }

        // Fonction de rafraîchissement de la liste
        function refreshList() {
            const filters = getFilterValues();

            $.ajax({
                url: `${apiUrl}/vente/clients/refresh-list`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: filters,
                beforeSend: function() {
                    $('#clientsContainer').addClass('opacity-50');
                },
                success: function(response) {
                    if (response.html) {
                        $('#clientsContainer').html(response.html);
                        $('[data-bs-toggle="tooltip"]').tooltip();

                        if (response.stats) {
                            updateStats(response.stats);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Erreur lors du rafraîchissement:', xhr);
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du rafraîchissement de la liste',
                        timer: 3000
                    });
                },
                complete: function() {
                    $('#clientsContainer').removeClass('opacity-50');
                }
            });
        }

        // Gestion des filtres
        let searchTimeout;
        function filterClients() {
            if (searchTimeout) clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => refreshList(), 300);
        }

        // Event Listeners pour les filtres
        $('#categorieFilter, #villeFilter, #statutFilter, #creditFilter').on('change', refreshList);
        $('#searchFilter').on('keyup', filterClients);

        // Gestion de la pagination
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');

            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    $('#clientsContainer').html(response.html);
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        });

        // Gestion du formulaire d'ajout
        // Gestion du switch statut
        $('#statutSwitch').on('change', function() {
            $(this).val(this.checked ? '1' : '0');
        });

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
        $('#addClientForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

        const formData = new FormData(this);
        formData.set('statut', $('#statutSwitch').is(':checked') ? '1' : '0');

        $.ajax({
            url: `${apiUrl}/vente/clients`,
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
                        title: response.message || 'Client créé avec succès',
                        timer: 2000
                    });

                    // Recharger la page après un court délai
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    displayErrors(response.errors);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const response = xhr.responseJSON;
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
                    Toast.fire({
                        icon: 'error',
                        title: 'Une erreur est survenue lors de la création du client',
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
        $('#addClientModal').on('show.bs.modal', function() {
            $('#addClientForm')[0].reset();
            $('#addClientForm').removeClass('was-validated');
            $('.invalid-feedback').remove();
            $('.is-invalid').removeClass('is-invalid');
            $('#statutSwitch').prop('checked', true).val('1');
        });

        // Initialisation des tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Rendre refreshList accessible globalement
        window.refreshList = refreshList;
    });
    </script>
