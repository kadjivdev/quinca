<div class="row g-3">
    {{-- Section Filtres --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row g-3">
                    <form action="{{route('transport-mouvements.index')}}" method="get">
                        <div class="row">
                            {{-- Filtre Client --}}
                            <div class="col-md-3">
                                <label class="form-label small">Client</label>
                                <select class="form-select alert-select2 form-select-sm" name="client_id" id="alert-select2">
                                    <option value="">Tous les clients</option>
                                    @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected($client->id==request()->get("client_id"))>{{ $client->code_client }} - {{ $client->raison_sociale }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Moyen d etransport --}}
                            <div class="col-md-3">
                                <label class="form-label small">Moyen de transport</label>
                                <select class="form-select alert-select2 form-select-sm" name="transportation_id" id="alert-select2">
                                    <option value="">Tous les moyens de transport</option>
                                    @foreach ($transportations as $transportation)
                                    <option value="{{ $transportation->id }}" @selected($transportation->id==request()->get("transportation_id"))>{{ $transportation->matricule }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtre Période --}}
                            <div class="col-md-5">
                                <label class="form-label small">Période</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" class="form-control" id="dateDebut" name="date_debut">
                                    <span class="input-group-text">au</span>
                                    <input type="date" class="form-control" id="dateFin" name="date_fin">
                                </div>
                            </div>

                        </div>
                        <div class="d-flex justify-content-center">
                            {{-- Bouton réinitialiser --}}
                            <button type="submit" class="w-50 mt-3 btn btn-light btn-sm">
                                <i class="fas fa-undo me-1"></i>
                                Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Table des mouvements --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="acomptesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">ID</th>
                            <th class="border-bottom-0 text-nowrap py-3">Référence</th>
                            <th class="border-bottom-0 text-nowrap py-3">Client</th>
                            <th class="border-bottom-0">Date opération</th>
                            <th class="border-bottom-0">Moyen de transport</th>
                            <th class="border-bottom-0 text-end">Montant</th>
                            <th class="border-bottom-0 text-end">Preuve</th>
                            <th class="border-bottom-0">Commentaire</th>
                            <th class="border-bottom-0">Créé par</th>
                            <th class="border-bottom-0 text-end" style="min-width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mouvements as $mouvement)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td class="text-nowrap py-3">
                                <span class="code-reference">{{ $mouvement->reference }}</span>
                            </td>
                            <td class="text-nowrap py-3">
                                <span class="bg-light text-dark shadow rounded border badge">{{ $mouvement->client?->raison_sociale }}</span>
                            </td>
                            <td class="text-nowrap py-3">
                                <span class="code-reference">{{ $mouvement->formated_date }}</span>
                            </td>
                            <td class="text-nowrap py-3">
                                <span class="code-reference">{{ $mouvement->transportation?->matricule }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-medium montant">
                                    {{ number_format($mouvement->montant, 0, ',', ' ') }} F
                                </span>
                            </td>
                            <td class="text-center">
                                @if($mouvement->preuve)
                                <a href="{{ $mouvement->preuve}}" target="_blank" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Voir la preuve">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $mouvement->comment ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $mouvement->createdBy?->name??'—' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">

                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="editMouvement({{ $mouvement->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!--  -->
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteAcompte({{ $mouvement->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucun mouvement de transport trouvé</h6>
                                    <p class="text-muted small mb-3">Les mouvement de transport que vous enregistrez apparaîtront ici</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTransportMouvementModal">
                                        <i class="fas fa-plus me-2"></i>Nouveau mouvement de transport
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

<style>
    /* On garde les mêmes styles que pour les clients en ajoutant quelques spécificités pour les acomptes */

    /* Référence acompte */
    .code-reference {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--kadjiv-orange);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: var(--kadjiv-orange-light);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    /* Badge pour les types de paiement */
    .badge[class*="bg-opacity-10"] {
        padding: 0.5em 0.8em;
        font-weight: 500;
        font-size: 0.75rem;
    }

    /* Montant */
    .montant {
        font-family: 'Consolas', monospace;
        font-size: 0.875rem;
    }

    /* Style pour la période */
    .input-group-sm .form-control[type="date"] {
        min-width: 130px;
    }
</style>

<script>
    // Fonction pour supprimer un acompte
    function deleteAcompte(id) {
        Swal.fire({
            title: 'Confirmer la suppression',
            text: "Voulez-vous vraiment supprimer ce versement ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                        url: `${apiUrl}/vente/transport-mouvements/${id}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    })
                    .then(response => {
                        if (!response.success) {
                            throw new Error(response.message || 'Erreur lors de la suppression');
                        }
                        return response;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            error.responseJSON?.message || 'Erreur lors de la suppression'
                        );
                    });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Toast.fire({
                    icon: 'success',
                    title: 'Mouvement de transport supprimé avec succès'
                });
                window.location.reload(); // Rafraîchir la liste
            }
        });
    }

    // Fonction pour valider un acompte
    function validateAcompte(id) {
        Swal.fire({
            title: 'Confirmer la validation',
            text: 'Êtes-vous sûr de vouloir valider ce versement ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/vente/transport-mouvement/validate/${id}`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            window.location.reload();
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erreur lors de la validation'
                        });
                    }
                });
            }
        });
    }
</script>

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