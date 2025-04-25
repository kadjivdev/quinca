@extends('layouts.parametre.vehicule')
@push('styles')
    @include('pages.parametre.vehicule.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.vehicule.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="vehiculesList">
            @include('pages.parametre.vehicule.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.parametre.vehicule.partials.add-modal')
    @include('pages.parametre.vehicule.partials.edit-modal')
    @include('pages.parametre.vehicule.partials.import-modal')
@endsection


@push('scripts')
    @include('pages.parametre.vehicule.partials.scripts')
    @include('pages.parametre.vehicule.partials.js-import-modal')
@endpush
