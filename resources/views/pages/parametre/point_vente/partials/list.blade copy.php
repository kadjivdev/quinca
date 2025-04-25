@forelse($pointsVente as $pointVente)
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body p-4">
                {{-- En-tête de la carte --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="point-vente-icon me-3">
                            <i class="fas fa-store fa-lg text-warning"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $pointVente->nom_pv }}</h5>
                            <div class="d-flex align-items-center">
                                <span class="badge {{ $pointVente->actif ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                    <i class="fas fa-circle fs-xs me-1"></i>
                                    {{ $pointVente->actif ? 'Actif' : 'Inactif' }}
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $pointVente->created_at->locale('fr')->isoFormat('D MMMM YYYY, HH:mm')}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-icon btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="editPointVente({{ $pointVente->id }})">
                                    <i class="far fa-edit me-2 text-warning"></i>
                                    Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="toggleStatus({{ $pointVente->id }})">
                                    <i class="fas {{ $pointVente->actif ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                    {{ $pointVente->actif ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="deletePointVente({{ $pointVente->id }})">
                                    <i class="far fa-trash-alt me-2"></i>
                                    Supprimer
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Contenu de la carte --}}
                <div class="card-content mb-4">
                    @if($pointVente->adresse_pv)
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-muted me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 text-muted">{{ $pointVente->adresse_pv }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Pied de la carte avec statistiques --}}
                <div class="row g-3">
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Caisses</span>
                            <h6 class="mb-0 mt-1">{{ $pointVente->caisses_count ?? 0 }}</h6>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Utilisateurs</span>
                            <h6 class="mb-0 mt-1">{{ $pointVente->utilisateurs_count ?? 0 }}</h6>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Code PV</span>
                            <h6 class="mb-0 mt-1">{{ $pointVente->code_pv }}</h6>
                        </div>
                    </div><div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Magasins</span>
                            <h6 class="mb-0 mt-1">{{ $pointVente->depot->count() }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 text-center">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3">
                        <i class="fas fa-store fa-2x text-muted"></i>
                    </div>
                    <h5 class="empty-state-title">Aucun point de vente</h5>
                    <p class="empty-state-description text-muted">
                        Commencez par ajouter votre premier point de vente en cliquant sur le bouton "Nouveau Point de Vente".
                    </p>
                    {{-- <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addPointVenteModal">
                        <i class="fas fa-plus me-2"></i>Ajouter un point de vente --}}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforelse

<style>
:root {
    --adjiv-orange: #FF9B00;
    --adjiv-orange-soft: rgba(255, 155, 0, 0.1);
}

/* Styles personnalisés pour les cartes */
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.bg-soft-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-soft-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.point-vente-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--adjiv-orange-soft);
    border-radius: 10px;
}

.fs-xs {
    font-size: 0.65rem;
}

.btn-icon {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.btn-warning {
    background-color: var(--adjiv-orange);
    border-color: var(--adjiv-orange);
    color: white;
}

.btn-warning:hover {
    background-color: #e68a00;
    border-color: #e68a00;
    color: white;
}

.stat-item {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    background-color: rgba(0, 0, 0, 0.03);
}

.empty-state {
    padding: 2rem;
}

.empty-state-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 0, 0, 0.03);
    border-radius: 50%;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.dropdown-item i {
    width: 16px;
}
</style>

<script>

</script>
