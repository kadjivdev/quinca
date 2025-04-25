@extends('layouts.catalogue.famille_article')
@push('styles')
    @include('pages.catalogues.famille_article.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.catalogues.famille_article.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="famille_articlesList">
            @include('pages.catalogues.famille_article.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.catalogues.famille_article.partials.add-modal')
    @include('pages.catalogues.famille_article.partials.edit-modal')
    @include('pages.catalogues.famille_article.partials.import-modal')
@endsection


@push('scripts')
    @include('pages.catalogues.famille_article.partials.scripts')
    @include('pages.catalogues.famille_article.partials.js-import-modal')
@endpush
