<div class="modal fade" id="addFactureProformaModal"  tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg modal-dialog-scrollable" style="overflow-y: scroll!important;">
            {{-- Header du modal avec un nouveau design --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle Facture Proforma</h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer une nouvelle
                            facture</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('proforma.store')}}" method="POST" id="addFactureProformaForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section informations générales --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations Générales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Client</label>
                                            <div class="input-group">
                                                <select class="form-select select2" name="client_id" required>
                                                    <option value="">Sélectionner un client</option>
                                                    @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}"
                                                        data-taux-aib="{{ $client->taux_aib }}">
                                                        {{ $client->raison_sociale }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le client est requis</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date facture</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_pf" required
                                                    value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section articles --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div class="row">
                                        <div class="col-4">
                                            <label class="form-label">Choisir l'article</label>
                                            <select class="form-select form-control test select2" name="article_id"
                                                id="articleSelect">
                                                <option value="">Choisir l'article </option>
                                                @foreach ($articles as $article)
                                                <option data-prixVente="{{ $article->prix_special }}"
                                                    value="{{ $article->id }}"> {{ $article->designation }} </option>
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
                                        <div class="col-4">
                                            <label class="form-label">Unité</label>
                                            <select class="form-select select2" name="unite_id" id="uniteSelect">
                                                @foreach($unites_mesures as $unite)
                                                <option value="{{$unite->id}}">{{$unite->libelle_unite}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm" id="ajouterArticle">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="editableTable" class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Article</th>
                                                    <th>Quantité</th>
                                                    <th>Prix Unit</th>
                                                    <th>Unité de mesure</th>
                                                    <th>Montant</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="lignesContainer">
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Template pour une nouvelle ligne --}}
<template id="ligneFactureTemplate">
    <tr class="ligne-facture">
        <td>
            <select class="form-select select2-articles" name="lignes[__INDEX__][article_id]" required>
                <option value="">Sélectionner un article</option>
            </select>
            <div class="invalid-feedback">L'article est requis</div>
        </td>
        <td>
            <div class="input-group">
                <input type="number" class="form-control text-end quantite-input" name="lignes[__INDEX__][quantite]"
                    placeholder="0.00" required min="0.01" step="0.01">
                <select class="form-select unite-select" name="lignes[__INDEX__][unite_vente_id]" hidden required>
                    {{-- <option value="">Unité</option> --}}
                </select>
            </div>
            <div class="invalid-feedback">La quantité est requise</div>
        </td>
        <td>
            <input type="number"
                class="form-control text-end select2-tarifs"
                name="lignes[__INDEX__][tarification_id]"
                placeholder="0.00"
                required
                min="0.01"
                step="0.01">
            <div class="invalid-feedback">Le prix est requis</div>
        </td>
        <td>
            <input type="number" class="form-control text-end remise-input" name="lignes[__INDEX__][taux_remise]"
                placeholder="0.00" min="0" max="100" step="0.01">
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">FCFA</span>
                <input type="text" class="form-control text-end total-ligne" readonly value="0">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-ligne">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push("scripts")
<script>
    $(".select2").select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: addFactureProformaModal,
    })
</script>

<script>
    var apiUrl = "{{ config('app.url_ajax') }}";

    // $('#editableTable tbody').on('input', 'input[name^="qte_cdes"], input[name^="prixUnits"]', function() {
    //     calculateTotal();
    // });

    // function calculateAmount(row) {
    //     const prixUnit = parseFloat(row.find('input[name^="prixUnits').val()) || 0;
    //     const qteCmde = parseFloat(row.find('input[name^="qte_cdes"]').val()) || 0;
    //     const montant = prixUnit * qteCmde;
    //     row.find('input[name^="montants"]').val(montant.toFixed(2));
    //     calculateTotal();
    // }

    // function calculateTotal() {
    //     var total = 0;

    //     $('#editableTable tbody tr').each(function() {
    //         const montant = parseFloat($(this).find('input[name^="montants"]').val()) || 0;
    //         total += montant;
    //     });
    //     $('#totalInput').val(total.toFixed(2));
    // }

    $(document).ready(function() {
        // $('#articleSelect').on('change', function() {
        //     // $('#uniteSelect').empty()
        //     var articleId = $(this).val();
        //     console.log(articleId)
        //     if (articleId) {
        //         $.ajax({
        //             url: apiUrl + '/vente/factures/articles/' + articleId + '/unites',
        //             type: 'GET',
        //             success: function(data) {
        //                 // console.log(data);
        //                 // console.log(data.unites)
        //                 var options = '<option value="">Choisir l\'unité</option>';
        //                 for (var i = 0; i < data.unites.length; i++) {
        //                     options += '<option value="' + data.unites[i].id + '">' + data
        //                         .unites[i].unite + '</option>';
        //                 }
        //                 $('#uniteSelect').html(options);
        //             },
        //             error: function(error) {
        //                 console.log('Erreur de la requête Ajax :', error);
        //             }
        //         });
        //     } else {
        //         $('#uniteSelect').html('<option value="">Choisir l\'unité</option>');
        //     }
        // });

        // Initialiser le tableau éditable
        // $('#editableTable').editableTableWidget();

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
                <td><button type="button" class="btn btn-danger btn-sm delete-row"><i class="bi bi-trash3"></i> Supprimer</button></td>
            </tr>`;

            $('#lignesContainer').append(newRow);
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
        $('#lignesContainer').on('click', '.delete-row', function() {
            $(this).closest('tr').remove();
            calculateTotal();
        });
    });
</script>
@endpush