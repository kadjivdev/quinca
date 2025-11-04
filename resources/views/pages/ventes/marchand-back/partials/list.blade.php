{{-- list-factures.blade.php --}}
<div class="row g-3">
    {{-- Table des factures --}}

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session()->has("success"))
    <div class="alert alert-success">{{session()->get('success')}}</div>
    @endif

    @if(session()->has("error"))
    <div class="alert alert-danger">{{session()->get('error')}}</div>
    @endif

    <div class="col-12">
        <div class="p-3 card border-0 shadow-sm">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="facturesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N° Marchandise</th>
                            <th class="border-bottom-0">Livraison</th>
                            <th class="border-bottom-0">Dépôt</th>
                            <th class="border-bottom-0">Client</th>
                            <th class="border-bottom-0 text-nowrap py-3">Date Insertion</th>
                            <th class="border-bottom-0">Date concernée</th>
                            <th class="border-bottom-0">Inserée par</th>
                            <th class="border-bottom-0 text-end">Document</th>
                            <th class="border-bottom-0 text-end">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($marchanBacks as $marchand)
                        <tr>
                            <td class="text-nowrap py-3">
                                <div class="d-flex align-items-center">
                                    <span class="numero-facture me-2">{{ $marchand->numero }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="w-100 badge btn bg-primary"
                                    onclick="showLivraison({{ $marchand->livraison->id }})"
                                    data-bs-toggle="tooltip" title="Détails livraison">
                                    <i class="fas fa-eye"></i> {{$marchand->livraison?->numero}}
                                </span>
                            </td>
                            <td>
                                <span class="w-100 badge btn bg-primary">
                                    {{$marchand->depot?->libelle_depot}}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-dark text-white">{{$marchand->livraison->facture->client?->raison_sociale}}</span>
                            </td>
                            <td>{{ Carbon\Carbon::parse($marchand->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $marchand->date->format('d/m/Y') }}</td>
                            <td> <span class="badge bg-light border text-dark">{{$marchand->createdBy?->name}}</span> </td>
                            <td class="text-end fw-medium">
                                @if($marchand->documents)
                                <a target="__blank" href="{{$marchand->documents}}" class="btn btn-sm btn-light-warning btn-icon ms-1"><i class="fas fa-file"></i></a>
                                @else
                                ---
                                @endif
                            </td>

                            <td class="text-end fw-medium">
                                @if(!$marchand->validated_by)
                                <span class="badge bg-primary">En attente</span>
                                @else
                                <span class="badge bg-light text-success">Validée</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="btn-group">
                                    <span class="w-100 badge btn bg-primary"
                                        onclick="showMarchand({{ $marchand->id }})"
                                        data-bs-toggle="tooltip" title="Détails marchandise">
                                        <i class="fas fa-eye"></i>
                                    </span>

                                    @if(!$marchand->validated_by)
                                    @if(auth()->user()->hasRole('CONTROLE INTERNE') || auth()->user()->hasRole('Super Administrateur') )
                                    <a href="{{route('vente.marchand-back.validate',$marchand->id)}}"
                                        class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="return confirm('Voulez-vous vraiment valider ce retour de marchandise??')"
                                        data-bs-toggle="tooltip" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    @endif
                                    @endif

                                    @if(!$marchand->validated_by)
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteFacture({{ $marchand->id }})"
                                        data-bs-toggle="tooltip" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucune marchandises trouvée</h6>
                                    <p class="text-muted small mb-3">Les marchandises que vous créez apparaîtront ici</p>
                                    <button class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addMarchandModal">
                                        <i class="fas fa-plus me-2"></i>Inserer une marchandise
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
    :root {
        --kadjiv-orange: #FFA500;
        --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
    }

    /* Styles pour les numéros de facture */
    .numero-facture {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--kadjiv-orange);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: var(--kadjiv-orange-light);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    /* Avatar client */
    .avatar-client {
        width: 40px;
        height: 40px;
        background-color: var(--kadjiv-orange-light);
        color: var(--kadjiv-orange);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    /* Table */
    .table thead {
        background-color: #f8f9fa;
    }

    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #555;
    }

    /* Badges */
    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
        border-radius: 30px;
    }

    .badge.bg-opacity-10 {
        border: 1px solid currentColor;
    }

    /* Boutons d'action */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }

    .btn-light-primary {
        color: var(--kadjiv-orange);
        background-color: var(--kadjiv-orange-light);
    }

    .btn-light-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .btn-light-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .btn-light-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .btn-light-secondary {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    /* État vide */
    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-state i {
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    /* Menu déroulant d'impression */
    .dropdown-menu {
        min-width: 200px;
        padding: 0.5rem;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
    }

    .dropdown-item:hover {
        background-color: var(--kadjiv-orange-light);
        color: var(--kadjiv-orange);
    }

    /* Animations */
    .btn-icon i {
        transition: transform 0.2s ease;
    }

    .btn-icon:hover i {
        transform: scale(1.1);
    }

    /* Card */
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }
</style>

<script>
    function deleteFacture(id) {
        Swal.fire({
            title: 'Êtes-vous sûr?',
            text: "Cette action est irréversible!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer!',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                // Récupérer le token CSRF
                const token = document.querySelector('meta[name="csrf-token"]').content;

                // Envoyer la requête de suppression
                fetch(`${apiUrl}/vente/marchand-back/${id}/destroy`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire(
                                'Supprimé!',
                                data.message,
                                'success'
                            ).then(() => {
                                // Recharger la page ou mettre à jour la liste
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Erreur!',
                                data.message,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        Swal.fire(
                            'Erreur!',
                            'Une erreur est survenue lors de la suppression',
                            'error'
                        );
                        console.error('Erreur:', error);
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