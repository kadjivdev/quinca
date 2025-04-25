{{-- pages/fournisseurs/partials/list.blade.php --}}
<div class="row" id="fournisseursBlock">
    @forelse($fournisseurs as $fournisseur)
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body p-4">
                {{-- En-tête de la carte --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="fournisseur-icon me-3">
                            <i class="fas fa-user-tie fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="text-center mb-1 fw-bold text-dark">{{ $fournisseur->raison_sociale }}</h5>
                            <br>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-soft-primary text-primary rounded-pill">
                                    <i class="fas fa-hashtag fs-xs me-1"></i>
                                    {{ $fournisseur->code_fournisseur }}
                                </span>
                                <span class="badge {{ $fournisseur->statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                    <i class="fas fa-circle fs-xs me-1"></i>
                                    {{ $fournisseur->statut ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $fournisseur->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <span>{{ $fournisseur->telephone }}</span>
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <span>{{ $fournisseur->email }}</span>
                                </span>
                                <span class="text-muted ms-2 small">
                                    <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                                    <p class="mb-0 text-muted">{{ $fournisseur->adresse }}</p>
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
                                <a class="dropdown-item" href="javascript:void(0)" onclick="editFournisseur({{ $fournisseur->id }})">
                                    <i class="far fa-edit me-2 text-warning"></i>
                                    Modifier
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteFournisseur({{ $fournisseur->id }})">
                                    <i class="far fa-trash-alt me-2"></i>
                                    Supprimer
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Contenu de la carte --}}
                <div class="card-content mb-4">
                    <!-- SOLDES -->
                    <div class="d-block shadow p-1 border text-left badge ms-2">
                        <h6 class="text-center">Approvisionnement</h6>
                        <span class="badge bg-success text-white">Solde : {{ number_format($fournisseur->totalAppro,2)  }} FCFA</span>
                        <span class="badge bg-success text-white">Reste: {{ number_format($fournisseur->reste_solde(),2)  }} FCFA</span>
                        <hr>
                        <h6 class="text-center">Achats</h6>
                        <span class="badge bg-success text-white">Solde : {{ number_format($fournisseur->factureAchatAmount,2)  }} FCFA</span>
                        <span class="badge bg-success text-white">Reste: {{ number_format($fournisseur->factureAchatAmount-$fournisseur->reglementsAmount,2)  }} FCFA</span>
                    </div>

                    {{-- Statistiques futures --}}
                    <div class="row g-3">
                        <div class="col-auto">
                            <div class="stat-item">
                                <span class="stat-label text-muted small">Commandes</span>
                                <h6 class="mb-0 mt-1">{{count($fournisseur->facture_fournisseurs)}}</h6>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-item">
                                <span class="stat-label text-muted small">Articles</span>
                                <h6 class="mb-0 mt-1">{{count($fournisseur->articles)}}</h6>
                            </div>
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
                        <i class="fas fa-users fa-2x text-muted"></i>
                    </div>
                    <h5 class="empty-state-title">Aucun fournisseur</h5>
                    <p class="empty-state-description text-muted">
                        Commencez par ajouter votre premier fournisseur en cliquant sur le bouton "Nouveau Fournisseur".
                    </p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFournisseurModal">
                        <i class="fas fa-plus me-2"></i>Ajouter un fournisseur
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforelse
</div>

@push('scripts')
<script>
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 4
    });

    var fournisseursInitiales = <?php echo json_encode($fournisseurs); ?>;

    const filtrage = (data) => {
        let newContent = '';
        data.forEach(fournisseur => {
            newContent += `
            <div class="col-12 col-xl-6 mb-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body p-4">
                        {{-- En-tête de la carte --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="fournisseur-icon me-3">
                                    <i class="fas fa-user-tie fa-lg text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">${fournisseur.raison_sociale }</h5>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-soft-primary text-primary rounded-pill">
                                            <i class="fas fa-hashtag fs-xs me-1"></i>
                                            ${fournisseur.code_fournisseur }
                                        </span>
                                        <span class="badge ${ fournisseur.statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' } rounded-pill">
                                            <i class="fas fa-circle fs-xs me-1"></i>
                                            ${fournisseur.statut ? 'Active' : 'Inactive' }
                                        </span>
                                        <span class="text-muted ms-2 small">
                                            <i class="far fa-clock me-1"></i>
                                            ${fournisseur.created_at}
                                        </span>
                                        <span class="text-muted ms-2 small">
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            <span>${fournisseur.telephone}</span>
                                        </span>
                                        <span class="text-muted ms-2 small">
                                            <i class="fas fa-envelope text-muted me-2"></i>
                                            <span>${fournisseur.email}</span>
                                        </span>
                                        <span class="text-muted ms-2 small">
                                            <i class="fas fa-map-marker-alt text-muted me-2 mt-1"></i>
                                            <p class="mb-0 text-muted">${fournisseur.adresse}</p>
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
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="editFournisseur(${fournisseur.id })">
                                            <i class="far fa-edit me-2 text-warning"></i>
                                            Modifier
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteFournisseur(${fournisseur.id})">
                                            <i class="far fa-trash-alt me-2"></i>
                                            Supprimer
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
            
                        {{-- Contenu de la carte --}}
                        <div class="card-content mb-4">
                            <!-- SOLDES -->
                            <div class="d-block shadow p-1 border text-left badge ms-2">
                                <h6 class="text-center">Approvisionnement</h6>
                                <span class="badge bg-success text-white">Solde : ${ formatter.format(fournisseur.totalAppro) } FCFA</span>
                                <span class="badge bg-success text-white">Reste: ${ formatter.format(fournisseur.reste_solde) } FCFA</span>
                                <hr>
                                <h6 class="text-center">Achats</h6>
                                <span class="badge bg-success text-white">Solde : ${ formatter.format(fournisseur.factureAchatAmount) } FCFA</span>
                                <span class="badge bg-success text-white">Reste: ${ formatter.format(fournisseur.factureAchatAmount - fournisseur.reglementsAmount) } FCFA</span>
                            </div>
                        </div>
            
                        {{-- Statistiques futures --}}
                        <div class="row g-3">
                            <div class="col-auto">
                                <div class="stat-item">
                                    <span class="stat-label text-muted small">Commandes</span>
                                    <h6 class="mb-0 mt-1">${fournisseur.facture_fournisseurs.length}</h6>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="stat-item">
                                    <span class="stat-label text-muted small">Articles</span>
                                    <h6 class="mb-0 mt-1">${fournisseur.articles.length}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `
        });

        $("#fournisseursBlock").append(newContent)
    }

    const HandleSearch = (text) => {
        $("#fournisseursBlock").empty()
        if (text.trim()) {
            const results = fournisseursInitiales.filter((item) => Object.values(item.raison_sociale).join('').toLocaleLowerCase().includes(text.toLocaleLowerCase()))
            // console.log(results[0].facture_fournisseurs.length)
            // console.log(results[0].articles.length)
            filtrage(results)
        } else {
            filtrage(fournisseursInitiales)
        }
    }

    $('#searchFournisseur').on("change", function(e) {
        console.log("searching ..")
        HandleSearch(e.target.value)
    })
</script>
@endpush