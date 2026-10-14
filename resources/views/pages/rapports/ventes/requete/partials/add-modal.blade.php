<div class="modal fade" id="addRequeteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle requete de stock</h5>
                        <p class="text-muted small mb-0" id="factureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="row p-3" action="{{ route('requete_stock.store') }}" id="_addRequeteForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf

                @if ($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                {{-- Lignes de la requête --}}
                <div class="col-12">
                    <button type="button" id="btnAddLigne" class="btn btn-sm btn-primary mb-3">
                        <i class="fas fa-plus me-1"></i> Ajouter une ligne
                    </button>

                    <div id="lignesContainer"></div>
                </div>

                <div class="col-12 mb-3">
                    <label for="commentaire">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="2" class="form-control">{{ old('commentaire') }}</textarea>
                    @error('commentaire') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="text-center">
                    <button type="submit" id="btnSaveRequete" class="w-50 btn btn-sm btn-primary">Enregistrer</button>
                    <div class="loader"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- template -->
{{-- Modèle d'une ligne (non envoyé tant qu'il n'est pas cloné) --}}
<template id="ligneTemplate">
    <div class="ligne-requete border rounded p-2 mb-2">
        <div class="row g-2 align-items-center">
            <div class="col-md-2">
                <label>Article</label>
                <select required name="lignes[__INDEX__][article_id]" class="form-select article-select" data-placeholder="Sélectionner un article">
                    <option value="">Choisir l'article</option>
                    @foreach ($articles as $article)
                    <option value="{{ $article->id }}"
                        data-article="{{ $article }}"
                        data-depots="{{ $article->depots }}">
                        {{ $article->code_article }}-({{ $article->designation }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label>Dépôt</label>
                <select required name="lignes[__INDEX__][depot_id]" class="form-select depot-select" data-placeholder="Sélectionner un dépôt"></select>
            </div>

            <div class="col-md-2">
                <label>Unité de mesure</label>
                <select required name="lignes[__INDEX__][unite_mesure_id]" class="form-select unite-select" data-placeholder="Sélectionner une unité"></select>
            </div>

            <div class="col-md-2">
                <label>Quantité</label>
                <input type="number" step="any" required class="form-control quantite-input" name="lignes[__INDEX__][quantite]">
            </div>

            <div class="col-md-2">
                <label>Preuve</label>
                <input type="file" class="form-control" name="lignes[__INDEX__][preuve]" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
            </div>

            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-ligne" title="Supprimer la ligne">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<link href="{{ asset('css/theme/modal.css') }}" rel="stylesheet">
<style>
    .required:after {
        content: " *";
        color: red;
    }

    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f8f9fa;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
</style>