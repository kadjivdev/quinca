<div class="row g-3">
    {{-- Filtres --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="margin-top: -0.5rem;">
            <div class="card-body py-2">
                <div class="row g-2">
                    {{-- Filtre Article --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">Article</label>
                        <select class="form-select form-select-sm" id="articleFilter" onchange="filterTarifications()">
                            <option value="">Tous les articles</option>
                            @foreach($articles as $article)
                            <option value="{{ $article->id }}">{{ $article->code_article }} - {{ $article->libelle_article }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtre Famille --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small mb-1">Famille d'Articles</label>
                        <select class="form-select form-select-sm" id="familleFilter" onchange="filterTarifications()">
                            <option value="">Toutes les familles</option>
                            @foreach($familles as $famille)
                            <option value="{{ $famille->id }}">{{ $famille->libelle_famille }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bouton Reset --}}
                    <div class="col-md-4">
                        <label class="form-label d-none d-md-block small mb-1">&nbsp;</label>
                        <button class="btn btn-secondary btn-sm w-100" onclick="resetFilters()">
                            <i class="fas fa-redo me-1"></i>Réinitialiser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table des tarifications --}}
    <div class="col-12">
        <div class="card p-3 border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle example1" id="tarificationsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-nowrap">Code Article</th>
                            <th>Libellé</th>
                            @foreach($typesTarifs as $typeTarif)
                            <th class="text-end" data-type-tarif="{{ $typeTarif->id }}">
                                {{ $typeTarif->libelle_type_tarif }}
                            </th>
                            @endforeach
                            <th class="text-end" style="min-width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                        <tr data-article="{{ $article->id }}" data-famille="{{ $article->famille_id }}">
                            <td class="text-nowrap">
                                <span class="code-article">{{ $article->code_article }}</span>
                            </td>
                            <td>
                                <span class="article-libelle">{{ $article->designation }}</span>
                            </td>
                            @foreach($typesTarifs as $typeTarif)
                            @php
                            $tarification = $article->tarifications
                            ->where('type_tarif_id', $typeTarif->id)
                            ->where('statut', true)
                            ->first();
                            @endphp

                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    @if($tarification)
                                    <div class="tarif-value d-flex align-items-center justify-content-between">
                                        <span class="fw-medium">{{ number_format($tarification->prix, 2, ',', ' ') }} FCFA</span>
                                        <div class="btn-group btn-group-sm ms-3 action-buttons">
                                            <button class="btn btn-link p-0 text-warning btn-animated"
                                                onclick="editTarification({{ $tarification->id }})"
                                                title="Modifier ce tarif">
                                                <i class="far fa-edit"></i>
                                            </button>
                                            <button class="btn btn-link p-0 text-danger ms-2 btn-animated"
                                                onclick="toggleTarificationStatus({{ $tarification->id }})"
                                                title="{{ $tarification->statut ? 'Désactiver' : 'Activer' }} ce tarif">
                                                <i class="fas {{ $tarification->statut ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @else
                                    <button class="btn btn-link btn-sm p-0 text-primary btn-animated"
                                        onclick="showAddTarificationModal({{ $article->id }}, {{ $typeTarif->id }})"
                                        title="Ajouter un tarif">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endforeach
                            <td class="text-end">
                                <div class="btn-group action-buttons">
                                    <button class="btn btn-sm btn-light-primary btn-animated"
                                        onclick="showAllTarifications({{ $article->id }})"
                                        title="Voir tous les tarifs">
                                        <i class="fas fa-eye me-1"></i>
                                        <span class="d-none d-md-inline">Voir tout</span>
                                    </button>
                                    <button class="btn btn-sm btn-light-warning btn-animated ms-1"
                                        onclick="showEditAllTarificationsModal({{ $article->id }})"
                                        title="Modifier tous les tarifs">
                                        <i class="fas fa-pencil-alt"></i>
                                        <span class="d-none d-md-inline">Modifier</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ 3 + count($typesTarifs) }}" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-tags fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Aucune tarification trouvée</p>
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
<style>
    /* Styles généraux de la table */
    .table> :not(caption)>*>* {
        padding: 1rem 1.25rem;
        vertical-align: middle;
    }

    /* Style des cellules */
    .table td {
        color: #495057;
    }

    /* Style spécifique pour le code article */
    .code-article {
        font-family: 'Monaco', 'Consolas', monospace;
        color: #2c3e50;
        font-weight: 500;
        padding-left: 0.5rem;
        display: inline-block;
        border-left: 3px solid rgba(var(--bs-primary-rgb), 0.3);
    }

    /* Style pour le libellé */
    .article-libelle {
        color: #2c3e50;
        font-weight: 500;
    }

    /* Style des boutons */
    .btn-light-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-light-primary:hover {
        background-color: rgba(var(--bs-primary-rgb), 0.2);
        transform: translateY(-1px);
    }

    .btn-light-warning {
        background-color: rgba(var(--bs-warning-rgb), 0.1);
        color: var(--bs-warning);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-light-warning:hover {
        background-color: rgba(var(--bs-warning-rgb), 0.2);
        transform: translateY(-1px);
    }

    /* Style des prix */
    .tarif-value {
        position: relative;
        padding: 0.35rem 0.75rem;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .tarif-value:hover {
        background: rgba(0, 0, 0, 0.04);
    }

    .tarif-value .fw-medium {
        color: #2c3e50;
        font-size: 0.95rem;
    }

    /* Style des boutons d'action */
    .action-buttons {
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }

    .action-buttons:hover {
        opacity: 1;
    }

    /* Animation pour les boutons */
    .btn-animated {
        transition: all 0.3s ease;
    }

    .btn-animated:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    /* Style du tableau */
    .table-container {
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.03em;
        padding: 1rem 1.25rem;
        border-bottom: 2px solid rgba(0, 0, 0, 0.05);
    }

    /* Style des états vides */
    .empty-state {
        padding: 3rem;
        text-align: center;
    }

    .empty-state-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1.5rem;
        background: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-state-icon i {
        font-size: 1.5rem;
        color: var(--bs-primary);
    }
</style>

<!-- DATATABLES -->
@push('scripts')
<script>
    $(".example1").DataTable({
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
    }).buttons().container().appendTo('.example1_wrapper .col-md-6:eq(0)');
</script>
@endpush