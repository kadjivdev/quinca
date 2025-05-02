@extends('layout.template')
@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Requete</h1>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="row">

                <div class="col-lg-12">

                    <div class="card px-4">
                        <!-- Afficher des messages de succès -->
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Afficher des erreurs de validation -->
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">Détail de la requête N° {{$requete->num_demande}}</h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="num_demande">N° demande</label>
                                    <input type="number" class="form-control" name="num_demande" id="num_demande" value="{{ $requete->num_demande }}" readonly> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="montant">Montant</label>
                                    <input type="number" class="form-control" name="montant" id="montant"  value="{{ $requete->montant }}" readonly>  
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="date_demande">Date</label>
                                    <input type="text" class="form-control" name="date_demande" id="date_demande"  value="{{ $requete->date_demande }}" readonly> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="client_id">Client</label>
                                    <input type="text" value="{{$requete->client->nom_client}}" readonly class="form-control" readonly>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="nature">Nature de la demande</label>
                                    <textarea class="form-control" name="nature" id="nature" readonly>{{ $requete->nature }}</textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="mention">Mention</label>
                                    <textarea class="form-control" name="mention" id="mention" readonly>{{ $requete->mention }}</textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="formulation">Formulation de la demande</label>
                                    <textarea class="form-control" name="formulation" id="formulation" readonly>{{ $requete->formulation }}</textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="articles">Articles</label>
                                    <div class="form-control" style="height: auto;">
                                        <ul class="mb-0">
                                            @foreach ($requete->articles as $article)
                                                <li>{{ $article->nom }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        {{-- <script src="{{ asset('assets/js/jquery3.6.min.js') }}"></script> --}}
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script>
            $(".js-example-basic-multiple").select2();
        </script>
        <script>
            $(document).ready(function() {
                // Initialize Select2 for the provided_articles dropdown
                $('#magasins').select2({
                    placeholder: 'Selectionner les magasins',
                    ajax: {
                        url: '{{ route('magasins-list') }}',
                        dataType: 'json',
                        processResults: function(data) {
                            return {
                                results: data.magasins.map(function(magasin) {
                                    console.log(magasin);
                                    return {
                                        id: magasin.id,
                                        text: magasin.nom
                                    };
                                })
                            };
                        }
                    }
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $("#connectBtn").on("click", function() {
                    $(".myLoader").show();
                    setTimeout(function() {
                        $(".myLoader").hide();
                    }, 2000);
                });
            });
        </script>

    </main>
@endsection
