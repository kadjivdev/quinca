<script>
    $(document).ready(function() {
    // Add User Form Submission
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();

        // Vérifier si un rôle est sélectionné
        if (!$('input[name="roles"]:checked').length) {
            Toast.fire({
                icon: 'error',
                title: 'Veuillez sélectionner un rôle'
            });
            return false;
        }

        const formData = $(this).serialize();

        $.ajax({
            url: `${apiUrl}/users`,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#submitAddUser').prop('disabled', true);
            },
            success: function(response) {
                if(response.success) {
                    $('#addUserModal').modal('hide');
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
                $('#submitAddUser').prop('disabled', false);
            }
        });
    });

    // Email availability check
    let emailTimeout;
    $('#email').on('input', function() {
        clearTimeout(emailTimeout);
        const email = $(this).val();
        const feedback = $(this).siblings('.invalid-feedback');

        emailTimeout = setTimeout(function() {
            if (email.length > 0 && email.includes('@')) {
                $.get('/users/check-email', { email: email }, function(response) {
                    if (!response.available) {
                        feedback.text('Cet email est déjà utilisé');
                        $('#email').addClass('is-invalid');
                    } else {
                        feedback.text('');
                        $('#email').removeClass('is-invalid');
                    }
                });
            }
        }, 500);
    });

    // Edit User
    $(document).on('click', '.edit-user', function() {
        const userId = $(this).data('id');

        $.ajax({
            url: `${apiUrl}/users/${userId}`,
            method: 'GET',
            success: function(data) {
                $('#edit_user_id').val(data.user.id);
                $('#edit_name').val(data.user.name);
                $('#edit_email').val(data.user.email);

                // Reset radio buttons
                $('.role-radio').prop('checked', false);

                // Check user role
                if (data.roles && data.roles.length > 0) {
                    // On prend le premier rôle car c'est un radio button
                    const userRole = data.roles[0];
                    console.log(userRole);
                    $(`input[name="roles"][value="${userRole.name}"]`).prop('checked', true);
                }

                $('#editUserModal').modal('show');
            },
            error: function() {
                Swal.fire('Erreur', 'Impossible de charger les données de l\'utilisateur', 'error');
            }
        });
    });

    // Edit User Form Submission
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        const userId = $('#edit_user_id').val();

        // Vérifier si un rôle est sélectionné
        if (!$('input[name="roles"]:checked').length) {
            Toast.fire({
                icon: 'error',
                title: 'Veuillez sélectionner un rôle'
            });
            return false;
        }

        $.ajax({
            url: `${apiUrl}/users/${userId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    $('#editUserModal').modal('hide');
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

    // Delete User
    $(document).on('click', '.delete-user', function(e) {
        e.preventDefault();
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Voulez-vous vraiment supprimer l'utilisateur "${userName}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/users/${userId}`,
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

    // Show User Details
    $(document).on('click', '.show-user', function() {
        const userId = $(this).data('id');

        $.ajax({
            url: `${apiUrl}/users/${userId}`,
            method: 'GET',
            success: function(data) {
                const user = data.user;

                // Remplir les informations
                $('.user-name').text(user.name);
                $('.user-email').text(user.email);

                // Afficher les rôles
                const rolesHtml = user.roles.map(role =>
                    `<span class="badge bg-primary-soft text-primary me-2">${role.name}</span>`
                ).join('');
                $('.user-roles').html(rolesHtml);

                // Statut
                const statusClass = user.is_active ? 'success' : 'danger';
                const statusText = user.is_active ? 'Actif' : 'Inactif';
                $('.user-status').html(
                    `<span class="badge bg-${statusClass}-soft text-${statusClass}">${statusText}</span>`
                );

                // Date de création
                $('.user-created-at').text(moment(user.created_at).format('DD/MM/YYYY HH:mm'));

                // Dernière connexion
                const lastLogin = user.last_login_at
                    ? moment(user.last_login_at).format('DD/MM/YYYY HH:mm')
                    : 'Jamais connecté';
                $('.user-last-login').text(lastLogin);

                // Configurer le bouton d'édition
                $('.edit-user-btn')
                    .data('id', user.id)
                    .toggleClass('d-none', user.roles.some(r => r.name === 'super-admin') && !data.currentUserIsSuperAdmin);

                $('#showUserModal').modal('show');
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Impossible de charger les détails de l\'utilisateur'
                });
            }
        });
    });

    // Bouton d'édition dans le modal show
    $('.edit-user-btn').on('click', function() {
        const userId = $(this).data('id');
        $('#showUserModal').modal('hide');
        $('.edit-user[data-id="' + userId + '"]').click();
    });

    // Reset forms when modal is closed
    $('#addUserModal, #editUserModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').html('');
    });

    // Password confirmation validation
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();
        const feedback = $(this).siblings('.invalid-feedback');

        if (password !== confirmation) {
            $(this).addClass('is-invalid');
            feedback.text('Les mots de passe ne correspondent pas');
        } else {
            $(this).removeClass('is-invalid');
            feedback.text('');
        }
    });
});
</script>
