@extends('layouts.ventes.encaissement')


@section('content')

    <div class="content">
        @include('pages.ventes.encaissement.partials.header')
        <div class="row g-3 list mt-3" id="stockEntriesList">
            @include('pages.ventes.encaissement.partials.list')
            @include('pages.ventes.encaissement.partials.show-modal')
            @include('pages.ventes.encaissement.partials.add-modal')
        </div>
    </div>
@endsection

@push('scripts')
    @include('pages.ventes.encaissement.partials.scripts')
@endpush
