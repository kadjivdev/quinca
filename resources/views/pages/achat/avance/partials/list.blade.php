<div class="row g-3">
    
    {{-- Table des avances --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Référence</th>
                            <th class="border-bottom-0">Date</th>
                            <th class="border-bottom-0">Fournisseur</th>
                            <th class="border-bottom-0 text-center">Type</th>
                            <th class="border-bottom-0 text-end">Montant</th>
                            <th class="border-bottom-0">Observation</th>
                            <th class="border-bottom-0">Créé par</th>
                            <th class="border-bottom-0">Requête</th>
                            <th class="border-bottom-0 text-end" style="min-width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($avances as $avance)
                        <tr>
                            <td class="text-nowrap py-3">
                                <span class="code-reference">{{ $avance->reference }}</span>
                            </td>
                            <td class="text-nowrap">
                                {{ $avance->date?->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-client me-2">
                                        {{ substr($avance->fournisseur?->raison_sociale, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $avance->fournisseur?->raison_sociale }}</div>
                                        <div class="text-muted small">{{ $avance->fournisseur?->code_fournisseur }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @switch($avance->type_paiement)
                                @case('espece')
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-money-bill-wave me-1"></i>Espèce
                                </span>
                                @break
                                @case('cheque')
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-money-check me-1"></i>Chèque
                                </span>
                                @break
                                @case('virement')
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-exchange-alt me-1"></i>Virement
                                </span>
                                @break
                                @endswitch
                            </td>
                            <td class="text-end">
                                <span class="fw-medium montant">
                                    {{ number_format($avance->montant, 0, ',', ' ') }} F
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $avance->observation ?: '—' }}</span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $avance->createdBy?->name ?: '—' }}
                                </small>
                            </td>
                            <td>
                                <small class="badge bg-light text-dark text-muted">
                                    {{ $avance->requete?->num_demande ?: '—' }}
                                </small>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    {{-- Bouton voir détails - toujours visible --}}
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showAcompte({{ $avance->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    {{-- Bouton modifier - visible uniquement si en attente --}}
                                    @if($avance->isEnAttente())
                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="editAcompte({{ $avance->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif

                                    {{-- Boutons de validation/rejet - visibles uniquement si en attente --}}
                                    @if($avance->isEnAttente())
                                    <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                        onclick="validateAcompte({{ $avance->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Valider">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    @endif

                                    {{-- Badge de statut --}}
                                    <span class="ms-1">
                                        @if($avance->isValide())
                                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Validé par {{ $avance->validatedBy?->name }} le {{ $avance->validated_at?->format('d/m/Y H:i') }}">
                                            Validé
                                        </span>
                                        @elseif($avance->isRejete())
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" title="Rejeté par {{ $avance->validatedBy?->name }} le {{ $avance->validated_at?->format('d/m/Y H:i') }}">
                                            Rejeté
                                        </span>
                                        @else
                                        <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </span>

                                    {{-- Bouton supprimer - visible si en attente et moins de 24h --}}
                                    @if($avance->isEnAttente() && $avance->created_at->diffInHours(now()) <= 24)
                                        <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteAcompte({{ $avance->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucune avance trouvée</h6>
                                    <p class="text-muted small mb-3">Les avances que vous enregistrez apparaîtront ici</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAcompteModal">
                                        <i class="fas fa-plus me-2"></i>Nouvel avance
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
    /* Référence avance */
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
    function filterAcomptes() {
        // Afficher le loader
        Swal.fire({
            title: 'Chargement...',
            text: 'Filtrage des avances en cours',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Récupérer les valeurs des filtres
        const filters = {
            fournisseur_id: $('#clientFilter').val(),
            type_paiement: $('#typePaiementFilter').val(),
            date_debut: $('#dateDebut').val(),
            date_fin: $('#dateFin').val(),
            search: $('#searchFilter').val()
        };

        // console.log(filters)
        // Faire la requête AJAX avec les filtres
        $.ajax({
            url: `${apiUrl}/achat/avances-frs/refresh-list`,
            method: 'GET',
            data: filters,
            success: function(response) {
                // Mettre à jour le tableau avec les nouvelles données
                $('#acomptesTable tbody').html($(response.html).find('#acomptesTable tbody').html());

                // Mettre à jour la pagination si elle existe
                if ($('.card-footer').length) {
                    $('.card-footer').html($(response.html).find('.card-footer').html());
                }

                // Mettre à jour les statistiques
                if (response.stats) {
                    $('.stat-total').text(response.stats.total.toLocaleString('fr-FR'));
                    $('.stat-montant').text(response.stats.montant_total.toLocaleString('fr-FR'));
                    $('.stat-mois').text(response.stats.avances_mois.toLocaleString('fr-FR'));
                    $('.stat-montant-mois').text(response.stats.montant_mois.toLocaleString('fr-FR'));
                }

                // Réinitialiser les tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();

                // Fermer le loader
                Swal.close();
            },
            error: function(xhr, status, error) {
                Swal.close();
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du filtrage des avances'
                });
                console.error('Erreur:', error);
            }
        });
    }

    // Réinitialiser les filtres
    function resetFilters() {
        $('#clientFilter').val('');
        $('#typePaiementFilter').val('');
        $('#dateDebut').val('');
        $('#dateFin').val('');
        $('#searchFilter').val('');
        filterAcomptes();
    }

    // Ajouter un délai pour la recherche
    let searchTimeout;
    $('#searchFilter').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterAcomptes, 500);
    });

    // Fonction pour supprimer une avance
    function deleteAcompte(id) {
        Swal.fire({
            title: 'Confirmer la suppression',
            text: "Voulez-vous vraiment supprimer cet avance ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                        url: `${apiUrl}/achat/avances-frs/${id}`,
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
                    title: 'Avance supprimée avec succès'
                });
                window.location.reload(); // Rafraîchir la liste
            }
        });
    }

    // Fonction pour voir les détails d'une avance
    function showAcompte(id) {
        $.ajax({
            url: `${apiUrl}/achat/avances-frs/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const avance = response.data.avance;
                    Swal.fire({
                        title: `<strong>Détails de l'avance</strong>`,
                        html: `
                            <div class="text-start">
                                <table id='example1' class="table table-sm">
                                    <tr>
                                        <td class="fw-bold">Référence:</td>
                                        <td>${avance.reference}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date:</td>
                                        <td>${avance.date}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Fournisseur:</td>
                                        <td>${avance.fournisseur?.code_fournisseur} - ${avance.fournisseur?.raison_sociale}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Type:</td>
                                        <td>${formatTypePaiement(avance.type_paiement)}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Montant:</td>
                                        <td>${formatMontant(avance.montant)}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Observation:</td>
                                        <td>${avance.observation || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Créé par:</td>
                                        <td>${avance.created_by || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date création:</td>
                                        <td>${avance.created_at}</td>
                                    </tr>
                                </table>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonText: 'Fermer',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du chargement des détails'
                    });
                }
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des détails'
                });
            }
        });
    }

    // Fonction pour formater les montants
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(montant) + ' FCFA';
    }

    // Fonction pour formater le type de paiement
    function formatTypePaiement(type) {
        const types = {
            'espece': '<span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-money-bill-wave me-1"></i>Espèce</span>',
            'cheque': '<span class="badge bg-info bg-opacity-10 text-info"><i class="fas fa-money-check me-1"></i>Chèque</span>',
            'virement': '<span class="badge bg-primary bg-opacity-10 text-primary"><i class="fas fa-exchange-alt me-1"></i>Virement</span>'
        };
        return types[type] || type;
    }

    // Initialisation au chargement de la page
    $(document).ready(function() {
        // Initialisation des tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Initialisation de Select2 pour le filtre client
        $('#clientFilter').select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner un client',
            allowClear: true,
            width: '100%'
        });

        // Initialisation des dates par défaut (mois en cours)
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

        $('#dateDebut').val(firstDay.toISOString().split('T')[0]);
        $('#dateFin').val(lastDay.toISOString().split('T')[0]);

        // Validation des dates
        $('#dateDebut, #dateFin').on('change', function() {
            const dateDebut = new Date($('#dateDebut').val());
            const dateFin = new Date($('#dateFin').val());

            if (dateDebut > dateFin) {
                Toast.fire({
                    icon: 'warning',
                    title: 'La date de début doit être inférieure à la date de fin'
                });
                $(this).val('');
                return;
            }
            window.location.reload();
        });

        // Event listeners pour les autres filtres
        $('#clientFilter, #typePaiementFilter').on('change', refreshList);

        // Recherche avec debounce
        let searchTimeout;
        $('#searchFilter').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(refreshList, 500);
        });

        // Gestion du rafraîchissement automatique
        setInterval(refreshList, 300000); // Rafraîchir toutes les 5 minutes
    });

    // Fonction pour réinitialiser les filtres
    function resetFilters() {
        $('#clientFilter').val(null).trigger('change');
        $('#typePaiementFilter').val('');

        // Réinitialiser les dates au mois en cours
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

        $('#dateDebut').val(firstDay.toISOString().split('T')[0]);
        $('#dateFin').val(lastDay.toISOString().split('T')[0]);

        $('#searchFilter').val('');

        window.location.reload();
    }

    // Fonction pour rafraîchir les statistiques
    function updateStats(stats) {
        if (stats) {
            $('#totalAcomptes').text(stats.total.toLocaleString('fr-FR'));
            $('#totalMontant').text(formatMontant(stats.total_montant));
            $('#acomptesMois').text(stats.avances_mois.toLocaleString('fr-FR'));
            $('#montantMois').text(formatMontant(stats.montant_mois));
        }
    }
</script>

<script>
    // Fonction pour valider un avance
    function validateAcompte(id) {
        Swal.fire({
            title: 'Confirmer la validation',
            text: 'Êtes-vous sûr de vouloir valider cette avance ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/achat/avances-frs/${id}/validate`,
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

    // Fonction pour rejeter un avance
    function rejectAcompte(id) {
        Swal.fire({
            title: 'Motif du rejet',
            text: 'Veuillez indiquer le motif du rejet',
            input: 'text',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Rejeter',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#dc3545',
            inputValidator: (value) => {
                if (!value) {
                    return 'Le motif du rejet est requis';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/achat/avances-frs/${id}/reject`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        motif_rejet: result.value
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
                            title: xhr.responseJSON?.message || 'Erreur lors du rejet'
                        });
                    }
                });
            }
        });
    }

    // Fonction pour afficher le statut avec badge
    function getStatusBadge(statut) {
        const badges = {
            en_attente: '<span class="badge bg-warning">En attente</span>',
            valide: '<span class="badge bg-success">Validé</span>',
            rejete: '<span class="badge bg-danger">Rejeté</span>'
        };
        return badges[statut] || '';
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