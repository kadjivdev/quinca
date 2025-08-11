@extends('layouts.achat.livraison-frs')
@push('styles')
@include('pages.catalogues.tarification.partials.styles')
@endpush

@section('content')
@include('pages.catalogues.tarification.partials.alerts')

<div class="content">
    {{-- En-tête de la page --}}
    @include('pages.catalogues.tarification.partials.header')

    {{-- Liste des dépôts --}}
    <div class="row g-3 list mt-3" id="tarificationsList">
        <!-- succès -->
        @if(session()->has("success"))
        <div class="alert alert-success">
            {{session()->get("success")}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- errors -->
        @if($errors->any())
        <ul class="alert alert-danger">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            @foreach($errors->all() as $error)
            <li class="">{{$error}}</li>
            @endforeach
        </ul>
        @endif

        @include('pages.catalogues.tarification.partials.list')
    </div>
</div>

{{-- Modals --}}
@include('pages.catalogues.tarification.partials.add-modal')
@include('pages.catalogues.tarification.partials.edit-modal')
@include('pages.catalogues.tarification.partials.import-modal')
@include('pages.catalogues.article.partials.import-modal')
@endsection

@push('scripts')
@include('pages.catalogues.tarification.partials.scripts')
@endpush