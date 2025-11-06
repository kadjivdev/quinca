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
            @include('pages.achat.fournisseur.partials.partial-soldes')
        </div>
    </div>

@endsection

