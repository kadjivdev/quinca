@extends('layouts.ventes.session')
@section('content')
<div class="content">
    @include('pages.ventes.session.partials.header')

    @if(session()->has("message"))
    <div class="alert alert-success">{{session()->get("message")}}</div>
    @endif

    @if(session()->has("error"))
    <div class="alert alert-danger">{{session()->get("error")}}</div>
    @endif

    <div class="row g-3 list mt-3" id="stockEntriesList">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-cash-register fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle Session de Caisse</h5>
                        <p class="text-muted small mb-0">Ouverture d'une nouvelle session</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('ventes.sessions.update',['sessionId'=>$session->id])}}" method="POST" class="needs-validation" novalidate>
                @csrf
                @method("PATCH")
                <input type="hidden" name="utilisateur_id" value="{{ auth()->id() }}">
                <div class="modal-body p-4">
                    {{-- Informations principales --}}
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- Caissier --}}
                                <div class="col-12">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        Caissier
                                    </label>
                                    <div class="form-control bg-white">
                                        {{ auth()->user()->name }}
                                    </div>
                                </div>

                                {{-- Montant d'ouverture --}}
                                <div class="col-12">
                                    <label class="form-label fw-medium required">
                                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                        Montant d'ouverture
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                            class="form-control text-end"
                                            name="montant_ouverture"
                                            required
                                            min="0"
                                            placeholder="Saisissez le montant d'ouverture"
                                            value="{{$session->montant_ouverture}}">
                                        <span class="input-group-text">F CFA</span>
                                    </div>
                                    <div class="invalid-feedback">Veuillez saisir un montant valide</div>
                                </div>

                                {{-- Observations --}}
                                <div class="col-12">
                                    <label for="observations" class="form-label fw-medium">
                                        <i class="fas fa-comment-alt me-2 text-primary"></i>
                                        Observations
                                    </label>
                                    <textarea class="form-control"
                                        id="observations"
                                        name="observations"
                                        rows="3"
                                        placeholder="{{$session->observations}}"></textarea>
                                </div>

                                {{-- Observations fermeture --}}
                                <div class="col-12">
                                    <label for="observations" class="form-label fw-medium">
                                        <i class="fas fa-comment-alt me-2 text-primary"></i>
                                        Observations fermeture
                                    </label>
                                    <textarea class="form-control"
                                        name="observations_fermeture"
                                        rows="3"
                                        placeholder="{{$session->observations_fermeture}}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <a href="{{route('vente.sessions.index')}}" class="btn btn-light px-4" >
                        <i class="fas fa-times me-2"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-primary px-4" id="saveSessionBtn">
                        <i class="fas fa-pencil me-2"></i>Modifier la session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
<style>
    :root {
        --kadjiv-orange: #FFA500;
        --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
    }

    /* Modal styles */
    .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }

    .modal-header {
        background: #fff !important;
    }

    .modal-header .bg-primary {
        background-color: var(--kadjiv-orange-light) !important;
    }

    .modal-header .text-primary {
        color: var(--kadjiv-orange) !important;
    }

    .modal-header .rounded-circle {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Form controls */
    .form-label {
        color: #2c3e50;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border-color: #e9ecef;
        padding: 0.6rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }

    .form-control:focus {
        border-color: var(--kadjiv-orange);
        box-shadow: 0 0 0 0.25rem rgba(255, 165, 0, 0.25);
    }

    .form-control[readonly],
    .form-control:disabled {
        background-color: #f8f9fa;
    }

    /* Card in modal */
    .modal .card {
        border-radius: 8px;
    }

    .modal .card.bg-light {
        background-color: #f8f9fa !important;
    }

    /* Buttons */
    .modal .btn {
        padding: 0.5rem 1.25rem;
        font-weight: 500;
        border-radius: 6px;
    }

    .modal .btn-primary {
        background-color: var(--kadjiv-orange);
        border-color: var(--kadjiv-orange);
    }

    .modal .btn-primary:hover {
        background-color: #e69400;
        border-color: #e69400;
    }

    .modal .btn-light {
        background-color: #f8f9fa;
        border-color: #e9ecef;
    }

    /* Icons in form labels */
    .form-label i {
        color: var(--kadjiv-orange) !important;
        width: 20px;
        text-align: center;
    }
</style>