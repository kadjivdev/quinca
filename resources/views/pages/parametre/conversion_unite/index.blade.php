@extends('layouts.parametre.conversion')
@push('styles')
    @include('pages.parametre.conversion_unite.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.conversion_unite.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="conversion_unitesList">
            @include('pages.parametre.conversion_unite.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.parametre.conversion_unite.partials.add-modal')
    @include('pages.parametre.conversion_unite.partials.edit-modal')
@endsection


@push('scripts')
    @include('pages.parametre.conversion_unite.partials.scripts')
@endpush
