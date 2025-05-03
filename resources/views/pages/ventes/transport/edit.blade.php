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
                <div class="">
                    <a href="{{route('transports.index')}}" class="btn btn-sm btn-primary float-right"><i class="bi bi-arrow-left-circle"></i> Retour</a>
                </div>
                <br>

                <form action="{{route('transports.update',$transport->id)}}" method="POST" id="editRequeteForm" class="needs-validation" novalidate>
                    @csrf
                    @method('PATCH')
                    <!-- <input type="hidden" name="reglement_id" id="editReglementId"> -->

                    <div class="modal-body p-4">
                        {{-- Informations principales --}}
                        <div class="row g-3">
                            <div class="col-6 mb-3">
                                <label for="montant">Montant</label>
                                <input type="number" class="form-control" name="montant" value="{{$transport->montant}}">
                            </div>

                            <div class="col-6 mb-3">
                                <label for="date_op">Date </label>
                                <input type="date" class="form-control" name="date_op" value="{{\Carbon\Carbon::parse($transport->date_op)->format('Y-m-d')}}">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-medium">Client</label>
                                <select name="client_id" id="edit_client_id" class="form-control form-select edit-select2">
                                    @foreach($clients as $client)
                                    <option @selected($client->id==$transport->client->id) value="{{$client->id}}">{{$client->raison_sociale}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="observation">Observation</label>

                                <textarea class="form-control" name="observation" id="observation">{{$transport->observation}}</textarea>
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