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
    @include('pages.ventes.requete.partials.header')
    <div class="row g-3 list mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="modal-body p-4">
                    {{-- Informations principales --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="">
                                <a href="{{route('requetes.index')}}" class="btn btn-sm btn-primary float-right"><i class="bi bi-arrow-left-circle"></i> Retour</a>
                            </div>
                            <br>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        {{-- NUM demande (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Numéro de demande</label>
                                            <input type="text" class="form-control" readonly name="num_demande" value="{{$requete->num_demande}}">
                                        </div>

                                        {{-- Montant --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-end"
                                                    name="montant" id="edit_montant"
                                                    required step="0.001" min="0" readonly value="{{$requete->montant}}">
                                                <span class="input-group-text">F CFA</span>
                                            </div>
                                        </div>

                                        {{-- Date de demande --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date du règlement</label>
                                            <input type="date" class="form-control"
                                                name="date_demande"
                                                id="edit_date_demande" readonly value="{{$requete->date_demande}}">
                                        </div>

                                        {{-- NATURE (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Nature</label>
                                            <textarea rows="1" readonly name="nature" id="edit_nature" class="form-control">{{$requete->nature}}</textarea>
                                        </div>

                                        {{-- Mention (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Mention</label>
                                            <textarea rows="1" readonly name="mention" id="edit_mention" class="form-control">{{$requete->mention}}</textarea>
                                        </div>

                                        {{-- Formulation (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Formulation</label>
                                            <textarea rows="1" readonly name="formulation" id="edit_formulation" class="form-control">{{$requete->formulation}}</textarea>
                                        </div>

                                        {{-- Client (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Client</label>
                                            <input type="text" readonly class="form-control" value="{{$requete->client->raison_sociale}}">
                                        </div>

                                        {{-- Article (readonly) --}}
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Articles</label>
                                            <textarea rows="2" name="" id="" class="form-control">
                                                @foreach($requete->articles as $article)
                                                {{$article->designation}};
                                                @endforeach
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection