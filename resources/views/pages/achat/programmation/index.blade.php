@extends('layouts.achat.programmation')

@push('styles')
    @include('pages.achat.programmation.partials.styles')
@endpush

@section('content')
    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.achat.programmation.partials.header')

        {{-- Liste des programmations --}}
        <div class="row g-3 list mt-3" id="programmationsList">
            @include('pages.achat.programmation.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.achat.programmation.partials.add-modal')
    @include('pages.achat.programmation.partials.edit-modal')
    @include('pages.achat.programmation.partials.rejet-modal')
    @include('pages.achat.programmation.partials.show-modal')
    @include('pages.achat.programmation.partials.import-modal')
@endsection

@push('scripts')
    @include('pages.achat.programmation.partials.scripts')
    {{-- @include('pages.achat.programmation.partials.js-import-modal') --}}
@endpush
