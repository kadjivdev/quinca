@extends('layouts.rapport.facture')

@section('title', 'Rapport du Stock Disponible')
@section('content')
<br><br>
<div class="col-12">
    <div class="card p-3 border-0 shadow-sm">
        <div class="row justify-content-center">
            <div class="col-6">
                <form action="" method="get">
                    <div class="mb-3">
                        <select name="depot_id" class="form-control" id="depot_select" required>
                            @foreach($depots as $dep)
                            <option value="{{$dep->id}}" @selected($dep->id==$depot->id)>{{$dep->libelle_depot}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <input type="date" name="date_ftr" class="form-control">
                    </div>
                    <br>
                    <button class="btn btn-success w-100">Rechercher....</button>
                </form>
            </div>
        </div>

        <br>
        <h4 class="">Historique du stock du dépôt : <span class="badge bg-light rounded borded text-success">{{$depot->libelle_depot}} @if($date_ftr) - Date filtrée : {{\Carbon\Carbon::parse($date_ftr)->format('d/m/Y')}} @endif </span> </h4>

        <div class="table-responsive">
            <table id="example1" class="table table-hover align-middle mb-0" id="livraisonsTable">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0">Code</th>
                        <th class="border-bottom-0 text-center">Désignation</th>
                        <th class="border-bottom-0">Stock départ</th>
                        <th class="border-bottom-0">Mesure inventaire</th>
                        <th class="border-bottom-0">Inventorié le:</th>
                        <th class="border-bottom-0">Approvisionné</th>
                        <th class="border-bottom-0">Stock disponible</th>
                        <th class="border-bottom-0">Unité de Stock</th>
                        <th class="border-bottom-0">Stock Requête</th>
                        <th class="border-bottom-0">Vente</th>
                        <th class="border-bottom-0">Unité de vente</th>
                        <th class="border-bottom-0">Stock final</th>
                        <th class="border-bottom-0">Unité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $article)
                    <tr>
                        <td><span class="badge bg-light text-dark">{{$article->code_article}}</span></td>
                        <td class="text-center"><span class="badge bg-light text-dark"> {{$article->designation}} </span></td>
                        <td><span class="badge bg-light text-dark">{{number_format($article->qteDepart,2,"."," ")}} </span></td>
                        <td><span class="badge bg-light text-dark">({{$article->inventUniteMesure}}) </span></td>
                        <td><span class="badge bg-light text-dark">{{$article->inventaire?->id}} | {{Carbon\carbon::parse($article->inventaire_date)->locale('fr')->isoFormat("D MMMM YYYY H:m:s")}} </span></td>
                        <td><span class="badge bg-light text-dark">{{number_format($article->qteAppro,2,"."," ")}}</span></td>
                        <td><span class="badge bg-light text-dark">{{number_format($article->stockDisponible,2,"."," ")}}</span></td>
                        <td><span class="badge bg-light text-dark">{{$article->unite_mesure}}</span></td>
                        <td><span class="badge bg-light text-dark">{{number_format($article->qantiteRequete,2,"."," ")}} ({{$article->uniteMesure?->libelle_unite}})</span></td>
                        <td><span class="badge bg-light text-dark">{{number_format($article->qteTotalVendu,2,"."," ")}}</span></td>
                        <td><span class="badge bg-light text-dark">{{$article->uniteMesure?->libelle_unite}}</span></td>
                        <td><span class="badge bg-light text-dark">{{number_format($article->resteStock,2,"."," ")}}</span></td>
                        <td><span class="badge bg-light text-dark">({{$article->uniteMesure?->libelle_unite}})</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-monospace {
        font-family: 'Monaco', 'Consolas', monospace;
    }

    .table-responsive {
        min-height: 300px;
    }

    .badge {
        font-size: 85%;
    }
</style>
@endpush


<!-- DATATABLES -->
@push('scripts')
<script>
    $(document).ready(function() {
        $("#depot_select").select2();
    });

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