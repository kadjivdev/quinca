<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 p-3 shadow-sm">
            <div class="table-responsive">
                <table id="example1" class="example1 table table-hover align-middle mb-0" id="reglementsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
                            <th class="border-bottom-0">Date Insertion</th>
                            <th class="border-bottom-0">Date Règlement</th>
                            <th class="border-bottom-0">Facture</th>
                            <th class="border-bottom-0">Mode</th>
                            <th class="border-bottom-0">Référence</th>
                            <th class="border-bottom-0">Fournisseur</th>
                            <th class="border-bottom-0 text-end">Montant</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reglements as $reglement)
                        <tr>
                            <td class="text-nowrap py-3">
                                <div class="d-flex align-items-center">
                                    <span class="code-reglement me-2">{{ $reglement->code }}</span>
                                    @if ($reglement->validated_at)
                                    <i class="fas fa-check-circle text-success" data-bs-toggle="tooltip"
                                        title="Règlement validé"></i>
                                    @endif
                                </div>
                            </td>
                            <td>{{ Carbon\Carbon::parse($reglement->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $reglement->date_reglement->format('d/m/Y') }}</td>
                            <td>
                                <a href="#" class="code-facture"
                                    onclick="showFacture({{ $reglement->facture_id }})">
                                    {{ $reglement->facture?->code }}
                                </a><br>
                                @foreach($reglement->multiple_factures() as $facture)
                                <a href="#" class="code-facture"
                                    onclick="showFacture({{ $facture->id }})">
                                    <span class="badge bg-dark">{{ $facture->code }} ({{number_format($facture->montant_ttc,2)}}) </span>
                                </a>
                                <hr>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-soft-info text-info">
                                    {{ $reglement->mode_reglement }}
                                </span>
                            </td>
                            <td>{{ $reglement->reference_reglement ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-fournisseur me-2 text-center">
                                        {{ $reglement->facture?->fournisseur->raison_sociale }} <br>
                                        @foreach($reglement->multiple_factures() as $facture)
                                        <span class="badge bg-dark">{{ $facture?->fournisseur->raison_sociale }} </span>
                                        <hr>
                                        @endforeach
                                    </div>
                                    <div>
                                        <div class="fw-medium">
                                            {{ $reglement->facture?->fournisseur->raison_sociale }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold">{{ number_format($reglement->montant_reglement, 2) }} FCFA
                                </div>
                            </td>
                            <td class="text-center">
                                @if ($reglement->validated_at)
                                <span class="badge bg-success">Validé</span>
                                @else
                                <span class="badge bg-warning">En attente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    @can("reglements.view")
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @endcan

                                    @if (!$reglement->validated_at)
                                    @can("reglements.edit")
                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="editReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan

                                    @can("reglements.validate")
                                    <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="validateReglement({{ $reglement->id }})"
                                        data-bs-toggle="tooltip" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan

                                    @can("reglements.delete")
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteReglement({{ $reglement->id }})"
                                        data-bs-toggle="tooltip" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan

                                    @endif

                                    <button class="btn btn-sm btn-light-secondary btn-icon ms-1"
                                        onclick="printReglement({{ $reglement->id }})" data-bs-toggle="tooltip"
                                        title="Imprimer">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucun règlement trouvé</h6>
                                    <p class="text-muted small mb-3">Les règlements créés apparaîtront ici</p>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addReglementModal">
                                        <i class="fas fa-plus me-2"></i>Créer un règlement
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

@push("scripts")
<script>
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