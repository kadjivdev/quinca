{{-- list-reglements.blade.php --}}
<div class="row g-3">
    {{-- Filtres --}}

    @if($errors->any())
    {!! implode('', $errors->all('<div>:message</div>')) !!}
    @endif

    @if(session()->has("success"))
    <div class="alert bg-sussess">{{session()->get("success")}}</div>
    @endif

    {{-- Table des règlements --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class=" table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0">N°</th>
                            <th class="border-bottom-0">Date</th>
                            <th class="border-bottom-0">Depot</th>
                            <th class="border-bottom-0">Recette</th>
                            <th class="border-bottom-0">Dépense</th>
                            <th class="border-bottom-0">Recette à verser</th>
                            <th class="border-bottom-0">Montant reverser</th>
                            <th class="border-bottom-0">Commentaires</th>
                            <th class="border-bottom-0">Preuve</th>
                            <th class="border-bottom-0">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reversements as $reversement)
                        <tr>
                            <td>{{$loop->iteration}} </td>
                            <td> <span class="badge bg-light border rounded">{{ \Carbon\carbon::parse($reversement->date)->locale('fr')->isoFormat("D MMMM YYYY")}}</span> </td>
                            <td>{{$reversement->depot?->libelle_depot}} </td>
                            <td> <span class="badge bg-light border rounded">{{ number_format($reversement->recette,2," "," ") }} FCFA</span> </td>
                            <td> <span class="badge bg-light border rounded">{{ number_format($reversement->depense,2," "," ") }} FCFA</span> </td>
                            <td> <span class="badge bg-light border rounded">{{ number_format($reversement->recette_to_reverse,2," "," ") }} FCFA</span> </td>
                            <td> <span class="badge bg-light border rounded">{{ number_format($reversement->montant_reversed,2," "," ") }} FCFA</span> </td>
                            <td>{{ $reversement->commentaire }}</td>
                            <td>@if($reversement->preuve)<a target="_blank" href="{{$reversement->preuve}}" class="btn btn-sm btn-dark"> Voir </a>@else -- @endif</td>
                            <td>
                                @if (is_null($reversement->validated_at))
                                <div class="dropdown">
                                    <button class="w-100 btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                        <li>
                                            <a href="{{route('reversements.edit', $reversement->id)}}" data-bs-toggle="tooltip" class="dropdown-item" data-bs-placement="left" data-bs-title="Editer"> Modifier </a>
                                        </li>

                                        <li>
                                            <form action="{{route('valider-reversement',$reversement->id)}}" method="post">
                                                @csrf
                                                <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Valider la requête" onclick="return confirm('Voulez-vous vraiment valider reversement??')">Valider </button>
                                            </form>
                                        </li>
                                        
                                        <li>
                                            <form action="{{route('reversements.destroy',$reversement->id)}}" method="post">
                                                @csrf
                                                @method("DELETE")
                                                <button type="submit" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Supprimer le reversement" onclick="return confirm('Voulez-vous vraiment supprimer ce reversement??')">Supprimer</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
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