<script>
    // public/js/roles.js

$(document).ready(function() {
    // DataTable Initialization
    // const rolesTable = $('#rolesTable').DataTable({
    //     language: {
    //         url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
    //     },
    //     order: [[0, 'desc']]
    // });

    // Add Role Form Submission

// Add Role Form Submission
$('#addRoleForm').on('submit', function(e) {
        e.preventDefault(); // Empêcher la soumission par défaut du formulaire

        // Vérifier si au moins une permission est sélectionnée
        if ($('.permission-checkbox:checked').length === 0) {
            Toast.fire({
                icon: 'error',
                title: 'Veuillez sélectionner au moins une permission'
            });
            return false;
        }

        const formData = $(this).serialize();

        $.ajax({
            url: `${apiUrl}/roles`,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                // Désactiver le bouton pendant la soumission
                $('#submitAddRole').prop('disabled', true);
            },
            success: function(response) {
                if(response.success) {
                    $('#addRoleModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    }).then(() => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Une erreur est survenue';
                if(xhr.status === 422) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                Toast.fire({
                    icon: 'error',
                    title: errorMessage
                });
            },
            complete: function() {
                // Réactiver le bouton après la soumission
                $('#submitAddRole').prop('disabled', false);
            }
        });
    });

    // Edit Role
    $(document).on('click', '.edit-role', function() {
        const roleId = $(this).data('id');

        $.ajax({
            url: `${apiUrl}/roles/${roleId}/edit`,
            method: 'GET',
            success: function(data) {
                $('#edit_role_id').val(data.role.id);
                $('#edit_name').val(data.role.name);

                // Reset checkboxes
                $('.permission-checkbox').prop('checked', false);

                // Check permissions
                data.rolePermissions.forEach(permission => {
                    $(`input[name="permissions[]"][value="${permission}"]`).prop('checked', true);
                });

                $('#editRoleModal').modal('show');
            },
            error: function() {
                Swal.fire('Erreur', 'Impossible de charger les données du rôle', 'error');
            }
        });
    });

    // Edit Role Form Submission
    $(document).on('submit', '#editRoleForm', function(e) {
        e.preventDefault();
        const roleId = $('#edit_role_id').val();

        $.ajax({
            url: `${apiUrl}/roles/${roleId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#editRoleModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Une erreur est survenue';
                if(xhr.status === 422) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                Swal.fire('Erreur', errorMessage, 'error');
            }
        });
    });

    // Delete Role
    $(document).on('click', '.delete-role', function(e) {
        e.preventDefault();
        const roleId = $(this).data('id');
        const roleName = $(this).data('name');

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Voulez-vous vraiment supprimer le rôle "${roleName}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/roles/${roleId}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Succès',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Erreur', xhr.responseJSON.message || 'Une erreur est survenue', 'error');
                    }
                });
            }
        });
    });

    // Reset forms when modal is closed
    $('#addRoleModal, #editRoleModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').html('');
    });
});
</script>
