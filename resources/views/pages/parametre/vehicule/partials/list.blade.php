{{-- pages/chauffeurs/partials/list.blade.php --}}
@forelse($vehicules as $vehicule)
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body p-4">
                {{-- En-tête de la carte --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="vehicule-icon me-3">
                            <i class="fas fa-car fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $vehicule->matricule }}</h5>
                            <div class="d-flex align-items-center">
                                <span class="badge {{ $vehicule->statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                    <i class="fas fa-circle fs-xs me-1"></i>
                                    {{ $vehicule->statut ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $vehicule->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="editVehicule({{ $vehicule->id }})">
                                    <i class="far fa-edit me-2 text-warning"></i>
                                    Modifier
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteVehicule({{ $vehicule->id }})">
                                    <i class="far fa-trash-alt me-2"></i>
                                    Supprimer
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-5 text-center">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-users fa-2x text-muted"></i>
                    </div>
                    <h5 class="empty-state-title">Aucun vehicule</h5>
                    <p class="empty-state-description text-muted">
                        Commencez par ajouter votre premier vehicule en cliquant sur le bouton "Nouveau vehicule".
                    </p>
                    {{-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehiculeModal">
                        <i class="fas fa-plus me-2"></i>Ajouter un vehicule
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
@endforelse
