<script>    
    $(document).ready(function() {

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
            calculerTotaux();
        });

        // Soumission du formulaire
        $('#addBonCommandeForm').on('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                saveBonCommande($(this));
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

    function showFacture(factureId) {
        console.log('Chargement Facture:', factureId);

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

        // Afficher un loader
        $('#detailsContainer').show();

        // Construire l'URL correctement
        const url = `${apiUrl}/revendeurs/factures/${factureId}`;

        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Réponse:', response);
                if (response.status) {
                    afficherDetailsFacture(response.data);               
                    Swal.close();
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Erreur lors du chargement'
                    });
                    $('#detailsContainer').hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur Ajax:', {
                    xhr,
                    status,
                    error
                });
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des détails'
                });
                $('#detailsContainer').hide();
                $('#btnSave').hide();
            }
        });
    }

    function afficherDetailsFacture(data) {
        console.log('Données à afficher:', data);
        const facture = data.facture;

        // Réinitialiser le contenu précédent
        $('#articlesSection').empty();

        $('#codeFact').text(facture.numero);

        // Afficher les informations de base
        $('#bonCommandeCodeShow').text(facture.numero);
        $('#pointVenteShow').text(facture.point_de_vente?.nom_pv);
        $('#montantTotalShow').text(facture.montant_ttc);
        $('#ClientShow').text(facture.client.raison_sociale);

        $('#codeFactureShow').val(facture.numero);
        $("[name='date_facture']").val(facture.date_facture.split('T')[0]);
        $("[name='type_facture']").val(facture.type_facture);

        // Vérification des articles
        if (facture.lignes && facture.lignes.length > 0) {
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
                                    <th class="text-end" style="width: 120px;">Quantité</th>
                                    <th class="text-end" style="width: 150px;">Prix Unitaire</th>
                                    <th class="text-end" style="width: 150px;">Montant HT</th>
                                </tr>
                            </thead>
                            <tbody>`;

            facture.lignes.forEach((ligne, index) => {
                const article = ligne.article;
                articlesHtml += `
                <tr>
                    <td>${article.code_article || ''}</td>
                    <td>${article.designation || ''}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end" name="articles[${index}][quantite]" value="${ligne.quantite}" readonly>
                    </td>
                    <td>
                        <input type="number" value="${ligne.prix_unitaire_ht || ''}"  readonly>
                    </td>
                    <td class="text-end">
                        <span class="total-ligne-${index}">${ligne.montant_ht}</span> F CFA
                    </td>
                </tr>`;
            });

            articlesHtml += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;

            $('#articlesSectionShow').html(articlesHtml);

            $("#montantHTShow").html(Number(facture.montant_ht).toFixed(2))
            $("#montantTVAShow").html(Number(facture.montant_tva).toFixed(2))
            $("#montantAIBShow").html(Number(facture.montant_aib).toFixed(2))
            $("#montantTTCShow").html(Number(facture.montant_ttc).toFixed(2))

            $("[name='commentaire']").val(facture.commentaire)

            // Calculer les totaux initiaux
            // data.articles.forEach((article, index) => {
            //     if (article.prix_unitaire) {
            //         calculerMontantLigne(index);
            //     }
            // });
            calculerTotaux();
        } else {
            $('#articlesSection').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Aucun article trouvé dans cette programmation
                </div>
            `);
        }

        // Afficher le conteneur et le bouton
        // $('#detailsContainer').show();
        // $('#btnSave').show();
        $("#showFactureModal").modal('show');
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

    function calculerTotaux() {
        try {
            let totalHT = 0;
            $('.prix-unitaire').each(function() {
                const index = $(this).data('index');
                totalHT += calculerMontantLigne(index);
            });

            // const tva = totalHT * 0.20;
            const tva = 0;
            const totalTTC = totalHT + tva;

            // Mise à jour des totaux avec formatage
            $('#montantHTMod').text(totalHT.toFixed(2));
            $('#montantTVAMod').text(tva.toFixed(2));
            $('#montantTTCMod').text(totalTTC.toFixed(2));
        } catch (e) {
            console.error('Erreur calcul totaux:', e);
            Toast.fire({
                icon: 'error',
                title: 'Erreur lors du calcul des totaux'
            });
        }
    }

    async function editFacture(id) {
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

            const response = await fetch(`${apiUrl}/revendeurs/factures/${id}`, {  // URL mise à jour
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

            if (result) {
                const editModal = document.getElementById('editFactureModal');
                const editForm = document.getElementById('editFactureForm');

                const data = result.data;
                const facture = data.facture;

                editForm.action = `/revendeurs/ventes-speciales/make-validation/${id}`;  // URL mise à jour

                $("#factIdMod").html(facture.numero);

                $('#bonCommandeCodeMod').text(facture.numero);
                $('#pointVenteMod').text(facture.point_de_vente?.nom_pv);
                $('#montantTotalMod').text(facture.montant_ttc);
                $('#ClientMod').text(facture.client.raison_sociale);

                $('#codeFactureMod').val(facture.numero);
                $("[name='date_facture']").val(facture.date_facture.split('T')[0]);
                // $("[name='type_facture']").val(data.type_facture);

                const lignes = facture.lignes || [];
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
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Prix Unitaire</th>
                                            <th class="text-end">Total HT</th>
                                        </tr>
                                    </thead>
                                    <tbody id="articlesTableBodyMod">`;
                if (lignes.length > 0) {

                    lignes.forEach((ligne, index) => {
                        articlesHtml += `
                            <tr>
                                <td>${ligne.article.code_article || ""}</td>
                                <td>${ligne.article.designation || ""}</td>
                                <td class="text-end">
                                    <input type="hidden" name="articles[${index}][article_id]" value="${ligne.article.id}">
                                    <input type="number" class="form-control     text-end"  name="articles[${index}][quantite]" value="${ligne.quantite || 0}" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-end prix-unitaire" name="articles[${index}][prix_unitaire]" step="0.01" min="0" value="${ligne.montant_ht || ""}" data-index="${index}">
                                    <div class="invalid-feedback">Le prix unitaire est requis</div>
                                </td>
                                <td class="text-end">
                                    <span class="total-ligne-${index}">${ligne.montant_ht}</span> F CFA
                                    <input type="hidden" name="articles[${index}][montant_ht]"
                                        class="montant-ht-input"
                                        value="${(ligne.quantite * ligne.montant_ht).toFixed(2)}">
                                    <input type="hidden" name="articles[${index}][taux_tva]"
                                        value="0">
                                    <input type="hidden" name="articles[${index}][taux_aib]"
                                        value="0">
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
                    calculateTotalsMod();
                }      

                if($('select[name="type_facture"]').val() === 'NORMALISE') {
                    $('.tva-aib-section').show();
                }
                
                editForm.querySelector('[name="commentaireMod"]').value = facture?.commentaire;

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

    // Fonction pour supprimer un bon de commande
    function deleteFacture(id) {
        Swal.fire({
            title: 'Confirmer la suppression',
            text: 'Êtes-vous sûr de vouloir supprimer cette facture ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/achat/factures/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Erreur lors de la suppression'
                        });
                    }
                });
            }
        });
    }

</script>
