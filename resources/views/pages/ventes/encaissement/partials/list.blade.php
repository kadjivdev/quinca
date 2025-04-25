<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="sessionsTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0">Caissier</th>
                            <th class="border-bottom-0">Date Vente</th>
                            <th class="border-bottom-0">Référence</th>
                            <th class="border-bottom-0">Montant</th>
                            <th class="border-bottom-0">Référence Reçu</th>
                            <th class="border-bottom-0">Client</th>
                            <th class="border-bottom-0 text-center">Statut Facture</th>
                            <th class="border-bottom-0 text-end" style="min-width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventes as $vente)
                            @if ($vente->montant_regle > 0)
                                
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-user me-2">
                                            {{ substr($vente->sessionCaisse->utilisateur->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $vente->sessionCaisse->utilisateur->name }}</div>
                                            <div class="text-muted small">Caissier</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-clock text-success me-2"></i>
                                        {{ \Carbon\Carbon::parse($vente->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{$vente->numero}}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{$vente->montant_regle}}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        {{$vente->reference_recu}}
                                    </div>
                                </td>
                                <td class="text-end fw-medium">
                                    <div class="d-flex align-items-center">
                                        {{$vente->client->raison_sociale}}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @switch($vente->statut_reel)
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
                                        <button class="btn btn-sm btn-light-secondary btn-icon ms-1"
                                                onclick="showFacture({{ $vente->id }})"
                                                data-bs-toggle="tooltip"
                                                title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        @if(is_null($vente->encaissed_at))
                                            <button class="btn btn-sm btn-light-success btn-icon"
                                                    onclick="encaisser({{ $vente->id }})"
                                                    data-bs-toggle="tooltip"
                                                    title="Encaisser la vente">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        @if (is_null($vente->date_validation))
                                            <button class="btn btn-sm btn-light-danger btn-icon"
                                                    onclick="deleteFacture({{ $vente->id }})"
                                                    data-bs-toggle="tooltip"
                                                    title="Annuler la vente">
                                                <i class="fas fa-trash"></i>
                                            </button>   
                                        @endif

                                        @if ($vente->statut_reel == 'payee')
                                            <button class="btn btn-sm btn-light-warning btn-icon print-bon"
                                                data-url-a4="{{ route('vente.livraisons.pdf.bon-livraison', ['facture' => $vente->id, 'format' => 'a4']) }}"
                                                data-url-a5="{{ route('vente.livraisons.pdf.bon-livraison', ['facture' => $vente->id, 'format' => 'a5']) }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#choixFormatModal"
                                                title="Imprimer le bordereau">
                                                <i class="fas fa-print"></i>
                                            </button>                                  
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($ventes->hasPages())
                <div class="card-footer border-0 py-3">
                    {{ $ventes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Choix Format -->
    <div class="modal fade" id="choixFormatModal" aria-labelledby="choixFormatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="choixFormatModalLabel">Choisissez le format</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Veuillez choisir le format du bordereau :</p>
                    <div class="d-flex justify-content-between">
                        <button id="btn-print-a4" class="btn btn-primary">Format A4</button>
                        <button id="btn-print-a5" class="btn btn-secondary">Format A5</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
:root {
    --kadjiv-orange: #FFA500;
    --kadjiv-orange-light: rgba(255, 165, 0, 0.1);
}

/* Numéro de session */
.numero-session {
    font-family: 'Monaco', 'Consolas', monospace;
    color: var(--kadjiv-orange);
    font-weight: 500;
    padding: 0.3rem 0.6rem;
    background-color: var(--kadjiv-orange-light);
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

/* Avatar utilisateur */
.avatar-user {
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

.btn-light-danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.btn-light-info {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.btn-light-secondary {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

/* Hover effects */
.btn-icon i {
    transition: transform 0.2s ease;
}

.btn-icon:hover i {
    transform: scale(1.1);
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

/* Card */
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.08) !important;
}

/* Bouton désactivé */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
