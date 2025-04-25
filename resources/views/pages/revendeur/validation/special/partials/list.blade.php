{{-- list.blade.php --}}
<div class="row g-3">
    {{-- Table des factures --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="facturesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">Code</th>
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
                                                <span class="badge bg-success bg-opacity-10 text-success px-3"><i  class="fas fa-check-circle"></i> Validée</span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning px-3"><i  class="fas fa-hourglass-half"></i> En attente</span>
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
            @if ($factures->hasPages())
                <div class="card-footer bg-white border-0 pt-0">
                    {{ $factures->links() }}
                </div>
            @endif
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
                url: `/achat/factures/${id}/validate`,
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
@endpush
