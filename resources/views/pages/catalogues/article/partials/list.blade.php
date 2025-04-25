    <div class="col-12">
        <div class="card p-3 border-0 shadow-sm">
            <form action="{{route('articles.storeMultipleInventaires')}}" method="post">
                @csrf
                @method("PATCH")
                <!-- depots ids -->
                <input type="text" name="depotIds" hidden class="form-control" value="{{$depotIds}}">
                <div class="table-responsive">
                    <table id="example1" class="table table-hover align-middle mb-0" id="livraisonsTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom-0 text-nowrap py-3">N°</th>
                                <th class="border-bottom-0">Code</th>
                                <th class="border-bottom-0 text-center">Désignation</th>
                                <th class="border-bottom-0">Famille</th>
                                <th class="border-bottom-0">Stockable</th>
                                <th class="border-bottom-0" style="min-width: 150px;">Dépôts</th>
                                <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($articles as $article)
                            <tr>
                                <td class="text-nowrap py-3">
                                    <span class="badge bg-light text-dark numero-bl me-2">{{ $loop->iteration }}</span>
                                </td>
                                <td><span class="badge bg-light text-dark">{{$article->code_article}}</span></td>
                                <td class="text-center"><span class="badge bg-light text-dark"> {{$article->designation}} </span></td>
                                <td><span class="badge bg-light text-dark">{{$article->famille?$article->famille->libelle_famille:'---'}}</span></td>
                                <td>
                                    @if(!$article->stockable)
                                    <span class="badge bg-secondary">Non stockable</span>
                                    @else
                                    <span class="badge bg-success">Stockable</span>
                                    @endif
                                </td>

                                <td class="border p-0">
                                    <ul class="m-0" style="width:100%;height:100px!important;overflow-y:scroll;">
                                        @forelse($article->stocks as $stock)
                                        <li class="bg-warning rounded p-2" style="list-style-type: none">
                                            <span class="badge d-block text-dark">Dépôt: {{$stock->depot->libelle_depot}}</span>
                                            <span class="badge d-block d-flex align-items-center">Qte : <input type="number" name="articles[{{$article->id}}][{{$stock->depot_id}}]" class="form-control" value="{{$stock->quantite_reelle}}"></span>
                                            <span class="badge d-block text-dark">Qte vendue: {{number_format($article->qteVendu($stock->depot_id)->sum('quantite'),2,'.','')}}</span>
                                            <span class="badge d-block text-dark">Qte restante: {{number_format($article->reste($stock->depot_id),2,'.','')}}</span>
                                        </li>
                                        <hr>
                                        @empty
                                        <li class="text-center">Ce article n'est disponible dans aucun dépôt!</li>
                                        @endforelse
                                    </ul>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group w-100">
                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                            onclick="editArticle({{ $article->id }})">
                                            <i class="bi bi-pencil me-1"></i>Modifier
                                        </button>
                                        <!-- <button type="button" class="btn btn-outline-success btn-sm"
                                            onclick="updateStock({{ $article->id }})">
                                            <i class="bi bi-box me-1"></i>Stock
                                        </button> -->
                                        <!-- <a target="_blank" href="{{route('articles.show',$article->id)}}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil me-1"></i>Dépôts
                                        </a> -->
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @can("inventaires.create")
                <br>
                <!-- invantaires -->
                <div class="row d-flex justify-content-center">
                    <div class="col-6">
                        <button class="btn btn-sm w-100 btn-dark"><i class="bi bi-plus-lg me-2"></i> Enregistrer un eventaire</button>
                    </div>
                </div>
                @endcan
            </form>
        </div>
    </div>

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