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
    @include('pages.ventes.marchand-back.partials.header')
    <div class="row g-3 list mt-3" id="stockEntriesList">
        @include('pages.ventes.marchand-back.partials.list')
    </div>
</div>

@include('pages.ventes.marchand-back.partials.add-modal')
@include('pages.ventes.marchand-back.partials.edit-modal')
@include('pages.ventes.marchand-back.partials.show-marchand-modal')
@include('pages.ventes.marchand-back.partials.show-livraison-modal')
@endsection

@push('scripts')
@include('pages.ventes.marchand-back.partials.update-js')
@include('pages.ventes.marchand-back.partials.js-show-livraison-modal')
@include('pages.ventes.marchand-back.partials.js-show-marchand-modal')
@include('pages.ventes.marchand-back.partials.scripts-part1')

<script type="text/javascript">
    // Attendre que jQuery soit chargé
    $(function() {
        console.log('Initialisation du gestionnaire de depenses');

        // Initialisation unique
        if (!window.depenseManager) {
            console.log('Création nouvelle instance DepenseManager');
            window.depenseManager = new DepenseManager();
            window.depenseManager.init();
        }
    });
</script>
@endpush