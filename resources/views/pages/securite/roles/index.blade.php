@extends('layouts.securite.Role')

@section('content')
    <div class="content">
        {{-- En-tête de la page --}}
        @include('pages.securite.roles.partials.header')

        {{-- Liste des rôles --}}
        <div class="row mt-3">
            <div class="col-12">
                @include('pages.securite.roles.partials.liste')
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.securite.roles.partials.add-modal')
    @include('pages.securite.roles.partials.edit-modal')
@endsection

@push('scripts')
    @include('pages.securite.roles.partials.js')
@endpush
