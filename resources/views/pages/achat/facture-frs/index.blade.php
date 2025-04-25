@extends('layouts.achat.facture-frs')

@push('styles')
@include('pages.achat.facture-frs.partials.styles')
@endpush

@section('content')
<div class="content">
    {{-- En-tête de la page --}}
    @include('pages.achat.facture-frs.partials.header')

    {{-- Liste des facture-frss --}}
    <div class="row g-3 list mt-3" id="facture-frssList">
        @include('pages.achat.facture-frs.partials.list')
    </div>
</div>

{{-- Modals --}}
@include('pages.achat.facture-frs.partials.add-modal')
@include('pages.achat.facture-frs.partials.rejet-modal')
@include('pages.achat.facture-frs.partials.show-modal')
@include('pages.achat.facture-frs.partials.edit-modal')
@endsection

@push('scripts')
<!-- D'abord les dépendances -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Ensuite votre script -->
@include('pages.achat.facture-frs.partials.scripts')
@endpush