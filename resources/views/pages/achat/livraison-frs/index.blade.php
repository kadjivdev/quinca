@extends('layouts.achat.livraison-frs')

@section('content')
    <div class="content">
        {{-- Liste des livraisons --}}
        @include('pages.achat.livraison-frs.partials.header')
        <div class="row g-3 list">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        @include('pages.achat.livraison-frs.partials.list')
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @include('pages.achat.livraison-frs.partials.add-modal')
    @include('pages.achat.livraison-frs.partials.show-modal')
    @include('pages.achat.livraison-frs.partials.edit-modal')
    @include('pages.achat.livraison-frs.partials.rejet-modal')
@endsection

@push('styles')
<link href="{{ asset('css/livraison-fournisseur.css') }}" rel="stylesheet">
@endpush

@push('scripts')
@include('pages.achat.livraison-frs.partials.js-add')
@include('pages.achat.livraison-frs.partials.js-delete')
@include('pages.achat.livraison-frs.partials.js-validate')
@endpush
