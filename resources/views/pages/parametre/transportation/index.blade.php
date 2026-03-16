@extends('layouts.parametre.transportation')
@push('styles')
    @include('pages.parametre.transportation.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.transportation.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="vehiculesList">
            @include('pages.parametre.transportation.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.parametre.transportation.partials.add-modal')
    @include('pages.parametre.transportation.partials.edit-modal')
@endsection


@push('scripts')
    @include('pages.parametre.transportation.partials.scripts')
@endpush
