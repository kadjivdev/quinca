@extends('layouts.ventes.session')


@section('content')

    <div class="content">
        @include('pages.ventes.session.partials.header')
        <div class="row g-3 list mt-3" id="stockEntriesList">
            @include('pages.ventes.session.partials.list')
        </div>
    </div>

    @include('pages.ventes.session.partials.add-modal')
@endsection

@push('scripts')
    @include('pages.ventes.session.partials.scripts')
@endpush
