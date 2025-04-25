<script>
    // Fonction pour charger les données du client dans le modal
function loadClientData(clientId) {
    $.ajax({
        url: `${apiUrl}/vente/clients/${clientId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                console.log('Données reçues:', response.data.client); // Pour debug
                const client = response.data.client;

                // Remplir le formulaire
                $('#edit_client_id').val(client.id);
                $('#edit_raison_sociale').val(client.raison_sociale);
                $('#edit_categorie').val(client.categorie);
                $('#edit_ifu').val(client.ifu);
                $('#edit_rccm').val(client.rccm);
                $('#edit_aib').val(client.taux_aib);
                $('#edit_telephone').val(client.telephone);
                $('#edit_email').val(client.email);
                $('#edit_adresse').val(client.adresse);
                $('#edit_ville').val(client.ville);
                $('#edit_plafond_credit').val(client.credit.plafond);
                $('#edit_delai_paiement').val(client.credit.delai_paiement);
                $('#edit_solde_initial').val(client.credit.solde_initial);

                // Correction pour les notes - accès direct
                $('#edit_notes').val(client.notes);

                // Correction pour le taux AIB - accès direct
                // $('#edit_taux_aib').val(client.taux_aib !== null ? client.taux_aib : '0.00');

                // Gestion du statut
                const isActive = Boolean(client.statut);
                $('#editStatutSwitch')
                    .prop('checked', isActive)
                    .val(isActive ? '1' : '0');

                // Afficher le modal
                $('#editClientModal').modal('show');
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des données',
                    timer: 3000
                });
            }
        },
        error: function() {
            Toast.fire({
                icon: 'error',
                title: 'Erreur lors du chargement des données',
                timer: 3000
            });
        }
    });
}

// Gestion du formulaire de modification
$('#editClientForm').on('submit', function(e) {
    e.preventDefault();

    const clientId = $('#edit_client_id').val();
    const submitBtn = $(this).find('button[type="submit"]');

    // Désactiver le bouton et montrer le spinner
    submitBtn.prop('disabled', true);
    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

    // Création du FormData
    const formData = new FormData(this);

    // S'assurer que tous les champs sont inclus
    formData.set('_method', 'PUT'); // Important pour la méthode PUT
    formData.set('statut', $('#editStatutSwitch').is(':checked') ? '1' : '0');
    formData.set('notes', $('#edit_notes').val());
    formData.set('taux_aib', $('#edit_taux_aib').val() || '0.00');

    // Afficher les données pour debug
    console.log('Données envoyées:', {
        notes: formData.get('notes'),
        taux_aib: formData.get('taux_aib')
    });

    $.ajax({
        url: `${apiUrl}/vente/clients/${clientId}`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Client modifié avec succès',
                    timer: 1500
                });

                // Attendre que le toast soit affiché avant de recharger
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                displayErrors(response.errors);
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;
                if (response.errors) {
                    displayErrors(response.errors);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Erreur de validation',
                        timer: 3000
                    });
                }
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Une erreur est survenue lors de la modification',
                    timer: 3000
                });
            }
        },
        complete: function() {
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="fas fa-save me-2"></i>Enregistrer');
        }
    });
});

// Validation du taux AIB
$('#edit_taux_aib').on('input', function() {
    let value = parseFloat($(this).val());
    if (isNaN(value)) {
        $(this).val('0.00');
        return;
    }
    if (value < 0) $(this).val('0.00');
    if (value > 100) $(this).val('100.00');
});

// Réinitialisation du modal
$('#editClientModal').on('hidden.bs.modal', function() {
    $('#editClientForm')[0].reset();
    $('#editClientForm').removeClass('was-validated');
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');
    $('#editStatutSwitch').prop('checked', true).val('1');
    $('#edit_taux_aib').val('0.00');
});
</script>
