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
    @include('pages.ventes.transport.partials.header')
    <div class="row g-3 list mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="modal-body p-4">
                    {{-- Informations principales --}}
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="">
                                <a href="{{route('transports.index')}}" class="btn btn-sm btn-primary float-right"><i class="bi bi-arrow-left-circle"></i> Retour</a>
                            </div>
                            <br>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-6 mb-3">
                                            <label for="montant">Montant</label>
                                            <input type="number" class="form-control" name="montant" readonly value="{{$transport->montant}}">
                                        </div>

                                        <div class="col-6 mb-3">
                                            <label for="date_op">Date</label>
                                            <input type="text" readonly class="form-control" name="date_op" id="date_op" value="{{$transport->date_op}}">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="client_id">Client</label>
                                            <input type="text" class="form-control" readonly value="{{$transport->client->raison_sociale}}">
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="observation">Observation</label>

                                            <textarea class="form-control" readonly name="observation" id="observation">{{$transport->observation}}</textarea>
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