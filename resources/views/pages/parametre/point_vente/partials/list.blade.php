<div class="row g-3">
    {{-- Table des programmations --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="programmationsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N°</th>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
                            <th class="border-bottom-0 text-nowrap py-3">Labelle</th>
                            <th class="border-bottom-0 text-center">Utilisateurs</th>
                            <th class="border-bottom-0 text-center">Magasins</th>
                            <th class="border-bottom-0">Adresse</th>
                            <th class="border-bottom-0">Status</th>
                            <th class="border-bottom-0">Crée le</th>
                            <th class="border-bottom-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pointsVente as $pointVente)
                        <tr>
                            <td class="">{{$loop->index +1}}</td>
                            <td class="text-nowrap py-3">
                                <span class="code-programmation me-2">{{ $pointVente->code_pv }}</span>
                            </td>
                            <td class=""><span class="badge bg-light text-dark">{{ $pointVente->nom_pv }}</span> </td>
                            <td class="">
                                <div class="border p-1" style="width:100%;overflow-y:scroll;height:70px!important">
                                    @forelse($pointVente->utilisateurs as $utilisateur)
                                    <span class="badge bg-light text-dark">{{ $utilisateur->name }}</span>
                                    @empty
                                    <p class="">Aucun utilisateur!</p>
                                    @endforelse
                                </div>
                            </td>
                            <td class="">
                                <div class="border p-1" style="width:100%;overflow-y:scroll;height:70px!important">
                                    @forelse($pointVente->depot as $depot)
                                    <span class="badge bg-light text-dark">{{ $depot->libelle_depot }}</span>
                                    @empty
                                    <p class="">Aucun utilisateur!</p>
                                    @endforelse
                                </div>
                            </td>
                            <td class=""><span class="badge bg-light text-dark">{{ $pointVente->adresse_pv }}</span> </td>
                            <td class="">
                                <span class="badge {{ $pointVente->actif ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                    <i class="fas fa-circle fs-xs me-1"></i>
                                    {{ $pointVente->actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="">
                                <span class="badge bg-light text-muted ms-2 small">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $pointVente->created_at->locale('fr')->isoFormat('D MMMM YYYY, HH:mm')}}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-icon btn-light w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="editPointVente({{ $pointVente->id }})">
                                                <i class="far fa-edit me-2 text-warning"></i>
                                                Modifier
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="toggleStatus({{ $pointVente->id }})">
                                                <i class="fas {{ $pointVente->actif ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                                {{ $pointVente->actif ? 'Désactiver' : 'Activer' }}
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deletePointVente({{ $pointVente->id }})">
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
</div>

<style>
    /* Code de la programmation */
    .code-programmation {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--bs-primary);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    /* Avatar point de vente et fournisseur */
    .avatar-point-vente,
    .avatar-fournisseur {
        width: 35px;
        height: 35px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
    }

    .avatar-fournisseur {
        background-color: rgba(var(--bs-info-rgb), 0.1);
        color: var(--bs-info);
    }

    /* Styles pour les badges */
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
        padding: 0.5em 1em;
    }

    /* Styles pour les boutons */
    .btn-light-primary,
    .btn-light-warning,
    .btn-light-success,
    .btn-light-danger,
    .btn-light-secondary {
        border: none;
        transition: all 0.2s ease;
    }

    .btn-light-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }

    .btn-light-warning {
        background-color: rgba(var(--bs-warning-rgb), 0.1);
        color: var(--bs-warning);
    }

    .btn-light-success {
        background-color: rgba(var(--bs-success-rgb), 0.1);
        color: var(--bs-success);
    }

    .btn-light-danger {
        background-color: rgba(var(--bs-danger-rgb), 0.1);
        color: var(--bs-danger);
    }

    .btn-light-secondary {
        background-color: rgba(var(--bs-secondary-rgb), 0.1);
        color: var(--bs-secondary);
    }

    /* Hover effects */
    .btn-light-primary:hover {
        background-color: var(--bs-primary);
        color: white;
    }

    .btn-light-warning:hover {
        background-color: var(--bs-warning);
        color: white;
    }

    .btn-light-success:hover {
        background-color: var(--bs-success);
        color: white;
    }

    .btn-light-danger:hover {
        background-color: var(--bs-danger);
        color: white;
    }

    .btn-light-secondary:hover {
        background-color: var(--bs-secondary);
        color: white;
    }

    /* Button icon styles */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }

    /* Table styles */
    .table> :not(caption)>*>* {
        padding: 1rem 1rem;
        border-bottom-color: rgba(0, 0, 0, 0.05);
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-state i {
        opacity: 0.5;
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

        .code-programmation {
            font-size: 0.75rem;
        }

        .avatar-point-vente,
        .avatar-fournisseur {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
    }
</style>


@push('scripts')
<!-- DATATABLE -->
<script>
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        // "order": [
        //     [5, 'asc']
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