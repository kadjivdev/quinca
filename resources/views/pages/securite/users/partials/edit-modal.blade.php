<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-edit fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Modifier l'Utilisateur</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editUserForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_user_id" name="user_id">

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label fw-semibold required">Nom complet</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_email" class="form-label fw-semibold required">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <small class="text-muted">Laissez vide pour conserver le mot de passe actuel</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold required">Rôle</label>
                            <div class="row g-3">
                                @foreach($roles as $role)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input role-radio"
                                                   name="roles" value="{{ $role->name }}"
                                                   id="edit_role_{{ $role->id }}"
                                                   required><!-- Ajout de required -->
                                            <label class="form-check-label" for="edit_role_{{ $role->id }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
