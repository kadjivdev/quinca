<script>
    // edit-modal.js
$(document).ready(function() {
    let currentReglementId = null;

    // Fonction pour charger les données du règlement
    window.editReglement = function(reglementId) {
        currentReglementId = reglementId;

        // Réinitialiser le formulaire
        $('#editReglementForm')[0].reset();
        $('#editReglementForm').removeClass('was-validated');

        // Afficher le modal
        const modal = $('#editReglementModal');
        modal.modal('show');

        // Charger les données du règlement
        $.ajax({
            url: `${apiUrl}/vente/reglement/${reglementId}/details`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const reglement = response.data;

                    // Remplir les champs
                    $('#editReglementId').val(reglement.id);
                    $('#editFactureDisplay').text(reglement.facture.numero);
                    $('#editClientDisplay').text(reglement.facture.client.raison_sociale);
                    $('#editTypeReglement').val(reglement.type_reglement);
                    $('#editMontant').val(reglement.montant);
                    $('#editDateReglement').val(reglement.date_reglement);
                    $('#editBanque').val(reglement.banque);
                    $('#editReference').val(reglement.reference_preuve);
                    $('#editDateEcheance').val(reglement.date_echeance);
                    $('#editNotes').val(reglement.notes);

                    // Afficher le reste à payer
                    const resteAPayer = reglement.facture.montant_ttc - reglement.facture.montant_regle;
                    $('#editResteAPayer').html(`Reste à payer: <strong>${formatMontant(resteAPayer)} F</strong>`);

                    // Configurer le montant maximum
                    $('#editMontant').attr('max', resteAPayer);

                    // Afficher/masquer les champs selon le type
                    handleEditTypeReglement(reglement.type_reglement);

                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du chargement du règlement'
                    });
                    modal.modal('hide');
                }
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement du règlement'
                });
                modal.modal('hide');
            }
        });
    };

    // Gestion du type de règlement
    function handleEditTypeReglement(type) {
        // Cacher tous les champs optionnels
        $('#editBanqueGroup, #editReferenceGroup, #editEcheanceGroup').hide();
        $('#editReference, [name="banque"], [name="date_echeance"]').prop('required', false);

        // Afficher les champs selon le type
        switch(type) {
            case 'cheque':
                $('#editBanqueGroup, #editReferenceGroup, #editEcheanceGroup').show();
                $('#editReference, [name="banque"], [name="date_echeance"]').prop('required', true);
                $('#editReferenceLabel').text('N° du chèque');
                $('#editReference').attr('placeholder', 'Numéro du chèque');
                break;

            case 'virement':
                $('#editBanqueGroup, #editReferenceGroup').show();
                $('#editReference, [name="banque"]').prop('required', true);
                $('#editReferenceLabel').text('Référence du virement');
                $('#editReference').attr('placeholder', 'Référence du virement');
                break;

            case 'carte_bancaire':
            case 'MoMo':
            case 'Flooz':
            case 'Celtis_Pay':
                $('#editReferenceGroup').show();
                $('#editReference').prop('required', true);
                $('#editReferenceLabel').text('N° de transaction');
                $('#editReference').attr('placeholder', 'Numéro de transaction');
                break;
        }
    }

    // Au changement du type de règlement
    $('#editTypeReglement').on('change', function() {
        handleEditTypeReglement($(this).val());
    });

    // Soumission du formulaire
    $('#editReglementForm').on('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const submitBtn = $('#btnUpdateReglement');
        submitBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-2"></i>Mise à jour...');

        $.ajax({
            url: `${apiUrl}/vente/reglement/${currentReglementId}/update`,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editReglementModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
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
                        .html('<i class="fas fa-save me-2"></i>Mettre à jour');
            }
        });
    });

    // Formatage des montants
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR').format(montant);
    }
});
</script>
