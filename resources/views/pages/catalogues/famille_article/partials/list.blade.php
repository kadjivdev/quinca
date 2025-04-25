@forelse($familleArticles as $famille)
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body p-4">
                {{-- En-tête de la carte --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="famille-icon me-3">
                            <i class="fas fa-box-open fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">{{ $famille->libelle_famille }}</h5>
                            <div class="d-flex align-items-center">
                                <span class="badge {{ $famille->statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                    <i class="fas fa-circle fs-xs me-1"></i>
                                    {{ $famille->statut ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $famille->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
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
                                <a class="dropdown-item" href="javascript:void(0)" onclick="editFamilleArticle({{ $famille->id }})">
                                    <i class="far fa-edit me-2 text-warning"></i>
                                    Modifier
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="toggleFamilleStatus({{ $famille->id }})">
                                    <i class="fas {{ $famille->statut ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                    {{ $famille->statut ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                            {{-- @if($famille->articles->count() === 0 && $famille->enfants->count() === 0) --}}
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteFamilleArticle({{ $famille->id }})">
                                        <i class="far fa-trash-alt me-2"></i>
                                        Supprimer
                                    </a>
                                </li>
                            {{-- @endif --}}
                        </ul>
                    </div>
                </div>

                {{-- Contenu de la carte --}}
                <div class="card-content mb-4">
                    @if($famille->description)
                        <div class="d-flex align-items-start mb-2">
                            <i class="fas fa-info-circle text-muted me-2 mt-1"></i>
                            <p class="mb-0 text-muted">{{ $famille->description }}</p>
                        </div>
                    @endif

                </div>

                {{-- Statistiques --}}
                <div class="row g-3">
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Code</span>
                            <h6 class="mb-0 mt-1">{{ $famille->code_famille }}</h6>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Articles</span>
                            {{-- <h6 class="mb-0 mt-1">{{ $famille->articles->count() }}</h6> --}}
                            <h6 class="mb-0 mt-1">{{ 000 }}</h6>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="stat-item">
                            <span class="stat-label text-muted small">Sous-familles</span>
                            {{-- <h6 class="mb-0 mt-1">{{ $famille->enfants->count() }}</h6> --}}
                            <h6 class="mb-0 mt-1">0 art</h6>
                        </div>
                    </div>
                    @if($famille->parent)
                        <div class="col-auto">
                            <div class="stat-item bg-soft-info">
                                <span class="stat-label text-info small">
                                    <i class="fas fa-level-up-alt me-1"></i>
                                    {{ $famille->parent->libelle_famille }}
                                </span>
                            </div>
                        </div>
                    @endif
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
                        <i class="fas fa-box-open fa-2x text-muted"></i>
                    </div>
                    <h5 class="empty-state-title">Aucune famille d'articles</h5>
                    <p class="empty-state-description text-muted">
                        Commencez par créer votre première famille d'articles en cliquant sur le bouton "Nouvelle Famille".
                    </p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFamilleModal">
                        <i class="fas fa-plus me-2"></i>Ajouter une famille
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforelse
