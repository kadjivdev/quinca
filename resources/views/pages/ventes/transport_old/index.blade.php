@extends('layout.template')
@section('content')
    <main id="main" class="main">

        <div class="pagetitle d-flex">
            <div class="col-6">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Tableau de Bord</a></li>
                        <li class="breadcrumb-item active">Transports</li>
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
                        <a href="{{ route('transports.create') }}" style="margin-left: 10px;" class="btn btn-warning float-end petit_bouton"> <i class="bi bi-plus-circle"></i> Ajouter une requête de transport</a>
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
                            <h5 class="card-title">Liste des requêtes de transport</h5>
                                <table  id="example" class=" table table-bordered border-warning  table-hover table-warning table-sm">
                                    <thead>
                                        <tr>
                                            <th>N°</th>
                                            <th>Client</th>
                                            <th>Date</th>
                                            <th>Montant</th>
                                            <th>Observation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transports as $transport)
                                        @php
                                            $i++;
                                        @endphp
                                            <tr>
                                                <td>{{ $i }} </td>
                                                <td>{{ $transport->client->nom_client }} </td>
                                                <td>{{ Carbon\Carbon::parse($transport->date_op)->format('d-m-Y') }} </td>
                                                <td>{{ $transport->montant }} </td>
                                                <td>{{ $transport->observation }} </td>
                                                <td>
                                                    @if (is_null($transport->validate_at))
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bi bi-gear"></i>
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">            
                                                                <li>
                                                                    <a href="{{route('transports.show', $transport->id)}}" data-bs-toggle="tooltip" class="dropdown-item" data-bs-placement="left" data-bs-title="Détail"> Détail </a>            
                                                                </li>
                                                                <li>
                                                                    <a href="{{route('transports.edit', $transport->id)}}" data-bs-toggle="tooltip" class="dropdown-item" data-bs-placement="left" data-bs-title="Editer"> Modifier </a>            
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('valider-transport', $transport->id) }}"
                                                                        method="POST" class="col-3">
                                                                        @csrf
                                                                        @method('POST')
                                                                        <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Valider la requête"  onclick="return confirm('Voulez vous vraiment valider cette requête ? Cette opération est irréversible')">Valider </button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('transports.destroy', $transport->id) }}"
                                                                        method="POST" class="col-3">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Supprimer la requête" onclick="return confirm('Voulez vous vraiment supprimer cette requête? Cette opération est irréversible')">Supprimer</button>
                                                                    </form>
                                                                </li>   
    
                                                            </ul>
                                                        </div>                                                        
                                                    @endif
                                                </td>
                                            </tr>                                            
                                        @endforeach

                                            <!-- Modal -->
                                            {{-- <div class="modal fade" id="staticBackdrop{{ $articles->id }}"
                                                data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
                                                aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('tauxSupplements.store') }}" method="post">
                                                            <div class="modal-header">
                                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">Configurer
                                                                    le taux</h1>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                @csrf
                                                                <div class="col-12">
                                                                    <label for="" class="form-label">Unité de
                                                                        base</label>
                                                                    <input type="text" readonly
                                                                        value="{{ $articles->uniteBase->unite }}" required
                                                                        class="form-control" name="unite">
                                                                    <input type="hidden" value="{{ $articles->id }}"
                                                                        name="article_id">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label">Taux de conversion</label>
                                                                    <input type="text" pattern="[0-9]+([,\.][0-9]+)?"
                                                                        class="form-control" name="taux_conversion"
                                                                        value="{{ old('taux_conversion') }}"
                                                                        placeholder="Ex: 1 ou 2.6 (Nombre entier ou nombre à virgule)">
                                                                    @if (old('taux_conversion') && !preg_match('/^[0-9]+(?:\.[0-9]+)?$/', old('taux_conversion')))
                                                                        <div class="alert alert-danger">
                                                                            Le champ doit contenir des chiffres ou un nombre à
                                                                            virgule.
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="col-12">
                                                                    <label for="">Unité de mesure </label>
                                                                    <select name="unite_mesure_id" id="unite_mesure_id"
                                                                        class="form-control">
                                                                        <option value="">Choisir l'unité à convertir
                                                                        </option>
                                                                        @foreach ($unites as $unite)
                                                                            <option value="{{ $unite->id }}">
                                                                                {{ $unite->unite }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Fermer</button>
                                                                <button type="submit"
                                                                    class="btn btn-primary">Enregistrer</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div> --}}
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
