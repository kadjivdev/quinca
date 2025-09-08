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
                        <h5 class="modal-title fw-bold mb-0">Nouvelle recette</h5>
                        <p class="text-muted small mb-0" id="factureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="row p-3" action="{{route('reversements.store')}}" id="_addRequeteForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf

                <div class="col-6 mb-3">
                    <label for="recette">Recette</label>
                    <input type="number" required class="form-control" name="recette" id="recette" value="{{ old('recette') }}">

                    @error("recette")
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-6 mb-3">
                    <label for="date_recette">Date</label>
                    <input type="date" required class="form-control" name="date_recette" id="date_recette" value="{{ old('date_recette') }}">

                    @error("date_recette")
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-6 mb-3">
                    <label for="client_id">Dépôt</label>
                    <select required name="depot_id" id="depot_id" class="select2 form-select">
                        <option value="">Choisir le dépôt </option>
                        @foreach ($depots as $depot)
                        <option value="{{ $depot->id }}" {{ old('depot_id') == $depot->id ? 'selected' : '' }}>
                            {{ $depot->libelle_depot }}
                        </option>
                        @endforeach
                    </select>

                    @error("depot_id")
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-6 mb-3">
                    <label for="depense">Dépense</label>
                    <input required type="number" class="form-control" name="depense" id="depense" value="{{ old('depense') }}"></inpu>

                    @error("depense")
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-6 mb-3">
                    <label for="recette_to_reverse">Recette à verser</label>
                    <input type="number"  required class="form-control" name="recette_to_reverse" id="recette_to_reverse" value="{{ old('recette_to_reverse') }}"></input>

                    @error("recette_to_reverse")
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-6 mb-3">
                    <label for="montant_reversed">Montant reversé</label>
                    <input type="number" required class="form-control" name="montant_reversed" id="montant_reversed" value="{{ old('montant_reversed') }}"></input>

                    @error("montant_reversed")
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-6 mb-3">
                    <label for="montant_reversed">Preuve</label>
                    <input type="file" required class="form-control" name="preuve" id="preuve"></input>

                    @error("preuve")
                    <span class="text-danger">{{$message}}</span>
                    @enderror
                </div>

                <div class="col-12 mb-3">
                    <label for="mention">Commentaire</label>
                    <textarea required class="form-control" name="commentaire" id="commentaire">{{ old('commentaire') }}</textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="w-50 btn btn-sm btn-primary">Enregistrer</button>
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