@extends('layouts.ventes.client')

@push('styles')
<style>
    /* Z-index fixes */
    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
    }

    .select2-container {
        z-index: 2000 !important;
    }

    .select2-dropdown {
        z-index: 2001 !important;
    }

    /* Select2 styling */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #dee2e6;
    }

    .ligne-facture {
        transition: all 0.3s ease;
    }

    .ligne-facture.loading {
        opacity: 0.6;
        pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="content container">
    <div class="row g-3 list mt-3 " id="stockEntriesList">
        <div class="col-12">
            <a href="{{route('vente.clients.index')}}" class="btn btn-sm btn-light border">Retour</a>
            <br>
            <div class="card mt-3 p-3 shadow-sm">
                <form action="" method="get" class="p-3 shadow border rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" class="form-control" name="debut" required>
                        </div>
                        <div class="col-md-4">
                            <input type="date" class="form-control" name="fin" required>
                        </div>
                        <div class="col-md-4">
                            <select name="client_id" id="client" class="form-control">
                                <option value="">Selectionnez un client</option>
                                @foreach($clients as $clt)
                                <option value="{{$clt->id}}" @if($client!=null) @selected($client->id==$clt->id)@endif> {{$clt->raison_sociale}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <div>
                        <button class="btn btn-dark border btn-sm w-100">Filtrer par période</button>
                    </div>
                </form>
                <br>
                <br>
                @if($debut || $fin)
                <div class="alert alert-secondary">
                    Historique de la période du <span class="badge bg-light text-dark border rounded">{{$debut}}</span> au <span class="badge bg-light text-dark border rounded">{{$fin}}</span>
                    @if($client)
                    pour le client <span class="badge bg-light text-dark border rounded">{{$client->raison_sociale}}</span>
                    @endif
                </div>
                @endif
                <div class="table-responsive">
                    <h5 class="mb-5">L'historique des opérations de <a href="{{route('vente.clients.allHistoriqueComptes')}}" class="btn btn-sm bg-primary text-white">Actualiser la page</a></h5>
                    <h5 class="mb-5">Montant total : <strong class="badge bg-light border text-dark">{{number_format($totalAmount,2,' ',' ')}} Fcfa</h5>

                    <table id="example1" class="table table-hover align-middle mb-0" id="clientsTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom-0 text-nowrap py-3">N°</th>
                                <th class="border-bottom-0 text-nowrap py-3">Client</th>
                                <th class="border-bottom-0 text-nowrap py-3">Inséré le</th>
                                <th class="border-bottom-0 text-nowrap py-3">Date Opération</th>
                                <th class="border-bottom-0">Opération</th>
                                <th class="border-bottom-0 text-nowrap py-3">Detail facture</th>
                                <th class="border-bottom-0">Réference</th>
                                <th class="border-bottom-0">Débit</th>
                                <th class="border-bottom-0">Crédit</th>
                                <th class="border-bottom-0">Opérateur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($compteClients as $compte)
                            <tr>
                                <td class="text-nowrap py-3">
                                    <span class="code-client">{{ $loop->iteration }}</span>
                                </td>
                                <td><span class="badge bg-light border text-dark">{{$compte->client?->raison_sociale}}</span></td>
                                <td><span class="badge bg-light border rounded text-dark">{{ $compte->createdDateFormated }} </span></td>
                                <td><span class="badge bg-light border rounded text-dark">{{ $compte->dateFormated }} </span></td>
                                <td>
                                    @if($compte->type_op=="FAC_CLT")
                                    Facture client <span class="badge bg-light text-dark">{{$compte->factureClient?->numero}}</span>
                                    @endif

                                    @if($compte->type_op=="FAC_REV")
                                    Facture revendeur <span class="badge bg-light text-dark">{{$compte->factureRevendeur?->numero}}</span>
                                    @endif

                                    @if($compte->type_op=="REG_CLT")
                                    Règlement client <span class="badge bg-light text-dark">{{$compte->reglementClient?->numero}}</span>
                                    @endif

                                    @if($compte->type_op=="REG_REV")
                                    Règlement revendeur <span class="badge bg-light text-dark">{{$compte->reglementRevendeur?->numero}}</span>
                                    @endif

                                    @if($compte->type_op=="AC_CLT")
                                    Accompte sur le compte du client <span class="badge bg-light text-dark">{{$compte->accompteClient?->reference}}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($compte->type_op=="REG_CLT")
                                    <button class="btn btn-sm btn-light"
                                        data-bs-toggle="tooltip" title="Détails"
                                        onclick="showFacture({{$compte->reglementClient?->facture_client_id}})"><i class="fas fa-eye"></i></button>
                                    @elseif($compte->type_op=="REG_REV")
                                    <button class="btn btn-sm btn-light"
                                        data-bs-toggle="tooltip" title="Détails"
                                        onclick="showFactureRev({{$compte->reglementClient?->facture_client_id}})"><i class="fas fa-eye"></i></button>
                                    @elseif($compte->factureClient)
                                    <button class="btn btn-sm btn-light"
                                        data-bs-toggle="tooltip" title="Détails"
                                        onclick="showFacture({{$compte->facture_client_id}})"><i class="fas fa-eye"></i></button>
                                    @elseif($compte->factureRevendeur)
                                    <button class="btn btn-sm btn-light"
                                        data-bs-toggle="tooltip" title="Détails"
                                        onclick="showFactureRev({{$compte->facture_revendeur_id}})"><i class="fas fa-eye"></i></button>
                                    @else
                                    --
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light border text-dark">
                                        <!-- Facture client -->
                                        @if($compte->factureClient)
                                        {{$compte->factureClient?->reference_recu}}
                                        @elseif($compte->reglementClient)
                                        <!-- Reglement client -->
                                        {{$compte->reglementClient->reference_preuve??$compte->reglementClient->facture->reference_recu}}
                                        @elseif($compte->reglementRevendeur)
                                        <!-- Reglement revendeur -->
                                        {{$compte->reglementRevendeur?->reference_preuve}}
                                        @elseif($compte->accompteClient)
                                        <!-- client accompte -->
                                        {{$compte->accompteClient?->reference}}
                                        @else
                                        ---
                                        @endif
                                    </span>
                                </td>
                                <td class="bg-secondary text-white">
                                    @if(in_array($compte->type_op,["FAC_CLT","FAC_REV","AC_CLT"]))
                                    {{number_format($compte->montant_op,0," "," ")}} FCFA
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="bg-light text-dark">
                                    @if(in_array($compte->type_op,["REG_CLT","REG_REV"]))
                                    {{number_format($compte->montant_op,0," "," ")}} FCFA
                                    @else
                                    -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{$compte->user->name}}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h6 class="text-muted mb-1">Aucune opération trouvée</h6>
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
</div>
@endsection>

@include('pages.ventes.client.partials.show-facture-modal')

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

@push("scripts")
<script>
    $('#client').select2({
        placeholder: "Sélectionner un client",
        allowClear: true
    });

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