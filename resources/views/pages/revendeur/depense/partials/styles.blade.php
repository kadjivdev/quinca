<div class="modal fade" id="showFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Détails de la Facture {{ $facture->numero }}</h5>
                        <p class="text-muted small mb-0">Créée le {{ $facture->date_facture->format('d/m/Y') }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    {{-- Section informations générales --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Informations Générales
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label text-muted">Date facture</label>
                                        <p class="fw-medium mb-0">{{ $facture->date_facture->format('d/m/Y') }}</p>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label text-muted">Client</label>
                                        <p class="fw-medium mb-0">{{ $facture->client->raison_sociale }}</p>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label text-muted">Échéance</label>
                                        <p class="fw-medium mb-0">{{ $facture->date_echeance->format('d/m/Y') }}</p>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label text-muted">Créée par</label>
                                        <p class="fw-medium mb-0">{{ $facture->createdBy->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section articles --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-box me-2"></i>Articles
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Article</th>
                                                <th>Tarif</th>
                                                <th>Quantité</th>
                                                <th>Remise (%)</th>
                                                <th class="text-end">Total HT</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($facture->lignes as $ligne)
                                            <tr>
                                                <td>{{ $ligne->article->designation }}</td>
                                                <td>
                                                    {{ number_format($ligne->prix_unitaire_ht, 0, ',', ' ') }} FCFA
                                                    @if($ligne->tarification && $ligne->tarification->typeTarif)
                                                        - {{ $ligne->tarification->typeTarif->libelle_type_tarif }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ number_format($ligne->quantite, 2, ',', ' ') }}
                                                    {{ $ligne->uniteVente->libelle_unite ?? '' }}
                                                </td>
                                                <td>{{ number_format($ligne->taux_remise, 2, ',', ' ') }}%</td>
                                                <td class="text-end">
                                                    {{ number_format($ligne->montant_ht, 0, ',', ' ') }} FCFA
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Total HT</td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($facture->montant_ht, 0, ',', ' ') }} FCFA
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Remise</td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($facture->montant_remise, 0, ',', ' ') }} FCFA
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">TVA ({{ number_format($facture->taux_tva, 0) }}%)</td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($facture->montant_tva, 0, ',', ' ') }} FCFA
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">AIB ({{ number_format($facture->taux_aib, 0) }}%)</td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($facture->montant_aib, 0, ',', ' ') }} FCFA
                                                </td>
                                            </tr>
                                            <tr class="table-light">
                                                <td colspan="4" class="text-end fw-bold">Total TTC</td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($facture->montant_ttc, 0, ',', ' ') }} FCFA
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section règlement --}}
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-money-bill-wave me-2"></i>Règlement
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted">Montant réglé</label>
                                        <p class="fw-medium mb-0">
                                            {{ number_format($facture->montant_regle, 0, ',', ' ') }} FCFA
                                        </p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label text-muted">Reste à payer</label>
                                        <p class="fw-medium mb-0">
                                            {{ number_format($facture->montant_ttc - $facture->montant_regle, 0, ',', ' ') }} FCFA
                                        </p>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label text-muted">État</label>
                                        <p class="fw-medium mb-0">
                                            @if($facture->montant_regle >= $facture->montant_ttc)
                                                <span class="badge bg-success">Payée</span>
                                            @elseif($facture->montant_regle > 0)
                                                <span class="badge bg-warning">Partiellement payée</span>
                                            @else
                                                <span class="badge bg-danger">Non payée</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section observations --}}
                    @if($facture->observations)
                    <div class="col-12">
                        <div class="card border border-light-subtle">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-comment-alt me-2"></i>Observations
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $facture->observations }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Fermer
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>
