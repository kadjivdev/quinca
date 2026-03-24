<div class="row g-3">
    {{-- Section Filtres --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="row g-3">
                    <form action="{{route('versements.index')}}" method="get">
                        @csrf
                        <div class="row">
                            {{-- Filtre Client --}}
                            <div class="col-md-3">
                                <label class="form-label small">Client</label>
                                <select class="form-select alert-select2 form-select-sm" name="client_id" id="alert-select2">
                                    <option value="">Tous les clients</option>
                                    @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->code_client }} - {{ $client->raison_sociale }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtre Type de Paiement --}}
                            <div class="col-md-2">
                                <label class="form-label small">Type de paiement</label>
                                <select class="form-select form-select-sm" id="typePaiementFilter" name="type_op">
                                    <option value="">Tous les types</option>
                                    <option value="MoMo">MoMo</option>
                                    <option value="MoMoPay">MoMoPay</option>
                                    <option value="MoMoMarchand">MoMoMarchand</option>
                                    <option value="Chèque">Chèque</option>
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

                            {{-- Filtre Statut de versement --}}
                            <div class="col-md-2">
                                <label class="form-label small">Status du versement</label>
                                <select class="form-select form-select-sm" name="status_op">
                                    <option value="">Tous les status</option>
                                    <option value="VALIDE">Validé</option>
                                    <option value="ATTENTE">En attente</option>
                                    <option value="EXTOURNER">Extourner</option>
                                </select>
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

    {{-- Table des versements --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3">
            @if(request()->get("status_op"))
            <h4 class="">Versements <span class="badge bg-{{request()->get('status_op')==='ATTENTE'?'warning':'success'}} text-dark">{{request()->get("status_op")}}</span> </h4>
            @endif

            <div class="table-responsive">
                <table id="example1" class="table table-hover align-middle mb-0" id="acomptesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">ID</th>
                            <th class="border-bottom-0 text-nowrap py-3">Référence</th>
                            <th class="border-bottom-0 text-nowrap py-3">Référence d'opération</th>
                            <th class="border-bottom-0 text-nowrap py-3">Accompte Client</th>
                            <th class="border-bottom-0">Date opération</th>
                            <th class="border-bottom-0">Date valeur</th>
                            <th class="border-bottom-0">Client</th>
                            <th class="border-bottom-0">Banque</th>
                            <th class="border-bottom-0 text-center">Type</th>
                            <th class="border-bottom-0 text-end">Montant</th>
                            <th class="border-bottom-0 text-end">Preuve</th>
                            <th class="border-bottom-0">Commentaire</th>
                            <th class="border-bottom-0">Créé par</th>
                            <!-- <th class="border-bottom-0">Validé par</th> -->
                            <th class="border-bottom-0">Extourné par</th>
                            <th class="border-bottom-0 text-end" style="min-width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements as $versement)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td class="text-nowrap py-3">
                                <span class="code-reference">{{ $versement->reference }}</span>
                            </td>
                            <td class="text-nowrap py-3">
                                <span class="code-reference">{{ $versement->reference_op }}</span>
                            </td>
                            <td class="text-nowrap py-3">
                                <span class="badge bg-light text-dark bold">{{$versement->accompteClient?->reference??'--'}}</span>
                            </td>
                            <td class="text-nowrap">
                                {{ $versement->date_op->format('d/m/Y') }}
                            </td>
                            <td class="text-nowrap">
                                {{ $versement->date_valeur->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-medium">{{ $versement->client?->raison_sociale }}</div>
                                        <div class="text-muted small">{{ $versement->client?->code_client }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                {{ $versement->banque }}
                            </td>
                            <td class="text-center">
                                @switch($versement->type_op)
                                @case('Chèque')
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-money-check me-1"></i>Chèque
                                </span>
                                @break
                                @case('MoMo')
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-exchange-alt me-1"></i>MoMo
                                </span>
                                @break
                                @case('MoMoPay')
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-exchange-alt me-1"></i>MoMoPay
                                </span>
                                @break
                                @case('MoMoMarchand')
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-exchange-alt me-1"></i>MoMoMarchand
                                </span>
                                @break
                                @endswitch
                            </td>
                            <td class="text-end">
                                <span class="fw-medium montant">
                                    {{ number_format($versement->montant, 0, ',', ' ') }} F
                                </span>
                            </td>
                            <td class="text-center">
                                @if($versement->preuve)
                                <a href="{{ $versement->preuve}}" target="_blank" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Voir la preuve">
                                    <i class="fas fa-paperclip"></i>
                                </a>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $versement->comment ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ $versement->createdBy?->name??'—' }}</span>
                            </td>
                            <!-- <td>
                                <span class="text-muted small">{{ $versement->validatedBy?->name??'—' }}</span>
                            </td> -->
                            <td>
                                <span class="text-muted small">{{ $versement->extournedBy?->name??'—' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    {{-- Bouton voir détails - toujours visible --}}
                                    <button class="btn btn-sm btn-light-primary btn-icon"
                                        onclick="showAcompte({{ $versement->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- non extourné & non validé -->
                                    @if(!$versement->extourned_by && !$versement->deleted_at)
                                    @can("accomptes.edit")
                                    @if(!$versement->accompteClient?->validated_by)
                                    <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                        onclick="editAcompte({{ $versement->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                    @endcan

                                    @can("accomptes.validate")
                                    @if(!$versement->accompteClient?->validated_by)
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="rejectAcompte({{ $versement->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Extourner">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    @endif
                                    @endcan

                                    @if(!$versement->accompteClient?->validated_by)
                                    @can("accomptes.delete")
                                    <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                        onclick="deleteAcompte({{ $versement->id }})"
                                        data-bs-toggle="tooltip"
                                        title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endcan
                                    @endif
                                    @endif

                                    {{-- Badge de statut --}}
                                    <span class="ms-1">
                                        @if($versement->accompteClient?->validated_by)
                                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Validé par {{ $versement->validatedBy?->name }} le {{ $versement->validated_at?->format('d/m/Y H:i') }}">
                                            Validé
                                        </span>
                                        @elseif($versement->extourned_by)
                                        <span class="badge bg-danger" data-bs-toggle="tooltip" title="Rejeté par {{ $versement->extournedBy?->name }} le {{ $versement->extourned_at?->format('d/m/Y H:i') }}">
                                            Extourné
                                        </span>
                                        @else
                                        <span class="badge bg-warning">En attente</span>
                                        @endif
                                    </span>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted mb-1">Aucun versement trouvé</h6>
                                    <p class="text-muted small mb-3">Les versements que vous enregistrez apparaîtront ici</p>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAcompteModal">
                                        <i class="fas fa-plus me-2"></i>Nouveau versement
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
    function filterAcomptes() {
        // Afficher le loader
        Swal.fire({
            title: 'Chargement...',
            text: 'Filtrage des acomptes en cours',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
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

    // Fonction pour voir les détails d'un acompte
    function showAcompte(id) {
        $.ajax({
            url: `${apiUrl}/vente/versements/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Afficher les détails dans un modal ou une carte
                    const versement = response.data.versement;
                    Swal.fire({
                        title: 'Détails du versement',
                        html: `
                        <div class="text-start">
                            <p><strong>Référence:</strong> ${versement.reference}</p>
                            <p><strong>Référence opération:</strong> ${versement.reference_op}</p>
                            <p><strong>Date opération:</strong> ${versement.date_op}</p>
                            <p><strong>Client:</strong> ${versement.client?.raison_sociale}</p>
                            <p><strong>Type:</strong> ${versement.type_op}</p>
                            <p><strong>Montant:</strong> ${versement.montant.toLocaleString('fr-FR')} F</p>
                            <p><strong>Commentaire:</strong> ${versement.comment || '—'}</p>
                            <p><strong>Créé par:</strong> ${versement.created_by || '—'}</p>
                            <p><strong>Date création:</strong> ${versement.created_at}</p>
                            <p><strong>Validé par:</strong> ${versement.validated_by || '—'}</p>
                        </div>
                    `,
                        icon: 'info'
                    });
                }
            }
        });
    }

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
                        url: `${apiUrl}/vente/versements/${id}`,
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
                    title: 'Versement supprimé avec succès'
                });
                window.location.reload(); // Rafraîchir la liste
            }
        });
    }

    // Fonction pour voir les détails d'un acompte
    function showAcompte(id) {
        $.ajax({
            url: `${apiUrl}/vente/versements/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const versement = response.data.versement;
                    Swal.fire({
                        title: `<strong>Détails du versement</strong>`,
                        html: `
                            <div class="text-start">
                                <table id='example1' class="table table-sm">
                                    <tr>
                                        <td class="fw-bold">Référence:</td>
                                        <td>${versement.reference}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Référence:</td>
                                        <td>${versement.reference_op}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date opération:</td>
                                        <td>${versement.date_op}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Client:</td>
                                        <td>${versement.client?.code_client} - ${versement.client?.raison_sociale}</td>
                                    </tr>
                                     <tr>
                                        <td class="fw-bold">Banque:</td>
                                        <td>${versement.banque}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Type:</td>
                                        <td>${formatTypePaiement(versement.type_op)}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Montant:</td>
                                        <td>${formatMontant(versement.montant)}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Commentaire:</td>
                                        <td>${versement.comment || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Créé par:</td>
                                        <td>${versement.created_by || '-'}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Date création:</td>
                                        <td>${versement.created_at}</td>
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
            'MoMo': '<span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-money-bill-wave me-1"></i>MoMo</span>',
            'Chèque': '<span class="badge bg-info bg-opacity-10 text-info"><i class="fas fa-money-check me-1"></i>Chèque</span>',
        };
        return types[type] || type;
    }


    // Initialisation au chargement de la page
    $(document).ready(function() {
        // Initialisation des tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Initialisation de Select2 pour le filtre client
        $('#alert-select2').select2({
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
            $('#acomptesMois').text(stats.versement_mois.toLocaleString('fr-FR'));
            $('#montantMois').text(formatMontant(stats.montant_mois));
        }
    }
</script>
<script>
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
                    url: `${apiUrl}/vente/versements/validate/${id}`,
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

    // Fonction pour rejeter un acompte
    function rejectAcompte(id) {
        Swal.fire({
            title: 'Motif du rejet',
            text: 'Veuillez indiquer le motif d\'extournement',
            input: 'text',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Extourner',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#dc3545',
            inputValidator: (value) => {
                if (!value) {
                    return 'Le motif d\'extournement est requis';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/vente/versements/extourne/${id}`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        extourned_comment: result.value
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