<script>
    $(document).ready(function() {
        function initClientSelect() {
            // Initialisation de Select2 pour le client
            $('#clientDisplay').select2({
                dropdownParent: $('#addReglementModal .modal-content'),
                theme: 'bootstrap-5',
                placeholder: 'Sélectionner un client',
                width: '100%',
                language: {
                    noResults: function() {
                        return "Aucune facture trouvée";
                    },
                    searching: function() {
                        return "Recherche...";
                    }
                }
            }).on('select2:open', function() {
                setTimeout(function() {
                    $('.select2-search__field').focus();
                }, 100);
            });

            $('#factureSelect').select2({
                dropdownParent: $('#addReglementModal .modal-content'),
                theme: 'bootstrap-5',
                placeholder: 'Sélectionner une facture',
                width: '100%',
                language: {
                    noResults: function() {
                        return "Aucune facture trouvée";
                    },
                    searching: function() {
                        return "Recherche...";
                    }
                }
            }).on('select2:open', function() {
                setTimeout(function() {
                    $('.select2-search__field').focus();
                }, 100);
            });
        }

        // Initialiser select2 à l'ouverture du modal
        $('#addReglementModal').on('shown.bs.modal', function() {
            // initFactureSelect();
            initClientSelect();
        });

        // Détruire select2 à la fermeture du modal
        $('#addReglementModal').on('hidden.bs.modal', function() {
            // if ($('#factureSelect').data('select2')) {
            //     $('#factureSelect').select2('destroy');
            // }

            if ($('#clientDisplay').data('select2')) {
                $('#clientDisplay').select2('destroy');
            }
            resetForm();
        });

        // Au changement de client
        $('#clientDisplay').on('change', function() {
            $('#factureSelect').empty()

            const selectedOption = $(this).find(':selected');
            const clientId = $(this).val();

            // Mettre à jour l'ID de la facture
            $('#clientId').val(clientId);

            if (clientId) {
                const factures = selectedOption.data('factures');

                // Mettre à jour l'affichage
                factures.forEach(facture => {
                    // console.log(facture)
                    $('#factureSelect').append(
                        `
                        <option value="${facture.id}"
                            data-client="${facture.client.raison_sociale}"
                            data-montant="${facture.montant_ttc}"
                            data-reste="${facture.montant_ttc - facture.montant_regle}">
                            ${facture.numero} - ${facture.client.raison_sociale}
                            (Reste: ${facture.montant_ttc - facture.montant_regle} F)
                        </option>
                        `
                    )
                });
            } else {
                // Réinitialiser les champs
                $('#clientDisplay').text('Sélectionnez un client');
                $('#resteAPayer').text('');
                $('#montant').prop('disabled', true).val('');
            }
            updateSaveButton();
        });

        // Au changement de facture
        $('#factureSelect').on('change', function() {
            const selectedOption = $(this).find(':selected');
            const factureId = $(this).val();

            // Mettre à jour l'ID de la facture
            $('#factureClientId').val(factureId);

            if (factureId) {
                // const clientName = selectedOption.data('client');
                const resteAPayer = selectedOption.data('reste');

                // console.log(selectedOption)
                // Mettre à jour l'affichage
                // $('#clientDisplay').text(clientName);
                $('#resteAPayer').html(
                    `Reste à payer: <strong>${formatMontant(resteAPayer)} F</strong>`);

                // Configurer le montant maximum
                $('#montant')
                    .attr('max', resteAPayer)
                    .prop('disabled', false)
                    .val('');
            } else {
                // Réinitialiser les champs
                $('#clientDisplay').text('Sélectionnez une facture');
                $('#resteAPayer').text('');
                $('#montant').prop('disabled', true).val('');
            }

            updateSaveButton();
        });

        // Au changement du type de règlement
        $('#typeReglement').on('change', function() {
            const type = $(this).val();

            // Cacher tous les champs optionnels
            $('#banqueGroup, #referenceGroup, #echeanceGroup').hide();
            $('#reference, [name="banque"], [name="date_echeance"]').prop('required', false);

            // Réinitialiser les champs
            $('#reference, [name="banque"], [name="date_echeance"]').val('');

            // Afficher les champs selon le type
            switch (type) {
                case 'cheque':
                    $('#banqueGroup, #referenceGroup, #echeanceGroup').show();
                    $('#reference, [name="banque"], [name="date_echeance"]').prop('required', true);
                    $('#referenceLabel').text('N° du chèque');
                    $('#reference').attr('placeholder', 'Numéro du chèque');
                    break;

                case 'virement':
                    $('#banqueGroup, #referenceGroup').show();
                    $('#reference, [name="banque"]').prop('required', true);
                    $('#referenceLabel').text('Référence du virement');
                    $('#reference').attr('placeholder', 'Référence du virement');
                    break;

                case 'carte_bancaire':
                case 'MoMo':
                case 'Flooz':
                case 'Celtis_Pay':
                    $('#referenceGroup').show();
                    $('#reference').prop('required', true);
                    $('#referenceLabel').text('N° de transaction');
                    $('#reference').attr('placeholder', 'Numéro de transaction');
                    break;
            }

            updateSaveButton();
        });

        // Au changement du montant
        $('#montant').on('input', function() {
            const montant = parseFloat($(this).val());
            const max = parseFloat($(this).attr('max'));

            if (montant > max) {
                $(this).val(max);
                Toast.fire({
                    icon: 'warning',
                    title: 'Le montant ne peut pas dépasser le reste à payer'
                });
            }

            updateSaveButton();
        });

        // Soumission du formulaire
        $('#addReglementForm').on('submit', function(e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            const submitBtn = $('#btnSaveReglement');
            submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');
            $.ajax({
                url: `${apiUrl}/vente/reglement/store`,
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#addReglementModal').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        // Réinitialiser le formulaire
                        resetForm();
                        // Rafraîchir la liste
                        refreshList();
                    } else {
                        Toast.fire({
                            icon: 'error',
                            title: response.message
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Une erreur est survenue';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Toast.fire({
                        icon: 'error',
                        title: message
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false)
                        .html('<i class="fas fa-save me-2"></i>Enregistrer');
                }
            });
        });

        // Fonction pour mettre à jour l'état du bouton de sauvegarde
        function updateSaveButton() {
            const factureSelected = $('#factureSelect').val() !== '';
            const typeSelected = $('#typeReglement').val() !== '';
            const montantValid = parseFloat($('#montant').val()) > 0;

            // Vérifier les champs requis selon le type
            let champsOptionelsValides = true;
            $('#addReglementForm').find('[required]').each(function() {
                if (!$(this).val()) {
                    champsOptionelsValides = false;
                    // console.log($(this)[0].name)
                }
            });

            $('#btnSaveReglement').prop('disabled',
                !(factureSelected && typeSelected && montantValid && champsOptionelsValides)
            );
        }

        $(document).on('blur', '#addReglementForm [required]', function() {
            updateSaveButton();
        });

        // Fonction pour réinitialiser le formulaire
        function resetForm() {
            // Réinitialiser le formulaire
            $('#addReglementForm')[0].reset();

            // Réinitialiser Select2
            $('#factureSelect').val('').trigger('change');

            // Réinitialiser les champs en lecture seule
            $('#clientDisplay').text('Sélectionnez une facture');
            $('#resteAPayer').text('');

            // Désactiver le champ montant
            $('#montant').prop('disabled', true);

            // Cacher les champs optionnels
            $('#banqueGroup, #referenceGroup, #echeanceGroup').hide();

            // Réinitialiser les validations
            $('#addReglementForm').removeClass('was-validated');

            // Désactiver le bouton de sauvegarde
            $('#btnSaveReglement').prop('disabled', true);
        }

        // Fonction pour formater les montants
        function formatMontant(montant) {
            return new Intl.NumberFormat('fr-FR').format(montant);
        }
    });
</script>