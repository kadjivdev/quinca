<div class="modal fade" id="showUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Détails de l'Utilisateur</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="avatar-circle bg-primary-soft mb-3 mx-auto">
                        <i class="fas fa-user fa-2x text-primary"></i>
                    </div>
                    <h4 class="fw-bold user-name mb-1"></h4>
                    <p class="text-muted user-email mb-0"></p>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="detail-item">
                            <label class="fw-semibold text-muted">Rôle(s)</label>
                            <div class="user-roles mt-2"></div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="detail-item">
                            <label class="fw-semibold text-muted">Statut</label>
                            <div class="user-status mt-2"></div>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="detail-item">
                            <label class="fw-semibold text-muted">Date de création</label>
                            <div class="user-created-at mt-2"></div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="detail-item">
                            <label class="fw-semibold text-muted">Dernière connexion</label>
                            <div class="user-last-login mt-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="button" class="btn btn-warning px-4 edit-user-btn">
                    <i class="fas fa-edit me-2"></i>Modifier
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.detail-item {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
}
</style>
