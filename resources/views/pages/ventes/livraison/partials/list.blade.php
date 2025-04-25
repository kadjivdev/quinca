{{-- list-livraisons.blade.php --}}
<div class="row g-3">
    {{-- Filtres --}}

    {{-- Table des livraisons --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="livraisonsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N° BL</th>
                            <th class="border-bottom-0">Date Insertion</th>
                            <th class="border-bottom-0">Date Livraison</th>
                            <th class="border-bottom-0">N° Facture</th>
                            <th class="border-bottom-0">Client</th>
                            <th class="border-bottom-0">Magasin</th>
                            <th class="border-bottom-0">Articles</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($livraisons as $livraison)
                        <tr>
                            <td class="text-nowrap py-3">
                                <span class="numero-bl me-2">{{ $livraison->numero }}</span>
                            </td>
                            <td>{{ Carbon\Carbon::parse($livraison->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $livraison->date_livraison->format('d/m/Y') }}</td>
                            <td>
                                <a href="#" class="text-decoration-none">
                                    {{ $livraison->facture->numero }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-client me-2">
                                        {{ substr($livraison->facture->client->raison_sociale, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $livraison->facture->client->raison_sociale }}
                                        </div>
                                        <div class="text-muted small">{{ $livraison->facture->client->telephone }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-warehouse text-primary me-2"></i>
                                    {{ $livraison->depot->libelle }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $livraison->lignes->count() }} article(s)
                                </span>
                            </td>
                            <td class="text-center">
                                @switch($livraison->statut)
                                @case('brouillon')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3">Brouillon</span>
                                @break

                                @case('valide')
                                <span class="badge bg-success bg-opacity-10 text-success px-3">Validé</span>
                                @break

                                @default
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3">Annulé</span>
                                @endswitch
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    {{-- Voir détails --}}
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showLivraison({{ $livraison->id }})" data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if ($livraison->statut === 'brouillon')
                                    {{-- Modifier --}}
                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="editLivraison({{ $livraison->id }})" data-bs-toggle="tooltip"
                                        title="Modifier"
                                        {{ $livraison->statut !== 'brouillon' ? 'disabled' : '' }}>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    {{-- Valider --}}
                                    <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="validateLivraison({{ $livraison->id }})"
                                        data-bs-toggle="tooltip" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </button>

                                    {{-- Supprimer --}}
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteLivraison({{ $livraison->id }})"
                                        data-bs-toggle="tooltip" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif

                                    {{-- Imprimer --}}
                                    <button class="btn btn-sm btn-light-secondary btn-icon ms-1"
                                        onclick="printLivraison({{ $livraison->id }})" data-bs-toggle="tooltip"
                                        title="Imprimer">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucun bon de livraison trouvé</h6>
                                    <p class="text-muted small mb-3">Les bons de livraison que vous créez
                                        apparaîtront ici</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#addLivraisonModal">
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

<style>
    :root {
        --kadjiv-orange: #FFA500;
        --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
    }

    /* Numéro BL */
    .numero-bl {
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
        text-transform: uppercase;
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

    /* Badge livraison */
    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
        border-radius: 30px;
    }

    .badge.bg-opacity-10 {
        border: 1px solid currentColor;
    }

    .badge.bg-primary {
        background-color: var(--kadjiv-orange-light) !important;
        color: var(--kadjiv-orange);
        border: 1px solid var(--kadjiv-orange);
    }

    /* Depot icon */
    .text-primary {
        color: var(--kadjiv-orange) !important;
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

    /* Hover effects */
    .btn-light-primary:hover {
        background-color: rgba(255, 165, 0, 0.2);
    }

    .btn-light-warning:hover {
        background-color: rgba(255, 193, 7, 0.2);
    }

    .btn-light-success:hover {
        background-color: rgba(25, 135, 84, 0.2);
    }

    .btn-light-danger:hover {
        background-color: rgba(220, 53, 69, 0.2);
    }

    .btn-light-secondary:hover {
        background-color: rgba(108, 117, 125, 0.2);
    }

    /* Links */
    .table a {
        color: var(--kadjiv-orange);
        text-decoration: none;
    }

    .table a:hover {
        color: #e69400;
        text-decoration: underline;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem;
    }

    .empty-state i {
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    /* Animation */
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

    /* Disabled button */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .btn-group {
            flex-wrap: wrap;
        }

        .btn-icon {
            width: 28px;
            height: 28px;
        }

        .numero-bl {
            font-size: 0.75rem;
        }

        .avatar-client {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }
    }
</style>

{{-- Appliquer les mêmes styles CSS que dans votre fichier original --}}
<style>
    /* ... Copier tous les styles du fichier original ... */
    /* Ajouter un style spécifique pour le numéro de BL */
    .numero-bl {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--bs-primary);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }
</style>

<script>
    function editLivraison(livraisonId) {
        // Réinitialiser le formulaire et afficher un spinner dans le tbody
        $('#editLignesFacture').html(`
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </td>
        </tr>
    `);

        // Charger les données de la livraison
        $.ajax({
            url: `${apiUrl}/vente/livraisons/${livraisonId}/edit`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Remplir les informations de base
                    $('#editLivraisonId').val(livraisonId);
                    $('#editClientName').text(response.livraison.facture.client.raison_sociale);
                    $('#editNumeroFacture').text(response.livraison.facture.numero);
                    $('#editDateFacture').text(response.livraison.facture.date_facture);
                    $('#editNotes').val(response.livraison.notes);

                    // Remplir le select des dépôts
                    const depotSelect = $('#editDepotId');
                    depotSelect.empty();
                    depotSelect.append('<option value="">Sélectionner un magasin</option>');
                    response.depots.forEach(depot => {
                        depotSelect.append(
                            `<option value="${depot.id}" ${depot.id == response.livraison.depot_id ? 'selected' : ''}>${depot.libelle_depot}</option>`
                        );
                    });

                    // Générer les lignes du tableau
                    let html = '';
                    response.lignes.forEach(ligne => {
                        // Construire le select des lots
                        let lotsSelect = `<select class="form-select form-select-sm lot-select"
                                            name="lignes[${ligne.id}][lot_id]"
                                            ${!ligne.has_lots ? 'disabled' : ''}>
                        <option value="">Sélection automatique</option>`;

                        ligne.lots.forEach(lot => {
                            lotsSelect += `<option value="${lot.id}"
                                             class="${lot.alerte}"
                                             data-quantite="${lot.quantite_restante}"
                                             title="Expire dans ${lot.jours_avant_expiration} jours"
                                             ${lot.id == ligne.lot_id ? 'selected' : ''}>
                            ${lot.titre_lot}
                        </option>`;
                        });
                        lotsSelect += '</select>';

                        html += `<tr>
                        <td>
                            <div class="fw-medium">${ligne.article.designation}</div>
                            <div class="small text-muted">${ligne.article.reference}</div>
                        </td>
                        <td class="text-center">${formatNumber(ligne.quantite_facturee)}</td>
                        <td class="text-center">${formatNumber(ligne.quantite_livree)}</td>
                        <td class="text-center">${formatNumber(ligne.reste_a_livrer)}</td>
                        <td class="text-center">
                            <input type="text"
                                   class="form-control form-control-sm quantite-input text-end"
                                   name="lignes[${ligne.id}][quantite]"
                                   value="${formatNumber(ligne.quantite)}"
                                   min="0"
                                   max="${ligne.reste_a_livrer}"
                                   data-ligne-id="${ligne.article.id}"
                                   data-max="${ligne.reste_a_livrer}"
                                   data-max-initial="${ligne.reste_a_livrer}">
                            <input type="hidden" name="lignes[${ligne.id}][article_id]" value="${ligne.article.id}">
                            <input type="hidden" name="lignes[${ligne.id}][unite_vente_id]" value="${ligne.unite_vente.id}">
                            <input type="hidden" name="lignes[${ligne.id}][prix_unitaire]" value="${ligne.prix_unitaire}">
                        </td>
                        <td class="text-center">
                            <span class="stock-dispo" id="edit-stock-${ligne.article.id}">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </td>
                        <td class="text-center">${lotsSelect}</td>
                    </tr>`;
                    });

                    $('#editLignesFacture').html(html);

                    // Initialiser les composants
                    initializeEditFormComponents();

                    // Vérifier le stock pour chaque article
                    if (response.livraison.depot_id) {
                        response.lignes.forEach(ligne => {
                            verifierStock(ligne.article.id, response.livraison.depot_id,
                                'edit-stock-');
                        });
                    }

                    // Afficher le modal
                    $('#editLivraisonModal').modal('show');
                } else {
                    Toast.fire({
                        icon: 'warning',
                        title: response.message
                    });
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des données'
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