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
    <div class="row g-3 list mt-3" id="stockEntriesList">
        @include('pages.ventes.requete.partials.list')
    </div>
</div>

@include('pages.ventes.requete.partials.add-modal')
@include('pages.ventes.requete.partials.show-modal')
@include('pages.ventes.requete.partials.edit-modal')

@endsection
@push('scripts')

@include('pages.ventes.requete.partials.js-validate')
@include('pages.ventes.requete.partials.js-add-modal')
@include('pages.ventes.requete.partials.js-show-modal')
@include('pages.ventes.requete.partials.js-cancel-modal')
@include('pages.ventes.requete.partials.js-edit-modal')
@include('pages.ventes.requete.partials.js-validate-modal')
@include('pages.ventes.requete.partials.js-delete-modal')
@include('pages.ventes.requete.partials.js-load-line-facture')

<script>
    // Initialisation des filtres Select2
    $(document).ready(function() {
        $('.select2-clients').select2({
            theme: 'bootstrap-5',
            placeholder: 'Tous les clients'
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Select2 pour les filtres (hors modal)
        $('.main-content .select2-clients').select2({
            theme: 'bootstrap-5',
            placeholder: 'Tous les clients',
            width: '100%'
        });
    });
</script>

@endpush