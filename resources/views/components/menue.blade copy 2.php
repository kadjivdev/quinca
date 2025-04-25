<nav class="navbar navbar-top navbar-slim justify-content-between fixed-top navbar-expand-lg" id="navbarTopSlim"
    data-navbar-appearance="">
    <div class="navbar-logo">
        <button class="btn navbar-toggler navbar-toggler-humburger-icon hover-bg-transparent" type="button"
            data-bs-toggle="collapse" data-bs-target="#navbarTopCollapse" aria-controls="navbarTopCollapse"
            aria-expanded="false" aria-label="Toggle Navigation">
            <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
        </button>
        <a class="navbar-brand navbar-brand" href="#">Quinca<span
                class="text-body-highlight d-none d-sm-inline">Kadjiv</span></a>
    </div>
    <div class="collapse navbar-collapse navbar-top-collapse order-1 order-lg-0 justify-content-center"
        id="navbarTopCollapse">
        <ul class="navbar-nav navbar-nav-top" data-dropdown-on-hover="data-dropdown-on-hover">

            <!-- ADMINISTRATION -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-cog"></span>Administration
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">

                    <li><a class="dropdown-item" href="{{ route('users.index') }}">
                            <div class="dropdown-item-wrapper">Utilisateurs</div>
                        </a></li>

                    <li><a class="dropdown-item" href="{{ route('roles.index') }}">
                            <div class="dropdown-item-wrapper">Gestion des Rôles</div>
                        </a></li>
                </ul>
            </li>

            <!-- PARAMETRE -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-cog"></span>Paramètre
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">
                    <li><a class="dropdown-item" href="{{ route('configuration.index') }}">
                            <div class="dropdown-item-wrapper">Configuration Société</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('point-vente.index') }}">
                            <div class="dropdown-item-wrapper">Points de Vente</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('depot.index') }}">
                            <div class="dropdown-item-wrapper">Magasin</div>
                            {{-- Dépot est devenu magasin --}}
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('caisse.index') }}">
                            <div class="dropdown-item-wrapper">Caisses</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('unite-mesure.index') }}">
                            <div class="dropdown-item-wrapper">Unités de Mesure</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('conversion.index') }}">
                            <div class="dropdown-item-wrapper">Conversions d'Unités</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('chauffeur.index') }}">
                            <div class="dropdown-item-wrapper">Chauffeurs</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('vehicule.index') }}">
                            <div class="dropdown-item-wrapper">Véhicules</div>
                        </a></li>
                </ul>
            </li>

            <!-- CATALOGUE -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-box"></span>Catalogue
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">
                    <li><a class="dropdown-item" href="{{ route('famille-article.index') }}">
                            <div class="dropdown-item-wrapper">Familles d'articles</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('articles.index') }}">
                            <div class="dropdown-item-wrapper">Articles</div>
                        </a></li>
                    <li><a class="dropdown-item" href="#">
                            <div class="dropdown-item-wrapper">Tarification</div>
                        </a></li>
                </ul>
            </li>

            <!-- ACHAT -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-box"></span>Achat
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">
                    <li><a class="dropdown-item" href="{{ route('fournisseur.index') }}">
                            <div class="dropdown-item-wrapper">Fourniseurs</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('programmations.index') }}">
                            <div class="dropdown-item-wrapper">Pré Commande</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('bon-commandes.index') }}">
                            <div class="dropdown-item-wrapper">Bon de Commande</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('factures.index') }}">
                            <div class="dropdown-item-wrapper">Facture Fournisseur</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('reglements.index') }}">
                            <div class="dropdown-item-wrapper">Règlement Fournisseur</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('livraisons.index') }}">
                            <div class="dropdown-item-wrapper">Livraison Fournisseur</div>
                        </a></li>
                </ul>
            </li>

            <!-- VENTES -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-dollar-sign"></span>Ventes
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">
                    <li><a class="dropdown-item" href="{{ route('vente.clients.index') }}">
                            <div class="dropdown-item-wrapper">Liste des Clients</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('vente.sessions.index') }}">
                            <div class="dropdown-item-wrapper">Ouverture/Fermeture Caisse</div>
                        </a></li>
                    <li><a class="dropdown-item" href="{{ route('vente.facture.index') }}">
                            <div class="dropdown-item-wrapper">Facturation Vente Client</div>
                        </a></li>


                    <li><a class="dropdown-item" href="{{ route('vente.reglement.index') }}">
                            <div class="dropdown-item-wrapper">Règlements Clients</div>
                        </a></li>

                    <li><a class="dropdown-item" href="{{ route('vente.livraisons.index') }}">
                            <div class="dropdown-item-wrapper">Bons de Livraison</div>
                        </a></li>

                </ul>
            </li>

            <!-- VENTES -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-dollar-sign"></span>Revendeur
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">
                    <li>
                        <a class="dropdown-item" href="{{ route('vente.clients.index') }}">
                            <div class="dropdown-item-wrapper">Liste des Clients</div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('revendeur.facture.index') }}">
                            <div class="dropdown-item-wrapper">Facturation Vente Client</div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('revendeur.speciales.index') }}">
                            <div class="dropdown-item-wrapper">Vente Spéciale</div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('revendeur.normale.rapport') }}">
                            <div class="dropdown-item-wrapper">Validation Vente</div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('revendeur.speciale.rapport') }}">
                            <div class="dropdown-item-wrapper">Validation Vente Spéciale</div>
                        </a>
                    </li>



                </ul>
            </li>

            <!-- ANALYSES & RAPPORTS  DES ACHATS-->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-chart-pie"></span>Rapports Achats
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">

                    <li><a class="dropdown-item" href="{{ route('rapports.pre-commandes') }}">
                            <div class="dropdown-item-wrapper">Pré-commandes</div>
                        </a></li>



                    <li><a class="dropdown-item" href="{{ route('rapports.bon-commandes') }}">
                            <div class="dropdown-item-wrapper">Bon de Commande</div>
                        </a></li>



                    <li><a class="dropdown-item" href="{{ route('rapports.facture-achats') }}">
                            <div class="dropdown-item-wrapper">Facture Fournisseur</div>
                        </a></li>

                        <li><a class="dropdown-item" href="{{ route('rapports.livraison-achats') }}">
                            <div class="dropdown-item-wrapper">Livraison Fournisseur</div>
                        </a></li>

                        <li><a class="dropdown-item" href="{{ route('rapports.reglement-achats') }}">
                            <div class="dropdown-item-wrapper">Règlement Fournisseur</div>
                        </a></li>

                        <li><a class="dropdown-item" href="{{ route('rapports.compte-fournisseur') }}">
                            <div class="dropdown-item-wrapper">Compte Fournisseur</div>
                        </a></li>

                </ul>
            </li>

             <!-- ANALYSES & RAPPORTS  DES VENTES-->
             <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-chart-pie"></span>Rapports Ventes
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">


                    <li><a class="dropdown-item" href="{{ route('rapports.ventes-articles') }}">
                            <div class="dropdown-item-wrapper">Ventes par Article</div>
                        </a></li>


                    <li><a class="dropdown-item" href="{{ route('rapports.ventes-familles') }}">
                            <div class="dropdown-item-wrapper">Ventes par Famille</div>
                        </a></li>


                    <li><a class="dropdown-item" href="{{ route('rapports.ventes-clients') }}">
                            <div class="dropdown-item-wrapper">Ventes par Client</div>
                        </a></li>

                        <li><a class="dropdown-item" href="{{ route('rapports.vente-journaliere') }}">
                            <div class="dropdown-item-wrapper">Ventes Journaliere</div>
                        </a></li>


                    <li><a class="dropdown-item" href="{{ route('rapports.creances.index') }}">
                            <div class="dropdown-item-wrapper">Suivi des créances</div>
                        </a></li>

                    <li><a class="dropdown-item" href="{{ route('vente.sessions.rapport') }}">
                            <div class="dropdown-item-wrapper">Vente Session</div>
                        </a></li>

                        <li><a class="dropdown-item" href="{{ route('rapports.compte-client') }}">
                            <div class="dropdown-item-wrapper">Compte Client</div>
                        </a></li>
                </ul>
            </li>

               <!-- ANALYSES & RAPPORTS  DES STOCKS-->
               <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle lh-1" href="#" role="button" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <span class="uil fs-8 me-2 uil-chart-pie"></span>Rapports Stocks
                </a>
                <ul class="dropdown-menu navbar-dropdown-caret">

                    <li><a class="dropdown-item" href="{{ route('rapports.mouvement-stock') }}">
                            <div class="dropdown-item-wrapper">Mouvements de Stock</div>
                        </a></li>

                        <li><a class="dropdown-item" href="{{ route('rapports.stock-dispo') }}">
                            <div class="dropdown-item-wrapper">Stock Disponible</div>
                        </a></li>


                    <li><a class="dropdown-item" href="{{ route('stock.rotation.index') }}">
                            <div class="dropdown-item-wrapper">Rotations de Stock</div>
                        </a></li>

                </ul>
            </li>
        </ul>
    </div>

    <!-- User Dropdown -->
    <ul class="navbar-nav navbar-nav-icons flex-row">
        <li class="nav-item dropdown">
            <a class="nav-link lh-1 pe-0 white-space-nowrap" id="navbarDropdownUser" href="#!" role="button"
                data-bs-toggle="dropdown" aria-haspopup="true" data-bs-auto-close="outside" aria-expanded="false">
                {{ Auth::user()->name }}
                <span class="d-inline-block" style="height:10.2px;width:10.2px;">
                    <span class="fa-solid fa-chevron-down fs-10"></span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border"
                aria-labelledby="navbarDropdownUser">
                <div class="card position-relative border-0">
                    <div class="card-body p-0">
                        <div class="text-center pt-4 pb-3">
                            <div class="avatar avatar-xl">
                                <img class="rounded-circle" src="../assets/img/profil.jpeg" alt="" />
                            </div>
                            <h6 class="mt-2 text-body-emphasis">{{ Auth::user()->email }}</h6>
                        </div>
                    </div>
                    <div class="px-3">
                        <a href="/storage/MANUEL_D_UTILISATION_QUINCAKADJIV.pdf" target="_blank" class="btn btn-link d-flex flex-center w-100">Manuel d'utilisation</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button class="btn btn-phoenix-secondary d-flex flex-center w-100" type="submit">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</nav>

@push('styles')
    <style>
        /* public/assets/css/custom-menu.css */
        .navbar-top {
            background-color: #fff;
            border-bottom: 1px solid #e3e6ed;
        }

        .navbar-slim {
            min-height: 44px;
            padding: 0.5rem 1rem;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .navbar-nav-top .nav-item {
            position: relative;
        }

        .navbar-nav-top .nav-link {
            padding: 0.5rem 1rem;
            color: #5e6e82;
        }

        .dropdown-menu {
            border: 1px solid #e3e6ed;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
            min-width: 12rem;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            color: #5e6e82;
        }

        .dropdown-item-wrapper {
            display: flex;
            align-items: center;
        }

        .dropdown-indicator-icon {
            margin-right: 0.5rem;
        }

        .navbar-dropdown-caret {
            margin-top: 0.125rem;
        }

        /* Support des sous-menus */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
        }

        /* Styles pour le mode mobile */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background: #fff;
                padding: 1rem;
            }

            .dropdown-menu {
                border: none;
                box-shadow: none;
            }

            .dropdown-submenu .dropdown-menu {
                margin-left: 1rem;
            }
        }
    </style>
@endpush
