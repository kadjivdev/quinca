<div class="col-12">
    <div class="card p-3 border-0 shadow-sm">
        <div class="table-responsive">
            <table id="example1" class="table table-hover align-middle mb-0" id="livraisonsTable">
                <thead class="bg-light">
                    <tr>
                        <th class="border-bottom-0 text-nowrap py-3">N°</th>
                        <th class="border-bottom-0">Code</th>
                        <th class="border-bottom-0">Libelle</th>
                        <th class="border-bottom-0">Point de vente</th>
                        <th class="border-bottom-0">Adresse</th>
                        <th class="border-bottom-0">Phone</th>
                        <th class="border-bottom-0 text-center">Status</th>
                        <th class="border-bottom-0">Type</th>
                        <th class="border-bottom-0">Crée le</th>
                        <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($depots as $depot)
                    <tr>
                        <td class="text-nowrap py-3">
                            <span class="badge bg-light text-dark numero-bl me-2">{{ $loop->iteration }}</span>
                        </td>
                        <td><span class="badge bg-light text-dark">{{$depot->code_depot}}</span></td>
                        <td class="text-center"><span class="badge bg-light text-dark"> {{$depot->libelle_depot}} </span></td>
                        <td><span class="badge bg-light text-dark">{{$depot->pointsVente?->nom_pv}}</span></td>
                        <td>{{$depot->adresse_depot}}</td>
                        <td>{{$depot->tel_depot?$depot->tel_depot:'--'}}</td>
                        <td>
                            @if(!$depot->actif)
                            <span class="badge bg-secondary">Inactif</span>
                            @else
                            <span class="badge bg-success">Actif</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-warning">{{ $depot->typeDepot ? $depot->typeDepot->code_type_depot : 'Non catégorisé' }}</span>
                        </td>

                        <td>
                            <span class="badge bg-light text-muted ms-2 small">
                                <i class="far fa-clock me-1"></i>
                                {{ $depot->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="w-100 btn btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)"
                                            onclick="editDepot({{ $depot->id }})">
                                            <i class="far fa-edit me-2 text-warning"></i>
                                            Modifier
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#"
                                            onclick="toggleDepotStatus({{ $depot->id }})">
                                            <i
                                                class="fas {{ $depot->actif ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                            {{ $depot->actif ? 'Désactiver' : 'Activer' }}
                                        </a>
                                    </li>
                                    <!-- @can("inventaires.view") -->
                                    <li>
                                        <a class="dropdown-item" href="#"
                                            data-bs-toggle="modal" data-bs-target="#inventairesModal"
                                            onclick="showInventories({{ $depot}})">
                                            <i
                                                class="fas fa-eye me-2"></i>
                                            Inventaires
                                        </a>
                                    </li>
                                    <!-- @endcan -->
                                    @if (!$depot->depot_principal)
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)"
                                            onclick="deleteDepot({{ $depot->id }})">
                                            <i class="far fa-trash-alt me-2"></i>
                                            Supprimer
                                        </a>
                                    </li>
                                    @endif
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