@extends('layouts.ventes.client')

@push('styles')
<style>
    /* Z-index fixes */
    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
    }

    .select2-container {
        z-index: 2000 !important;
    }

    .select2-dropdown {
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

    .ligne-facture {
        transition: all 0.3s ease;
    }

    .ligne-facture.loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="content">

    @if(session("error"))
    <div class="alert alert-danger">{{session()->get('error')}}</div>
    @elseif(session("success"))
    <div class="alert alert-success">{{session()->get('success')}}</div>
    @endif

    <div class="row g-3 list mt-3 justify-content-center" id="stockEntriesList">
        <div class="col-6 ">
            <h3 class="">Affectation de zone au client : <span class="badge bg-light border rounded text-dark">{{$client->raison_sociale}}</span> </h3>
            <form action="{{route('affect-to-zone',$client->id)}}" class="p-3 bg-light border rounded" method="POST">
                @csrf
                <div class="card p-3">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label fw-medium required">Zone</label> <br>
                            <select class="form-select" name="zone_id" id="zone_id" required>
                                <option value="">Choisissez une zone</option>
                                @foreach($zones as $zone)
                                <option value="{{$zone->id}}" @selected($zone->id==$client->zone_id)>{{$zone->libelle}}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un agent</div>
                        </div>
                    </div>
                    <br>
                    <button class="btn w-100 btn-sm btn-success" type="submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush