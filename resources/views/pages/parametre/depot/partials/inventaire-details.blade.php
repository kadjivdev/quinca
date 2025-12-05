@extends('layouts.parametre.point_vente')
@push('styles')
@include('pages.parametre.depot.partials.styles')
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
                        <h6 class="header-title mb-0">Gestion des Magasins</h6>
                    </div>
                </div>

                {{-- Section droite --}}
                <button disabled type="button" class="btn btn-primary btn-sm d-flex align-items-center">
                    {{$inventaire->depot?->libelle_depot}} |
                </button>
            </div>
        </div>
    </div>

    {{-- Liste des dépôts --}}
    <div class="row g-3 list mt-3" id="depotsList">
        <div class="card">
            <div class="card-header">
                <h6 class="modal-title fs-5" id="">Détails de l'inventaire : <span class="badge bg-warning">#{{$inventaire->id}} | {{\Carbon\carbon::parse($inventaire->created_at)->locale('fr')->isoFormat('dddd D MMMM YYYY')}} | {{$inventaire->auteur?->name}}</span></h6>
            </div>
            <div class="card-body">
                <table class="table table-hover align-middle mb-0" id="example1">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N°</th>
                            <th class="border-bottom-0">Date Inventaire</th>
                            <th class="border-bottom-0">Auteur</th>
                            <th class="border-bottom-0">Dépôt</th>
                            <th class="border-bottom-0">Article</th>
                            <th class="border-bottom-0">Unité</th>
                            <th class="border-bottom-0">Qte précedente</th>
                            <th class="border-bottom-0">Qte actuelle</th>
                        </tr>
                    </thead>
                    <tbody class="">
                        @foreach($inventaire->details as $detail)
                        <tr>
                            <td class="text-nowrap py-3">
                                <span class="badge bg-light text-dark numero-bl me-2">#{{$loop->iteration}}</span>
                            </td>
                            <td><span class="badge bg-light text-dark">{{\Carbon\carbon::parse($detail->created_at)->locale('fr')->isoFormat('dddd D MMMM YYYY')}}</span></td>
                            <td class="text-center"><span class="badge bg-light text-dark"> {{$inventaire->auteur?->name}} </span></td>
                            <td class="border p-2">
                                <span class="badge bg-light border text-dark"> {{$detail->stockDepot?->depot?->libelle_depot}} </span>
                            </td>
                            <td class="border p-2">
                                <span class="badge bg-light border text-dark">{{$detail->stockDepot?->article?->code_article}} - {{$detail->stockDepot?->article?->designation}} </span>
                            </td>
                            <td class="border p-2">
                                <span class="badge bg-light border text-dark"> {{$detail->stockDepot?->uniteMesure?->libelle_unite}} </span>
                            </td>
                            <td class="border p-2">
                                <span class="badge bg-light border text-dark"> {{number_format($detail->qte_stock,2,"."," ") }} </span>
                            </td>
                            <td class="border p-2">
                                <span class="badge bg-light border text-dark"> {{number_format($detail->qte_reel,2,"."," ") }} </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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