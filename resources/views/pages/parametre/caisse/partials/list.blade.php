@forelse($caisses as $caisse)
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body p-4">
                {{-- En-tête de la carte --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="caisse-icon me-3">
                            <i class="fas fa-cash-register fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $caisse->libelle }}</h5>
                            <div class="d-flex align-items-center">
                                <span
                                    class="badge {{ $caisse->actif ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                    <i class="fas fa-circle fs-xs me-1"></i>
                                    {{ $caisse->actif ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $caisse->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
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
                                <a class="dropdown-item" href="javascript:void(0)"
                                    onclick="editCaisse({{ $caisse->id }})">
                                    <i class="far fa-edit me-2 text-warning"></i>
                                    Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#"
                                    onclick="toggleCaisseStatus({{ $caisse->id }})">
                                    <i
                                        class="fas {{ $caisse->actif ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                    {{ $caisse->actif ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)"
                                    onclick="deleteCaisse({{ $caisse->id }})">
                                    <i class="far fa-trash-alt me-2"></i>
                                    Supprimer
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Contenu de la carte --}}
                <div class="card-content mb-4">
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-store text-muted me-2 mt-1"></i>
                        <p class="mb-0 text-muted">Point de vente : {{ $caisse->pointVente->nom_pv }}</p>
                    </div>
                    {{-- <div class="d-flex align-items-start">
                        <i class="fas fa-building text-muted me-2 mt-1"></i>
                        <p class="mb-0 text-muted">Magasin :
                            {{ $caisse->pointVente?->depot?->libelle_depot ?? 'Non défini' }}</p>
                        {{-- <p class="mb-0 text-muted">Magasin : {{ $caisse->pointVente->depot->libelle_depot }}</p>
                    </div> --}}
                </div>

                {{-- Statistiques --}}
                <div class="row g-3">
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Code</span>
                            <h6 class="mb-0 mt-1">{{ $caisse->code_caisse }}</h6>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Sessions</span>
                            {{-- <h6 class="mb-0 mt-1">{{ $caisse->sessions->count() }}</h6> --}}
                            <h6 class="mb-0 mt-1">000</h6>
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
                        <i class="fas fa-cash-register fa-2x text-muted"></i>
                    </div>
                    <h5 class="empty-state-title">Aucune caisse</h5>
                    <p class="empty-state-description text-muted">
                        Commencez par ajouter votre première caisse en cliquant sur le bouton "Nouvelle Caisse".
                    </p>
                    {{-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCaisseModal">
                        <i class="fas fa-plus me-2"></i>Ajouter une caisse
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
@endforelse

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

    .caisse-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(13, 110, 253, 0.1);
        border-radius: 0.5rem;
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
        border-radius: 0.5rem;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border-radius: 50%;
        margin: 0 auto;
    }
</style>
