<!-- <div class="row d-flex justify-content-center">
    <div class="col-6">
        <input type="search" name="" id="converions-search" class="form-control" placeholder="Rechercher une conversion ...">
    </div>
</div> -->
<!--  -->

<div class="">
    <table id="example1" class="table table-hover align-middle mb-0" id="clientsTable">
        <thead class="bg-light">
            <tr>
                <th class="border-bottom-0 text-nowrap py-3" style="width: 25%;">Désignations des articles</th>
                <th class="border-bottom-0" style="width: 75%;">Conversions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conversions as $articleId=>$articleConversions)
            <tr>
                <td class="text-nowrap py-3">
                    <span class="badge bg-light border rounded text-dark"> {{ $articleConversions->first()->article?->designation }} - ( {{ $articleConversions->first()->article?->code_article }})</span>
                </td>
                <td>
                    <div class="row">
                        @foreach($articleConversions as $conversion)
                        <div class="col-md-12 conversions my-1">
                            <div class="card h-100 border-0 shadow-sm hover-shadow w-100">
                                <div class="card-body p-3">
                                    {{-- En-tête avec actions --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center">
                                            <div class="conversion-icon me-2">
                                                <i class="fas fa-exchange-alt text-warning"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">
                                                    {{ $conversion->uniteSource->code_unite }} → {{ $conversion->uniteDest->code_unite }}
                                                </h6>
                                                <div class="text-muted small">
                                                    {{ $conversion->uniteSource->libelle_unite }} vers {{ $conversion->uniteDest->libelle_unite }}
                                                </div>
                                            </div>
                                        </div>
    
                                        <div class="dropdown">
                                            <button class="btn btn-icon btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0)" onclick="toggleStatutConversion({{ $conversion->id }})">
                                                        <i class="fas {{ $conversion->statut ? 'fa-ban text-warning' : 'fa-check text-success' }} me-2"></i>
                                                        {{ $conversion->statut ? 'Désactiver' : 'Activer' }}
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteConversion({{ $conversion->id }})">
                                                        <i class="far fa-trash-alt me-2"></i>Supprimer
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
    
                                    <div class="row g-2">
                                        {{-- Coefficient --}}
                                        <div class="col-12">
                                            <div class="conversion-details p-2 bg-light rounded">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="conversion-value fw-bold text-warning">1</span>
                                                    <span class="mx-1 text-muted">{{ $conversion->uniteSource->libelle_unite }}</span>
                                                    <i class="fas fa-equals mx-1 text-muted"></i>
                                                    <span class="conversion-value fw-bold text-warning">{{ number_format($conversion->coefficient, 4) }}</span>
                                                    <span class="ms-1 text-muted">{{ $conversion->uniteDest->libelle_unite }}</span>
                                                </div>
                                            </div>
                                        </div>
    
                                        {{-- Badges --}}
                                        <div class="col-12">
                                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                <span class="badge {{ $conversion->statut ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }} rounded-pill">
                                                    <i class="fas fa-circle fs-xs me-1"></i>
                                                    {{ $conversion->statut ? 'Active' : 'Inactive' }}
                                                </span>
    
                                                <span class="badge bg-soft-warning text-warning rounded-pill">
                                                    <i class="far fa-calendar fs-xs me-1"></i>
                                                    {{ $conversion->created_at->locale('fr')->isoFormat('D MMM YYYY') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted mb-1">Aucune conversion trouvée</h6>
                        <p class="text-muted small mb-3">Les conversions que vous ajoutez apparaîtront ici</p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addConversionModal">
                            <i class="fas fa-plus me-2"></i>Ajouter une conversion
                        </button>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    :root {
        --adjiv-orange: #FF9B00;
        --adjiv-orange-rgb: 255, 155, 0;
    }

    /* ... [Reste du CSS avec les couleurs adaptées] ... */
</style>

@push("scripts")
<script>
    const searchInput = document.getElementById("converions-search");
    const resultsList = document.getElementById("convertions-blocks");
    const items = resultsList.getElementsByClassName("conversions");

    searchInput.addEventListener("input", function(e) {
        const filter = searchInput.value.toLowerCase();
        for (let i = 0; i < items.length; i++) {
            const text = items[i].textContent.toLowerCase();
            items[i].style.display = text.includes(filter) ? "" : "none";
        }
    })
</script>

<!-- DATATABLE -->
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