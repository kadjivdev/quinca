<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header text-white border-0 py-3" style="background-color: #FFB800;">
                <div class="d-flex align-items-center">
                    <div class="modal-title-icon me-3">
                        <i class="fas fa-user-plus fa-lg"></i>
                    </div>
                    <h5 class="modal-title fw-semibold mb-0">Nouvel Utilisateur</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addUserForm" action="javascript:void(0)">
                @csrf
                <div class="modal-body p-4">
                    <!-- Alert Info -->
                    <div class="alert bg-warning-soft border-0 mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-warning me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                Les champs marqués d'un astérisque (<span class="text-danger">*</span>) sont obligatoires
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Informations Personnelles -->
                        <div class="col-12 mb-2">
                            <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: #FFB800;">
                                <i class="fas fa-user me-2"></i>Informations Personnelles
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label fw-medium required">Nom complet</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">
                                    <i class="fas fa-user" style="color: #FFB800;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0"
                                       id="name" name="name" placeholder="Entrez le nom complet" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium required">Adresse email</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">
                                    <i class="fas fa-envelope" style="color: #FFB800;"></i>
                                </span>
                                <input type="email" class="form-control border-start-0 ps-0"
                                       id="email" name="email" placeholder="exemple@kadjiv.com" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Accès et Sécurité -->
                        <div class="col-12 mt-4 mb-2">
                            <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: #FFB800;">
                                <i class="fas fa-shield-alt me-2"></i>Accès et Sécurité
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-medium required">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">
                                    <i class="fas fa-lock" style="color: #FFB800;"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0"
                                        id="password" name="password" required minlength="8">
                            </div>
                            <small class="text-muted mt-1">Le mot de passe doit contenir au moins 8 caractères</small>
                            <div class="invalid-feedback">Le mot de passe doit contenir au moins 8 caractères</div>
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-medium required">Confirmation</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">
                                    <i class="fas fa-lock" style="color: #FFB800;"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-0"
                                        id="password_confirmation" name="password_confirmation" required>
                            </div>
                            <div class="invalid-feedback">Les mots de passe ne correspondent pas</div>
                        </div>
                        <!-- Affectation -->
                        <div class="col-12 mt-4 mb-2">
                            <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: #FFB800;">
                                <i class="fas fa-store me-2"></i>Affectation
                            </h6>
                        </div>

                        <div class="col-12">
                            <label for="point_de_vente_id" class="form-label fw-medium required">Point de vente</label>
                            <select class="form-select" id="point_de_vente_id" name="point_de_vente_id" required>
                                <option value="">Sélectionner un point de vente</option>
                                @foreach($pointsDeVente as $pdv)
                                    <option value="{{ $pdv->id }}">{{ $pdv->nom_pv }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12 mt-3">
                            <label class="form-label fw-medium required mb-3">Rôle</label>
                            <div class="row g-3">
                                @foreach($roles->chunk(2) as $chunk)
                                    @foreach($chunk as $role)
                                        <div class="col-md-6">
                                            <div class="form-check card border shadow-sm p-3">
                                                <input type="radio" class="form-check-input"
                                                       name="roles" value="{{ $role->name }}"
                                                       id="role_{{ $role->id }}">
                                                <label class="form-check-label d-block" for="role_{{ $role->id }}">
                                                    <span class="fw-medium">{{ $role->name }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn px-4 text-white" id="submitAddUser" style="background-color: #FFB800;">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-warning-soft {
    background-color: rgba(255, 184, 0, 0.1);
}

.modal-content {
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.form-control, .form-select {
    padding: 0.6rem 1rem;
    border-radius: 0.375rem;
}

.form-control:focus, .form-select:focus {
    border-color: #FFB800;
    box-shadow: 0 0 0 0.25rem rgba(255, 184, 0, 0.25);
}

.input-group-text {
    padding: 0.6rem 1rem;
    background-color: #f8f9fa;
}

.required:after {
    content: ' *';
    color: #dc3545;
    font-weight: bold;
}

.form-check.card {
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    border-radius: 0.375rem;
}

.form-check.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.form-check-input:checked + .form-check-label {
    color: #FFB800;
}

.form-check.card:has(.form-check-input:checked) {
    border-color: #FFB800;
    background-color: rgba(255, 184, 0, 0.1);
}

.btn:hover {
    opacity: 0.9;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');

        // Validation du mot de passe
        passwordInput.addEventListener('input', function() {
            const isValid = this.value.length >= 8;
            this.classList.toggle('is-invalid', !isValid);
        });

        // Validation de la confirmation du mot de passe
        confirmPasswordInput.addEventListener('input', function() {
            const passwordMatch = this.value === passwordInput.value;
            this.classList.toggle('is-invalid', !passwordMatch);
        });

        // Validation à la soumission du formulaire
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmation = confirmPasswordInput.value;

            if (password.length < 8) {
                e.preventDefault();
                passwordInput.classList.add('is-invalid');
                return false;
            }

            if (password !== confirmation) {
                e.preventDefault();
                confirmPasswordInput.classList.add('is-invalid');
                return false;
            }
        });
    });
    </script>
