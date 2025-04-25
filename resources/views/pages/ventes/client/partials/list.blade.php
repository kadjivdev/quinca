<div class="row g-3">
    {{-- Section Filtres --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row g-3">
                    {{-- Filtre Catégorie --}}
                    <div class="col-md-2">
                        <label class="form-label small">Catégorie</label>
                        <select class="form-select form-select-sm" id="categorieFilter" onchange="filterClients()">
                            <option value="">Toutes les catégories</option>
                            <option value="particulier">Particulier</option>
                            <option value="professionnel">Professionnel</option>
                            <option value="societe">Société</option>
                        </select>
                    </div>

                    {{-- Recherche --}}
                    <!-- <div class="col-md-4">
                        <label class="form-label small">Recherche</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text"
                                class="form-control form-control-sm border-start-0"
                                id="searchFilter"
                                placeholder="Nom, Code, IFU, RCCM, Téléphone..."
                                onkeyup="filterClients()">
                        </div>
                    </div> -->

                    {{-- Filtre Statut --}}
                    <div class="col-md-2">
                        <label class="form-label small">Statut</label>
                        <select class="form-select form-select-sm" id="statutFilter" onchange="filterClients()">
                            <option value="">Tous les statuts</option>
                            <option value="1">Actif</option>
                            <option value="0">Inactif</option>
                        </select>
                    </div>

                    {{-- Filtre Crédit --}}
                    <div class="col-md-2">
                        <label class="form-label small">Crédit</label>
                        <select class="form-select form-select-sm" id="creditFilter" onchange="filterClients()">
                            <option value="">Tous</option>
                            <option value="with_credit">Avec crédit</option>
                            <option value="exceeded">Dépassement</option>
                        </select>
                    </div>

                    {{-- Filtre Ville --}}
                    <div class="col-md-2">
                        <label class="form-label small">Ville</label>
                        <select class="form-select form-select-sm" id="villeFilter" onchange="filterClients()">
                            <option value="">Toutes les villes</option>
                            @foreach ($villes as $ville)
                            <option value="{{ $ville }}">{{ $ville }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bouton réinitialiser --}}
                    <button class="col-md-2 btn btn-light btn-sm mt-3" onclick="resetFilters()">
                        <i class="fas fa-undo me-1"></i>
                        Réinitialiser les filtres
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table des clients --}}
    <div class="col-12">
        <div class="card border-0 p-3 shadow-sm">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="clientsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Code Client</th>
                            <th class="border-bottom-0 text-nowrap py-3">Date Insertion</th>
                            <th class="border-bottom-0">Raison Sociale</th>
                            <th class="border-bottom-0">Département</th>
                            <th class="border-bottom-0">Agent</th>
                            <th class="border-bottom-0">Contact</th>
                            <th class="border-bottom-0">Catégorie</th>
                            <th class="border-bottom-0">Crédit</th>
                            <th class="border-bottom-0">Solde</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                        <tr>
                            <td class="text-nowrap py-3">
                                <span class="code-client">{{ $client->code_client }}</span>
                            </td>
                            <td>{{ Carbon\Carbon::parse($client->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-client me-2">
                                        {{ substr($client->raison_sociale, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $client->raison_sociale }}</div>
                                        <div class="text-muted small">
                                            @if($client->ifu)
                                            IFU: {{ $client->ifu }}
                                            @endif
                                            @if($client->rccm)
                                            @if($client->ifu) | @endif
                                            RCCM: {{ $client->rccm }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $client->departement?->libelle }}</td>
                            <td>{{ $client->agent?->nom }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="text-primary">
                                        <i class="fas fa-phone me-1"></i>
                                        {{ $client->telephone }}
                                    </div>
                                    @if($client->email)
                                    <div class="text-muted small">
                                        <i class="fas fa-envelope me-1"></i>
                                        {{ $client->email }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @switch($client->categorie)
                                @case('particulier')
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-user me-1"></i>Particulier
                                </span>
                                @break
                                @case('professionnel')
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-briefcase me-1"></i>Professionnel
                                </span>
                                @break
                                @case('comptoir')
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-briefcase me-1"></i>Comptoir
                                </span>
                                @break
                                @case('societe')
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-building me-1"></i>Société
                                </span>
                                @break
                                @endswitch
                            </td>
                            <td>
                                @if($client->plafond_credit > 0)
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ number_format($client->plafond_credit, 0, ',', ' ') }} F</span>
                                    <small class="text-muted">{{ $client->delai_paiement }} jours</small>
                                </div>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium {{ $client->solde_courant > $client->plafond_credit ? 'text-danger' : '' }}">
                                        {{ number_format($client->solde_courant, 0, ',', ' ') }} F
                                    </span>
                                    @if($client->hasDepassementCredit())
                                    <small class="text-danger">
                                        +{{ number_format($client->depassement_credit, 0, ',', ' ') }} F
                                    </small>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @if($client->statut)
                                <span class="badge bg-success bg-opacity-10 text-success">Actif</span>
                                @else
                                <span class="badge bg-danger bg-opacity-10 text-danger">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showClient({{ $client->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="loadClientData({{ $client->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    @if($client->facturesClient->count() == 0)
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteClient({{ $client->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucun client trouvé</h6>
                                    <p class="text-muted small mb-3">Les clients que vous ajoutez apparaîtront ici</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addClientModal">
                                        <i class="fas fa-plus me-2"></i>Ajouter un client
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --kadjiv-orange: #FFA500;
        --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
    }

    /* Filtres */
    .form-label {
        color: #2c3e50;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .form-select,
    .form-control {
        border-color: #e9ecef;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: var(--kadjiv-orange);
        box-shadow: 0 0 0 0.2rem var(--kadjiv-orange-light);
    }

    .input-group-text {
        border-color: #e9ecef;
    }

    /* Code client */
    .code-client {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--kadjiv-orange);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: var(--kadjiv-orange-light);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    /* Avatar client */
    .avatar-client {
        width: 40px;
        height: 40px;
        background-color: var(--kadjiv-orange-light);
        color: var(--kadjiv-orange);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Table */
    .table thead {
        background-color: #f8f9fa;
    }

    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #555;
    }

    /* Badges */
    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
        border-radius: 30px;
    }

    .badge.bg-opacity-10 {
        border: 1px solid currentColor;
    }

    /* Boutons d'action */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }

    .btn-light-primary {
        color: var(--kadjiv-orange);
        background-color: var(--kadjiv-orange-light);
    }

    .btn-light-primary:hover {
        background-color: rgba(255, 165, 0, 0.2);
    }

    .btn-light-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .btn-light-warning:hover {
        background-color: rgba(255, 193, 7, 0.2);
    }

    .btn-light-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .btn-light-danger:hover {
        background-color: rgba(220, 53, 69, 0.2);
    }

    /* État vide */
    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-state i {
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    /* Card */
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }

    /* Primary color overrides */
    .text-primary {
        color: var(--kadjiv-orange) !
    }

    /* Input group */
    .input-group-sm>.form-control,
    .input-group-sm>.input-group-text {
        padding: 0.4rem 0.8rem;
        font-size: 0.875rem;
    }

    /* Phone and email icons */
    .contact-icon {
        width: 20px;
        color: var(--kadjiv-orange);
    }

    /* Montants */
    .montant {
        font-family: "Consolas", monospace;
        font-weight: 500;
    }

    .montant.danger {
        color: var(--bs-danger);
    }

    /* Hover effects */
    .btn-icon i {
        transition: transform 0.2s ease;
    }

    .btn-icon:hover i {
        transform: scale(1.1);
    }

    /* Badge variations */
    .badge.bg-info.bg-opacity-10 {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }

    .badge.bg-primary.bg-opacity-10 {
        background-color: rgba(255, 165, 0, 0.1) !important;
        color: var(--kadjiv-orange) !important;
    }

    .badge.bg-success.bg-opacity-10 {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    /* Tooltips custom style */
    .tooltip {
        font-size: 0.75rem;
    }

    /* Card footer with pagination */
    .card-footer {
        background: transparent;
    }

    .pagination {
        margin: 0;
    }

    .page-link {
        color: var(--kadjiv-orange);
        padding: 0.375rem 0.75rem;
        border: none;
        margin: 0 0.125rem;
        border-radius: 0.25rem;
    }

    .page-item.active .page-link {
        background-color: var(--kadjiv-orange);
        border-color: var(--kadjiv-orange);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .btn-group {
            flex-wrap: wrap;
        }

        .btn-icon {
            width: 28px;
            height: 28px;
        }

        .code-client {
            font-size: 0.75rem;
        }

        .avatar-client {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .table td,
        .table th {
            padding: 0.5rem;
        }
    }

    /* Animation pour les changements d'état */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge,
    .btn-icon {
        animation: fadeIn 0.3s ease-out;
    }

    /* Style pour le bouton réinitialiser */
    .btn-reset {
        color: #6c757d;
        background-color: #f8f9fa;
        border-color: #e9ecef;
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }

    .btn-reset:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    /* Style pour les select et inputs */
    .form-select-sm,
    .form-control-sm {
        font-size: 0.875rem;
        min-height: 31px;
    }

    /* Style pour la section des filtres */
    .filters-section {
        background-color: #fff;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    /* Style pour les liens dans la table */
    table a {
        color: var(--kadjiv-orange);
        text-decoration: none;
    }

    table a:hover {
        color: #e69400;
        text-decoration: underline;
    }

    /* Style pour les cellules avec montants */
    td.montant {
        font-family: 'Consolas', monospace;
        text-align: right;
        white-space: nowrap;
    }

    /* Amélioration du style empty state */
    .empty-state {
        background-color: #f8f9fa;
        border-radius: 1rem;
        padding: 3rem;
    }

    .empty-state i {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    .empty-state h6 {
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 1.5rem;
    }

    /* Style pour les info-bulles */
    .info-bubble {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        color: #6c757d;
        margin-right: 0.5rem;
    }

    .info-bubble i {
        margin-right: 0.25rem;
        font-size: 0.875rem;
    }
</style>

<script>
    function filterClients() {
        // Afficher le loader
        Swal.fire({
            title: 'Chargement...',
            text: 'Filtrage des clients en cours',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Récupérer les valeurs des filtres
        const filters = {
            categorie: $('#categorieFilter').val(),
            search: $('#searchFilter').val(),
            statut: $('#statutFilter').val(),
            ville: $('#villeFilter').val(),
            credit: $('#creditFilter').val()
        };

        // Faire la requête AJAX avec les filtres
        $.ajax({
            url: `${apiUrl}/vente/clients/refresh-list`,
            method: 'GET',
            data: filters,
            success: function(response) {
                // Mettre à jour le tableau avec les nouvelles données
                $('#clientsTable tbody').html($(response.html).find('#clientsTable tbody').html());

                // Mettre à jour la pagination si elle existe
                if ($('.card-footer').length) {
                    $('.card-footer').html($(response.html).find('.card-footer').html());
                }

                // Mettre à jour les statistiques
                updateStats(response.stats);

                // Réinitialiser les tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Fermer le loader
                Swal.close();
            },
            error: function(xhr, status, error) {
                Swal.close();

                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du filtrage des clients'
                });

                console.error('Erreur:', error);
            }
        });
    }

    // Fonction pour mettre à jour les statistiques
    function updateStats(stats) {
        $('.stat-total').text(stats.total.toLocaleString('fr-FR'));
        $('.stat-actifs').text(stats.actifs.toLocaleString('fr-FR'));
        $('.stat-professionnels').text(stats.professionnels.toLocaleString('fr-FR'));
        $('.stat-avec-credit').text(stats.avec_credit.toLocaleString('fr-FR'));
    }

    // Ajouter un délai pour la recherche (debounce)
    let searchTimeout;
    $('#searchFilter').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterClients, 500); // Attendre 500ms après la dernière frappe
    });

    // Event listeners pour les autres filtres (sélects)
    $('#categorieFilter, #statutFilter, #villeFilter, #creditFilter').on('change', filterClients);

    // Réinitialiser les filtres
    function resetFilters() {
        $('#categorieFilter').val('');
        $('#searchFilter').val('');
        $('#statutFilter').val('');
        $('#villeFilter').val('');
        $('#creditFilter').val('');
        filterClients();
    }

    // Ajouter un bouton pour réinitialiser les filtres
    $(document).ready(function() {
        const resetButton = `
        <button class="btn btn-light btn-sm ms-2" onclick="resetFilters()">
            <i class="fas fa-undo me-1"></i>Réinitialiser
        </button>
    `;
        $('.card-body .row').append(`
        <div class="col-12 mt-3 text-end">
            ${resetButton}
        </div>
    `);
    });

    function showClient(id) {
        // Implémenter l'affichage des détails
    }

    function editClient(id) {
        // Implémenter la modification
    }

    function deleteClient(id) {
        // Implémenter la suppression
    }

    // Initialisation des tooltips
    $(function() {
        $('[data-bs-toggle="tooltip"]').tooltip()
    })
</script>

@push("scripts")
<script>
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [0, 'asc']
        ],
        "pageLength": 15,
        language: {
            "emptyTable": "Aucune donnée disponible dans le tableau",
            "lengthMenu": "Afficher _MENU_ éléments",
            "loadingRecords": "Chargement...",
            "processing": "Traitement...",
            "zeroRecords": "Aucun élément correspondant trouvé",
            "paginate": {
                "first": "Premier",
                "last": "Dernier",
                "previous": "Précédent",
                "next": "Suiv"
            },
            "aria": {
                "sortAscending": ": activer pour trier la colonne par ordre croissant",
                "sortDescending": ": activer pour trier la colonne par ordre décroissant"
            },
            "select": {
                "rows": {
                    "_": "%d lignes sélectionnées",
                    "1": "1 ligne sélectionnée"
                },
                "cells": {
                    "1": "1 cellule sélectionnée",
                    "_": "%d cellules sélectionnées"
                },
                "columns": {
                    "1": "1 colonne sélectionnée",
                    "_": "%d colonnes sélectionnées"
                }
            },
            "autoFill": {
                "cancel": "Annuler",
                "fill": "Remplir toutes les cellules avec <i>%d<\/i>",
                "fillHorizontal": "Remplir les cellules horizontalement",
                "fillVertical": "Remplir les cellules verticalement"
            },
            "searchBuilder": {
                "conditions": {
                    "date": {
                        "after": "Après le",
                        "before": "Avant le",
                        "between": "Entre",
                        "empty": "Vide",
                        "equals": "Egal à",
                        "not": "Différent de",
                        "notBetween": "Pas entre",
                        "notEmpty": "Non vide"
                    },
                    "number": {
                        "between": "Entre",
                        "empty": "Vide",
                        "equals": "Egal à",
                        "gt": "Supérieur à",
                        "gte": "Supérieur ou égal à",
                        "lt": "Inférieur à",
                        "lte": "Inférieur ou égal à",
                        "not": "Différent de",
                        "notBetween": "Pas entre",
                        "notEmpty": "Non vide"
                    },
                    "string": {
                        "contains": "Contient",
                        "empty": "Vide",
                        "endsWith": "Se termine par",
                        "equals": "Egal à",
                        "not": "Différent de",
                        "notEmpty": "Non vide",
                        "startsWith": "Commence par"
                    },
                    "array": {
                        "equals": "Egal à",
                        "empty": "Vide",
                        "contains": "Contient",
                        "not": "Différent de",
                        "notEmpty": "Non vide",
                        "without": "Sans"
                    }
                },
                "add": "Ajouter une condition",
                "button": {
                    "0": "Recherche avancée",
                    "_": "Recherche avancée (%d)"
                },
                "clearAll": "Effacer tout",
                "condition": "Condition",
                "data": "Donnée",
                "deleteTitle": "Supprimer la règle de filtrage",
                "logicAnd": "Et",
                "logicOr": "Ou",
                "title": {
                    "0": "Recherche avancée",
                    "_": "Recherche avancée (%d)"
                },
                "value": "Valeur"
            },
            "searchPanes": {
                "clearMessage": "Effacer tout",
                "count": "{total}",
                "title": "Filtres actifs - %d",
                "collapse": {
                    "0": "Volet de recherche",
                    "_": "Volet de recherche (%d)"
                },
                "countFiltered": "{shown} ({total})",
                "emptyPanes": "Pas de volet de recherche",
                "loadMessage": "Chargement du volet de recherche..."
            },
            "buttons": {
                "copyKeys": "Appuyer sur ctrl ou u2318 + C pour copier les données du tableau dans votre presse-papier.",
                "collection": "Collection",
                "colvis": "Visibilité colonnes",
                "colvisRestore": "Rétablir visibilité",
                "copy": "Copier",
                "copySuccess": {
                    "1": "1 ligne copiée dans le presse-papier",
                    "_": "%ds lignes copiées dans le presse-papier"
                },
                "copyTitle": "Copier dans le presse-papier",
                "csv": "CSV",
                "excel": "Excel",
                "pageLength": {
                    "-1": "Afficher toutes les lignes",
                    "_": "Afficher %d lignes"
                },
                "pdf": "PDF",
                "print": "Imprimer"
            },
            "decimal": ",",
            "info": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
            "infoEmpty": "Affichage de 0 à 0 sur 0 éléments",
            "infoThousands": ".",
            "search": "Rechercher:",
            "thousands": ".",
            "infoFiltered": "(filtrés depuis un total de _MAX_ éléments)",
            "datetime": {
                "previous": "Précédent",
                "next": "Suivant",
                "hours": "Heures",
                "minutes": "Minutes",
                "seconds": "Secondes",
                "unknown": "-",
                "amPm": [
                    "am",
                    "pm"
                ],
                "months": [
                    "Janvier",
                    "Fevrier",
                    "Mars",
                    "Avril",
                    "Mai",
                    "Juin",
                    "Juillet",
                    "Aout",
                    "Septembre",
                    "Octobre",
                    "Novembre",
                    "Decembre"
                ],
                "weekdays": [
                    "Dim",
                    "Lun",
                    "Mar",
                    "Mer",
                    "Jeu",
                    "Ven",
                    "Sam"
                ]
            },
            "editor": {
                "close": "Fermer",
                "create": {
                    "button": "Nouveaux",
                    "title": "Créer une nouvelle entrée",
                    "submit": "Envoyer"
                },
                "edit": {
                    "button": "Editer",
                    "title": "Editer Entrée",
                    "submit": "Modifier"
                },
                "remove": {
                    "button": "Supprimer",
                    "title": "Supprimer",
                    "submit": "Supprimer",
                    "confirm": {
                        "1": "etes-vous sure de vouloir supprimer 1 ligne?",
                        "_": "etes-vous sure de vouloir supprimer %d lignes?"
                    }
                },
                "error": {
                    "system": "Une erreur système s'est produite"
                },
                "multi": {
                    "title": "Valeurs Multiples",
                    "restore": "Rétablir Modification",
                    "noMulti": "Ce champ peut être édité individuellement, mais ne fait pas partie d'un groupe. ",
                    "info": "Les éléments sélectionnés contiennent différentes valeurs pour ce champ. Pour  modifier et "
                }
            }
        },
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
</script>
@endpush