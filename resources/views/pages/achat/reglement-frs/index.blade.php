@extends('layouts.achat.reglement-frs')

@push('styles')
@include('pages.achat.reglement-frs.partials.styles')
@endpush

@section('content')
<div class="content">
    {{-- En-tête de la page --}}
    @include('pages.achat.reglement-frs.partials.header')

    {{-- Liste des reglement-frss --}}
    <div class="row g-3 list mt-3" id="reglement-frssList">
        @include('pages.achat.reglement-frs.partials.list')
    </div>
</div>

{{-- Modals --}}
@include('pages.achat.reglement-frs.partials.add-modal')
@include('pages.achat.reglement-frs.partials.show-modal')
@include('pages.achat.reglement-frs.partials.edit-modal')
@endsection

@push('scripts')
<!-- D'abord les dépendances -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Ensuite votre script -->
@include('pages.achat.reglement-frs.partials.scripts')
@endpush