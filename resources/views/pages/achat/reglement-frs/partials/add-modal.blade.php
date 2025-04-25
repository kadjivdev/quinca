<div class="modal fade" id="addReglementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow-lg" style="overflow-y: scroll!important;">
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau Règlement</h5>
                        <p class="text-muted small mb-0">Créez un nouveau règlement pour une facture fournisseur</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('reglements.store') }}" method="POST" id="addReglementForm" class="needs-validation"
                novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-file-invoice me-2"></i>Sélectionez 1 ou plusieurs factures
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="bg-warning p-1 rounded">
                                        Les factures ayant des reglements en attentes de validations sont désactivées . <br>
                                        En cas de réglement multiple, le total des factures ne doit pas dépasser le solde actuel du fournisseur . <br>
                                        En cas de réglement multiple, vous ne devez pas entrer le montant du règlement . <br>
                                        C'est après validation d'un règement que le solde du fournisseur est defalqué
                                    </p>
                                    <select class="form-select select2" multiple name="facture_fournisseur_id[]" id="_factureSelect"
                                        required>
                                        <option value="">Sélectionner une facture</option>
                                        @foreach ($factures as $facture)
                                        <option value="{{ $facture->id }}"
                                            data-code="{{ $facture->code }}"
                                            data-fournisseur="{{ $facture->fournisseur->raison_sociale }}"
                                            data-montant="{{ $facture->montant_ttc }}"
                                            data-solde="{{ $facture->montant_ttc - $facture->reglements->sum('montant_reglement') }}"
                                            @if(count($facture->reglements->whereNull("validated_by"))>0 || count($facture->reglements_grouped()->whereNull("validated_by"))>0) disabled @endif
                                            >
                                            <!-- {{count($facture->reglements->whereNull("validated_by"))}} - {{count($facture->reglements_grouped()->whereNull("validated_by"))}} -->
                                            {{ $facture->code }} [ Montant: {{ number_format($facture->montant_ttc, 2) }}; Reste: {{ number_format($facture->facture_amont(), 2) }} FCFA] <br>
                                            - {{ $facture->fournisseur->raison_sociale }} [ Solde: {{ number_format($facture->fournisseur->approvisionnements()->sum("montant"),2) }}; Reste: {{ number_format( $facture->fournisseur->reste_solde(),2) }} ]
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Veuillez sélectionner une facture</div>
                                </div>
                            </div>
                        </div>

                        <div id="factureLoader" style="display: none;">
                            <div class="d-flex justify-content-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>

                        <div id="reglementDetails" style="display: none;">
                            <div class="col-12">
                                <div class="card border border-light-subtle">
                                    <div class="card-header bg-light">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Informations Règlement
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Code Règlement</label>
                                                <input type="text" class="form-control" id="codeReglement"
                                                    name="code" readonly required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Référence Document</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="referenceDocument"
                                                        name="reference_document" placeholder="Laissez vide pour auto-génération">
                                                    <button class="btn btn-outline-secondary" type="button" id="generateReference">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </div>
                                                <div class="form-text">Si non renseigné, une référence sera générée automatiquement</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Date de Règlement</label>
                                                <input type="date" class="form-control" name="date_reglement" required>
                                                <div class="invalid-feedback">La date de règlement est requise</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Mode de Règlement</label>
                                                <select class="form-select" name="mode_reglement" id="modeReglement" required>
                                                    <option value="">Sélectionner un mode</option>
                                                    <option value="ESPECE">Espèces</option>
                                                    <option value="CHEQUE">Chèque</option>
                                                    <option value="VIREMENT">Virement</option>
                                                    <option value="DECHARGE">Décharge</option>
                                                    <option value="AUTRES">Autres</option>
                                                </select>
                                                <div class="invalid-feedback">Le mode de règlement est requis</div>
                                            </div>
                                            <div class="col-md-6" id="referenceField" style="display: none;">
                                                <label class="form-label">Référence</label>
                                                <input type="text" class="form-control" name="reference_reglement">
                                                <div class="invalid-feedback">La référence est requise pour ce mode de paiement</div>
                                            </div>

                                            <div class="form-check d-block">
                                                <input class="form-check-input" type="checkbox" value="" id="checkAmont">
                                                <label class="form-check-label btn btn-dark btn-sm" for="checkAmont">
                                                    Faire un reglement partiel
                                                </label>
                                            </div>


                                            <div class="col-12 montant_reglement_block d-none">
                                                <label class="form-label">Montant du Règlement</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" name="montant_reglement"
                                                        id="_montantReglement" step="0.01" min="0">
                                                    <span class="input-group-text">FCFA</span>
                                                </div>
                                                <div class="invalid-feedback">Le montant du règlement est requis</div>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Commentaire</label>
                                                <textarea class="form-control" name="commentaire" rows="3"
                                                    placeholder="Ajouter un commentaire (optionnel)"></textarea>
                                            </div>
                                        </div>
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

        $("#checkAmont").on("click", function() {
            if (this.checked) {
                $(".montant_reglement_block").removeClass("d-none")
            } else {
                $(".montant_reglement_block").addClass("d-none")
                $("#_montantReglement").val(null)
            }

            console.log(this.checked)
        })

        // Configuration initiale Select2
        try {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addReglementModal')
            });
        } catch (e) {
            console.error('Erreur initialisation Select2:', e);
        }


        let lastGeneratedNumber = 0;

        // Fonction pour générer une référence unique selon le mode de règlement
        function generateReference(mode) {
            const date = new Date();
            const year = date.getFullYear().toString().substr(-2);
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            lastGeneratedNumber++;
            const sequence = lastGeneratedNumber.toString().padStart(4, '0');

            const prefixes = {
                'ESPECE': 'ESP',
                'CHEQUE': 'CHQ',
                'VIREMENT': 'VIR',
                'DECHARGE': 'DCH',
                'AUTRES': 'AUT'
            };

            return `${prefixes[mode] || 'REG'}${year}${month}${sequence}`;
        }

        // Fonction pour générer le code règlement
        function generateReglementCode() {
            const date = new Date();
            const year = date.getFullYear().toString().substr(-2);
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const random = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
            return `REG${year}${month}${random}`;
        }

        // Gestion du bouton de génération de référence
        $('#generateReference').click(function() {
            const mode = $('#modeReglement').val();
            if (mode) {
                $('#referenceDocument').val(generateReference(mode));
            } else {
                Toast.fire({
                    icon: 'warning',
                    title: 'Veuillez d\'abord sélectionner un mode de règlement'
                });
            }
        });

        // Gestion de la sélection de facture avec loader
        $('#_factureSelect').change(function() {
            const option = $(this).find(':selected');
            if (option.val()) {
                $('#factureLoader').show();
                $('#reglementDetails').hide();

                // Simuler un chargement
                setTimeout(() => {
                    const montantRestant = parseFloat(option.data('solde'));
                    $('#montantRestant').text(montantRestant.toFixed(2));
                    $('#reglementDetails').show();
                    $('#btnSave').show();
                    $('#codeReglement').val(generateReglementCode());
                    $('#montantReglement').attr('max', montantRestant);
                    $('#factureLoader').hide();
                }, 500);
            } else {
                $('#reglementDetails').hide();
                $('#btnSave').hide();
            }
        });

        // Gestion du mode de règlement
        $('#modeReglement').change(function() {
            const mode = $(this).val();
            if (mode === 'CHEQUE' || mode === 'VIREMENT') {
                $('#referenceField').show();
                $('input[name="reference_reglement"]').prop('required', true);
            } else {
                $('#referenceField').hide();
                $('input[name="reference_reglement"]').prop('required', false);
            }

            // Générer automatiquement une nouvelle référence si le champ est vide
            if ($('#referenceDocument').val() === '') {
                $('#referenceDocument').val(generateReference(mode));
            }
        });

        // Gestion de la soumission du formulaire
        $('#addReglementForm').on('submit', function(e) {
            e.preventDefault();

            if (this.checkValidity()) {
                const formData = new FormData(this);

                // Si la référence document est vide, en générer une
                if (!formData.get('reference_document')) {
                    const mode = formData.get('mode_reglement');
                    formData.set('reference_document', generateReference(mode));
                }

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#addReglementModal').modal('hide');
                            Toast.fire({
                                icon: 'success',
                                title: 'Règlement créé avec succès'
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Erreur lors de la création du règlement'
                        });
                    }
                });
            }

            $(this).addClass('was-validated');
        });
    });
</script>
@endpush

@push('styles')
<style>
    .select2-container {
        width: 100% !important;
    }

    /* .modal-lg {
        max-width: 800px;
    } */

    .was-validated .select2-selection {
        border-color: #dc3545 !important;
    }

    .was-validated .select2-selection--single:valid {
        border-color: #198754 !important;
    }

    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f8f9fa;
    }

    #factureLoader {
        background: rgba(255, 255, 255, 0.8);
        position: relative;
        z-index: 1;
    }
</style>
@endpush