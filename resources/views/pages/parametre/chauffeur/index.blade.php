@extends('layouts.parametre.chauffeur')
@push('styles')
    @include('pages.parametre.chauffeur.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.chauffeur.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="chauffeursList">
            @include('pages.parametre.chauffeur.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.parametre.chauffeur.partials.add-modal')
    @include('pages.parametre.chauffeur.partials.edit-modal')
    @include('pages.parametre.chauffeur.partials.import-modal')
@endsection


@push('scripts')
    @include('pages.parametre.chauffeur.partials.scripts')
    @include('pages.parametre.chauffeur.partials.js-import-modal')
@endpush
