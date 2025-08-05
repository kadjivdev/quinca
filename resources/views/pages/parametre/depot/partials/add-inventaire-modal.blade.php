<div class="modal fade" id="addInventaireModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl bg-white">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-warehouse fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouveau inventaire</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('depot.inventairesStore',$depot->id)}}" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Date --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold required">Date d'inventaire</label>
                            <input type="date" required value="{{$date}}" class="form-control" name="date_inventaire">
                        </div>

                        <!-- Magasin associé -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-center">
                                <input type="search" placeholder="Rechercher ....." name="" class="form-control border bordered w-50" id="search">
                            </div>
                            <div class="d-flex justify-content-center">
                                <div class="form-check">
                                    <input type="checkbox" name="check_all_article" id="check_all_article" />
                                    <label class="form-check-label" for="check_all_article">
                                        Ramener tous les stocks à zéro(0)
                                    </label>
                                </div>
                                <!-- &nbsp;
                                <input type="number" name="all_qte_reel" id="all_qte_reel_input" class="form-control d-none"> -->
                            </div>
                            <div class="card-body">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0 " style="width: 10%;">Dépôt/Article</th>
                                            <th class="border-bottom-0 text-center">Unité de mesure</th>
                                            <th class="border-bottom-0 text-center">Qte actuelle</th>
                                            <th class="border-bottom-0 text-center">Qte réelle</th>
                                        </tr>
                                    </thead>
                                    <tbody class="" id="stock_depot_lignes">
                                        <!-- Géré avec du js -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer l'inventaire
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push("scripts")
<script type="text/javascript">
    $(document).ready(function() {
        $("#showAddInventaireModalBtn").on("click", function(e) {
            // Afficher l'animation de chargement
            Swal.fire({
                title: 'Chargement...',
                html: 'Récupération des détails des stocks',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            let depotId = "{{$depot->id}}";

            $.ajax({
                url: `${apiUrl}/parametres/depots/${depotId}/show`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Fermer le loader
                    Swal.close();

                    let data = response.data
                    if (response.success) {
                        let bodyHtml = ``

                        if (data.stocks.length > 0) {
                            data.stocks.forEach(stock => {
                                bodyHtml += `
                                        <tr class="stock-row">
                                            <td class="text-nowrap py-3" style="width: 10%;">
                                                <input type="checkbox" name="stock_depots[${stock.id}][checked]" class='article-check'/>
                                                <input hidden name="stock_depots[${stock.id}][depot_id]" value="${stock.depot.id}"/>
                                                <input hidden name="stock_depots[${stock.id}][id]" value="${stock.id}"/>
                                                <small>${stock.article.code_article}</small><br>
                                                <span class="badge bg-light text-dark numero-bl me-2">${stock.depot.libelle_depot.slice(0, 30) + '...'} / ${stock.article.designation.slice(0, 30) + '...'}</span>
                                            </td>
                                            <td class="text-center">
                                                <input hidden type="number" class="form-control" name="stock_depots[${stock.id}][unite_mesure_id]" value="${stock.unite_mesure_id}"/>
                                                <span class="badge bg-light text-dark">${stock.unite_mesure.libelle_unite}</span>
                                            </td>
                                            <td class="text-center">
                                                <input hidden type="number" class="form-control" name="stock_depots[${stock.id}][qte_stock]" value="${stock.quantite_reelle}"/>
                                                <span class="badge bg-light text-dark"> ${stock.quantite_reelle}</span>
                                            </td>
                                            <td class="text-center"> 
                                                <input type="text" class="form-control article_quantite_reelle_primitive" hidden value="${stock.quantite_reelle}"/> 
                                                <input type="text" class="form-control article_qte_reel_hidden" hidden name="stock_depots[${stock.id}][qte_reel]" value="${stock.quantite_reelle}"/> 
                                                <input type="text" class="form-control article_qte_reel" name="stock_depots[${stock.id}][qte_reel]" value="${stock.quantite_reelle}"/> 
                                            </td>
                                        </tr>
                                    `
                            });
                        } else {
                            bodyHtml = '<p class="">Aucun élement disponible</p>'
                        }
                        // Nettoyer le tbody avant d'ajouter les nouvelles lignes
                        $("#stock_depot_lignes").empty().append(bodyHtml)
                    } else {
                        Swal.close();
                        showError('Erreur de communication avec le serveur');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    showError('Erreur de communication avec le serveur');
                    console.error('Erreur AJAX:', xhr);
                }
            });
            $("#addInventaireModal").modal("show");
        })

        // Système de recherche dynamique
        $(document).on('input', '#search', function() {
            let search = $(this).val().toLowerCase();
            $("#stock_depot_lignes tr.stock-row").each(function() {
                let rowText = $(this).text().toLowerCase();
                if (rowText.indexOf(search) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        //Inventorier tous les articles
        $(document).on("click", '#check_all_article', function() {
            if ($(this).is(':checked')) {
                $("#all_qte_reel_input").removeClass("d-none")
                $(".article-check").prop("checked", true)

                //On fixe le champ reel à 0 et on le bloque 
                $(".article_qte_reel").val(0)
                $(".article_qte_reel").attr("disabled", true)

                //c'est la valeur cachée qui sera considérée
                $(".article_qte_reel_hidden").val(0)
            } else {
                $(".article-check").prop("checked", false)

                $(".article_qte_reel_hidden").val()
                //On restitue la quantité primitive
                $(".article_qte_reel").val($(".article_quantite_reelle_primitive").val())
                $(".article_qte_reel").attr("disabled", false)

                //on retire la valeur cachée, elle n'a plus d'importance
                $(".article_qte_reel_hidden").addClass("d-none")
            }
        })

        function showError(msg) {
            Swal.fire({
                title: 'Error',
                html: msg,
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    })
</script>
@endpush