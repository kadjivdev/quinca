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
    // Vérification de l'existence des éléments
    if (!$('#programmationSelect').length) {
        console.error('Select programmation non trouvé');
        return;
    }

    // Initialisation de Select2 avec gestion d'erreur
    try {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addBonCommandeModal')
        });
    } catch (e) {
        console.error('Erreur initialisation Select2:', e);
    }

    // Initialiser la date du jour
    $('input[name="date_commande"]').val(new Date().toISOString().split('T')[0]);

    // Générer le code au chargement
    generateCode();

    // Écouteur de changement de programmation
    $('#programmationSelect').on('change', function() {
        const programmationId = $(this).val();
        if (programmationId) {
            chargerDetailsProgrammation(programmationId);
        } else {
            $('#detailsContainer').hide();
            $('#btnSave').hide();
        }
    });

    // Calculer les montants lors de la saisie
    $(document).on('input', '.prix-unitaire', function() {
        const index = $(this).data('index');
        calculerMontantLigne(index);
        calculerTotaux('add');
        calculerTotaux('update');
    });

    // Soumission du formulaire
    $('#addBonCommandeForm').on('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            saveBonCommande($(this));
        }
        $(this).addClass('was-validated');
    });

    // Soumission du formulaire
    $('#editBonCommandeForm').on('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            updateBonCommande($(this));
        }
        $(this).addClass('was-validated');
    });
});

function generateCode() {
    const date = new Date();
    const year = date.getFullYear().toString().substr(-2);
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const random = Math.floor(Math.random() * 9000 + 1000);
    const code = `BC${year}${month}${day}${random}`;
    $('#codeBC').val(code);
}

function chargerDetailsProgrammation(programmationId) {
    console.log('Chargement programmation:', programmationId);

    // Afficher un loader
    $('#detailsContainer').show();

    // Construire l'URL correctement
    const url = `${apiUrl}/achat/programmations/${programmationId}/show`; // Modifier selon votre route

    $.ajax({
        url: url,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Réponse:', response);
            if (response.success) {
                afficherDetailsProgrammation(response.data);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Erreur lors du chargement'
                });
                $('#detailsContainer').hide();
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur Ajax:', {xhr, status, error});
            Toast.fire({
                icon: 'error',
                title: 'Erreur lors du chargement des détails'
            });
            $('#detailsContainer').hide();
            $('#btnSave').hide();
        }
    });
}

function afficherDetailsProgrammation(programmation) {
    console.log("Données à afficher:", programmation);

    // Réinitialiser le contenu précédent
    $("#articlesSection").empty();

    // Afficher les informations de base
    const selectedOption = $("#programmationSelect option:selected");
    $("#programmationCode").text(selectedOption.data("code") || "");
    $("#pointVente").text(selectedOption.data("point-vente") || "");
    $("#fournisseur").text(selectedOption.data("fournisseur") || "");
    $("#dateValidation").text(selectedOption.data("validation") || "");

    // Vérification de la structure des données
    if (!programmation) {
        console.error("Pas de données de programmation");
        return;
    }

    const articles = programmation.articles || [];
    if (articles.length > 0) {
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

        articles.forEach((article, index) => {
            articlesHtml += `
                <tr>
                    <td>${article.reference || ""}</td>
                    <td>${article.designation || ""}</td>
                    <td>${article.unite || ""}</td>
                    <td class="text-end">
                        <input type="hidden" name="articles[${index}][article_id]" value="${
                article.id
            }">
                        <input type="number" class="form-control form-control-sm text-end"
                               name="articles[${index}][quantite]" value="${
                article.quantite || 0
            }"
                               readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end prix-unitaire"
                               name="articles[${index}][prix_unitaire]" step="0.01" min="0"
                               value="${
                                   article.prix_unitaire || ""
                               }" data-index="${index}"
                               required>
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

        $("#articlesSection").html(articlesHtml);
        calculerTotaux('add');
    } else {
        console.warn("Aucun article trouvé dans la programmation");
        $("#articlesSection").html(
            '<div class="alert alert-info">Aucun article trouvé dans cette programmation</div>'
        );
    }


    // Afficher le conteneur et le bouton
    $("#detailsContainer").show();
    $("#btnSave").show();
}

function calculerMontantLigne(index) {
    try {
        const quantite = parseFloat($(`input[name="articles[${index}][quantite]"]`).val()) || 0;
        const prixUnitaire = parseFloat($(`input[name="articles[${index}][prix_unitaire]"]`).val()) || 0;
        const total = quantite * prixUnitaire;
        $(`.total-ligne-${index}`).text(total.toFixed(2));
        return total; // Retourner le total pour le calcul global
    } catch (e) {
        console.error('Erreur calcul ligne:', e);
        return 0;
    }
}

function calculerTotaux(type) { //Type est soit un ajout sopit une modificaton Valeurs possible : 'add', 'update'
    try {
        let totalHT = 0;
        $('.prix-unitaire').each(function() {
            const index = $(this).data('index');
            totalHT += calculerMontantLigne(index);
        });

        const tva = totalHT * 0.20;
        const totalTTC = totalHT + tva;

        // Mise à jour des totaux avec formatage
        if(type=='add'){
            $('#montantTotal').text(totalHT.toFixed(2));
            $('#montantTVA').text(tva.toFixed(2));
            $('#montantTTC').text(totalTTC.toFixed(2));
        }else if(type=='update'){
            $('#montantTotalMod').text(totalHT.toFixed(2));
            $('#montantTVAMod').text(tva.toFixed(2));
            $('#montantTTCMod').text(totalTTC.toFixed(2));
        }
    } catch (e) {
        console.error('Erreur calcul totaux:', e);
        Toast.fire({
            icon: 'error',
            title: 'Erreur lors du calcul des totaux'
        });
    }
}

function saveBonCommande(form) {
    console.log("saving bon de commande")
    // Récolter les données du formulaire
    let data = {
        code: $('#codeBC').val(),
        date_commande: $('input[name="date_commande"]').val(),
        programmation_id: $('#programmationSelect').val(),
        commentaire: $('textarea[name="commentaire"]').val(),
        cout_transport: $('input[name="cout_transport"]').val(),
        cout_chargement: $('input[name="cout_chargement"]').val(),
        autre_cout: $('input[name="autre_cout"]').val(),
        lignes: [] // Initialiser le tableau des lignes
    };

    console.log("Récollage des données des lignes",data)
    // Récolter les données des lignes
    // Modifier le sélecteur pour cibler le bon tableau
    $('#articlesSection table tbody tr').each(function(index) {
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
            console.log("Response at success ",response)

            if (response.success) {
                $('#addBonCommandeModal').modal('hide');
                Toast.fire({
                    icon: 'success',
                    title: response.message
                });
                setTimeout(() => window.location.reload(), 1000);
            }else{
                Toast.fire({
                icon: 'error',
                title: response.message
            });
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

function updateBonCommande(form) {
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

        const response = await fetch(`${apiUrl}/achat/bon-commandes/${id}`, {  // URL mise à jour
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

            editForm.action = `${apiUrl}/achat/bon-commandes/${id}`;  // URL mise à jour

            const responseProg = await fetch(`${apiUrl}/achat/programmations/validees`, {  // URL mise à jour
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
$('#addBonCommandeModal').on('hidden.bs.modal', function() {
    const form = $('#addBonCommandeForm');
    form.removeClass('was-validated');
    form[0].reset();
    $('#programmationSelect').val('').trigger('change');
    $('#detailsContainer').hide();
    $('#articlesSection').empty();
    $('#btnSave').hide();
    generateCode();
});
</script>
