{{-- add-modal.blade.php --}}
<div class="modal fade" id="addFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle Facture Fournisseur</h5>
                        <p class="text-muted small mb-0">Créez une nouvelle facture à partir d'un bon de commande</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('factures.store') }}" method="POST" id="addFactureForm" class="needs-validation"
                novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section sélection bon de commande --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-shopping-cart me-2"></i>Sélection Bon de Commande
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <select class="form-select select2" name="bon_commande_id" id="bonCommandeSelect"
                                        required>
                                        <option value="">Sélectionner un bon de commande</option>
                                        @foreach ($bonsCommande as $bc)
                                            <option value="{{ $bc->id }}" data-code="{{ $bc->code }}"
                                                data-point-vente="{{ $bc->pointVente->nom_pv }}"
                                                data-point-vente-id="{{ $bc->point_de_vente_id }}"
                                                data-fournisseur="{{ $bc->fournisseur->raison_sociale }}"
                                                data-fournisseur-id="{{ $bc->fournisseur_id }}"
                                                data-montant="{{ $bc->montant_total }}">
                                                {{ $bc->code }} - {{ $bc->fournisseur->raison_sociale }}
                                                ({{ number_format($bc->montant_total, 2) }} FCFA)
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner un bon de commande</div>

                                    <div id="loaderSection" style="display: none;" class="col-12 text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Chargement des données du bon de commande...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="detailsContainer" style="display: none;">
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
                                                <p class="fw-medium mb-0" id="bonCommandeCode"></p>
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Point de Vente</p>
                                                <p class="fw-medium mb-0" id="pointVente"></p>
                                                <input type="hidden" name="point_de_vente_id" id="pointVenteId">
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Fournisseur</p>
                                                <p class="fw-medium mb-0" id="fournisseur"></p>
                                                <input type="hidden" name="fournisseur_id" id="fournisseurId">
                                            </div>
                                            <div class="col-md-3">
                                                <p class="text-muted mb-1">Montant Total</p>
                                                <p class="fw-medium mb-0" id="montantTotal"></p>
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
                                                <input type="text" class="form-control" id="codeFacture"
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
                                                        <th class="text-end">Quantité</th>
                                                        <th class="text-end">Prix Unitaire</th>

                                                        <th class="text-end">Montant HT</th>
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
                                                <div class="mb-3 tva-aib-section" style="display: none;">
                                                    <label class="form-label">Taux TVA (%)</label>
                                                    <input type="number" class="form-control" name="taux_tva"
                                                        id="tauxTVA" value="20" min="0" max="100"
                                                        step="0.01">
                                                </div>
                                                <div class="mb-3 tva-aib-section" style="display: none;">
                                                    <label class="form-label">Taux AIB (%)</label>
                                                    <input type="number" class="form-control" name="taux_aib"
                                                        id="tauxAIB" value="0" min="0" max="100"
                                                        step="0.01">
                                                </div>
                                                <table class="table table-sm">
                                                    <tr>
                                                        <th>Total HT</th>
                                                        <td class="text-end">
                                                            <span id="montantHT">0.00</span> FCFA
                                                            <input type="hidden" name="montant_ht"
                                                                id="montantHTInput">
                                                        </td>
                                                    </tr>
                                                    <tr class="row-tva">
                                                        <th>Total TVA</th>
                                                        <td class="text-end">
                                                            <span id="montantTVA">0.00</span> FCFA
                                                            <input type="hidden" name="montant_tva"
                                                                id="montantTVAInput">
                                                        </td>
                                                    </tr>
                                                    <tr class="row-aib">
                                                        <th>Total AIB</th>
                                                        <td class="text-end">
                                                            <span id="montantAIB">0.00</span> FCFA
                                                            <input type="hidden" name="montant_aib"
                                                                id="montantAIBInput">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Net à Payer</th>
                                                        <td class="text-end">
                                                            <span id="montantTTC">0.00</span> FCFA
                                                            <input type="hidden" name="montant_ttc"
                                                                id="montantTTCInput">
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

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialisation de Select2
            $('#bonCommandeSelect').select2({
                theme: 'bootstrap-5',
                placeholder: 'Sélectionner un bon de commande'
            });

            // Gestionnaire de changement pour le bon de commande
            $('#bonCommandeSelect').change(function() {
                const option = $(this).find(':selected');
                if (option.val()) {
                    // Masquer le conteneur des détails et afficher le loader
                    $('#detailsContainer').hide();
                    $('#loaderSection').show();
                    $('#btnSave').hide();

                    // Remplir les informations de base
                    $('#bonCommandeCode').text(option.data('code'));
                    $('#pointVente').text(option.data('point-vente'));
                    $('#pointVenteId').val(option.data('point-vente-id'));
                    $('#fournisseur').text(option.data('fournisseur'));
                    $('#fournisseurId').val(option.data('fournisseur-id'));
                    $('#montantTotal').text(new Intl.NumberFormat('fr-FR').format(option.data('montant')) +
                        ' FCFA');

                    // Charger les articles
                    $.ajax({
                        url: `${apiUrl}/achat/bon-commandes/${option.val()}/articles`,
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                displayArticles(response.data);
                                // Masquer le loader et afficher les détails
                                $('#loaderSection').hide();
                                $('#detailsContainer').show();
                                $('#btnSave').show();
                            }
                        },
                        error: function() {
                            // En cas d'erreur, masquer le loader
                            $('#loaderSection').hide();
                            Toast.fire({
                                icon: 'error',
                                title: 'Erreur lors du chargement des articles'
                            });
                        }
                    });
                } else {
                    $('#detailsContainer, #btnSave').hide();
                }
            });

            // Fonction pour charger les articles
            function loadBonCommandeArticles(bonCommandeId) {
                $.ajax({
                    url: `${apiUrl}/achat/bon-commandes/${bonCommandeId}/articles`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            displayArticles(response.data);
                        }
                    }
                });
            }

            // Fonction pour afficher les articles
            function displayArticles(articles) {
                const tbody = $('#articlesTableBody');
                tbody.empty();

                articles.forEach(article => {
                    tbody.append(`
            <tr>
                <td>${article.code_article}</td>
                <td>${article.designation}</td>
                <td>${article.unite_mesure}</td>
                <td class="text-end">
                    <input type="number" class="form-control form-control-sm text-end quantite"
                           name="articles[${article.id}][quantite]"
                           value="${article.quantite}"
                           readonly>
                </td>
                <td class="text-end">
                    <input type="number" class="form-control form-control-sm text-end prix"
                           name="articles[${article.id}][prix_unitaire]"
                           value="${article.prix_unitaire}"
                           readonly>
                </td>
                <td class="text-end">
                    <span class="montant-ht">${(article.quantite * article.prix_unitaire).toFixed(2)}</span> FCFA
                    <input type="hidden" name="articles[${article.id}][montant_ht]"
                           class="montant-ht-input"
                           value="${(article.quantite * article.prix_unitaire).toFixed(2)}">
                    <input type="hidden" name="articles[${article.id}][unite_mesure_id]"
                           value="${article.unite_mesure_id}">
                    <input type="hidden" name="articles[${article.id}][taux_tva]"
                           value="0">
                    <input type="hidden" name="articles[${article.id}][taux_aib]"
                           value="0">
                </td>
            </tr>
        `);
                });

                calculateTotals();
            }

            // Ajouter les champs TVA et AIB aux écouteurs
            $('#tauxTVA, #tauxAIB').on('input', function() {
                calculateTotals();
            });

            // Fonction pour initialiser les calculs
            function initializeCalculations() {
                const calculFields = '#tauxTVA, #tauxAIB';

                $(document).on('input', calculFields, function() {
                    calculateTotals();
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
            function calculateTotals() {
                let totalHT = 0;
                $('#articlesTableBody tr').each(function() {
                    totalHT += parseFloat($(this).find('.montant-ht-input').val()) || 0;
                });

                const isNormalise = $('select[name="type_facture"]').val() === 'NORMALISE';
                const tauxTVA = isNormalise ? (parseFloat($('#tauxTVA').val()) || 0) : 0;
                const tauxAIB = isNormalise ? (parseFloat($('#tauxAIB').val()) || 0) : 0;

                const totalTVA = totalHT * (tauxTVA / 100);
                const totalAIB = totalHT * (tauxAIB / 100);
                const totalTTC = isNormalise ? (totalHT + totalTVA + totalAIB) : totalHT;

                $('#montantHT').text(totalHT.toFixed(2));
                $('#montantTVA').text(totalTVA.toFixed(2));
                $('#montantAIB').text(totalAIB.toFixed(2));
                $('#montantTTC').text(totalTTC.toFixed(2));

                $('#montantHTInput').val(totalHT.toFixed(2));
                $('#montantTVAInput').val(totalTVA.toFixed(2));
                $('#montantAIBInput').val(totalAIB.toFixed(2));
                $('#montantTTCInput').val(totalTTC.toFixed(2));
            }

            // Validation du formulaire
            $('#addFactureForm').on('submit', function(e) {
                e.preventDefault();

                if (this.checkValidity()) {
                    const formData = new FormData(this);

                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                $('#addFactureModal').modal('hide');
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Facture créée avec succès'
                                });
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            Toast.fire({
                                icon: 'error',
                                title: 'Erreur lors de la création de la facture'
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
                calculateTotals();
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
