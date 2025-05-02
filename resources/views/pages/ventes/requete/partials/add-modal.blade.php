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
                        <h5 class="modal-title fw-bold mb-0">Nouvelle requete</h5>
                        <p class="text-muted small mb-0" id="factureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="row p-3" action="#" id="addRequeteForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <div class="col-6 mb-3">
                    <label for="num_demande">N° demande</label>
                    <input type="number" required class="form-control" name="num_demande" id="num_demande" value="{{ old('num_demande') }}">
                </div>

                <div class="col-6 mb-3">
                    <label for="montant">Montant</label>
                    <input type="number" required class="form-control" name="montant" id="montant" value="{{ old('montant') }}">
                </div>

                <div class="col-6 mb-3">
                    <label for="date_demande">Date</label>
                    <input type="date" required class="form-control" name="date_demande" id="date_demande" value="{{ old('date_demande') }}">
                </div>

                <div class="col-6 mb-3">
                    <label for="client_id">Client</label>
                    <select required name="client_id" id="client_id" class="select2 form-select">
                        <option value="">Choisir l'article </option>
                        @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->raison_sociale }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="nature">Nature de la demande</label>
                    <textarea required class="form-control" name="nature" id="nature">{{ old('nature') }}</textarea>
                </div>

                <div class="col-12 mb-3">
                    <label for="mention">Mention</label>
                    <textarea required class="form-control" name="mention" id="mention">{{ old('mention') }}</textarea>
                </div>

                <div class="col-12 mb-3">
                    <label for="formulation">Formulation de la demande</label>
                    <textarea required class="form-control" name="formulation" id="formulation">{{ old('formulation') }}</textarea>
                </div>

                <div class="col-12 mb-3">
                    <label for="articles">Motif</label>
                    <select name="motif" id="motif" class="select2 form-select" required>
                        <option value="">Choisir le motif </option>
                        <option value="Articles">Articles </option>
                        <option value="Autres">Autres </option>
                    </select>
                </div>

                <div class="col-12 mb-3 d-none" id="art_div">
                    <label for="articles">Articles</label>
                    <select name="articles[]" id="articles" multiple class="select2 form-select">
                        <option value="">Choisir l'article </option>
                        @foreach ($articles as $article)
                        <option value="{{ $article->id }}" {{ in_array($article->id, old('articles', [])) ? 'selected' : '' }}>
                           <span class="badge">{{ $article->designation }}</span>
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mb-3" id="autre_motif_div" style="display: none">
                    <label for="autre_motif">Contenu du motif</label>
                    <textarea class="form-control" name="autre_motif" id="autre_motif">{{ old('autre_motif') }}</textarea>
                    <br>
                    <input type="file" class="form-control" name="fichier">
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