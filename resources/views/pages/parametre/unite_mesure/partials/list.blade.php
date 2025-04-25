<!-- resources/views/parametrage/unite-mesure/partials/liste.blade.php -->

<div class="row g-4">
    @forelse($uniteMesures as $unite)
        <div class="col-12 col-xl-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-body p-4">
                    {{-- En-tête de la carte --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="unite-icon me-3">
                                <i class="fas fa-ruler-combined fa-lg {{ $unite->unite_base ? 'text-primary' : 'text-secondary' }}"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 fw-bold text-dark">{{ $unite->libelle_unite }}</h5>
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="badge {{ $unite->statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                        <i class="fas fa-circle fs-xs me-1"></i>
                                        {{ $unite->statut ? 'Actif' : 'Inactif' }}
                                    </span>
                                    <span class="badge {{ $unite->unite_base ? 'bg-soft-primary text-primary' : 'bg-soft-info text-info' }} rounded-pill ms-2">
                                        <i class="fas {{ $unite->unite_base ? 'fa-star' : 'fa-code-branch' }} fs-xs me-1"></i>
                                        {{ $unite->unite_base ? 'Unité de Base' : 'Unité Dérivée' }}
                                    </span>
                                    <span class="text-muted ms-2 small">
                                        <i class="far fa-clock me-1"></i>
                                        {{ $unite->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
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
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="editUniteMesure({{ $unite->id }})">
                                        <i class="far fa-edit me-2 text-warning"></i>
                                        Modifier
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="toggleUniteStatus({{ $unite->id }})">
                                        <i class="fas {{ $unite->statut ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                        {{ $unite->statut ? 'Désactiver' : 'Activer' }}
                                    </a>
                                </li>
                                {{-- <li>
                                    <a class="dropdown-item" href="javascript:void(0)" wire:click="showConversions({{ $unite->id }})">
                                        <i class="fas fa-exchange-alt me-2 text-info"></i>
                                        Gérer les conversions
                                    </a>
                                </li> --}}
                                {{-- @if($unite->conversions->isEmpty()) --}}
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteUniteMesure({{ $unite->id }})">
                                            <i class="far fa-trash-alt me-2"></i>
                                            Supprimer
                                        </a>
                                    </li>
                                {{-- @endif --}}
                            </ul>
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($unite->description)
                        <div class="card-content mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle text-muted me-2 mt-1"></i>
                                <p class="mb-0 text-muted">{{ $unite->description }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Statistiques --}}
                    <div class="row g-3">
                        <div class="col-auto">
                            <div class="stat-item">
                                <span class="stat-label text-muted small">Code</span>
                                <h6 class="mb-0 mt-1">{{ $unite->code_unite }}</h6>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-item">
                                <span class="stat-label text-muted small">Conversions</span>
                                {{-- <h6 class="mb-0 mt-1">{{ $unite->conversions->count() }}</h6> --}}
                                <h6 class="mb-0 mt-1">0</h6>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-item {{ $unite->unite_base ? 'bg-soft-primary' : 'bg-soft-secondary' }}">
                                <span class="stat-label {{ $unite->unite_base ? 'text-primary' : 'text-secondary' }} small">
                                    <i class="fas {{ $unite->unite_base ? 'fa-star' : 'fa-code-branch' }} me-1"></i>
                                    {{ $unite->unite_base ? 'Base' : 'Dérivée' }}
                                </span>
                            </div>
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
                            <i class="fas fa-ruler-combined fa-2x text-muted"></i>
                        </div>
                        <h5 class="empty-state-title">Aucune unité de mesure</h5>
                        <p class="empty-state-description text-muted">
                            Commencez par ajouter votre première unité de mesure en cliquant sur le bouton ci-dessous.
                        </p>
                        {{-- <button class="btn btn-primary" wire:click="showCreateForm">
                            <i class="fas fa-plus me-2"></i>Ajouter une unité
                        </button> --}}
                    </div>
                </div>
            </div>
        </div>
    @endforelse
</div>


<style>
.hover-shadow {
    transition: box-shadow 0.3s ease-in-out;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.stat-item {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    background-color: #f8f9fa;
}
.bg-soft-primary {
    background-color: rgba(13, 110, 253, 0.1) !important;
}
.bg-soft-success {
    background-color: rgba(25, 135, 84, 0.1) !important;
}
.bg-soft-danger {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
.bg-soft-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}
.bg-soft-info {
    background-color: rgba(13, 202, 240, 0.1) !important;
}
.bg-soft-secondary {
    background-color: rgba(108, 117, 125, 0.1) !important;
}
.empty-state {
    max-width: 450px;
    margin: 0 auto;
}
.filters-bar {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
}
</style>
