// Initialisation du Toast si non défini
if (typeof Toast === "undefined") {
    var Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
    });
}

$(document).ready(function () {
    // Vérification de l'existence des éléments
    if (!$("#programmationSelect").length) {
        console.error("Select programmation non trouvé");
        return;
    }

    // Initialisation de Select2 avec gestion d'erreur
    try {
        $(".select2").select2({
            theme: "bootstrap-5",
            width: "100%",
            dropdownParent: $("#addBonCommandeModal"),
        });
    } catch (e) {
        console.error("Erreur initialisation Select2:", e);
    }

    // Initialiser la date du jour
    $('input[name="date_commande"]').val(
        new Date().toISOString().split("T")[0]
    );

    // Générer le code au chargement
    generateCode();

    // Écouteur de changement de programmation
    $("#programmationSelect").on("change", function () {
        const programmationId = $(this).val();
        if (programmationId) {
            chargerDetailsProgrammation(programmationId);
        } else {
            $("#detailsContainer").hide();
            $("#btnSave").hide();
        }
    });

    // Calculer les montants lors de la saisie
    $(document).on("input", ".prix-unitaire", function () {
        const index = $(this).data("index");
        calculerMontantLigne(index);
        calculerTotaux();
    });

    // Soumission du formulaire
    $("#addBonCommandeForm").on("submit", function (e) {
        e.preventDefault();
        if (this.checkValidity()) {
            saveBonCommande($(this));
        }
        $(this).addClass("was-validated");
    });
});

function generateCode() {
    const date = new Date();
    const year = date.getFullYear().toString().substr(-2);
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const random = Math.floor(Math.random() * 9000 + 1000);
    const code = `BC${year}${month}${day}${random}`;
    $("#codeBC").val(code);
}

function chargerDetailsProgrammation(programmationId) {
    console.log("Chargement programmation:", programmationId);

    // Afficher un loader
    $("#detailsContainer").html(
        '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>'
    );
    $("#detailsContainer").show();

    $.ajax({
        url: `/achat/programmations/${programmationId}`,
        method: "GET",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            console.log("Réponse:", response);
            if (response.success) {
                afficherDetailsProgrammation(response.data);
            } else {
                Toast.fire({
                    icon: "error",
                    title: response.message || "Erreur lors du chargement",
                });
            }
        },
        error: function (xhr) {
            console.error("Erreur Ajax:", xhr);
            Toast.fire({
                icon: "error",
                title: "Erreur lors du chargement des détails",
                text:
                    xhr.responseJSON?.message || "Veuillez réessayer plus tard",
            });
            $("#detailsContainer").hide();
        },
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
        calculerTotaux();
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
        const quantite =
            parseFloat($(`input[name="articles[${index}][quantite]"]`).val()) ||
            0;
        const prixUnitaire =
            parseFloat(
                $(`input[name="articles[${index}][prix_unitaire]"]`).val()
            ) || 0;
        const total = quantite * prixUnitaire;
        $(`.total-ligne-${index}`).text(total.toFixed(2));
    } catch (e) {
        console.error("Erreur calcul ligne:", e);
    }
}

function calculerTotaux() {
    try {
        let totalHT = 0;
        $(".prix-unitaire").each(function () {
            const index = $(this).data("index");
            const quantite =
                parseFloat(
                    $(`input[name="articles[${index}][quantite]"]`).val()
                ) || 0;
            const prixUnitaire = parseFloat($(this).val()) || 0;
            totalHT += quantite * prixUnitaire;
        });

        const tva = totalHT * 0.2;
        const totalTTC = totalHT + tva;

        $("#montantTotal").text(totalHT.toFixed(2));
        $("#montantTVA").text(tva.toFixed(2));
        $("#montantTTC").text(totalTTC.toFixed(2));
    } catch (e) {
        console.error("Erreur calcul totaux:", e);
    }
}

function saveBonCommande(form) {
    const formData = form.serialize();

    $.ajax({
        url: form.attr("action"),
        method: "POST",
        data: formData,
        beforeSend: function () {
            $("#btnSave").prop("disabled", true).html(`
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Enregistrement...
            `);
        },
        success: function (response) {
            if (response.success) {
                // Fermer le modal
                $("#addBonCommandeModal").modal("hide");

                // Notification succès
                Toast.fire({
                    icon: "success",
                    title:
                        response.message || "Bon de commande créé avec succès",
                });

                // Recharger la page après un court délai
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                Toast.fire({
                    icon: "error",
                    title: response.message || "Erreur lors de la création",
                });
                $("#btnSave").prop("disabled", false).html(`
                    <i class="fas fa-save me-2"></i>Enregistrer
                `);
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors;
            let errorMessage = "Erreur lors de la création du bon de commande";

            if (errors) {
                errorMessage = Object.values(errors)[0][0];
            }

            Toast.fire({
                icon: "error",
                title: errorMessage,
            });

            $("#btnSave").prop("disabled", false).html(`
                <i class="fas fa-save me-2"></i>Enregistrer
            `);
        },
    });
}

// Nettoyer le formulaire quand le modal est fermé
$("#addBonCommandeModal").on("hidden.bs.modal", function () {
    const form = $("#addBonCommandeForm");
    form.removeClass("was-validated");
    form[0].reset();
    $("#programmationSelect").val("").trigger("change");
    $("#detailsContainer").hide();
    $("#articlesSection").empty();
    $("#btnSave").hide();
    generateCode();
});
