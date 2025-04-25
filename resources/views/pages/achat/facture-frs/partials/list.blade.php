{{-- list.blade.php --}}
<div class="row g-3">
    {{-- Filtre dropdown --}}
    <div class="float-end">
        <div class="dropdown">
            <button class="btn btn-light-primary btn-sm dropdown-toggle d-flex align-items-center"
                type="button" data-bs-toggle="dropdown">
                <i class="fas fa-filter me-2"></i>
                Filtrer par
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="filterByDate('today')">Aujourd'hui</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterByDate('week')">Cette semaine</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterByDate('month')">Ce mois</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="filterByStatus('NON_LIVRE')">Non livrées</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterByStatus('PARTIELLEMENT_LIVRE')">Partiellement livrées</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterByStatus('LIVRE')">Livrées</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="filterByPayment('NON_PAYE')">Non payées</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterByPayment('PARTIELLEMENT_PAYE')">Partiellement payées</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterByPayment('PAYE')">Payées</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" onclick="filterByType('SIMPLE')">SIMPLE</a></li>
                <li><a class="dropdown-item" href="#" onclick="filterByType('NORMALISE')">NORMALISE</a></li>
            </ul>
        </div>
    </div>

    {{-- Table des factures --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="facturesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
                            <th class="border-bottom-0">Date Insertion</th>
                            <th class="border-bottom-0">Date Facture</th>
                            <th class="border-bottom-0">Bon Commande</th>
                            <th class="border-bottom-0">Fournisseur</th>
                            <th class="border-bottom-0 text-end">Montant HT</th>
                            <th class="border-bottom-0 text-end">Montant TTC</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factures as $facture)
                        <tr>
                            <td class="text-nowrap py-3">
                                <div class="d-flex align-items-center">
                                    <span class="code-facture me-2">{{ $facture->code }}</span>
                                    @if($facture->validated_at)
                                    <i class="fas fa-check-circle text-success" data-bs-toggle="tooltip" title="Facture validée"></i>
                                    @endif
                                </div>
                            </td>
                            <td>{{ Carbon\Carbon::parse($facture->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                            <td>
                                <a href="#" class="code-commande"
                                    onclick="showBonCommande({{ $facture->bon_commande_id }})">
                                    {{ $facture->bonCommande->code }}
                                </a>
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-fournisseur me-2">
                                        {{ substr($facture->fournisseur->raison_sociale, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $facture->fournisseur->raison_sociale }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="fw-medium">{{ number_format($facture->montant_ht, 2) }} FCFA</div>
                                <small class="text-muted">
                                    TVA: {{ number_format($facture->montant_tva, 2) }} FCFA
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold">{{ number_format($facture->montant_ttc, 2) }} FCFA</div>
                                <small class="text-muted">
                                    AIB: {{ number_format($facture->montant_aib, 2) }} FCFA
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <span class="badge bg-{{ $facture->statut_livraison === 'LIVRE' ? 'success' : ($facture->statut_livraison === 'PARTIELLEMENT_LIVRE' ? 'warning' : 'danger') }}">
                                        {{ str_replace('_', ' ', $facture->statut_livraison) }}
                                    </span>
                                    <span class="badge bg-{{ $facture->statut_paiement === 'PAYE' ? 'success' : ($facture->statut_paiement === 'PARTIELLEMENT_PAYE' ? 'warning' : 'danger') }}">
                                        {{ str_replace('_', ' ', $facture->statut_paiement) }}
                                    </span>
                                </div>
                                <small class="text-muted">
                                    (
                                    @if ($facture->rejected_by)
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3"><i class="fas fa-minus-circle"></i> Rejetée</span>
                                    @elseif ($facture->validated_at)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3"><i class="fas fa-check-circle"></i> Validée</span>
                                    @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3"><i class="fas fa-hourglass-half"></i> En attente</span>
                                    @endif
                                    )
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showFacture({{ $facture->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if(!$facture->validated_at && !$facture->rejected_at)
                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="editFacture({{ $facture->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="validateFacture({{ $facture->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Valider">
                                        <i class="fas fa-check"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="initRejetFacture({{ $facture->id }})"
                                        data-bs-toggle="tooltip" title="Rejeter">
                                        <i class="fas fa-ban"></i>
                                    </button>

                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteFacture({{ $facture->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif

                                    <div class="btn-group ms-1">
                                        <button class="btn btn-sm btn-light-secondary btn-icon"
                                            data-bs-toggle="dropdown">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" target="_blank"
                                                    href="{{ route('factures.print', $facture->id) }}">
                                                    <i class="fas fa-file-pdf me-2"></i>Imprimer
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    onclick="exportExcel({{ $facture->id }})">
                                                    <i class="fas fa-file-excel me-2"></i>Exporter Excel
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucune facture trouvée</h6>
                                    <p class="text-muted small mb-3">Les factures créées apparaîtront ici</p>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFactureModal">
                                        <i class="fas fa-plus me-2"></i>Créer une facture
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
    /* Styles spécifiques pour les factures */
    .code-facture {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--bs-primary);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .code-commande {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--bs-info);
        text-decoration: none;
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: rgba(var(--bs-info-rgb), 0.1);
        border-radius: 0.25rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .badge {
        padding: 0.4em 0.6em;
        font-size: 0.75em;
        font-weight: 500;
    }
</style>

@push('scripts')
<script>
    var apiUrl = "{{ config('app.url_ajax') }}";
    // Fonction pour valider une facture
    function validateFacture(id) {
        Swal.fire({
            title: 'Valider la facture',
            text: 'Êtes-vous sûr de vouloir valider cette facture ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Validation en cours...',
                    html: 'Veuillez patienter...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `${apiUrl}/achat/factures/${id}/validate`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Validation réussie',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: xhr.responseJSON?.message || 'Erreur lors de la validation'
                        });
                    }
                });
            }
        });
    }

    // Autres fonctions JS (showFacture, deleteFacture, etc.)
    // ... (similaires à celles des bons de commande)
</script>

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