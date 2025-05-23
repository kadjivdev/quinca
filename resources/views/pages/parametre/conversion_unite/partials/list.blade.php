<div class="row d-flex justify-content-center">
    <div class="col-6">
        <input type="search" name="" id="converions-search" class="form-control" placeholder="Rechercher une conversion ...">
    </div>
</div>
<!--  -->
<div id="convertions-blocks" class="row g-3">
    @foreach($conversions as $conversion)
    <div class="col-md-6 col-lg-4 conversions">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body p-3">
                {{-- En-tête avec actions --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        <div class="conversion-icon me-2">
                            <i class="fas fa-exchange-alt text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">
                                {{ $conversion->uniteSource->code_unite }} → {{ $conversion->uniteDest->code_unite }}
                            </h6>
                            <div class="text-muted small">
                                {{ $conversion->uniteSource->libelle_unite }} vers {{ $conversion->uniteDest->libelle_unite }}
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-icon btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <!-- <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="editConversion({{ $conversion->id }})">
                                    <i class="far fa-edit me-2 text-warning"></i>Modifier
                                </a>
                            </li> -->
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatutConversion({{ $conversion->id }})">
                                    <i class="fas {{ $conversion->statut ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                    {{ $conversion->statut ? 'Désactiver' : 'Activer' }}
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteConversion({{ $conversion->id }})">
                                    <i class="far fa-trash-alt me-2"></i>Supprimer
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="row g-2">
                    {{-- Coefficient --}}
                    <div class="col-12">
                        <div class="conversion-details p-2 bg-light rounded">
                            <div class="d-flex align-items-center justify-content-center">
                                <span class="conversion-value fw-bold text-warning">1</span>
                                <span class="mx-1 text-muted">{{ $conversion->uniteSource->libelle_unite }}</span>
                                <i class="fas fa-equals mx-1 text-muted"></i>
                                <span class="conversion-value fw-bold text-warning">{{ number_format($conversion->coefficient, 4) }}</span>
                                <span class="ms-1 text-muted">{{ $conversion->uniteDest->libelle_unite }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Badges --}}
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            <span class="badge {{ $conversion->statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                <i class="fas fa-circle fs-xs me-1"></i>
                                {{ $conversion->statut ? 'Active' : 'Inactive' }}
                            </span>

                            @if($conversion->article)
                            <span class="badge bg-soft-warning text-warning rounded-pill">
                                <i class="fas fa-box fs-xs me-1"></i>
                                {{ $conversion->article->designation }} ({{ $conversion->article->code_article }})
                            </span>
                            @else
                            <span class="badge bg-soft-secondary text-secondary rounded-pill">
                                <i class="fas fa-globe fs-xs me-1"></i>
                                Général
                            </span>
                            @endif

                            <span class="badge bg-soft-warning text-warning rounded-pill">
                                <i class="far fa-calendar fs-xs me-1"></i>
                                {{ $conversion->created_at->locale('fr')->isoFormat('D MMM YYYY') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
    :root {
        --adjiv-orange: #FF9B00;
        --adjiv-orange-rgb: 255, 155, 0;
    }

    /* ... [Reste du CSS avec les couleurs adaptées] ... */
</style>

@push("scripts")
<script>
    const searchInput = document.getElementById("converions-search");
    const resultsList = document.getElementById("convertions-blocks");
    const items = resultsList.getElementsByClassName("conversions");

    searchInput.addEventListener("input", function(e) {
        const filter = searchInput.value.toLowerCase();
        for (let i = 0; i < items.length; i++) {
            const text = items[i].textContent.toLowerCase();
            items[i].style.display = text.includes(filter) ? "" : "none";
        }
    })
</script>
@endpush