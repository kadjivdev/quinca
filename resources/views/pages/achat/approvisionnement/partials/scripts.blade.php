<script>
    var apiUrl = "{{ config('app.url_ajax') }}";
    // Initialisation du Toast si non défini
    if (typeof Toast === 'undefined') {
        var Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }

    $(document).ready(function() {

        // Initialisation de Select2 avec gestion d'erreur
        try {
            $('#addApprovisionnementModal .select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addApprovisionnementModal')
            });

            $('#editApprovisionnementModal .select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#editApprovisionnementModal')
            });
        } catch (e) {
            console.error('Erreur initialisation Select2:', e);
        }

        // Soumission du formulaire
        $('#addApprovisionnementForm').on('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                saveApprovisionnement($(this));
            }
            $(this).addClass('was-validated');
        });

        // // Soumission du formulaire
        // $('#editApprovisionnementForm').on('submit', function(e) {
        //     e.preventDefault();
        //     if (this.checkValidity()) {
        //         updateBonCommande($(this));
        //     }
        //     $(this).addClass('was-validated');
        // });
    });


    function saveApprovisionnement(form) {
        // Récolter les données du formulaire
        let data = {
            fournisseur_id: $('#fournisseur_id').val(),
            montant: $('input[name="montant"]').val(),
            source: $('input[name="source"]').val(),
            document: $('input[name="document"]').val(),
        };

        // Envoyer les données
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: JSON.stringify(data), // Important: stringify les données
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#btnSave').prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
            `);
            },
            success: function(response) {
                if (response.success) {
                    $('#addApprovisionnementModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    setTimeout(() => window.location.reload(), 1000);
                }
            },
            error: function(xhr) {
                console.error('Erreur Ajax:', xhr);
                let errorMessage = 'Erreur lors de la création';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                }
                Toast.fire({
                    icon: 'error',
                    title: errorMessage
                });
                $('#btnSave').prop('disabled', false);
            }
        });
    }

    function updateApprovisionnement(form) {
        // Récolter les données du formulaire
        let data = {
            date_commande: $('input[name="date_commandeMod"]').val(),
            cout_transport: $('input[name="cout_transport_mod"]').val(),
            cout_chargement: $('input[name="cout_chargement_mod"]').val(),
            autre_cout: $('input[name="autre_cout_mod"]').val(),
            commentaire: $('textarea[name="commentaireMod"]').val(),
            lignes: [] // Initialiser le tableau des lignes
        };

        // Récolter les données des lignes
        // Modifier le sélecteur pour cibler le bon tableau
        $('#articlesSectionMod table tbody tr').each(function(index) {
            data.lignes.push({
                article_id: $(`input[name="articles[${index}][article_id]"]`).val(),
                unite_mesure_id: $(`input[name="articles[${index}][unite_mesure_id]"]`).val(),
                quantite: $(`input[name="articles[${index}][quantite]"]`).val(),
                prix_unitaire: $(`input[name="articles[${index}][prix_unitaire]"]`).val()
            });
        });

        console.log('Données à envoyer:', data); // Pour déboguer

        // Envoyer les données
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: JSON.stringify(data), // Important: stringify les données
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#btnSaveMod').prop('disabled', true).html(`
                <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
            `);
            },
            success: function(response) {
                if (response.success) {
                    $('#editBonCommandeModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    setTimeout(() => window.location.reload(), 1000);
                }
            },
            error: function(xhr) {
                console.error('Erreur Ajax:', xhr);
                let errorMessage = 'Erreur lors de la modification';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors)[0][0];
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                }
                Toast.fire({
                    icon: 'error',
                    title: errorMessage
                });
                $('#btnSave').prop('disabled', false);
            }
        });
    }

    async function editApprovisionnement(id) {
        try {
            Swal.fire({
                title: 'Chargement...',
                text: 'Veuillez patienter...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`${apiUrl}/achat/approvisionnements/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des données');
            }

            const result = await response.json();
            console.log(result);

            // Correction : la réponse est l'objet directement, pas { success, data }
            const data = result?.data;

            console.log("data :", data)

            const editModal = document.getElementById('editApprovisionnementModal');
            const editForm = document.getElementById('editApprovisionnementForm');

            editForm.action = `${apiUrl}/achat/approvisionnements/${id}`;

            // Vider le contenu actuel du select
            const selecteFournisseur = $("#fournisseur_id").empty();

            // Ajouter une option par défaut
            selecteFournisseur.append('<option value="">Sélectionner un fournisseur</option>');

            const fournisseurs = @json($fournisseurs);

            console.log("fournisseurs: ", fournisseurs)

            // Ajouter les fournisseurs
            fournisseurs.forEach(fr => {
                selecteFournisseur.append(
                    `<option value="${fr.id}">${fr.raison_sociale}</option>`
                );
            });

            selecteFournisseur.val(data.fournisseur_id);

            $("#codeAppro").val(data.reference);
            $("#montant").val(data.montant);
            $("#date").val(data.date?.split("T")?.[0]);
            $("#reference").val(data.reference);
            $("#source").val(data.source);

            const modal = new bootstrap.Modal(editModal);
            modal.show();
            Swal.close();

        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des données',
                timer: 3000
            });
        }
    }

    function validateBonCommande(id) {
        console.log('Validation appelée pour ID:', id);

        Swal.fire({
            title: 'Confirmer la validation',
            text: 'Êtes-vous sûr de vouloir valider ce bon de commande ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const loadingAlert = Swal.fire({
                    title: 'Validation en cours...',
                    text: 'Veuillez patienter...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                axios.post(`bon-commandes/${id}/validate`)
                    .then(response => {
                        if (response.data.success) {
                            loadingAlert.close();
                            Swal.fire({
                                icon: 'success',
                                title: 'Validation réussie !',
                                text: 'Le bon de commande a été validé avec succès.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(response.data.message || 'Erreur lors de la validation');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        loadingAlert.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: error.response?.data?.message ||
                                'Une erreur est survenue lors de la validation.',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }

    // Nettoyer le formulaire quand le modal est fermé
    $('#addApprovisionnementModal').on('hidden.bs.modal', function() {
        const form = $('#addApprovisionnementForm');
        form.removeClass('was-validated');
        form[0].reset();
        $('#fournisseurs').val('').trigger('change');
        $('#btnSave').hide();
    });
</script>