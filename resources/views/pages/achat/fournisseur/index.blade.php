@extends('layouts.achat.fournisseur')
@push('styles')
    @include('pages.achat.fournisseur.partials.styles')
@endpush

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.achat.fournisseur.partials.header')

        {{-- Liste des dépôts --}}
        <div class="row g-3 list mt-3" id="fournisseursList">
            @include('pages.achat.fournisseur.partials.list')
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.achat.fournisseur.partials.add-modal')
    @include('pages.achat.fournisseur.partials.edit-modal')
    @include('pages.achat.fournisseur.partials.import-modal')
@endsection


@push('scripts')
    @include('pages.achat.fournisseur.partials.scripts')
    @include('pages.achat.fournisseur.partials.js-import-modal')
@endpush
