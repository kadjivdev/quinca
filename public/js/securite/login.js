document.addEventListener('DOMContentLoaded', function () {
    console.log(apiUrl);

    const loginForm = document.getElementById('loginForm');
    const submitButton = loginForm.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;

    function showLoader() {
        submitButton.innerHTML = `
            <div class="d-flex align-items-center justify-content-center">
                <div class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                Connexion en cours...
            </div>
        `;
        submitButton.disabled = true;
    }

    function hideLoader() {
        submitButton.innerHTML = originalButtonText;
        submitButton.disabled = false;
    }

    function formatErrorMessage(errors) {
        if (typeof errors === 'string') return errors;
        if (errors.email) return errors.email[0];
        if (errors.password) return errors.password[0];

        // Si c'est un objet d'erreurs, on les concatène
        if (typeof errors === 'object') {
            return Object.values(errors)
                .flat()
                .join('<br>');
        }

        return 'Une erreur inattendue est survenue';
    }

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!loginForm.checkValidity()) {
            return;
        }

        showLoader();

        const formData = new FormData(loginForm);


        fetch(apiUrl + "/login", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
            .then(response => response.json().then(data => ({ status: response.status, data })))
            .then(({ status, data }) => {
                hideLoader();
                if (data.status === 'success') {
                    // Succès de la connexion
                    Swal.fire({
                        icon: 'success',
                        title: 'Connexion réussie!',
                        text: data.message || 'Redirection en cours...',
                        timer: 1500,
                        showConfirmButton: false,
                        willClose: () => {
                            window.location.href = apiUrl + '/portail';
                        }
                    });
                } else {
                    // Gestion détaillée des erreurs
                    let errorMessage = '';
                    let errorTitle = 'Erreur';

                    if (status === 422) {
                        // Erreurs de validation
                        errorTitle = 'Erreur de validation';
                        errorMessage = formatErrorMessage(data.errors || data.message);
                    } else if (status === 401) {
                        // Erreur d'authentification
                        errorTitle = 'Erreur d\'authentification';
                        errorMessage = data.message || 'Identifiants incorrects';
                    } else if (status === 403) {
                        // Erreur d'autorisation
                        errorTitle = 'Accès refusé';
                        errorMessage = data.message || 'Vous n\'avez pas les droits nécessaires';
                    } else if (data.message) {
                        // Autres erreurs avec message
                        errorMessage = data.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: errorTitle,
                        html: errorMessage,
                        confirmButtonColor: '#ffc107',
                        confirmButtonText: 'Réessayer'
                    });

                    // Si les erreurs concernent des champs spécifiques, on les marque
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = loginForm.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.parentElement.querySelector('.invalid-feedback');
                                if (feedback) {
                                    feedback.textContent = data.errors[field][0];
                                }
                            }
                        });
                    }
                }
            })
            .catch(error => {
                hideLoader();
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur de connexion',
                    text: 'Impossible de contacter le serveur. Veuillez vérifier votre connexion internet et réessayer.',
                    confirmButtonColor: '#ffc107',
                    confirmButtonText: 'Réessayer'
                });
            });
    });

    // Réinitialisation des erreurs lors de la saisie
    const inputs = loginForm.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            input.classList.remove('is-invalid');
            const feedback = input.parentElement.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        });

        // Gestion des erreurs de validation en temps réel
        input.addEventListener('invalid', (e) => {
            e.preventDefault();
            input.classList.add('shake');
            setTimeout(() => input.classList.remove('shake'), 600);
        });
    });
});
