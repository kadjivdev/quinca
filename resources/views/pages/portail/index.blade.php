@extends('layouts.portail.portail')
@section('content-portail')
    <div class="content bg-light">
        {{-- Header --}}
        @include('pages.portail.partials.header')

        {{-- Alert Banner --}}
        <div class="alert-section px-4 py-3 mt-4">
            <div class="rounded-3 bg-dark position-relative overflow-hidden">
                <div class="position-absolute start-0 top-0 h-100 bg-warning d-flex align-items-center px-4">
                    <i class="fa-solid fa-bell text-dark fs-5"></i>
                </div>
                <div class="alert-slider ms-5 py-2">
                    <div class="swiper alert-swiper" data-swiper='{"loop": true, "autoplay": {"delay": 3000}, "speed": 800}'>
                        <div class="swiper-wrapper text-white">
                            {{-- Alert 1 --}}
                            <div class="swiper-slide">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="badge bg-warning text-dark rounded-pill">Stock</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                                        <span>Stock faible: Produit A - 5 unités restantes</span>
                                    </div>
                                </div>
                            </div>
                            {{-- Alert 2 --}}
                            <div class="swiper-slide">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="badge bg-warning text-dark rounded-pill">Nouveau</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-circle-plus text-warning"></i>
                                        <span>Nouveau produit B ajouté à l'inventaire</span>
                                    </div>
                                </div>
                            </div>
                            {{-- Alert 3 --}}
                            <div class="swiper-slide">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="badge bg-warning text-dark rounded-pill">Livraison</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-truck text-warning"></i>
                                        <span>Réapprovisionnement Produit C - Arrivée: 05/11/24</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="dashboard-grid container-fluid py-5">
            <div class="row g-4">
                {{-- Stock Management Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm hover-shadow-md transition-300">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                    <i class="fa-solid fa-box text-warning fs-4"></i>
                                </div>
                                <h4 class="card-title mb-0 fw-bold">Suivi des Stocks</h4>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Visualisez en temps réel vos niveaux de stock. Recevez des alertes et planifiez pour éviter les ruptures.
                            </p>
                            <a href="#" class="btn btn-warning text-dark fw-semibold w-100">
                                Accéder
                                <i class="fa-solid fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Order Management Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm hover-shadow-md transition-300">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                    <i class="fa-solid fa-cart-shopping text-warning fs-4"></i>
                                </div>
                                <h4 class="card-title mb-0 fw-bold">Gestion des Commandes</h4>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Gérez les commandes efficacement, suivez les réceptions et optimisez votre chaîne d'approvisionnement.
                            </p>
                            <a href="#" class="btn btn-warning text-dark fw-semibold w-100">
                                Accéder
                                <i class="fa-solid fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Analytics Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm hover-shadow-md transition-300">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                    <i class="fa-solid fa-chart-line text-warning fs-4"></i>
                                </div>
                                <h4 class="card-title mb-0 fw-bold">Analyse et Rapports</h4>
                            </div>
                            <p class="card-text text-muted mb-4">
                                Accédez à des insights détaillés sur vos performances et prenez des décisions éclairées.
                            </p>
                            <a href="#" class="btn btn-warning text-dark fw-semibold w-100">
                                Accéder
                                <i class="fa-solid fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom CSS --}}
    @push('styles')
    <style>
        .transition-300 {
            transition: all 0.3s ease-in-out;
        }
        .hover-shadow-md:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
        }
        .alert-section .alert-slider {
            padding-left: 3rem;
        }
        .alert-swiper .swiper-slide {
            opacity: 0.85;
            transition: opacity 0.3s ease;
        }
        .alert-swiper .swiper-slide-active {
            opacity: 1;
        }
    </style>
    @endpush

    {{-- Custom JavaScript --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper('.alert-swiper', {
                loop: true,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false
                },
                speed: 800
            });
        });
    </script>
    @endpush
@endsection
