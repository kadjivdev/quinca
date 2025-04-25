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
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addApprovisionnementModal')
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

        // Soumission du formulaire
        $('#editApprovisionnementForm').on('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                updateBonCommande($(this));
            }
            $(this).addClass('was-validated');
        });
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



    async function editBonCommande(id) {
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

            const response = await fetch(`${apiUrl}/achat/bon-commandes/${id}`, { // URL mise à jour
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
            console.log(result.data);

            if (result.success) {
                const editModal = document.getElementById('editBonCommandeModal');
                const editForm = document.getElementById('editBonCommandeForm');

                const data = result.data;

                editForm.action = `${apiUrl}/achat/bon-commandes/${id}`; // URL mise à jour

                const responseProg = await fetch(`${apiUrl}/achat/programmations/validees`, { // URL mise à jour
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const resultProg = await responseProg.json();

                const selectProgrammations = $('#programmationSelectMod');

                // Vider le contenu actuel du select
                selectProgrammations.empty();

                // Ajouter une option par défaut
                selectProgrammations.append('<option value="">Sélectionner une programmation</option>');

                // Ajouter les programmations validées
                resultProg.data.forEach(prog => {
                    selectProgrammations.append(
                        `<option 
                        value="${prog.id}"
                        data-code="${prog.code || ''}"
                        data-point-vente="${prog.point_vente.nom_pv || ''}"
                        data-point-vente-id="${prog.point_de_vente_id || ''}"
                        data-fournisseur="${prog.fournisseur.raison_sociale || ''}"
                        data-fournisseur-id="${prog.fournisseur_id || ''}"
                        data-validation="${prog.validated_at ? new Date(prog.validated_at).toLocaleDateString('fr-FR') : ''}">
                        ${prog.code || 'N/A'} - (Validée le ${prog.validated_at ? new Date(prog.validated_at).toLocaleDateString('fr-FR') : 'N/A'})
                    </option>`
                    );
                });

                // Marquer la programmation sélectionnée
                const option = selectProgrammations.find(`option[value="${data.programmation_id}"]`);
                if (option) {
                    option.prop('selected', true);
                }

                $("#bonIdMod").html(data.programmation.code);

                $("#programmationCodeMod").html(data.programmation.code);
                $("#pointVenteMod").html(data.point_vente.nom_pv);
                $("[name='point_vente_id']").val(data.point_vente.id);
                $("#fournisseurMod").html(data.fournisseur.raison_sociale);
                $("[name='fournisseur_id']").val(data.fournisseur_id);
                $("#dateValidationMod").html(data.programmation.validated_at.split('T')[0]);

                $("[name='code']").val(data.code);
                $("[name='date_commandeMod']").val(data.date_commande.split('T')[0]);

                const lignes = data.lignes || [];
                let articlesHtml = `
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
                                        <th class="text-end">Total HT</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                if (lignes.length > 0) {

                    lignes.forEach((ligne, index) => {
                        articlesHtml += `
                        <tr>
                            <td>${ligne.article.code_article || ""}</td>
                            <td>${ligne.article.designation || ""}</td>
                            <td>${ligne.unite_mesure.libelle_unite || ""}</td>
                            <td class="text-end">
                                <input type="hidden" name="articles[${index}][article_id]" value="${ligne.article.id}">
                                <input type="number" class="form-control form-control-sm text-end"  name="articles[${index}][quantite]" value="${ligne.quantite || 0}" readonly>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-end prix-unitaire" name="articles[${index}][prix_unitaire]" step="0.01" min="0" value="${ligne.prix_unitaire || ""}" data-index="${index}" required>
                                <div class="invalid-feedback">Le prix unitaire est requis</div>
                            </td>
                            <td class="text-end">
                                <span class="total-ligne-${index}">0.00</span> F CFA
                            </td>
                        </tr>`;
                    });

                    articlesHtml += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>`;

                    $("#articlesSectionMod").html(articlesHtml);
                    calculerTotaux('update');
                }

                editForm.querySelector('[name="cout_transport_mod"]').value = data.cout_transport;
                editForm.querySelector('[name="cout_chargement_mod"]').value = data.cout_chargement;
                editForm.querySelector('[name="autre_cout_mod"]').value = data.autre_cout;

                editForm.querySelector('[name="commentaireMod"]').value = data.commentaire;

                const modal = new bootstrap.Modal(editModal);
                modal.show();
                Swal.close();
            } else {
                throw new Error(result.message || 'Erreur lors du chargement des données');
            }
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