@extends('layout.template')
@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Proforma</h1>
        </div><!-- End Page Title -->
        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
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
                            <h5 class="card-title">Ajouter un Proforma</h5>
                            <!-- Vertical Form -->
                            <form class="row g-3" action="{{ route('devis.store') }}" method="POST">
                                @csrf
                                <div class="col-6">
                                    <label class="form-label">Client</label>
                                    <select class="js-data-example-ajax form-control" name="client_id"
                                        id="client_select"></select>
                                </div>

                                <div class="col-3">
                                            <label class="form-label">Date</label>
                                            <input type="date" name="date_pf" id="data_pf" class="form-control">
                                        </div>

                                <fieldset>
                                    <legend> Articles à ajouter:</legend>
                                    <div class="row">
                                        <div class="col-3">
                                            <label class="form-label">Choisir l'article</label>
                                            <select class="form-select form-control test" name="article_id"
                                                id="articleSelect">
                                                <option value="">Choisir l'article </option>
                                                @foreach ($articles as $article)
                                                    <option data-prixVente="{{ $article->prix_special }}"
                                                        value="{{ $article->id }}"> {{ $article->nom }} </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-2">
                                            <label class="form-label">Quantité</label>
                                            <input type="text" name="qte" id="qte" class="form-control">
                                        </div>

                                        <div class="col-2">
                                            <label class="form-label">Prix unitaire</label>
                                            <input type="text" name="prix" id="prix" class="form-control">
                                        </div>
                                        <div class="col-2">
                                            <label class="form-label">Unité</label>
                                            <select class="form-select" name="unite_id" id="uniteSelect">

                                            </select>
                                        </div>


                                        <div class="col-2 py-2">
                                            <button class="btn btn-primary mt-4" type="button" id="ajouterArticle">
                                                Ajouter</button>
                                        </div>
                                    </div>
                                </fieldset>

                                <div id="dynamic-fields-container">
                                    <table id="editableTable" class="table table-responsive">
                                        <thead>
                                            <tr>
                                                <th>Article</th>
                                                <th>Quantité</th>
                                                <th>Prix unit</th>
                                                <th>Unité mesure</th>
                                                <th>Montant</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td colspan="2">Total HT</td>
                                                <td colspan="3"><input type="text" id="totalInput"
                                                        class="form-control" name="montant_facture" readonly>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
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
        <script>
            $(document).ready(function() {
                $(".articles").select2({
                    placeholder: "Selectionner un article",
                    allowClear: true
                });
                $("#connectBtn").on("click", function() {
                    $(".myLoader").show();
                    setTimeout(function() {
                        $(".myLoader").hide();
                    }, 2000); // 2000 millisecondes (2 secondes) dans cet exemple
                });
            });
        </script>
    </main>
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" /> --}}

    <script src="{{ asset('assets/js/jquery3.6.min.js') }}"></script>
    <script src="{{ asset('assets/js/mindmup-editabletable.js') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script>
         var apiUrl = "{{ config('app.url_ajax') }}";
        $(document).ready(function() {
            // Initialize Select2 for the provided_articles dropdown
            $('.js-data-example-ajax').select2({
                placeholder: 'Selectionner client',
                ajax: {
                    url: apiUrl + '/cltListAjax',
                    dataType: 'json',
                    data: function(params) {
                        console.log(params);
                        return {
                            term: params.term // search term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.clients.map(function(frs) {
                                return {
                                    id: frs.id,
                                    text: frs.nom_client
                                };
                            })
                        };
                    }
                }
            });
        });
    </script>
    <script>
         var apiUrl = "{{ config('app.url_ajax') }}";

        $('#editableTable tbody').on('input', 'input[name^="qte_cdes"], input[name^="prixUnits"]', function() {
            calculateTotal();
        });

        function calculateAmount(row) {
            const prixUnit = parseFloat(row.find('input[name^="prixUnits').val()) || 0;
            const qteCmde = parseFloat(row.find('input[name^="qte_cdes"]').val()) || 0;
            const montant = prixUnit * qteCmde;
            row.find('input[name^="montants"]').val(montant.toFixed(2));
            calculateTotal();
        }

        function calculateTotal() {
            var total = 0;

            $('#editableTable tbody tr').each(function() {
                const montant = parseFloat($(this).find('input[name^="montants"]').val()) || 0;
                total += montant;
            });
            $('#totalInput').val(total.toFixed(2));
        }
        $(document).ready(function() {
            $('#articleSelect').select2({
                width: 'resolve' // Ajuste la largeur en fonction du parent
            });

            $('#articleSelect').on('change', function() {
                var articleId = $(this).val();
                console.log(articleId, 'id article');
                if (articleId) {
                    $.ajax({
                        url:  apiUrl + '/getUnitesByArticle/' + articleId,
                        type: 'GET',
                        success: function(data) {
                            console.log(data);
                            var options = '<option value="">Choisir l\'unité</option>';
                            for (var i = 0; i < data.unites.length; i++) {
                                options += '<option value="' + data.unites[i].id + '">' + data
                                    .unites[i].unite + '</option>';
                            }
                            $('#uniteSelect').html(options);
                        },
                        error: function(error) {
                            console.log('Erreur de la requête Ajax :', error);
                        }
                    });
                } else {
                    $('#uniteSelect').html('<option value="">Choisir l\'unité</option>');
                }
            });

            // Initialiser le tableau éditable
            $('#editableTable').editableTableWidget();

            // Écouteur d'événement pour le bouton Ajouter
            $('#ajouterArticle').click(function() {
                // Récupérer les valeurs des champs
                var articleId = $('#articleSelect').val();
                var articleNom = $('#articleSelect option:selected').text();
                var uniteId = $('#uniteSelect option:selected').val();
                var uniteNom = $('#uniteSelect option:selected').text();
                var prix = $('#prix').val();
                var quantite = $('#qte').val();
                var total = prix * quantite;
                var prixMin = $('#articleSelect option:selected').attr('data-prixVente');
                $('#prix').attr('min', prixMin);

                // Ajouter une nouvelle ligne au tableau
                var newRow = `
                    <tr>
                        <td>${articleNom}<input type="hidden" required name="articles[]" value="${articleId}"></td>
                        <td>${quantite} <input type="hidden" required name="qte_cdes[]" value="${quantite}"</td>
                        <td>${prix} <input type="hidden" required name="prixUnits[]" value="${prix}"</td>
                        <td>${uniteNom} <input type="hidden" required name="unites[]" value="${uniteId}"</td>
                        <td>${total} <input type="hidden" required name="montants[]" value="${total}"</td>
                        <td><button type="button" class="btn btn-danger btn-sm delete-row">Supprimer</button></td>
                    </tr>`;

                $('#editableTable tbody').append(newRow);
                calculateTotal();

                // Effacer les champs après l'ajout
                $('#articleSelect').val(null).trigger('change');
                $('#uniteSelect').val('');
                $('#prix').val('');
                $('#qte').val('');
            });

            // Écouteur d'événement pour le bouton Enregistrer
            $('#enregistrerVente').click(function() {
                // Soumettre le formulaire avec les données du tableau
                $('#venteForm').submit();
            });

            // Écouteur d'événement pour le clic sur le bouton Supprimer
            $('#editableTable').on('click', '.delete-row', function() {
                $(this).closest('tr').remove();
                calculateTotal();

            });
        });
    </script>
@endsection
