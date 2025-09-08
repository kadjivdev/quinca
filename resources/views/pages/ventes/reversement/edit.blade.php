@extends('layouts.ventes.reglement')

@push('styles')
<style>
    /* Z-index fixes */
    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
    }

    /* Select2 en dehors du modal */
    .main-content .select2-container {
        z-index: 1000 !important;
    }

    .main-content .select2-dropdown {
        z-index: 1001 !important;
    }

    /* Select2 dans le modal */
    .modal .select2-container {
        z-index: 2000 !important;
    }

    .modal .select2-dropdown {
        z-index: 2001 !important;
    }

    /* Select2 styling */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #dee2e6;
    }

    /* Pour masquer les select2 quand le modal est ouvert */
    .modal-open .main-content .select2-container {
        display: none;
    }
</style>
@endpush

@section('content')

<div class="content">
    @include('pages.ventes.reversement.partials.header')
    <div class="row g-3 list mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="">
                    <a href="{{route('reversements.index')}}" class="btn btn-sm btn-primary float-right"><i class="bi bi-arrow-left-circle"></i> Retour</a>
                </div>
                <br>
                <form action="{{route('reversements.update',$reversement->id)}}" method="POST" id="editRequeteForm" class="needs-validation" novalidate enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    @csrf

                    <div class="row">

                        <div class="col-6 mb-3">
                            <label for="recette">Recette</label>
                            <input type="number" required class="form-control" name="recette" id="recette" value="{{ old('recette',$reversement->recette) }}">

                            @error("recette")
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-6 mb-3">
                            <label for="date_recette">Date</label>
                            <input type="date" required class="form-control" name="date_recette" id="date_recette" value="{{ old('date_recette',$reversement->date_recette) }}">

                            @error("date_recette")
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-6 mb-3">
                            <label for="client_id">Dépôt</label>
                            <select required name="depot_id" id="depot_id" class="select2 form-select">
                                <option value="">Choisir le dépôt </option>
                                @foreach ($depots as $depot)
                                <option value="{{ $depot->id }}" {{ $reversement->depot_id == $depot->id ? 'selected' : '' }}>
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
                            <input required placeholder="Ex: 567 567,89" type="number" class="form-control" value="{{old('depense',$reversement->depense)}}" name="depense" id="depense"></input>

                            @error("depense")
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-6 mb-3">
                            <label for="recette_to_reverse">Recette à verser</label>
                            <input type="number" placeholder="Ex: 567 567,89" required class="form-control" name="recette_to_reverse" id="recette_to_reverse" value="{{ old('recette_to_reverse',$reversement->recette_to_reverse) }}"></input>

                            @error("recette_to_reverse")
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-6 mb-3">
                            <label for="montant_reversed">Montant reversé</label>
                            <input type="number" placeholder="Ex: 567 567,89" required class="form-control" name="montant_reversed" id="montant_reversed" value="{{ old('montant_reversed',$reversement->montant_reversed) }}"></input>

                            @error("montant_reversed")
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-6 mb-3">
                            <label for="preuve">Preuve</label>
                            <input type="file" class="form-control" name="preuve" id="preuve"></input>

                            @error("preuve")
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="mention">Commentaire</label>
                            <input required class="form-control" name="commentaire" id="commentaire" value="{{ old('commentaire',$reversement->commentaire) }}"></input>
                        </div>
                    </div>

                    <div class="row justify-content-center d-flex">
                        <div class="col-6">
                            <button type="button" class="btn btn-light">
                                <i class="fas fa-times me-2"></i>Annuler
                            </button>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push("scripts")
<script type="text/javascript">
    $(".edit-select2").select2({
        theme: 'bootstrap-5',
        placeholder: 'Sélectionner un client',
        width: '100%',
    })
</script>
@endpush

@endsection