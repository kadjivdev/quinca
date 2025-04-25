<div class="page-header mb-4">
    <div class="row align-items-center g-3">
        <!-- Titre -->
        <div class="col-12">
            <h1 class="page-header-title mb-0">
                <i class="fas fa-shield-alt me-2" style="color: #FFB800;"></i>
                Gestion des Rôles et Permissions
            </h1>
        </div>

        <!-- Statistiques -->
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle" style="background-color: rgba(255, 184, 0, 0.1);">
                                        <i class="fas fa-shield-alt fa-lg" style="color: #FFB800;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="card-subtitle text-muted mb-1">Total Rôles</h6>
                                    <h4 class="card-title mb-0 fw-bold">{{ $roles->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle" style="background-color: rgba(25, 135, 84, 0.1);">
                                        <i class="fas fa-key fa-lg text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="card-subtitle text-muted mb-1">Total Permissions</h6>
                                    <h4 class="card-title mb-0 fw-bold">{{ $permissions->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
