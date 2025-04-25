@extends('layouts.ventes.livraison')

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
        @include('pages.ventes.livraison.partials.header')
        <div class="row g-3 list mt-3" id="stockEntriesList">
            @include('pages.ventes.livraison.partials.list')
        </div>
    </div>

    @include('pages.ventes.livraison.partials.add-modal')
    @include('pages.ventes.livraison.partials.edit-modal')
    @include('pages.ventes.livraison.partials.show-modal')
@endsection
@push('scripts')

@include('pages.ventes.livraison.partials.js-validate')
@include('pages.ventes.livraison.partials.js-add-modal')
@include('pages.ventes.livraison.partials.js-edit-modal')
@include('pages.ventes.livraison.partials.js-show-modal')
@include('pages.ventes.livraison.partials.js-validate-modal')
@include('pages.ventes.livraison.partials.js-delete-modal')
@include('pages.ventes.livraison.partials.js-load-line-facture')

@endpush
