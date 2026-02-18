<div class="modal fade" id="addRequeteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
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

            <form class="row p-3" action="{{route('requete_stock.store')}}" id="_addRequeteForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf

                <!-- article -->
                <div class="col-md-6 mb-3">
                    <label for="article_id">Article</label>
                    <select required name="article_id" id="article_id" class="select2 form-select">
                        <option value="">Choisir l'article </option>
                        @foreach ($articles as $article)
                        <option value="{{ $article->id }}"
                            data-article="{{$article}}"
                            data-depots="{{$article->depots}}"
                            data-unites="{{$article->unites}}"
                            {{ old('article_id') == $article->id ? 'selected' : '' }}>
                            {{ $article->code_article }}-({{$article->designation}})
                        </option>
                        @endforeach
                    </select>

                    @error('article_id')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <!-- Dépôts -->
                <div class="col-md-6 mb-3">
                    <label for="depot_id">Dépôt</label>
                    <select required name="depot_id" id="depot_id" class="select2 form-select">
                        <!-- -->
                    </select>

                    @error('depot_id')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <!-- Unités de mesure -->
                <div class="col-md-6 mb-3">
                    <label for="unite_mesure_id">Unités de mesure</label>
                    <select required name="unite_mesure_id" id="unite_mesure_id" class="select2 form-select">
                        <!--  -->
                    </select>

                    @error('unite_mesure_id')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <!-- Quantité -->
                <div class="col-md-6 mb-3">
                    <div class="">
                        <label for="quantite">Quantité</label>
                        <input type="text" step="any" required class="form-control" name="quantite" id="quantite" value="{{ old('quantite') }}">
                    </div>

                    @error('quantite')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <!-- Preuve -->
                <div class="col-md-12 mb-3">
                    <div class="">
                        <label>Preuve justificative</label>
                        <input type="file" class="form-control" name="preuve">
                    </div>

                    @error('preuve')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-12 mb-3">
                    <label for="commentaire">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="2" class="form-control"></textarea>

                    @error('commentaire')
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit" id="btnSaveRequete" class="w-50 btn btn-sm btn-primary">Enregistrer</button>
                    <div class="loader"></div>
                </div>
            </form>
        </div>
    </div>
</div>
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