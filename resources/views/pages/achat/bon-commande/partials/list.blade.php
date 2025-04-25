<div class="row g-3">
    {{-- Table des bons de commande --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="bonCommandesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
                            <th class="border-bottom-0">Date Insertion</th>
                            <th class="border-bottom-0">Date Commande</th>
                            <th class="border-bottom-0">Point de Vente</th>
                            <!-- <th class="border-bottom-0">Dépôt</th> -->
                            <th class="border-bottom-0">Fournisseur</th>
                            <th class="border-bottom-0 text-end">Montant Total</th>
                            <th class="border-bottom-0 text-center">Programmation</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bonCommandes as $bonCommande)
                        <tr>
                            <td class="text-nowrap py-3">
                                <div class="d-flex align-items-center">
                                    <span class="code-commande me-2">{{ $bonCommande->code }}</span>
                                </div>
                            </td>
                            <td>{{ Carbon\Carbon::parse($bonCommande->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $bonCommande->date_commande->format('d/m/Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-point-vente me-2">
                                        {{ substr($bonCommande->pointVente->nom_pv, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $bonCommande->pointVente->nom_pv }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- <td class="text-center">
                                <span class="badge bg-warning">{{ $bonCommande->programmation->_depot?$bonCommande->programmation->_depot->libelle_depot:"--" }}</span>
                            </td> -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-fournisseur me-2">
                                        {{ substr($bonCommande->fournisseur->raison_sociale, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $bonCommande->fournisseur->raison_sociale }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold">{{ number_format($bonCommande->montant_total, 2) }} F
                                    CFA</span>
                            </td>
                            <td class="text-center">
                                <a href="#" class="code-programmation"
                                    onclick="showProgrammation({{ $bonCommande->programmation_id }})">
                                    {{ $bonCommande->programmation->code }}
                                </a>
                            </td>
                            <td class="text-center">
                                @if ($bonCommande->rejected_by)
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3"><i class="fas fa-minus-circle"></i> Rejetée</span>
                                @elseif ($bonCommande->validated_at)
                                <span class="badge bg-success bg-opacity-10 text-success px-3"><i class="fas fa-check-circle"></i> Validée</span>
                                @else
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3"><i class="fas fa-hourglass-half"></i> En attente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showBonCommande({{ $bonCommande->id }})" data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    @if (!$bonCommande->validated_at && !$bonCommande->rejected_at)
                                    @can("bon-commandes.edit")
                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="editBonCommande({{ $bonCommande->id }})" data-bs-toggle="tooltip"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endcan

                                    @can("bon-commandes.validate")
                                    <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="validateBonCommande({{ $bonCommande->id }})"
                                        data-bs-toggle="tooltip" title="Valider">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endcan

                                    <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="initRejetBonCommande({{ $bonCommande->id }})"
                                        data-bs-toggle="tooltip" title="Rejeter">
                                        <i class="fas fa-ban"></i>
                                    </button>

                                    @can("bon-commandes.delete")
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteBonCommande({{ $bonCommande->id }})"
                                        data-bs-toggle="tooltip" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                    @endif

                                    <div class="btn-group ms-1">
                                        <button class="btn btn-sm btn-light-secondary btn-icon"
                                            data-bs-toggle="dropdown">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" target="blank" href="#">
                                                    <i class="fas fa-file-pdf me-2"></i>Imprimer
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    onclick="exportExcel({{ $bonCommande->id }})">
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
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucun bon de commande trouvé</h6>
                                    <p class="text-muted small mb-3">Les bons de commande créés apparaîtront ici</p>
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
    /* Code du bon de commande */
    .code-commande {
        font-family: 'Monaco', 'Consolas', monospace;
        color: var(--bs-primary);
        font-weight: 500;
        padding: 0.3rem 0.6rem;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    /* Code de la programmation */
    .code-programmation {
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

    .code-programmation:hover {
        color: var(--bs-info);
        background-color: rgba(var(--bs-info-rgb), 0.2);
    }

    /* Avatar point de vente et fournisseur */
    .avatar-point-vente,
    .avatar-fournisseur {
        width: 35px;
        height: 35px;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
    }

    .avatar-fournisseur {
        background-color: rgba(var(--bs-info-rgb), 0.1);
        color: var(--bs-info);
    }

    /* Styles pour les boutons */
    .btn-light-primary,
    .btn-light-warning,
    .btn-light-success,
    .btn-light-danger,
    .btn-light-secondary {
        border: none;
        transition: all 0.2s ease;
    }

    .btn-light-primary {
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
    }

    .btn-light-warning {
        background-color: rgba(var(--bs-warning-rgb), 0.1);
        color: var(--bs-warning);
    }

    .btn-light-success {
        background-color: rgba(var(--bs-success-rgb), 0.1);
        color: var(--bs-success);
    }

    .btn-light-danger {
        background-color: rgba(var(--bs-danger-rgb), 0.1);
        color: var(--bs-danger);
    }

    .btn-light-secondary {
        background-color: rgba(var(--bs-secondary-rgb), 0.1);
        color: var(--bs-secondary);
    }

    /* Hover effects */
    .btn-light-primary:hover {
        background-color: var(--bs-primary);
        color: white;
    }

    .btn-light-warning:hover {
        background-color: var(--bs-warning);
        color: white;
    }

    .btn-light-success:hover {
        background-color: var(--bs-success);
        color: white;
    }

    .btn-light-danger:hover {
        background-color: var(--bs-danger);
        color: white;
    }

    .btn-light-secondary:hover {
        background-color: var(--bs-secondary);
        color: white;
    }

    /* Button icon styles */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }

    /* Empty state styling */
    .empty-state {
        text-align: center;
        padding: 2rem;
    }

    .empty-state i {
        opacity: 0.5;
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

        .code-commande,
        .code-programmation {
            font-size: 0.75rem;
        }

        .avatar-point-vente,
        .avatar-fournisseur {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }
    }
</style>

<script>
    // Fonction pour afficher les détails d'un bon de commande
    function showBonCommande(id) {
        Swal.fire({
            title: 'Chargement...',
            text: 'Veuillez patienter...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `${apiUrl}/achat/bon-commandes/${id}`,
            method: 'GET',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    fillBonCommandeDetails(response.data);
                    $('#showBonCommandeModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.close();
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des données'
                });
            }
        });
    }

    function closeObject() {
        $("#show_object").attr('hidden', true)
    }

    function exportPdf() {
        $("#show_object").removeAttr("hidden")
    }

    function exportation() {
        let bon_id = $("#bon_id").val()
        let bon_object = $("#bon_object").val()
        if (bon_object) {
            window.open(`${apiUrl}/achat/bon-commandes/${bon_id}/${bon_object}/pdf`)
            // window.location.href = `/quinkadjiv_refont/public/achat/bon-commandes/${bon_id}/${bon_object}/pdf`
        }
        alert("Saisissez un object dans le champ ...")
    }

    $("#exportForm").on("submit", function(e) {
        e.preventDefault()
        alert("submit .....")
    })

    function fillBonCommandeDetails(data) {
        console.log('Données à afficsssher:', data);

        // Réinitialiser le contenu précédent
        $('#articlesSectionShow').empty();

        $('#bon_id').val(data.id)
        // Afficher les informations de base
        $('#bonCodeShow').text(data.code);
        $('#refProgrammationShow').text(data.programmation.code);
        $('#pointVenteShow').text(data.point_vente.nom_pv);
        $('#dateProgrammationShow').text(data.programmation.date_programmation.split('T')[0]);
        $('#fournisseurShow').text(data.fournisseur.raison_sociale);

        // $("#exportPdf").attr("href", `/quinkadjiv_refont/public/achat/bon-commandes/${data.id}/pdf`);

        const statutBadge = data.programmation.rejected_at ?
            '<span class="badge bg-danger bg-opacity-10 text-danger"><i class="fas fa-minus-circle"></i> Rejetée</span>' :
            data.programmation.validated_at ?
            '<span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i> Validée</span>' :
            '<span class="badge bg-warning bg-opacity-10 text-warning"><i class="fas fa-hourglass-half"></i> En attente</span>';
        $('#statutShow').html(statutBadge);

        $('#dateValidation').text(data.programmation.validated_at);
        $('#commentaireShow').html(data.commentaire);

        // Vérification des articles
        if (data.lignes && data.lignes.length > 0) {
            let articlesHtml = `
            <div class="card border border-light-subtle">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-box me-2"></i>Articles
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="example1" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Désignation</th>
                                    <th>Unité</th>
                                    <th class="text-end" style="width: 120px;">Quantité</th>
                                    <th class="text-end" style="width: 150px;">Prix Unitaire</th>
                                    <th class="text-end" style="width: 150px;">Total HT</th>
                                </tr>
                            </thead>
                            <tbody>`;

            data.lignes.forEach((ligne, index) => {
                articlesHtml += `
                <tr>
                    <td>${ligne.article.code_article || ''}</td>
                    <td>${ligne.article.designation || ''}</td>
                    <td>${ligne.unite_mesure.libelle_unite || ''}</td>
                    <td class="text-end">
                        <input type="hidden" name="articles[${index}][article_id]" value="${ligne.article.id}">
                        <input type="number"
                               class="form-control form-control-sm text-end"
                               name="articles[${index}][quantite]"
                               value="${ligne.quantite}"
                               readonly>
                    </td>
                    <td>
                        <input type="number"
                               class="form-control form-control-sm text-end prix-unitaire"
                               name="articles[${index}][prix_unitaire]"
                               step="0.01"
                               min="0"
                               value="${ligne.prix_unitaire || ''}"
                               data-index="${index}"
                               placeholder="0.00"
                               readonly>
                        <div class="invalid-feedback">Le prix unitaire est requis</div>
                    </td>
                    <td class="text-end">
                        <span class="total-ligne-${index}">0.00</span> F CFA
                    </td>
                </tr>`;
            });

            articlesHtml += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>`;

            $('#articlesSectionShow').html(articlesHtml);

            // Calculer les totaux initiaux
            data.lignes.forEach((ligne, index) => {
                if (ligne.prix_unitaire) {
                    calculerMontantLigne(index);
                }
            });
            calculerTotaux();
        } else {
            $('#articlesSectionShow').html(`
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Aucun article trouvé dans cette programmation
            </div>
        `);
        }

        $('#cout_transport_show').val(data.cout_transport);
        $("#cout_chargement_show").val(data.cout_chargement);
        $("#autre_cout_show").val(data.autre_cout);

        // Afficher le conteneur et le bouton
        // $('#detailsContainer').show();
        // $('#btnSave').show();
    }

    // Fonction pour supprimer un bon de commande
    function deleteBonCommande(id) {
        Swal.fire({
            title: 'Confirmer la suppression',
            text: 'Êtes-vous sûr de vouloir supprimer ce bon de commande ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/achat/bon-commandes/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Erreur lors de la suppression'
                        });
                    }
                });
            }
        });
    }

    // Initialisation des tooltips
    $(document).ready(function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
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