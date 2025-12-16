@extends('layouts.parametre.point_vente')

@push('styles')
@include('pages.parametre.depot.partials.styles')

<style>
    .page-header {
        background: #fff;
        padding: 1rem 1.25rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .header-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 0.5rem;
    }

    .header-pretitle {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .header-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .btn-primary {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 0.375rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.15s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Animation subtile pour l'icône */
    .header-icon i {
        transition: transform 0.2s ease;
    }

    .header-icon:hover i {
        transform: scale(1.1);
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .page-header {
            padding: 0.75rem 1rem;
        }

        .header-icon {
            width: 35px;
            height: 35px;
        }

        .header-title {
            font-size: 1rem;
        }

        .btn-primary {
            padding: 0.4rem 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="content">

    {{-- En-tête de la page --}}
    <div class="page-header">
        <div class="container-fluid p-0">
            <div class="d-flex align-items-center justify-content-between">
                {{-- Section gauche --}}
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <i class="fas fa-warehouse fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ $date }}</div>
                        <h6 class="header-title mb-0">Gestion des inventaires</h6>
                    </div>
                </div>

                {{-- Section droite --}}
                @can("inventaires.create")
                <button type="button" class="btn btn-warning btn-sm d-flex align-items-center" id="showAddInventaireModalBtn">
                    <i class="fas fa-plus me-2"></i>
                    Ajouter un Inventaire
                </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Liste des dépôts --}}
    <div class="row g-3 list mt-3" id="depotsList">
        <!-- les erreurs -->
        @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session()->has("success"))
        <div class="alert alert-success">{{session()->get("success")}}</div>
        @elseif(session()->has("error"))
        <div class="alert alert-danger">{{session()->get("error")}}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h6 class="modal-title fs-5" id="">Dépôt : <span class="badge bg-warning depot-title"> ID: {{$depot?->id}} | {{$depot?->libelle_depot}}</span></h6>
            </div>
            <div class="card-body">
                <table class="table table-hover align-middle mb-0" id="example1">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N°</th>
                            <th class="border-bottom-0 text-center">Date Inventaire</th>
                            <th class="border-bottom-0 text-center">Auteur</th>
                            <th class="border-bottom-0 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="">
                        @foreach($inventaires as $inventaire)
                        <tr>
                            <td class="text-nowrap py-3">
                                <span class="badge bg-light text-dark numero-bl me-2">#{{$loop->iteration}} | ID: {{$inventaire->id}}</span>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark">{{\Carbon\carbon::parse($inventaire->created_at)->locale('fr')->isoFormat('dddd D MMMM YYYY à HH:mm:ss')}}</span></td>
                            <td class="text-center"><span class="badge bg-light text-dark"> {{$inventaire->auteur?->name}} </span></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="w-100 btn btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a target="__blank" class="dropdown-item text-dark" href="{{route('depot.inventaire.details',$inventaire->id)}}">
                                                <i class="fa fa-eye"></i> Voir les détails
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="{{route('depot.inventaireDelete',$inventaire->id)}}">
                                                <i class="far fa-trash-alt me-2"></i>
                                                Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('pages.parametre.depot.partials.add-inventaire-modal')
</div>
@endsection

<!-- DATATABLES -->
@push('scripts')
<script>
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        // "order": [
        //     [7, 'asc']
        // ],
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