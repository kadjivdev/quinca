@extends('layout.template')
@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Requête de transport</h1>
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
                            <h5 class="card-title">Détail de requête</h5>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="montant">Montant</label>
                                    <input type="number" class="form-control" name="montant" id="montant"  value="{{ $transport->montant }}" readonly>  
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="date_op">Date</label>
                                    <input type="text" class="form-control" name="date_op" id="date_op"  value="{{ $transport->date_op }}" readonly> 
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="client_id">Client</label>
                                    <input type="text" value="{{$transport->client->nom_client}}" readonly class="form-control" readonly>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="observation">Observation</label>
                                    <textarea class="form-control" name="observation" id="observation" readonly>{{ $transport->observation }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
