@extends('layouts.ventes.facture')

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
    @include('pages.ventes.facture.partials.header')
    <div class="row g-3 list mt-3" id="stockEntriesList">
        @include('pages.ventes.facture.partials.list-credits')
    </div>
</div>

@include('pages.ventes.facture.partials.add-modal')
@include('pages.ventes.facture.partials.update-modal')
@include('pages.ventes.facture.partials.show-modal')
@include('pages.ventes.facture.partials.add-reg-modal')
@endsection

@push('scripts')
@include('pages.ventes.facture.partials.js-validate')
@include('pages.ventes.facture.partials.update-js')
@include('pages.ventes.facture.partials.js-delete')

<script type="text/javascript">
    let table;
    $(document).ready(function() {
        // Initialize DataTable but don't calculate widths yet
        table = $('#exampleModalShow').DataTable({
            responsive: true,
            dom: 'Bfrtip', // Buttons layout
            buttons: [
                'copy', 'excel', 'pdf', 'print'
            ]
        });

        // When modal is shown, adjust the table layout
        $('#showFactureModal').on('shown.bs.modal', function() {
            table.columns.adjust().draw();
        });
    });

    // Attendre que jQuery soit chargé
    $(function() {
        console.log('Initialisation du gestionnaire de factures');

        // Votre code de configuration et classe ici        
        @include('pages.ventes.facture.partials.scripts-part1')

        // Initialisation unique
        if (!window.factureManager) {
            console.log('Création nouvelle instance FactureManager');
            window.factureManager = new FactureManager();
            window.factureManager.init();
        }
    });
</script>
@endpush