<div class="page-header mb-4">
    <div class="row align-items-center g-3">
        <!-- Titre -->
        <div class="col-12">
            <h1 class="page-header-title mb-0">
                <i class="fas fa-users me-2" style="color: #FFB800;"></i>
                Gestion des Utilisateurs
            </h1>
        </div>

        <!-- Statistiques -->
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle" style="background-color: rgba(255, 184, 0, 0.1);">
                                        <i class="fas fa-users fa-lg" style="color: #FFB800;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="card-subtitle text-muted mb-1">Total Utilisateurs</h6>
                                    <h4 class="card-title mb-0 fw-bold">{{ $users->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle" style="background-color: rgba(25, 135, 84, 0.1);">
                                        <i class="fas fa-user-check fa-lg text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="card-subtitle text-muted mb-1">Utilisateurs Actifs</h6>
                                    <h4 class="card-title mb-0 fw-bold">{{ $users->where('is_active', true)->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle bg-info-soft">
                                        <i class="fas fa-store fa-lg text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="card-subtitle text-muted mb-1">Points de Vente</h6>
                                    <h4 class="card-title mb-0 fw-bold">{{ $pointsDeVente->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle bg-danger-soft">
                                        <i class="fas fa-user-shield fa-lg text-danger"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="card-subtitle text-muted mb-1">Rôles</h6>
                                    <h4 class="card-title mb-0 fw-bold">{{ $roles->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        {{-- <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Point de Vente</label>
                            <select class="form-select" name="point_de_vente">
                                <option value="">Tous</option>
                                @foreach($pointsDeVente as $pdv)
                                    <option value="{{ $pdv->id }}">{{ $pdv->nom_pv }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Rôle</label>
                            <select class="form-select" name="role">
                                <option value="">Tous</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="status">
                                <option value="">Tous</option>
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Rechercher</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Nom ou email...">
                                <button class="btn text-white" type="submit" style="background-color: #FFB800;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div> --}}
    </div>
</div>

<style>
.page-header-title {
    color: #2c3038;
    font-size: 1.5rem;
    font-weight: 600;
}

.avatar-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-info-soft {
    background-color: rgba(13, 202, 240, 0.1);
}

.bg-danger-soft {
    background-color: rgba(220, 53, 69, 0.1);
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.form-select, .form-control {
    border-color: #e9ecef;
}

.form-select:focus, .form-control:focus {
    border-color: #FFB800;
    box-shadow: 0 0 0 0.25rem rgba(255, 184, 0, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des filtres
    const filterForm = document.getElementById('filterForm');
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Ajoutez ici la logique de filtrage
    });
});
</script>
