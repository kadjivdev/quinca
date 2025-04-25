@extends('layouts.achat.bon-commande')

@push('styles')
    @include('pages.achat.bon-commande.partials.styles')
@endpush

@section('content')
    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.achat.bon-commande.partials.header')

        {{-- Liste des bon-commandes --}}
        <div class="row g-3 list mt-3" id="bon-commandesList">
            @include('pages.achat.bon-commande.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.achat.bon-commande.partials.add-modal')
    @include('pages.achat.bon-commande.partials.show-modal')
    @include('pages.achat.bon-commande.partials.edit-modal')
    @include('pages.achat.bon-commande.partials.rejet-modal')
@endsection

@push('scripts')
<!-- D'abord les dépendances -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Ensuite votre script -->
@include('pages.achat.bon-commande.partials.scripts')
@endpush
