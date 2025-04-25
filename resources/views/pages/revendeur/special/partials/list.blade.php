{{-- list-factures.blade.php --}}
<div class="row g-3">
    {{-- Table des factures --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="facturesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N° Facture</th>
                            <th class="border-bottom-0">Date</th>
                            <th class="border-bottom-0">Client</th>
                            <th class="border-bottom-0">Échéance</th>
                            <th class="border-bottom-0 text-end">Montant HT</th>
                            <th class="border-bottom-0 text-end">Montant TTC</th>
                            <th class="border-bottom-0 text-end">Reste à payer</th>
                            <th class="border-bottom-0 text-center">Statut</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factures as $facture)
                            <tr>
                                <td class="text-nowrap py-3">
                                    <div class="d-flex align-items-center">
                                        <span class="numero-facture me-2">{{ $facture->numero }}</span>
                                        @if ($facture->is_proforma)
                                            <span class="badge bg-info bg-opacity-10 text-info">Proforma</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $facture->date_facture->format('d/m/Y') }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-client me-2">
                                            {{ substr($facture->client->raison_sociale, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $facture->client->raison_sociale }}</div>
                                            <div class="text-muted small">{{ $facture->client->telephone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $facture->date_echeance->format('d/m/Y') }}</td>
                                <td class="text-end fw-medium">
                                    {{ number_format($facture->montant_ht, 0, ',', ' ') }} F
                                </td>
                                <td class="text-end fw-medium">
                                    {{ number_format($facture->montant_ttc, 0, ',', ' ') }} F
                                </td>
                                <td class="text-end">
                                    @if ($facture->reste_a_payer > 0)
                                        <span class="text-danger fw-medium">
                                            {{ number_format($facture->reste_a_payer, 0, ',', ' ') }} F
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success">Soldée</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @switch($facture->statut_reel)
                                        @case('brouillon')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-3">Brouillon</span>
                                        @break

                                        @case('validee')
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-3">Validée</span>
                                        @break

                                        @case('payee')
                                            <span class="badge bg-success bg-opacity-10 text-success px-3">Payée</span>
                                        @break

                                        @case('partiellement_payee')
                                            <span class="badge bg-info bg-opacity-10 text-info px-3">Partiellement payée</span>
                                        @break

                                        @default
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3">Annulée</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        {{-- Voir détails --}}
                                        <button class="btn btn-sm btn-light-primary btn-icon"
                                            onclick="showFacture({{ $facture->id }})" data-bs-toggle="tooltip"
                                            title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        @if ($facture->statut === 'brouillon')
                                            {{-- Modifier --}}
                                            <button class="btn btn-sm btn-light-warning btn-icon ms-1"
                                                onclick="editFacture({{ $facture->id }})" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Valider --}}
                                            {{-- <button class="btn btn-sm btn-light-success btn-icon ms-1"
                                                onclick="validateFacture({{ $facture->id }})" data-bs-toggle="tooltip"
                                                title="Valider">
                                                <i class="fas fa-check"></i>
                                            </button> --}}

                                            {{-- Supprimer --}}
                                            <button class="btn btn-sm btn-light-danger btn-icon ms-1"
                                                onclick="deleteFacture({{ $facture->id }})" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif

                                        {{-- Imprimer --}}
                                        <div class="btn-group ms-1">
                                            <button class="btn btn-sm btn-light-secondary btn-icon"
                                                data-bs-toggle="dropdown">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" target="blank"
                                                        href="{{ route('vente.facture.print', $facture->id) }}">
                                                        <i class="fas fa-file-invoice me-2"></i>Facture
                                                    </a>
                                                </li>
                                                {{-- <li>
                                                    <a class="dropdown-item" target="blank"
                                                        href="{{ route('vente.facture.print', $facture->id) }}">
                                                        <i class="fas fa-file-alt me-2"></i>Proforma
                                                    </a>
                                                </li> --}}
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
                                            <p class="text-muted small mb-3">Les factures que vous créez apparaîtront ici
                                            </p>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#addFactureModal">
                                                <i class="fas fa-plus me-2"></i>Créer une facture
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($factures->hasPages())
                    <div class="card-footer border-0 py-3">
                        {{ $factures->links() }}
                    </div>
                @endif
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
        // Fonction principale de filtrage
        function filterFactures() {
            // Afficher le loader
            Swal.fire({
                title: 'Chargement...',
                html: `
            <div class="d-flex flex-column align-items-center">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <span class="text-muted small">Filtrage des factures en cours</span>
            </div>
        `,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            // Récupérer les valeurs des filtres
            let params = new URLSearchParams({
                client_id: $('#clientFilter').val() || '',
                statut: $('#statutFilter').val() || '',
                date_debut: $('#dateDebut').val() || '',
                date_fin: $('#dateFin').val() || '',
                filter: true // Pour identifier que c'est une requête de filtrage
            });

            // Faire la requête AJAX
            $.ajax({
                url: 'ventes-speciales/clients/filter?' + params.toString(),
                method: 'GET',
                success: function(response) {
                    Swal.close();

                    if (response.success) {
                        // Mettre à jour tout le contenu de la table
                        $('#facturesTable tbody').html(response.html);

                        // Mettre à jour la pagination si elle existe
                        if (response.pagination) {
                            $('.card-footer').html(response.pagination);
                        }

                        // Réinitialiser les tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: 'Erreur lors du filtrage'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur lors du filtrage:', error);
                    Swal.close();
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du filtrage des factures'
                    });
                }
            });
        }

        // Validation des dates
        function validateDates() {
            const dateDebut = $('#dateDebut').val();
            const dateFin = $('#dateFin').val();

            if (dateDebut && dateFin && dateDebut > dateFin) {
                Toast.fire({
                    icon: 'warning',
                    title: 'La date de début doit être inférieure à la date de fin'
                });
                $('#dateFin').val('');
                return false;
            }
            return true;
        }

        // Event listeners
        $(document).ready(function() {
            // Select2 pour le filtre client
            $('.select2-clients').select2({
                theme: 'bootstrap-5',
                placeholder: 'Sélectionner un client',
                allowClear: true,
                width: '100%'
            });

            // Écouteurs d'événements pour les filtres
            $('#dateDebut, #dateFin').on('change', function() {
                if (validateDates()) {
                    filterFactures();
                }
            });

            $('#clientFilter, #statutFilter').on('change', function() {
                filterFactures();
            });
        });

        // Réinitialisation des filtres
        function resetFilters() {
            // Réinitialiser les valeurs
            $('#clientFilter').val('').trigger('change');
            $('#statutFilter').val('');

            // Réinitialiser les dates au premier et dernier jour du mois en cours
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
                .toISOString().split('T')[0];
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0)
                .toISOString().split('T')[0];

            $('#dateDebut').val(firstDay);
            $('#dateFin').val(lastDay);

            // Relancer le filtrage
            filterFactures();
        }

        // Configuration Toast
        // const Toast = Swal.mixin({
        //     toast: true,
        //     position: 'top-end',
        //     showConfirmButton: false,
        //     timer: 3000,
        //     timerProgressBar: true
        // });

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
                    fetch(`ventes-speciales/${id}/delete`, {
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
