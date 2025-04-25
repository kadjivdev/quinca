@extends('layouts.catalogue.article')

@push('styles')
@include('pages.catalogues.article.partials.styles')
@endpush

@section('content')
<div class="content">
    {{-- En-tête de la page --}}
    @include('pages.catalogues.article.partials.header')

    {{-- Liste des articles --}}
    <div class="row g-3 list mt-3" id="articlesList">

        <!-- gestion des erreurs -->
        @if(session()->has("success"))
        <div class="alert alert-success"> {{session()->get("success")}} </div>
        @elseif(session()->has("error"))
        <div class="alert alert-danger"> {{session()->get("error")}} </div>
        @endif

        <!-- erreurs de validation -->
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- end errors -->

        @include('pages.catalogues.article.partials.list')
    </div>
</div>

{{-- Modals --}}
@include('pages.catalogues.article.partials.add-modal')
@include('pages.catalogues.article.partials.edit-modal')
@include('pages.catalogues.article.partials.import-modal')
@endsection

@push('scripts')
@include('pages.catalogues.article.partials.scripts')
@include('pages.catalogues.article.partials.scripts_filter')
@include('pages.catalogues.article.partials.js-import-modal')
@endpush