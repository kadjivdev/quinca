@extends('layout.template')
@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Transport</h1>
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
                            <h5 class="card-title">Ajouter une requête de transport</h5>

                            <!-- Vertical Form -->
                            <form class="row px-3" action="{{ route('transports.store') }}" method="POST">
                                @csrf
                                <div class="col-6 mb-3">
                                    <label for="montant">Montant</label>
                                    <input type="number" class="form-control" name="montant" id="montant" required value="{{ old('montant') }}"> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="date_op">Date</label>
                                    <input type="date" class="form-control" name="date_op" id="date_op" required value="{{ old('date_op') }}"> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="client_id">Client</label>
                                    <select name="client_id" id="client_id" class="js-example-basic-multiple form-select" required>
                                        <option value="">Choisir le client </option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->nom_client }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="observation">Observation</label>
                                    <textarea class="form-control" name="observation" id="observation">{{ old('observation') }}</textarea>
                                </div>
                                
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

    </main>
@endsection
