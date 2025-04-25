<div class="modal fade" id="addBonCommandeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg ">
        <div class="modal-content border-0 shadow-lg modal-dialog-scrollable" style="overflow-y: scroll!important;">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-shopping-cart fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau Bon de Commande</h5>
                        <p class="text-muted small mb-0">Créez un nouveau bon de commande à partir d'une programmation
                            validée</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('bon-commandes.store') }}" method="POST" id="addBonCommandeForm"
                class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section sélection programmation --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-list-check me-2"></i>Sélection Programmation
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- <small class="text-warning">Les programmations non liés à un dépôt sont désactivées</small> -->
                                    <select class="form-select select2" name="programmation_id" id="programmationSelect"
                                        required>
                                        <option value="">Sélectionner une programmation validée</option>
                                        @foreach ($programmationsValidees as $prog)
                                        <option
                                            value="{{ $prog->id }}"
                                            data-code="{{ $prog->code }}"
                                            data-point-vente="{{ $prog->pointVente->nom_pv }}"
                                            data-point-vente-id="{{ $prog->point_de_vente_id }}"
                                            data-fournisseur="{{ $prog->fournisseur->raison_sociale }}"
                                            data-fournisseur-id="{{ $prog->fournisseur_id }}"
                                            data-validation="{{ $prog->validated_at->format('d/m/Y') }}"
                                            >
                                            {{ $prog->code }} - {{ $prog->fournisseur->raison_sociale }}
                                            (Validée le {{ $prog->validated_at->format('d/m/Y') }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner une programmation</div>
                                </div>
                            </div>
                        </div>

                        <div id="detailsContainer" style="display: none;">
                            {{-- Section informations programmation --}}
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Informations Programmation
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Code Programmation</p>
                                                <p class="fw-medium mb-0" id="programmationCode"></p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Point de Vente</p>
                                                <p class="fw-medium mb-0" id="pointVente"></p>
                                                <input type="hidden" name="point_vente_id" id="pointVenteId">
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Fournisseur</p>
                                                <p class="fw-medium mb-0" id="fournisseur"></p>
                                                <input type="hidden" name="fournisseur_id" id="fournisseurId">
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Date Validation</p>
                                                <p class="fw-medium mb-0" id="dateValidation"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section informations bon de commande --}}
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-file-invoice me-2"></i>Informations Bon de Commande
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Code Bon de Commande</label>
                                                <input type="text" class="form-control" id="codeBC" name="code"
                                                    readonly required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Date de Commande</label>
                                                <input type="date" class="form-control" name="date_commande"
                                                    required>
                                                <div class="invalid-feedback">La date de commande est requise</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section articles --}}
                            <div id="articlesSection" class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-box me-2"></i>Articles
                                        </h6>
                                    </div>

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Référence</th>
                                                        <th>Désignation</th>
                                                        <th>Unité</th>
                                                        <th class="text-end" style="width: 120px;">Quantité</th>
                                                        <th class="text-end" style="width: 150px;">Prix Unitaire</th>
                                                        <th class="text-end" style="width: 100px;">Remise %</th>
                                                        <th class="text-end" style="width: 150px;">Montant HT</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="articlesTableBody">
                                                    <!-- Rempli dynamiquement -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section totaux --}}
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-calculator me-2"></i>Récapitulatif
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row justify-content-end">
                                            <div class="col-md-4">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th>Total HT</th>
                                                        <td class="text-end">
                                                            <span id="montantTotal">0.00</span> F CFA
                                                            <input type="hidden" name="montant_total"
                                                                id="montantTotalInput">
                                                        </td>
                                                    </tr>

                                                    {{-- <tr>
                                                        <th>Total TVA</th>
                                                        <td class="text-end">
                                                            <span id="montantTVA">0.00</span> F CFA
                                                            <input type="hidden" name="montant_tva" id="montantTVAInput">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Total TTC</th>
                                                        <td class="text-end">
                                                            <span id="montantTTC">0.00</span> F CFA
                                                            <input type="hidden" name="montant_ttc" id="montantTTCInput">
                                                        </td>
                                                    </tr> --}}
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section Autres coûts --}}
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-file-invoice me-2"></i>Coûts Supplémentaires
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Coût du transport</label>
                                                <input type="number" class="form-control" id="cout_transport" name="cout_transport" value="0" required>
                                                <div class="invalid-feedback">Le coût du transport est requis</div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Coût du Chargement/Déchargement</label>
                                                <input type="number" class="form-control" id="cout_chargement" name="cout_chargement" value="0" required>
                                                <div class="invalid-feedback">Le coût du Chargement/Déchargement est requis</div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Autres Coût</label>
                                                <input type="number" class="form-control" id="autre_cout" name="autre_cout" value="0" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section commentaire --}}
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-comments me-2"></i>Commentaire
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="commentaire" rows="3" placeholder="Ajouter un commentaire (optionnel)"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4" id="btnSave" style="display: none;">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('styles')
<style>
    .modal-dialog {
        /* max-width: 95%; */
        margin: 1.75rem auto;
    }

    .invalid-feedback {
        font-size: 80%;
    }

    .prix-unitaire {
        min-width: 100px;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: calc(1.5em + 0.75rem + 2px);
    }

    .table> :not(caption)>*>* {
        padding: 0.5rem;
    }

    .form-control-sm {
        min-height: calc(1.5em + 0.5rem + 2px);
    }

    .card {
        margin-bottom: 0;
    }

    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header {
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .btn-icon {
        padding: 0.25rem 0.5rem;
    }

    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
</style>
@endpush