<div class="page-header mb-4">
    <div class="container-fluid p-0">
        {{-- En-tête principal --}}
        <div class="row align-items-center mb-4">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <a class="btn btn-light px-3 d-inline-flex align-items-center" href="{{route('requete_stock.index')}}">
                    <i class="fas fa-sync-alt me-2"></i>
                    Actualiser
                </a>

                @can("requetes.create")
                <button type="button"
                    class="btn btn-primary px-3 d-inline-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#addRequeteModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle requête de stock
                </button>
                @endcan
            </div>
        </div>

        <!-- GESTION DES ERREURES -->
        <div class="row justify-content-center d-flex">
            <div class="col-md-6">
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @elseif(session()->has('error'))
                <div class="alert alert-danger">
                    {{session()->get("error") }}
                </div>
                @endif

                @if (session()->has('success'))
                <div class="alert alert-success">
                    {{session()->get("success") }}
                </div>
                @endif
            </div>
        </div>
        <!-- FIN GESTION DES ERREURES -->

        <!-- FILTRE -->
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <form action="{{route('requete_stock.index')}}" method="get" class=" p-3 border rounded shadow">
                    <div class="mb-2">
                        <select name="filtre_article_id" id="filtre_article_id" class="form-control">
                            <option value="">Choisir l'article </option>
                            @foreach ($articles as $article)
                            <option value="{{ $article->id }}" {{ request()->get("article_id") == $article->id ? 'selected' : '' }}>
                               {{$article->code_article}} - ({{$article->designation}})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="filtre_depot_id" id="filtre_depot_id" class="form-control">
                            <option value="">Choisir le dépôt </option>
                            @foreach ($depots as $depot)
                            <option value="{{ $depot->id }}" {{ request()->get("depot_id") == $depot->id ? 'selected' : '' }}>
                                {{ $depot->libelle_depot }}({{$depot->code_depot}})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary my-3 w-100">Filtrer</button>
                    </div>
                </form>
            </div>
            <div class="col-md-3"></div>
        </div>

    </div>
</div>
<link href="{{ asset('css/theme/header.css') }}" rel="stylesheet">

<style>
    .page-header {
        margin-bottom: 2rem;
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header-icon .icon-wrapper {
        transition: transform 0.3s ease;
    }

    .header-icon:hover .icon-wrapper {
        transform: scale(1.1);
    }

    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }

    .badge {
        padding: 0.5rem 0.75rem;
    }

    .btn {
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn i {
        transition: transform 0.3s ease;
    }

    .btn:active i {
        transform: scale(0.9);
    }
</style>