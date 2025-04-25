@extends('layouts.parametre.unite_mesure')
@push('styles')
    @include('pages.parametre.unite_mesure.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.unite_mesure.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="unite_mesuresList">
            @include('pages.parametre.unite_mesure.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.parametre.unite_mesure.partials.add-modal')
    @include('pages.parametre.unite_mesure.partials.edit-modal')
@endsection


@push('scripts')
    @include('pages.parametre.unite_mesure.partials.scripts')
@endpush
