{{-- add-modal.blade.php --}}
<div class="modal fade" id="editFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modification de Facture Fournisseur <span id="factIdMod"></span></h5>
                        {{-- <p class="text-muted small mb-0">Créez une nouvelle facture à partir d'un bon de commande</p> --}}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="" method="POST" id="editFactureForm" class="needs-validation">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section sélection bon de commande --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-shopping-cart me-2"></i>Bon de Commande
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <span class="form-select select2" name="bon_commande_id" id="bonCommandeSelectMod"> </span>
                                </div>
                            </div>
                        </div>

                        <div id="detailsContainer">
                            {{-- Section informations bon de commande --}}
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Informations Bon de Commande
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Code Bon de Commande</p>
                                                <p class="fw-medium mb-0" id="bonCommandeCodeMod"></p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Point de Vente</p>
                                                <p class="fw-medium mb-0" id="pointVenteMod"></p>
                                                <input type="hidden" name="point_de_vente_id" id="pointVenteId">
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Fournisseur</p>
                                                <p class="fw-medium mb-0" id="fournisseurMod"></p>
                                                <input type="hidden" name="fournisseur_id" id="fournisseurId">
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Montant Total</p>
                                                <p class="fw-medium mb-0" id="montantTotalMod"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section informations facture --}}
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-file-invoice me-2"></i>Informations Facture
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Code Facture</label>
                                                <input type="text" class="form-control" id="codeFactureMod"
                                                    name="code" readonly required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Date de Facture</label>
                                                <input type="date" class="form-control" name="date_facture" required>
                                                <div class="invalid-feedback">La date de facture est requise</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Type de Facture</label>
                                                <select class="form-select" name="type_facture" required>
                                                    <option value="SIMPLE">Facture Simple</option>
                                                    <option value="NORMALISE">Facture Normalisée</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Section articles --}}
                            <div id="articlesSectionMod" class="col-12">
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
                                                        <th class="text-end">Quantité</th>
                                                        <th class="text-end">Prix Unitaire</th>

                                                        <th class="text-end">Montant HT</th>
                                                    </tr>
                                                </thead>
                                                <tbody >
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
                                                <div class="mb-3 tva-aib-section" style="display: none;">
                                                    <label class="form-label">Taux TVA (%)</label>
                                                    <input type="number" class="form-control" name="taux_tva"
                                                        id="tauxTVAMod" value="0" min="0" max="100"
                                                        step="0.01">
                                                </div>
                                                <div class="mb-3 tva-aib-section" style="display: none;">
                                                    <label class="form-label">Taux AIB (%)</label>
                                                    <input type="number" class="form-control" name="taux_aib"
                                                        id="tauxAIBMod" value="0" min="0" max="100"
                                                        step="0.01">
                                                </div>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th>Total HT</th>
                                                        <td class="text-end">
                                                            <span id="montantHTMod">0.00</span> FCFA
                                                            <input type="hidden" name="montant_ht"
                                                                id="montantHTInputMod">
                                                        </td>
                                                    </tr>
                                                    <tr class="row-tva">
                                                        <th>Total TVA</th>
                                                        <td class="text-end">
                                                            <span id="montantTVAMod">0.00</span> FCFA
                                                            <input type="hidden" name="montant_tva"
                                                                id="montantTVAInputMod">
                                                        </td>
                                                    </tr>
                                                    <tr class="row-aib">
                                                        <th>Total AIB</th>
                                                        <td class="text-end">
                                                            <span id="montantAIBMod">0.00</span> FCFA
                                                            <input type="hidden" name="montant_aib"
                                                                id="montantAIBInputMod">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Net à Payer</th>
                                                        <td class="text-end">
                                                            <span id="montantTTCMod">0.00</span> FCFA
                                                            <input type="hidden" name="montant_ttc"
                                                                id="montantTTCInputMod">
                                                        </td>
                                                    </tr>
                                                </table>
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
                                        <textarea class="form-control" name="commentaireMod" rows="3" placeholder="Ajouter un commentaire (optionnel)"></textarea>
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
                    <button type="submit" class="btn btn-primary px-4" id="btnSave">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialisation de Select2
            $('#bonCommandeSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Sélectionner un bon de commande'
            });

            // Fonction pour charger les articles
            function loadBonCommandeArticles(bonCommandeId) {
                $.ajax({
                    url: `/achat/bon-commandes/${bonCommandeId}/articles`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            displayArticles(response.data);
                        }
                    }
                });
            }

            // Ajouter les champs TVA et AIB aux écouteurs
            $('#tauxTVAMod, #tauxAIBMod').on('input', function() {
                calculateTotalsMod();
            });

            // Fonction pour initialiser les calculs
            function initializeCalculations() {
                const calculFields = '#tauxTVAMod, #tauxAIBMod';

                $(document).on('input', calculFields, function() {
                    calculateTotalsMod();
                });
            }


            // Calculer les montants pour une ligne
            function calculateLineMontants(row) {
                const quantite = parseFloat(row.find('.quantite').val()) || 0;
                const prix = parseFloat(row.find('.prix').val()) || 0;
                const montantHT = quantite * prix;

                row.find('.montant-ht').text(montantHT.toFixed(2));
                row.find('.montant-ht-input').val(montantHT.toFixed(2));
            }

            // Calculer les totaux
            function calculateTotalsMod() {
                let totalHT = 0;
                $('#articlesTableBodyMod tr').each(function() {
                    totalHT += parseFloat($(this).find('.montant-ht-input').val()) || 0;
                });

                const isNormalise = $('select[name="type_facture"]').val() === 'NORMALISE';
                const tauxTVA = isNormalise ? (parseFloat($('#tauxTVAMod').val()) || 0) : 0;
                const tauxAIB = isNormalise ? (parseFloat($('#tauxAIBMod').val()) || 0) : 0;

                console.log(totalHT)

                const totalTVA = totalHT * (tauxTVA / 100);
                const totalAIB = totalHT * (tauxAIB / 100);
                const totalTTC = isNormalise ? (totalHT + totalTVA + totalAIB) : totalHT;

                $('#montantHTMod').text(totalHT.toFixed(2));
                $('#montantTVAMod').text(totalTVA.toFixed(2));
                $('#montantAIBMod').text(totalAIB.toFixed(2));
                $('#montantTTCMod').text(totalTTC.toFixed(2));

                $('#montantHTInputMod').val(totalHT.toFixed(2));
                $('#montantTVAInputMod').val(totalTVA.toFixed(2));
                $('#montantAIBInputMod').val(totalAIB.toFixed(2));
                $('#montantTTCInputMod').val(totalTTC.toFixed(2));
            }

            // Validation du formulaire
            $('#editFactureForm').on('submit', function(e) {
                e.preventDefault();

                if (this.checkValidity()) {
                    const formData = $("#editFactureForm").serialize();

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'PUT',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#editFactureModal').modal('hide');
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Facture modifiée avec succès'
                                });
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Erreur lors de la modification de la facture'
                            });
                        }
                    });
                }

                $(this).addClass('was-validated');
            });

            // Génération automatique du code facture
            function generateFactureCode() {
                const date = new Date();
                const year = date.getFullYear().toString().substr(-2);
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const random = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
                return `FAC${year}${month}${random}`;
            }

            // Initialiser le code facture lors de la sélection du bon de commande
            $('#bonCommandeSelect').on('change', function() {
                if ($(this).val()) {
                    $('#codeFacture').val(generateFactureCode());
                    $('select[name="type_facture"]').trigger('change')
                }
            });

            $('select[name="type_facture"]').change(function() {
                const isNormalise = $(this).val() === 'NORMALISE';
                if (isNormalise) {
                    $('.tva-aib-section, .row-tva, .row-aib').show();
                } else {
                    $('.tva-aib-section, .row-tva, .row-aib').hide();
                    $('#tauxTVA, #tauxAIB').val(0);
                }
                calculateTotalsMod();
            });

        });
    </script>
@endpush

@push('styles')
    <style>
        .form-control-sm {
            height: calc(1.5em + 0.5rem + 2px);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .table td {
            vertical-align: middle;
        }

        .table input[type="number"] {
            min-width: 80px;
        }

        .card {
            margin-bottom: 0;
        }

        .select2-container {
            width: 100% !important;
        }

        .was-validated .select2-selection {
            border-color: #dc3545 !important;
        }

        .was-validated .select2-selection--single:valid {
            border-color: #198754 !important;
        }
    </style>
@endpush
