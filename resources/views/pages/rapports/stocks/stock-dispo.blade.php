@extends('layouts.rapport.facture')

@section('title', 'Rapport du Stock Disponible')
@section('content')
<br><br>
    <div class="container-fluid">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Magasin actuel : {{ $selectedDepot->libelle_depot }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('rapports.stock.changeDepot') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <select class="form-select" name="depot_id" onchange="this.form.submit()">
                            @foreach ($depots as $depot)
                                <option value="{{ $depot->id }}" {{ $selectedDepot->id == $depot->id ? 'selected' : '' }}>
                                    {{ $depot->libelle_depot }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Stock Disponible</h5>
                <div>
                    <button class="btn btn-sm btn-primary" onclick="exportStock()">
                        <i class="fas fa-file-excel me-1"></i> Exporter
                    </button>
                    <button class="btn btn-sm btn-info ms-2" onclick="printStock()">
                        <i class="fas fa-print me-1"></i> Imprimer
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example1" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Article</th>
                                <th>Unité</th>
                                <th class="text-end">Qté Réelle</th>
                                <th class="text-end">Qté Réservée</th>
                                <th class="text-end">Qté Disponible</th>
                                <th class="text-end">Prix Moyen</th>
                                <th class="text-end">Valeur Stock</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stocks as $stock)
                                <tr @if ($stock['statut'] === 'Alerte') class="table-danger" @endif>
                                    <td>{{ $stock['article']['code'] }}</td>
                                    <td>{{ $stock['article']['designation'] }} ({{ $stock['article']['unite'] }})</td>
                                    <td>{{$stock["unite_stock"]}} </td>
                                    <td class="text-end">{{ number_format($stock['quantite_reelle'], 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($stock['quantite_reservee'], 2, ',', ' ') }}</td>
                                    <td class="text-end">{{ number_format($stock['quantite_disponible'], 2, ',', ' ') }}</td>
                                    <!-- <td class="text-end">{{ number_format($stock['prix_moyen'], 0, ',', ' ') }} FCFA</td> -->
                                    <td class="text-end">---</td>
                                    <td class="text-end">{{ number_format($stock['valeur_stock'], 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        @switch($stock['statut'])
                                            @case('Alerte')
                                                <span class="badge bg-danger">Alerte</span>
                                                @break
                                            @case('Minimum')
                                                <span class="badge bg-warning">Minimum</span>
                                                @break
                                            @case('Maximum')
                                                <span class="badge bg-info">Maximum</span>
                                                @break
                                            @default
                                                <span class="badge bg-success">Normal</span>
                                        @endswitch
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

@push('scripts')
    <script>
        function exportStock() {
            const depot_id = document.querySelector('select[name="depot_id"]').value;
            window.location.href = `{{ route('rapports.stock.export') }}?depot_id=${depot_id}`;
        }

        function printStock() {
            const depot_id = document.querySelector('select[name="depot_id"]').value;
            window.open(`{{ route('rapports.stock.print') }}?depot_id=${depot_id}`, '_blank');
        }
    </script>
@endpush

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
