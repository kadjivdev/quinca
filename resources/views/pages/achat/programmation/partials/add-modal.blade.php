<div class="modal fade" id="addProgrammationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-clipboard-list fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle Précommande</h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer une nouvelle
                            précommande</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addProgrammationForm" class="needs-validation" novalidate>
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
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Code</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-hashtag text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control" name="code" id="code"
                                                    required readonly>
                                            </div>
                                            <div class="invalid-feedback">Le code est requis</div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Date precommande</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_programmation"
                                                    required value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <label class="form-label fw-medium required my-1" for="">Fournisseur</label>
                                                <div class="input-group">
                                                    <select class="form-select select2" name="fournisseur_id" id="_fournisseurSelect"
                                                        required>
                                                        <option value="">Selectionner un fournisseur</option>
                                                        @foreach ($fournisseurs as $fournisseur)
                                                        <option value="{{ $fournisseur->id }}">
                                                            {{ $fournisseur->raison_sociale }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="invalid-feedback">Le fournisseur est requis</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Choisir un depot --}}
                        <!-- <div class="col-md-12">
                            <div class="card border">
                                <div class="card-header bg-light border-light-subtle d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i>Les Dépôts de votre point de vente <span class="text-danger">*</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <input type="number" id="depot_id" name="depot" class="form-control d-none">
                                    
                                    <select class="form-control form-select select2" required id="depot_select" required>
                                        <option value="">Selectionner un dépôt</option>
                                        @foreach (auth()->user()->pointDeVente->depot as $depot)
                                        <option value="{{ $depot }}">
                                            {{ $depot->libelle_depot }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div> -->


                        {{-- Section articles --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i>Articles
                                    </h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnAddLigne">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 50%">Article</th>
                                                    <th style="width: 25%">Quantité</th>
                                                    <th style="width: 20%">Unité</th>
                                                    <th style="width: 5%"></th>
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

                        {{-- Section commentaire --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Commentaire
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="commentaire" rows="3" placeholder="Commentaire éventuel"></textarea>
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
<template id="ligneProgrammationTemplate">
    <tr class="ligne-programmation hover:bg-gray-50 transition-colors duration-200">
        <td class="p-2">
            <div class="input-group">
                <!-- <label class="d-block form-label fw-medium required my-1" for="">Article</label> -->
                <select class="form-select  select2-articles articles_select" name="articles[]" required>
                    <option value="">Selectionner un article</option>
                    @foreach($articles as $article)
                    <option value="{{$article->id}}">
                        {{$article->code_article }} {{$article->designation }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="invalid-feedback">L'article est requis</div>
        </td>
        <td class="p-2">
            <input type="number" class="form-control text-end articleQte" name="quantites[]" placeholder="0.00" required
                min="0.01" step="0.01">
            <div class="invalid-feedback">La quantité est requise</div>
        </td>
        <td class="p-2">
            <select class="form-select select2" name="unites[]" required>
                <option value="">Sélectionner une unité</option>
                @foreach ($unitesMesure as $unite)
                <option value="{{ $unite->id }}">
                    {{ $unite->code_unite }} - {{ $unite->libelle_unite }}
                </option>
                @endforeach
            </select>
            <div class="invalid-feedback">L'unité est requise</div>
        </td>
        <td class="p-2 text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-ligne">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
    $(document).ready(function() {
        // 
        // $("#depot_select").on('change', function() {
        //     if ($(this).val()) {
        //         $("#articles-bloc").removeClass("d-none");

        //         let depot = JSON.parse($(this).val());

        //         $("#depot_id").val(depot.id)

        //         // Stockage du depot dans une session
        //         localStorage.setItem("depot", $(this).val())

        //         $(".articles_select").empty()

        //         let rows = `<option value="">Selectionner un article</option>`
        //         if (depot.articles.length > 0) {
        //             depot.articles.forEach(article => {
        //                 rows += `
        //                     <option ${(!article.reste || article.reste<0)? "disabled" :''} value="${ article.id }">
        //                         ${ article.code_article } ${ article.designation } - Reste : ${article.reste}
        //                     </option>
        //                     `
        //             });
        //         }
        //         $(".articles_select").append(rows)
        //     } else {
        //         $("#articles-bloc").addClass("d-none")
        //     }
        // })

        // let articles = JSON.parse({
        //     {
        //         $articles
        //     }
        // });
        // $(".articles_select").empty();
        // let rows = `<option value="">Selectionner un article</option>`
        // if (articles.length > 0) {
        //     articles.forEach(article => {
        //         rows += `
        //                     <option value="${ article.id }">
        //                         ${ article.code_article } ${ article.designation }
        //                     </option>
        //                     `
        //     });
        // }
        // $(".articles_select").append(rows)


        // Initialisation de Select2 avec gestion d'erreur
        try {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addProgrammationModal')
            });
        } catch (e) {
            console.error('Erreur initialisation Select2:', e);
        }

        // Charger les articles quand le fournisseur change
        $('#_fournisseurSelect').on('change', function() {
            const fournisseurId = $(this).val();
            if (fournisseurId) {
                loadArticles(fournisseurId);
            }
        });

        // Ajouter une nouvelle ligne
        // $('#btnAddLigne').on('click', function() {
        //     let _depot = localStorage.getItem("depot");

        //     let depot = JSON.parse(_depot)

        //     let rows = ``
        //     if (depot.articles.length > 0) {
        //         depot.articles.forEach(article => {
        //             rows += `
        //                     <option ${(!article.reste || article.reste<0)? "disabled" :''} value="${ article.id }">
        //                         ${ article.code_article } ${ article.designation } - Reste : ${article.reste}
        //                     </option>
        //                     `
        //         });
        //     }
        //     $(".articles_select").append(rows)
        // });

        // Supprimer une ligne
        $(document).on('click', '.remove-ligne', function() {
            $(this).closest('tr').remove();
        });

        // Soumission du formulaire
        $('#addProgrammationForm').on('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                saveProgrammation($(this));
            }

            $(this).addClass('was-validated');
        });
    });

    function loadArticles(fournisseurId) {
        $.ajax({
            url: `${apiUrl}/achat/programmations/fournisseurs/${fournisseurId}/articles`,
            method: 'GET',
            success: function(response) {
                const articles = response;
                updateArticlesOptions(articles);
            }
        });
    }

    function updateArticlesOptions(articles) {
        let options = '<option value="">Sélectionner un article ...</option>';
        articles.forEach(article => {
            options += `<option value="${article.id}" data-unites='${JSON.stringify(article.unites)}'>
                    ${article.designation}
                </option>`;
        });
        $('.select2-articles').html(options);
    }
</script>
@endpush