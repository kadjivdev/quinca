@extends('layouts.achat.bon-commande')

@push('styles')
    @include('pages.achat.approvisionnement.partials.styles')
@endpush

@section('content')
    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.achat.approvisionnement.partials.header')

        {{-- Liste des approvisionnements --}}
        <div class="row g-3 list mt-3" id="approvisionnementsList">
            @include('pages.achat.approvisionnement.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.achat.approvisionnement.partials.add-modal')
    @include('pages.achat.approvisionnement.partials.show-modal')
    @include('pages.achat.approvisionnement.partials.edit-modal')
    @include('pages.achat.approvisionnement.partials.rejet-modal')
@endsection

@push('scripts')
<!-- D'abord les dépendances -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Ensuite votre script -->
@include('pages.achat.approvisionnement.partials.scripts')
@endpush
