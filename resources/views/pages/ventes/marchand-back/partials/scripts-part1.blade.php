<script type="text/javascript">
    // Configuration centralisée
    const FactureConfig = {
        selectors: {
            modal: '#addDepenseModal',
            addButton: '#addDepenseBtn',
            form: '#addDepenseForm',
        },
        routes: {
            store: 'revendeurs/depenses/store'
        },
    };

    class DepenseManager {

        init() {
            this.initializeFormValidation();
            this.initEvents();
        }

        initEvents() {
            const {
                addButton,
                form,
                modal
            } = FactureConfig.selectors;

            $(form).attr("action")
            // Gestion du formulaire
            // $(form).off('submit').on('submit', (e) => handleSubmit(e));

            // Réinitialisation lors de la fermeture du modal
            $(modal).off('hidden.bs.modal').on('hidden.bs.modal', () => {
                if (confirm(FactureMessages.confirmations.cancel)) {
                    this.resetForm();
                }
            });
        }

        async handleSubmit(event) {
            event.preventDefault();

            alert("soumettant");

            const form = event.target;
            if (!form.checkValidity()) {
                event.stopPropagation();
                $(form).addClass('was-validated');
                this.showNotification('error', 'Erreure de validation');
                return;
            }

            try {
                this.showFormLoading();

                const response = await this.fetchWithHeaders(FactureConfig.routes.store, {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await response.json();

                if (data.status === 'success') {
                    this.showNotification('success', data.message);
                    this.resetForm();
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Insertion échouée!');
                }

            } catch (error) {
                console.error('Erreur soumission:', error);
                this.showNotification('error', error.message);
            } finally {
                this.hideFormLoading();
            }
        }

        // Méthodes utilitaires
        fetchWithHeaders(url, options = {}) {
            return fetch(url, {
                ...options,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    ...(options.headers || {})
                }
            });
        }

        showFormLoading() {
            const submitBtn = $(FactureConfig.selectors.form).find('button[type="submit"]');
            submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...');
            $(FactureConfig.selectors.form).addClass('form-loading');
        }

        hideFormLoading() {
            const submitBtn = $(FactureConfig.selectors.form).find('button[type="submit"]');
            submitBtn.prop('disabled', false)
                .html('<i class="fas fa-save me-2"></i>Enregistrer');
            $(FactureConfig.selectors.form).removeClass('form-loading');
        }

        showNotification(type, message) {
            if (window.Swal) {
                Swal.fire({
                    icon: type,
                    title: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            } else {
                alert(message);
            }
        }

        resetForm() {
            const form = $(FactureConfig.selectors.form);

            // Réinitialiser les validations
            form.removeClass('was-validated');

            // Réinitialiser les champs
            form[0].reset();

            // Réinitialiser les select2
            form.find('select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).val(null).trigger('change');
                }
            });

            // Vider le cache
            ArticleCache.clear();
        }

        initializeFormValidation() {
            const form = document.querySelector(FactureConfig.selectors.form);
            if (!form) return;

            // Désactiver la validation HTML5 par défaut
            form.setAttribute('novalidate', '');

            // Ajouter nos propres validations
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);

            // Validation en temps réel des champs
            form.querySelectorAll('input, select').forEach(input => {
                input.addEventListener('input', () => {
                    if (input.checkValidity()) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                });
            });
        }
    }

    // Initialisation unique
    $(document).ready(() => {
        if (!window.depenseManager) {
            window.depenseManager = new DepenseManager();
            window.depenseManager.init();
        }
    });
</script>