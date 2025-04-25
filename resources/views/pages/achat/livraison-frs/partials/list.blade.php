<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 rounded-3 p-3 shadow-sm">
                <div class="table-responsive">
                    <table id="example1" class="table table-borderless align-middle mb-0" id="livraisonsFournisseurTable">
                        <thead>
                            <tr class="bg-light">
                                <th class="px-4 py-3 text-secondary small text-uppercase">Code BL</th>
                                <th class="py-3 text-secondary small text-uppercase">Date Insertion</th>
                                <th class="py-3 text-secondary small text-uppercase">Date</th>
                                <th class="py-3 text-secondary small text-uppercase">Fournisseur</th>
                                <th class="py-3 text-secondary small text-uppercase">Magasin</th>
                                <th class="py-3 text-secondary small text-uppercase">Transport</th>
                                <th class="py-3 text-secondary small text-uppercase">Articles</th>
                                <th class="py-3 text-secondary small text-uppercase text-center">Statut</th>
                                <th class="pe-4 py-3 text-end" style="min-width: 150px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($livraisons as $livraison)
                            <tr class="border-bottom">
                                <td class="px-4 py-3">
                                    <span class="fw-semibold text-warning">{{ $livraison->code }}</span>
                                </td>
                                <td>{{ Carbon\Carbon::parse($livraison->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td class="py-3 text-muted">{{ $livraison->date_livraison->format('d/m/Y') }}</td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle p-2 bg-warning bg-opacity-10 me-3">
                                            <i class="fas fa-building text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $livraison->fournisseur->raison_sociale }}
                                            </div>
                                            @if ($livraison->fournisseur->telephone)
                                            <div class="text-muted small">
                                                {{ $livraison->fournisseur->telephone }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle p-2 bg-success bg-opacity-10 me-3">
                                            <i class="fas fa-warehouse text-success"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $livraison->depot->libelle_depot }}</div>
                                            <div class="text-muted small">{{ $livraison->depot->code_depot }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if ($livraison->vehicule && $livraison->chauffeur)
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle p-2 bg-info bg-opacity-10 me-3">
                                            <i class="fas fa-truck text-info"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $livraison->vehicule->matricule }}</div>
                                            <div class="text-muted small">{{ $livraison->chauffeur->nom_chauf }}
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <span class="text-muted">Non spécifié</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3">
                                        {{ $livraison->lignes->count() }} article(s)
                                    </span>
                                </td>
                                <td class="py-3 text-center">
                                    @if ($livraison->validated_at)
                                    <span
                                        class="badge rounded-pill bg-success bg-opacity-10 text-success px-3">Validé</span>
                                    @elseif($livraison->rejected_at)
                                    <span
                                        class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3">Rejeté</span>
                                    @else
                                    <span
                                        class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3">En
                                        attente</span>
                                    @endif
                                </td>
                                <td class="pe-4 py-3 text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-link btn-sm text-dark p-2"
                                            onclick="showLivraisonFournisseur({{ $livraison->id }})"
                                            data-bs-toggle="tooltip" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if (!$livraison->validated_at && !$livraison->rejected_at)
                                        <button class="btn btn-link btn-sm text-warning p-2"
                                            onclick="editLivraisonFournisseur({{ $livraison->id }})"
                                            data-bs-toggle="tooltip" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <a target="_blank" href="{{route('livraisons.validate',$livraison->id)}}">Valider</a>

                                        <button class="btn btn-link btn-sm text-success p-2"
                                            onclick="validateLivraisonFournisseur({{ $livraison->id }})"
                                            data-bs-toggle="tooltip" title="Valider">
                                            <i class="fas fa-check"></i>
                                        </button>

                                        <button class="btn btn-link btn-sm text-danger p-2"
                                            onclick="initRejetLivraison({{ $livraison->id }})"
                                            data-bs-toggle="tooltip" title="Rejeter">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        <button class="btn btn-link btn-sm text-danger p-2 btn-delete-livraison"
                                            data-id="{{ $livraison->id }}" data-bs-toggle="tooltip"
                                            title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif

                                        <button class="btn btn-link btn-sm text-secondary p-2"
                                            onclick="printLivraisonFournisseur({{ $livraison->id }})"
                                            data-bs-toggle="tooltip" title="Imprimer">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <div
                                            class="rounded-circle bg-warning bg-opacity-10 p-4 mx-auto mb-4 d-inline-flex">
                                            <i class="fas fa-truck-loading fa-2x text-warning"></i>
                                        </div>
                                        <h6 class="text-dark mb-2">Aucun bon de livraison</h6>
                                        <p class="text-muted small mb-4">Les bons de livraison fournisseurs que vous
                                            créez apparaîtront ici</p>
                                        <button class="btn btn-warning rounded-pill px-4" data-bs-toggle="modal"
                                            data-bs-target="#addLivraisonFournisseurModal">
                                            <i class="fas fa-plus me-2"></i>Créer un bon de livraison
                                        </button>
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

<style>
    .bg-gradient-light {
        background: linear-gradient(to right, #fff, #fff8e1);
    }

    .btn-warning {
        background-color: #ffa000;
        border-color: #ffa000;
        color: white;
    }

    .btn-warning:hover {
        background-color: #ff8f00;
        border-color: #ff8f00;
        color: white;
    }

    .text-warning {
        color: #ffa000 !important;
    }

    .btn-group .btn-link:hover {
        background-color: #f8f9fa;
        border-radius: 50%;
    }

    .table> :not(caption)>*>* {
        padding: 1rem 0.75rem;
    }

    .badge {
        font-weight: 500;
    }

    .empty-state {
        padding: 2rem;
    }
</style>

@push("scripts")
<script>
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [1, 'desc']
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