<div class="card shadow-lg rounded-3 border-0">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: #FFB800;">
                <i class="fas fa-shield-alt me-2"></i>Liste des Rôles
            </h5>
            <button class="btn text-white px-4" style="background-color: #FFB800;" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="fas fa-plus-circle me-2"></i>Ajouter un rôle
            </button>
        </div>
    </div>
    <div class="card-body p-#">
        <div class="table-responsive">
            <table id="example1" class="table table-hover align-middle" id="rolesTable">
                <thead>
                    <tr class="bg-light">
                        <th class="border-0 px-4 py-3 text-secondary">#</th>
                        <th class="border-0 px-4 py-3 text-secondary">Rôle</th>
                        <th class="border-0 px-4 py-3 text-secondary">Permissions</th>
                        <th class="border-0 px-4 py-3 text-secondary">Utilisateurs</th>
                        <th class="border-0 px-4 py-3 text-secondary">Date création</th>
                        <th class="border-0 px-4 py-3 text-end text-secondary">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td class="px-4">{{ $role->id }}</td>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3" style="background-color: rgba(255, 184, 0, 0.1);">
                                    <i class="fas fa-shield-alt" style="color: #FFB800;"></i>
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $role->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4">
                            <!-- Remplacer l'ancienne div des permissions par : -->
                            <div class="d-flex align-items-center">
                                <div class="position-relative">
                                    <button type="button"
                                        class="btn btn-sm btn-light-warning"
                                        data-bs-toggle="popover"
                                        data-bs-placement="top"
                                        data-bs-trigger="focus"
                                        title="Permissions"
                                        data-bs-html="true"
                                        data-bs-content="
                                                <div class='permissions-popover'>
                                                    @php
                                                        $groupedPermissions = $role->permissions->groupBy(function($permission) {
                                                            return explode('.', $permission->name)[0];
                                                        });
                                                    @endphp
                                                    @foreach($groupedPermissions as $group => $permissions)
                                                        <div class='permission-group mb-2'>
                                                            <div class='fw-bold text-uppercase small text-muted mb-1'>{{ $group }}</div>
                                                            @foreach($permissions as $permission)
                                                                <div class='small mb-1'>
                                                                    <i class='fas fa-check text-success me-1'></i>
                                                                    {{ $permission->name }}
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                ">
                                        <i class="fas fa-key me-1" style="color: #FFB800;"></i>
                                        <span>{{ $role->permissions->count() }}</span>
                                        <i class="fas fa-chevron-down ms-1 small" style="color: #FFB800;"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-users text-muted me-2"></i>
                                {{ $role->users->count() }}
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-muted me-2"></i>
                                {{ $role->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex justify-content-end gap-2">
                                @if($role->id !== 1 && $role->name !== 'super-admin')
                                <button type="button"
                                    class="btn btn-sm btn-light-warning edit-role"
                                    data-id="{{ $role->id }}"
                                    data-bs-toggle="tooltip"
                                    title="Modifier">
                                    <i class="fas fa-edit" style="color: #FFB800;"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-sm btn-light-danger delete-role"
                                    data-id="{{ $role->id }}"
                                    data-name="{{ $role->name }}"
                                    data-bs-toggle="tooltip"
                                    title="Supprimer">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                                @else
                                <span class="badge rounded-pill" style="background-color: rgba(255, 184, 0, 0.1); color: #FFB800;">
                                    <i class="fas fa-lock me-1"></i>Rôle système protégé
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="empty-state">
                                <div class="empty-state-icon mb-3">
                                    <i class="fas fa-shield-alt fa-3x" style="color: #FFB800;"></i>
                                </div>
                                <h5 class="empty-state-title fw-medium">Aucun rôle trouvé</h5>
                                <p class="empty-state-description text-muted">
                                    Commencez par ajouter un nouveau rôle.
                                </p>
                                <button class="btn mt-3 text-white" style="background-color: #FFB800;" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter un rôle
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

<!-- Styles identiques à la première liste -->
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-light-warning {
        background-color: rgba(255, 184, 0, 0.1);
        border: none;
    }

    .btn-light-warning:hover {
        background-color: rgba(255, 184, 0, 0.2);
    }

    .btn-light-danger {
        background-color: rgba(220, 53, 69, 0.1);
        border: none;
    }

    .btn-light-danger:hover {
        background-color: rgba(220, 53, 69, 0.2);
    }

    .badge {
        padding: 0.5rem 0.8rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.05);
    }

    .empty-state {
        padding: 3rem 0;
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* Styles pour le popover des permissions */
    .permissions-popover {
        max-height: 300px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .permissions-popover::-webkit-scrollbar {
        width: 4px;
    }

    .permissions-popover::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .permissions-popover::-webkit-scrollbar-thumb {
        background: #FFB800;
        border-radius: 4px;
    }

    .permission-group:not(:last-child) {
        border-bottom: 1px solid #eee;
        padding-bottom: 0.5rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation des tooltips existants
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Initialisation des popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl, {
                sanitize: false
            });
        });
    });
</script>

@push('scripts')
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