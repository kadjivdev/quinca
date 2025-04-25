<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-2">Gestion des Articles</h1>
        <p class="text-muted">Gérez votre catalogue d'articles et leur stock</p>
    </div>

    <button type="button"
        class="btn btn-dark btn-sm d-flex align-items-center"
        data-bs-toggle="modal"
        data-bs-target="#importArticleModal">
        <i class="fas fa-plus me-2"></i>
        Importer Article
    </button>

    <div class="d-flex gap-2">
        <div class="position-relative">
            <a href="{{route('articles.index')}}" class="btn btn-dark">
                <i class="bi bi-plus-lg me-2"></i>Actualiser
            </a>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addArticleModal">
            <i class="bi bi-plus-lg me-2"></i>Nouvel Article
        </button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Total Articles</h6>
                <h2 class="card-title mb-0">{{ $totalArticles }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Articles en Stock</h6>
                <h2 class="card-title mb-0">{{ $articlesEnStock }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Stock Critique</h6>
                <h2 class="card-title mb-0 text-danger">{{ $articlesCritiques }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 text-muted">Articles Actifs</h6>
                <h2 class="card-title mb-0 text-success">{{ $articlesActifs }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-secondary active" data-view="grid">
            <i class="bi bi-grid me-2"></i>Grille
        </button>
        <button type="button" class="btn btn-outline-secondary" data-view="list">
            <i class="bi bi-list me-2"></i>Liste
        </button>
    </div>

    <select class="form-select d-inline-block w-auto ms-2" id="filterFamille">
        <option value="">Toutes les familles</option>
        @foreach($familles as $famille)
        <option value="{{ $famille->id }}">{{ $famille->libelle_famille }}</option>
        @endforeach
    </select>

    <select class="form-select d-inline-block w-auto ms-2" id="filterStock">
        <option value="">Tous les stocks</option>
        <option value="normal">Stock Normal</option>
        <option value="alerte">Stock en Alerte</option>
        <option value="critique">Stock Critique</option>
        <option value="surplus">Surplus</option>
    </select>

    <br><br>
    <div class="">
        <form action="{{route('articles.index')}}" method="get" class="btn-group">
            <select class="form-select select2 d-inline-block w-auto ms-2" name="depot">
                <option value="tous">Tous les dépots</option>
                @foreach($depots as $depot)
                <option value="{{$depot->id}}">{{$depot->libelle_depot}}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Filtrer</button>
        </form>
    </div>
</div>