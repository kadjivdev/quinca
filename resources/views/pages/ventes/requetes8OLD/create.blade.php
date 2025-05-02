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
                            <h5 class="card-title">Ajouter une requête</h5>

                            <!-- Vertical Form -->
                            <form class="row px-3" action="{{ route('requetes.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="col-6 mb-3">
                                    <label for="num_demande">N° demande</label>
                                    <input type="number" class="form-control" name="num_demande" id="num_demande" value="{{ old('num_demande') }}"> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="montant">Montant</label>
                                    <input type="number" class="form-control" name="montant" id="montant"  value="{{ old('montant') }}"> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="date_demande">Date</label>
                                    <input type="date" class="form-control" name="date_demande" id="date_demande"  value="{{ old('date_demande') }}"> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="client_id">Client</label>
                                    <select name="client_id" id="client_id" class="js-example-basic-multiple form-select">
                                        <option value="">Choisir l'article </option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->nom_client }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="nature">Nature de la demande</label>
                                    <textarea class="form-control" name="nature" id="nature">{{ old('nature') }}</textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="mention">Mention</label>
                                    <textarea class="form-control" name="mention" id="mention">{{ old('mention') }}</textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="formulation">Formulation de la demande</label>
                                    <textarea class="form-control" name="formulation" id="formulation">{{ old('formulation') }}</textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="articles">Motif</label>
                                    <select name="motif" id="motif" class="js-example-basic-multiple form-select" required>
                                        <option value="">Choisir le motif </option>
                                        <option value="Articles">Articles </option>
                                        <option value="Autres">Autres </option>
                                    </select>
                                </div>

                                <div class="col-12 mb-3"  id="art_div">
                                    <label for="articles">Articles</label>
                                    <select name="articles[]" id="articles" multiple class="js-example-basic-multiple form-select">
                                        <option value="">Choisir l'article </option>
                                        @foreach ($articles as $article)
                                            <option value="{{ $article->id }}" {{ in_array($article->id, old('articles', [])) ? 'selected' : '' }}>
                                                {{ $article->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3" id="autre_motif_div" style="display: none">
                                    <label for="autre_motif">Contenu du motif</label>
                                    <textarea class="form-control" name="autre_motif" id="autre_motif">{{ old('autre_motif') }}</textarea>
                                </div>

                                <input type="file" name="fichier">
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    <div class="loader"></div>

                                    <button type="reset" class="btn btn-secondary">Annuler</button>
                                </div>
                            </form>

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
            
            $("#art_div").hide();
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

        <script>
            $("#motif").on('change', function() {
                if (this.value == 'Articles'){
                    $("#art_div").show();
                    $("#autre_motif_div").hide();
                }else if(this.value == 'Autres'){
                    $("#autre_motif_div").show();
                    $("#art_div").hide();
                }
            });
        </script>

    </main>
@endsection
