@extends('layouts.catalogue.article')

@push('styles')
@include('pages.catalogues.article.partials.styles')
@endpush

@section('content')
<div class="content">
    {{-- En-tête de la page --}}
    @include('pages.catalogues.article.partials.header')

    {{-- Liste des articles --}}
    <div class="row g-3 list mt-3" id="articlesList">

        <!-- gestion des erreurs -->
        @if(session()->has("success"))
        <div class="alert alert-success"> {{session()->get("success")}} </div>
        @elseif(session()->has("error"))
        <div class="alert alert-danger"> {{session()->get("error")}} </div>
        @endif

        <!-- erreurs de validation -->
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- end errors -->

        <div class="row justify-content-center d-flex">
            <div class="col-8">
                <form method="POST" action="{{route('articles.affect',$article->id)}}" class="border rounded" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-4">

                            <div class="col-12">
                                <h5 class="">Les dépôts déjà associés :</h5>
                                @forelse($article->stocks as $stock)
                                <span class="badge bg-warning"> {{$stock->depot->libelle_depot}} <strong class="text-dark"> Stock : {{number_format($stock->quantite_reelle,2,"."," ")}} </strong> </span>;
                                @empty
                                <span class="badge bg-light text-dark">Aucun dépôt</span>
                                @endforelse
                            </div>
                            @php
                            $attached_depotIds = $article->stocks->pluck("depot_id")->toArray();
                            @endphp
                            <div class="col-12">
                                <div class="">
                                    <h5 class="text">Mis à jour des dépôts </h5>
                                    <h5 class="text">Article : <span class="badge bg-warning">{{$article->code_article}} {{$article->designation}}</span> </h5>
                                </div>

                                <br>
                                <div class="form-group mb-3">
                                    <label for="">Choisir un dépôt <span class="text-danger">*</span> </label>
                                    <select multiple required id="update_depots" class="form-control select2" name="depots[]">
                                        <option >***Tous les dépôts***</option>
                                        @foreach($depots as $depot)
                                        <option @class(["bg-secondary text-white"=>in_array($depot->id,$attached_depotIds)]) @disabled(in_array($depot->id,$attached_depotIds)) value="{{$depot->id}}">{{$depot->libelle_depot}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="">La quantité du stock <span class="text-danger">*</span> </label>
                                    <input type="number" required name="quantite_reelle" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-center text-center bg-light border-top-0 py-3">
                        <a href="{{route('articles.index')}}" class="mx-2 btn btn-secondary px-4">
                            <i class="fas fa-times me-2"></i>Retour
                        </a>
                        <button type="submit" class="btn btn-primary px-4" id="editSubmitBtn">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>