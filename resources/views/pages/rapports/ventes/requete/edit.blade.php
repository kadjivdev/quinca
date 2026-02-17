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
    @include('pages.rapports.ventes.requete.partials.header')
    <div class="row g-3 list mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-center">
                    <a href="{{route('requete_stock.index')}}" class="btn btn-sm btn-primary float-right"><i class="fa fa-row"></i> Retour</a>
                </div>
                <br>
                <h4 class="">Requête à modifier : <span class="badge bg-light border rounded text-dark shadow">{{$requete->numero}}</span> </h4>
                <form action="{{route('requete_stock.update',$requete->id)}}" method="POST" id="editRequeteForm" class="needs-validation row p-3" novalidate enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <!-- article -->
                    <div class="col-md-6 mb-3">
                        <label for="article_id">Article</label>
                        <select required name="article_id" id="article_id" class="form-select">
                            <option value="">Choisir l'article </option>
                            @foreach ($articles as $article)
                            <option value="{{ $article->id }}"
                                data-depots="{{$article->depots}}"
                                data-unites="{{$article->unites}}"
                                {{ $requete->article_id == $article->id ? 'selected' : '' }}>
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
                        <select required name="depot_id" id="depot_id" class="form-select">
                            <!--  -->
                        </select>

                        @error('depot_id')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <!-- Unités de mesure -->
                    <div class="col-md-6 mb-3">
                        <label for="unite_mesure_id">Unités de mesure</label>
                        <select required name="unite_mesure_id" id="unite_mesure_id" class="form-select">
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
                            <input type="text" step="any" required class="form-control" name="quantite" id="quantite" value="{{ old('quantite',$requete->quantite) }}">
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
                        <textarea name="commentaire" id="commentaire" rows="2" class="form-control" placeholder="{{old('commenatire',$requete->commentaire)}}">{{$requete->commentaire}}</textarea>

                        @error('commentaire')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                    </div>

                    <div class="text-center">
                        <button type="submit" id="btnSaveRequete" class="w-50 btn btn-sm btn-primary"><i class="fas fa-pencil"></i> Enregistrer</button>
                        <div class="loader"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@include('pages.rapports.ventes.requete.partials.js-add-modal')
@endpush

@endsection