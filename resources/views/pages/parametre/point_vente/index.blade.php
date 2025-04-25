@extends('layouts.parametre.point_vente')

@section('content')

    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.parametre.point_vente.partials.header')

        <div class="row g-3 list mt-3" id="reportsList">
            @include('pages.parametre.point_vente.partials.list')
        </div>
    </div>

    @include('pages.parametre.point_vente.partials.add-modal')
    @include('pages.parametre.point_vente.partials.edit-modal')
@endsection

@push('scripts')
    @include('pages.parametre.point_vente.partials.scripts')
@endpush
