@extends('layouts.parametre.societe')

@section('content')
   {{-- Contenu principal --}}
    <div class="content">
        <form id="configurationForm"
              action="{{ route('configuration.update') }}"
              method="POST"
              enctype="multipart/form-data"
              class="needs-validation"
              novalidate>
            @csrf
            <div class="row g-4">
                {{-- Logo et Informations basiques --}}
                @include('pages.parametre.societe.partials.view_bloc')

                {{-- Formulaire principal --}}
                @include('pages.parametre.societe.partials.update_bloc')
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @include('pages.parametre.societe.partials.scripts')
@endpush
