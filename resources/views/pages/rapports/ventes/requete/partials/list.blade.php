{{-- list-reglements.blade.php --}}
<div class="row g-3">

    {{-- Table des règlements --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class=" table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0">N°</th>
                            <th class="border-bottom-0">Reference</th>
                            <th class="border-bottom-0">Article</th>
                            <th class="border-bottom-0">Dépôt</th>
                            <th class="border-bottom-0">Unité Mesure</th>
                            <th class="border-bottom-0">Qte</th>
                            <th class="border-bottom-0">Preuve</th>
                            <th class="border-bottom-0">Commentaire</th>
                            <th class="border-bottom-0">Insérée par:</th>
                            <th class="border-bottom-0">Insérée le:</th>
                            <th class="border-bottom-0">Validée par:</th>
                            <th class="border-bottom-0">Validée le:</th>
                            <th class="border-bottom-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requetes as $requete)
                        <tr>
                            <td>{{$loop->index +1}}</td>
                            <td>
                                <span class="badge bg-light text-dark rounded">{{$requete->numero}}</span>
                                @if($requete->inventaire)
                                <span class="badge bg-light text-warning rounded">Inventorié: {{$requete->inventaire_id}}</span>
                                @endif
                            </td>
                            <td> {{ $requete->article?->code_article }} {{ $requete->article?->designation }}</td>
                            <td> {{ $requete->depot?->code_depot }} {{ $requete->depot?->libelle_depot }}</td>
                            <td>{{ $requete->uniteMesure?->libelle_unite }} ({{$requete->uniteMesure?->code_unite}})</td>
                            <td>{{ $requete->quantite }}</td>
                            <td> @if($requete->preuve) <a href="{{$requete->preuve}}" target="_blank" class="btn btn-light"><i class="fas fa-file"></i></a> @else --- @endif </td>
                            <td>
                                <textarea rows="2" placeholder="{{$requete->commentaire}}" class="form-control"></textarea>
                            </td>
                            <td><span class="badge bg-light text-dark border rounded">{{$requete->createdBy?->name ?? '---'}}</span></td>
                            <td><span class="badge bg-light text-dark border rounded">{{$requete->created_at}}</span></td>

                            <td><span class="badge bg-light text-dark border rounded">{{$requete->validatedBy?->name ?? '---'}}</span></td>
                            <td><span class="badge bg-light text-dark border rounded">{{$requete->validated_at}}</span></td>

                            <td>
                                @if(!$requete->validate())
                                <div class="dropdown">
                                    <button class="w-100 btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        @can("requetes.edit")
                                        <li>
                                            <a href="{{route('requete_stock.edit',['requete_stock'=> $requete->id])}}" data-bs-toggle="tooltip" class="dropdown-item" data-bs-placement="left" data-bs-title="Editer"> <i class="fas fa-pencil me-2"></i> Modifier </a>
                                        </li>
                                        @endcan

                                        @can("requetes.validate")
                                        <li>
                                            <form action="{{route('requete_stock.validateRequete',['requete_stock'=> $requete->id])}}" method="POST">
                                                @csrf
                                                @method("POST")
                                                <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Valider la requête" onclick="return confirm('Voulez-vous vraiment valider cette requete??')"><i class="fas fa-check"></i> Valider </button>
                                            </form>
                                        </li>
                                        @endcan

                                        @can("requetes.delete")
                                        <li>
                                            <form action="{{route('requete_stock.destroy',$requete->id)}}" method="post">
                                                @csrf
                                                @method("DELETE")
                                                <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Supprimer la requête" onclick="return confirm('Voulez-vous vraiment supprimer cette requete??')"><i class="fas fa-trash"></i> Supprimer</button>
                                            </form>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                                @else
                                ---
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/table.css') }}" rel="stylesheet">

<style>
    .numero-recu {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--bs-primary);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .avatar-client {
        width: 40px;
        height: 40px;
        background-color: var(--bs-light);
        color: var(--bs-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
    }
</style>
</div>

@push("scripts")

<script type="text/javascript">
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [0, 'desc']
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