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
                <form action="{{route('requetes.update',$requete->id)}}" method="POST" id="editRequeteForm" class="needs-validation" novalidate>
                    @csrf
                    @method('PATCH')
                    <!-- <input type="hidden" name="reglement_id" id="editReglementId"> -->

                    <div class="modal-body p-4">
                        {{-- Informations principales --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            {{-- NUM demande (readonly) --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Numéro de demande</label>
                                                <input type="text" class="form-control" name="num_demande" value="{{$requete->num_demande}}">
                                            </div>

                                            {{-- Montant --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium required">Montant</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control text-end"
                                                        name="montant" id="edit_montant"
                                                        required step="0.001" min="0" value="{{$requete->montant}}">
                                                    <span class="input-group-text">F CFA</span>
                                                </div>
                                                <div class="invalid-feedback">Veuillez saisir un montant valide</div>
                                            </div>

                                            {{-- Date de demande --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium required">Date du règlement</label>
                                                <input type="date" class="form-control"
                                                    name="date_demande"
                                                    id="edit_date_demande" value="{{$requete->date_demande}}">
                                                <div class="invalid-feedback">Veuillez sélectionner une date</div>
                                            </div>

                                            {{-- NATURE (readonly) --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Nature</label>
                                                <textarea rows="1" name="nature" id="edit_nature" class="form-control">{{$requete->nature}}</textarea>
                                            </div>

                                            {{-- Mention (readonly) --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Mention</label>
                                                <textarea rows="1" name="mention" id="edit_mention" class="form-control">{{$requete->mention}}</textarea>
                                            </div>

                                            {{-- Formulation (readonly) --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Formulation</label>
                                                <textarea rows="1" name="formulation" id="edit_formulation" class="form-control">{{$requete->formulation}}</textarea>
                                            </div>

                                            {{-- Client (readonly) --}}
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Client</label>
                                                <select name="client_id" id="edit_client_id" class="form-control form-select edit-select2">
                                                    @foreach($clients as $client)
                                                    <option @selected($client->id==$requete->client->id) value="{{$client->id}}">{{$client->raison_sociale}}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Article (readonly) --}}

                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Articles</label>
                                                <select name="article[]" multiple class="form-control form-select edit-select2">
                                                    @foreach($articles as $article)
                                                    <option  @selected(in_array($client->id,$requete->articles->pluck('id')->toArray())) value="{{$article->id}}">{{$article->designation}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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