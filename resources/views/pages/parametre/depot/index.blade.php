@extends('layouts.parametre.point_vente')
@push('styles')
    @include('pages.parametre.depot.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.depot.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="depotsList">
            @include('pages.parametre.depot.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.parametre.depot.partials.add-modal')
    @include('pages.parametre.depot.partials.inventories')
    @include('pages.parametre.depot.partials.edit-modal')
@endsection


@push('scripts')
    @include('pages.parametre.depot.partials.scripts')
@endpush
