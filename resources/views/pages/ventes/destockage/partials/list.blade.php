<div class="row d-flex justify-content-center">
    <div class="col-md-6 border bg-light rounded p-3">
        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <br>
        @endif

        @if(session()->has("message"))
        <div class="alert alert-success">{{session()->get('message')}}</div>
        @endif
        
        <form action="{{route('destockages.index')}}" method="GET">
            @csrf
            <div class="row">
                <div class="col-6">
                    <select class="form-select form-control select2-form" name="depot_id">
                        <option value="">Sélectionner un dépôt</option>
                        @foreach($depots as $depot)
                        <option value="{{$depot->id}}">{{$depot->libelle_depot}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6">
                    <select class="form-select form-control select2-form" name="client_id">
                        <option value="">Sélectionner un client</option>
                        @foreach($clients as $client)
                        <option value="{{$client->id}}" class="">{{$client->raison_sociale}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="input-group">
                        <select class="form-select _select2-form" name="article_id">
                            <option value="">Sélectionner un article</option>
                            @foreach ($articles as $article)
                            <option value="{{ $article->id }}">
                                {{ $article->code_article }} - {{$article->designation}}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="invalid-feedback">L'article est requis</div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-6">
                    <label for="debut">Date de début</label>
                    <input type="date" name="debut" class="form-control" id="debut">
                </div>
                <div class="col-6">
                    <label for="debut">Date de fin</label>
                    <input type="date" name="fin" class="form-control" id="fin">
                </div>
            </div>
            <button class="w-100 btn btn-primary mt-2 px-4">
                <i class="fas fa-save me-2"></i>Filtrer
            </button>
        </form>
    </div>
</div>

{{-- Table des destockages --}}
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 p-3 shadow-sm">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="facturesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N°</th>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
                            <th class="border-bottom-0 text-nowrap py-3">Reference</th>
                            <th class="border-bottom-0 text-nowrap py-3">Date Insertion</th>
                            <th class="border-bottom-0">Date d'opération</th>
                            <th class="border-bottom-0">Dépôt</th>
                            <th class="border-bottom-0">Client</th>

                            <th class="border-bottom-0">Inseré par</th>
                            <th class="border-bottom-0">Validé par</th>
                            <th class="border-bottom-0">Validé le</th>
                            <th class="border-bottom-0">Observation</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($destockages as $destockage)
                        <tr>
                            <td class="text-center">{{$loop->iteration}}</td>
                            <td class="text-nowrap py-3">
                                <div class="d-flex align-items-center">
                                    <span class="numero-facture me-2">{{ $destockage->code }}</span>
                                </div>
                            </td>
                            <td class="text-nowrap py-3">
                                <div class="d-flex align-items-center">
                                    <span class="numero-facture me-2">{{ $destockage->reference }}</span>
                                </div>
                            </td>
                            <td>{{ Carbon\Carbon::parse($destockage->created_at)->locale('fr')->isoFormat('D MMMM YYYY') }}</td>
                            <td>{{ Carbon\Carbon::parse($destockage->date_op)->locale('fr')->isoFormat('D MMMM YYYY') }}</td>
                            <td>{{ $destockage->depot?->libelle_depot??'---' }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-client me-2">
                                        {{ substr($destockage->client?->raison_sociale, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $destockage->client?->raison_sociale }}</div>
                                        <div class="text-muted small">{{ $destockage->client?->telephone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark rounded border">{{$destockage->createdBy?->name}}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark rounded border">{{$destockage->validatedBy?->name??'---'}}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark rounded border">{{$destockage->validated_at? Carbon\Carbon::parse($destockage->validated_at)->locale('fr')->isoFormat('D MMMM YYYY'):'---' }} </span>
                            </td>

                            <td>
                                <textarea name="" class="form-control">{{$destockage->Observation}}</textarea>
                            </td>

                            <td class="text-end">
                                <div class="btn-group">
                                    @can("destockage.view")
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showDestockage({{ $destockage }})"
                                        data-bs-toggle="tooltip" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @endcan

                                    <!-- @can("destockage.edit")
                                    <button class="btn btn-sm btn-light-warning btn-icon"
                                        onclick="editDestockage({{ $destockage }})"
                                        data-bs-toggle="tooltip" title="Modifier">
                                        <i class="fas fa-pencil"></i>
                                    </button>
                                    @endcan -->

                                    @if(!$destockage->validated_by)
                                    @can("destockage.validate")
                                    <a href="{{route('destockages.validate',$destockage->id)}}"
                                        class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="confirm('Voulez-vous vraiment valider ce destockage??')"
                                        data-bs-toggle="tooltip" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    @endcan

                                    @can("destockage.delete")
                                    <form action="{{route('destockages.destroy',$destockage->id)}}" method="post">
                                        @csrf
                                        @method("DELETE")
                                        <button type="submit" class="btn btn-sm btn-light-danger btn-icon ms-1"
                                            onclick="confirm('Voulez-vous vraiment supprimer ce destockage??')"
                                            data-bs-toggle="tooltip" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                    @else
                                    ---
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucun destockage trouvé</h6>
                                    <p class="text-muted small mb-3">Le destockage que vous créez apparaîtront ici</p>
                                    <button class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addDestockageModal">
                                        <i class="fas fa-plus me-2"></i>Créer un destockage
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

@push("scripts")
<script>
    $(document).ready(function() {
        $("._select2-form").select2({
            theme: 'bootstrap-5',
            width: '100%',
        })
    })

    // ✅ DataTable pour la liste principale
    let mainTable = null;
    let detailTable = null;

    $(document).ready(function() {
        // ==========================
        // 1. DataTable liste principale
        // ==========================
        mainTable = $("#example1").DataTable({
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

        // ==========================
        // 2. DataTable dans le modal de détail
        // ==========================
        $('#showDestockageModal').on('shown.bs.modal', function() {
            // Détruire l'instance précédente
            if (detailTable) {
                detailTable.destroy();
                detailTable = null;
            }

            detailTable = $("#detailtable").DataTable({
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
            }).buttons().container().appendTo('#detailtable_wrapper .col-md-6:eq(0)');
        })

    })
</script>
@endpush