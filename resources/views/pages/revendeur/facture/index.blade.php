@extends('layouts.revendeur.facture')

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
        @include('pages.revendeur.facture.partials.header')
        <div class="row g-3 list mt-3" id="stockEntriesList">
            @include('pages.revendeur.facture.partials.list')
        </div>
    </div>

    @include('pages.revendeur.facture.partials.add-modal')
    @include('pages.revendeur.facture.partials.edit-modal')
    @include('pages.revendeur.facture.partials.show-modal')
    @include('pages.revendeur.facture.partials.add-reg-modal')
@endsection

@push('scripts')
@include('pages.revendeur.facture.partials.js-validate')

<script type="text/javascript">
    // Attendre que jQuery soit chargé
    $(function() {
        console.log('Initialisation du gestionnaire de factures');

        // Votre code de configuration et classe ici
        @include('pages.revendeur.facture.partials.scripts-part1')
        @include('pages.revendeur.facture.partials.js-delete')


        // Initialisation unique
        if (!window.factureManager) {
            console.log('Création nouvelle instance FactureManager');
            window.factureManager = new FactureManager();
            window.factureManager.init();
        }
    });
</script>
@endpush
