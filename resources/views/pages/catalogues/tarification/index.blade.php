@extends('layouts.app.catalogue.tarification')
@push('styles')
    @include('pages.catalogues.tarification.partials.styles')
@endpush

@section('content')
    @include('pages.catalogues.tarification.partials.alerts')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.catalogues.tarification.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="tarificationsList">
            @include('pages.catalogues.tarification.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.catalogues.tarification.partials.add-modal')
    @include('pages.catalogues.tarification.partials.edit-modal')
@endsection


@push('scripts')
    @include('pages.catalogues.tarification.partials.scripts')
@endpush
