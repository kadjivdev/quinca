<div class="card shadow-lg rounded-3 border-0">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="color: #FFB800;">
                <i class="fas fa-users me-2"></i>Liste des Utilisateurs
            </h5>
            <button class="btn text-white px-4" style="background-color: #FFB800;" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus-circle me-2"></i>Ajouter un utilisateur
            </button>
        </div>
    </div>
    <br>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="example1 table table-hover align-middle" id="usersTable">
                <thead>
                    <tr class="bg-light">
                        <th class="border-0 px-4 py-3 text-secondary">#</th>
                        <th class="border-0 px-4 py-3 text-secondary">Utilisateur</th>
                        <th class="border-0 px-4 py-3 text-secondary">Email</th>
                        <th class="border-0 px-4 py-3 text-secondary">Rôle</th>
                        <th class="border-0 px-4 py-3 text-secondary">Point de Vente</th>
                        <th class="border-0 px-4 py-3 text-secondary">Date création</th>
                        <th class="border-0 px-4 py-3 text-secondary">Statut</th>
                        <th class="border-0 px-4 py-3 text-end text-secondary">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="align-middle">
                        <td class="px-4">{{ $user->id }}</td>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3" style="background-color: rgba(255, 184, 0, 0.1);">
                                    <i class="fas fa-user" style="color: #FFB800;"></i>
                                </div>
                                <div>
                                    <span class="fw-medium">{{ $user->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-muted me-2"></i>
                                {{ $user->email }}
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                <span class="badge rounded-pill" style="background-color: rgba(255, 184, 0, 0.1); color: #FFB800;">
                                    <i class="fas fa-shield-alt me-1"></i>{{ $role->name }}
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-store text-muted me-2"></i>
                                {{ $user->pointDeVente->nom_pv ?? 'Non assigné' }}
                            </div>
                        </td>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-muted me-2"></i>
                                {{ $user->created_at->format('d/m/Y H:i') }}
                            </div>
                        </td>
                        <td class="px-4">
                            <span class="badge rounded-pill bg-{{ $user->is_active ? 'success' : 'danger' }}-subtle text-{{ $user->is_active ? 'success' : 'danger' }}">
                                <i class="fas fa-{{ $user->is_active ? 'check-circle' : 'times-circle' }} me-1"></i>
                                {{ $user->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-4">
                            <div class="d-flex justify-content-end gap-2">
                                @if($user->id != 1) {{-- Si ce n'est pas le premier utilisateur --}}
                                <button type="button"
                                    class="btn btn-sm btn-light-warning edit-user"
                                    data-id="{{ $user->id }}"
                                    data-bs-toggle="tooltip"
                                    title="Modifier"
                                    @if($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) disabled @endif>
                                    <i class="fas fa-edit" style="color: #FFB800;"></i>
                                </button>

                                @if(!$user->hasRole('super-admin') || auth()->user()->hasRole('super-admin'))
                                <button type="button"
                                    class="btn btn-sm btn-light-danger delete-user"
                                    data-id="{{ $user->id }}"
                                    data-name="{{ $user->name }}"
                                    data-bs-toggle="tooltip"
                                    title="Supprimer">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                                @endif
                                @else
                                <span class="badge rounded-pill" style="background-color: rgba(255, 184, 0, 0.1); color: #FFB800;">
                                    <i class="fas fa-lock me-1"></i>Compte système
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="empty-state">
                                <div class="empty-state-icon mb-3">
                                    <i class="fas fa-users fa-3x" style="color: #FFB800;"></i>
                                </div>
                                <h5 class="empty-state-title fw-medium">Aucun utilisateur trouvé</h5>
                                <p class="empty-state-description text-muted">
                                    Commencez par ajouter un nouvel utilisateur.
                                </p>
                                <button class="btn mt-3 text-white" style="background-color: #FFB800;" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter un utilisateur
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

    .table> :not(caption)>*>* {
        padding: 1rem 0;
    }

    .badge {
        padding: 0.5rem 0.8rem;
        font-weight: 500;
    }

    .empty-state {
        padding: 3rem 0;
    }

    .card {
        margin-bottom: 2rem;
    }

    /* Animation des boutons */
    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    /* Animation des badges */
    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: scale(1.05);
    }
</style>

<script>
    // Initialisation des tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@push('scripts')
<script>
    $(".example1").DataTable({
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
    }).buttons().container().appendTo('.example1_wrapper .col-md-6:eq(0)');
</script>
@endpush