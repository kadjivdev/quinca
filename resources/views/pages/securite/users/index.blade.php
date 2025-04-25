@extends('layouts.securite.User')

@section('content')
    <div class="content">
        {{-- En-tête de la page --}}

        @include('pages.securite.users.partials.header')

        {{-- Liste des utilisateurs --}}
        <div class="row mt-3">
            <div class="col-12">
                @include('pages.securite.users.partials.liste')
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('pages.securite.users.partials.add-modal')
    @include('pages.securite.users.partials.edit-modal')
    @include('pages.securite.users.partials.show-modal')
@endsection

@push('styles')
    <style>
    .icon-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-primary-soft {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
    }

    .bg-success-soft {
        background-color: rgba(var(--bs-success-rgb), 0.1);
    }

    .bg-danger-soft {
        background-color: rgba(var(--bs-danger-rgb), 0.1);
    }

    .empty-state {
        padding: 2rem;
        text-align: center;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
    }
    </style>
@endpush

@push('scripts')
    @include('pages.securite.users.partials.js')
@endpush
