@extends('layouts.ventes.reglement')

@push('styles')
<style>
    /* Z-index fixes */
    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
    }

    /* Select2 en dehors du modal */
    .main-content .select2-container {
        z-index: 1000 !important;
    }

    .main-content .select2-dropdown {
        z-index: 1001 !important;
    }

    /* Select2 dans le modal */
    .modal .select2-container {
        z-index: 2000 !important;
    }

    .modal .select2-dropdown {
        z-index: 2001 !important;
    }

    /* Select2 styling */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #dee2e6;
    }

    /* Pour masquer les select2 quand le modal est ouvert */
    .modal-open .main-content .select2-container {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="content">
    @include('pages.ventes.reglement.partials.header')
    <div class="row g-3 list mt-3" id="stockEntriesList">
        <!-- GESTION DES ERREURES -->
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
        <!-- FIN GESTION DES ERREURES -->
         
    </div>
</div>

<main id="main" class="main">

    <div class="pagetitle d-flex">
        <div class="col-6">
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Tableau de Bord</a></li>
                    <li class="breadcrumb-item active">Requêtes</li>
                </ol>
            </nav>
        </div>
        <div class="col-6 justify-content-end">

            {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                        Importer article
                    </button>

                    <a href="{{ route('liste_taux_convert') }}" class="btn btn-primary float-end">Gérer les taux de conversions</a>


            <a href="{{ route('UniteBase') }}" class="btn btn-primary float-end" style="margin-right: 1%"> Unité de base</a>
            <button type="button" class="btn btn-primary float-end mx-2" id="tauxBtn">
                Mettre Taux </button> --}}

            {{-- @can('articles.ajouter-article') --}}
            <a href="{{ route('requetes.create') }}" style="margin-left: 10px;" class="btn btn-warning float-end petit_bouton"> <i class="bi bi-plus-circle"></i> Ajouter une requête</a>
            {{-- @endcan --}}


        </div>
    </div><!-- End Page +++ -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="alert alert-success d-none" id="tauxMsg">
                </div>
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Liste des requêtes</h5>
                        <table id="example" class=" table table-bordered border-warning  table-hover table-warning table-sm">
                            <thead>
                                <tr>
                                    <th>N° demande</th>
                                    <th>Client</th>
                                    <th>Date</th>
                                    <th>Mention</th>
                                    <th>Articles</th>
                                    <th>Montant</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requetes as $requete)
                                <tr>
                                    <td>{{ $requete->num_demande }} </td>
                                    <td>{{ $requete->client->nom_client }}</td>
                                    <td>{{ $requete->date_demande }}</td>
                                    <td>{{ $requete->mention }}</td>

                                    <td>
                                        @if ($requete->motif == 'Articles')
                                        <ul>
                                            @foreach ($requete->articles as $article)
                                            <li>{{ $article->nom }}</li>
                                            @endforeach
                                        </ul>
                                        @elseif ($requete->motif == 'Autres')
                                        {{$requete->motif_content}}
                                        @endif
                                    </td>
                                    <td>{{ $requete->montant }}</td>
                                    <td>
                                        @if (is_null($requete->validate_at))
                                        <div class="dropdown">
                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li>
                                                    <a href="{{route('requetes.show', $requete->id)}}" data-bs-toggle="tooltip" class="dropdown-item" data-bs-placement="left" data-bs-title="Détail"> Détail </a>
                                                </li>
                                                <li>
                                                    <a href="{{route('requetes.edit', $requete->id)}}" data-bs-toggle="tooltip" class="dropdown-item" data-bs-placement="left" data-bs-title="Editer"> Modifier </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('valider-requete', $requete->id) }}"
                                                        method="POST" class="col-3">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Valider la requête" onclick="return confirm('Voulez vous vraiment valider cette requête ? Cette opération est irréversible')">Valider </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('requetes.destroy', $requete->id) }}"
                                                        method="POST" class="col-3">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Su^pprimer la requête" onclick="return confirm('Voulez vous vraiment valider cette requête? Cette opération est irréversible')">Supprimer</button>
                                                    </form>
                                                </li>

                                            </ul>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- End Table with stripped rows -->
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
{{-- <script src="{{ asset('assets/js/jquery3.6.min.js') }}"></script> --}}
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

{{-- <script>
        $('#importForm1').submit(function(e) {
            e.preventDefault();

            console.log(formData, $('#upload_xls')[0].files[0]);
            $.ajax({
                url: '{{ route('article-import') }}',
method: 'POST',
data: {
_token: '{{ csrf_token() }}',
upload_xls: $('#upload_xls')[0].files[0]
},
contentType: false,
processData: false,
success: function(response) {
console.log(response);
alert('Import successful');
},
error: function(xhr, status, error) {
console.error(error);
alert('An error occurred during import');
}
});
});
</script> --}}
<script>
    var apiUrl = "{{ config('app.url_ajax') }}";

    $(document).ready(function() {
        $('#tauxBtn').click(function() {
            console.log('jai cliqué');
            $.ajax({
                url: apiUrl + '/taux-par-defaut',
                type: 'GET',
                success: function(response) {
                    // window.location.href = response.redirectUrl;
                    console.log('jai cliqué et succès', response);
                    $('#tauxMsg').removeClass('d-none');
                    $('#tauxMsg').html(response.message);
                },
                error: function(error) {
                    // La requête a échoué, vous pouvez gérer l'erreur ici
                    $('#tauxMsg').removeClass('d-none');
                    $('#tauxMsg').html('Erreur lors de la maj des taux de bases');

                }
            });
        });
    });
</script>

<script>
    $('#id_art_sel').select2({
        width: 'resolve'
    });
</script>
@endsection