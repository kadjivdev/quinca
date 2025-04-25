@extends('layouts.parametre.caisse')
@push('styles')
    @include('pages.parametre.caisse.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.caisse.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="caissesList">
            @include('pages.parametre.caisse.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.parametre.caisse.partials.add-modal')
    @include('pages.parametre.caisse.partials.edit-modal')
@endsection


@push('scripts')
    @include('pages.parametre.caisse.partials.scripts')

@endpush
