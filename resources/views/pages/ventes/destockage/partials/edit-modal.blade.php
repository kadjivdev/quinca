<div class="modal fade" id="editDestockageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl bg-white">
        <div class="modal-content border-0 ">
            {{-- Header du modal avec un nouveau design --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier le destockage : <strong id="editDestockageCode"></strong> </h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer un nouveau destockage.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST">
                @csrf
                @method("PATCH")
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section informations générales --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations Générales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Date</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-clock text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_op"
                                                    id="editDateOp"
                                                    required>
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Reference</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-clock text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control"
                                                    id="editDestockageReference"
                                                    name="reference"
                                                    placeholder="XXXXXXXXXXX"
                                                    required>
                                            </div>
                                            <div class="invalid-feedback">La reference est est requise</div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Dépôt</label>
                                            <div class="input-group">
                                                <select class="form-select select2" name="depot_id"
                                                    id="editDepotId" required>
                                                    <!--  -->
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le dépôt est requis</div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Client</label>
                                            <div class="input-group">
                                                <select class="form-select select2"
                                                    id="editClientId"
                                                    name="client_id" required>
                                                    <!--  -->
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le client est requis</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section articles --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i>Articles
                                    </h6>
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="editAddLigne()">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="exampleModal" class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Article</th>
                                                    <th>Unité</th>
                                                    <th>Quantité</th>
                                                    <th>Prix unitaire</th>
                                                    <th>Montant</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="editDestockageLignesContainer">
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section observations --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Observations
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="observation"
                                        id="editObservation" rows="3" placeholder="Observations éventuelles"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Template pour une nouvelle ligne --}}
<template id="editLigneDestockageTemplate">
    <tr class="ligne-facture">
        <td>
            <select class="form-select select-modal"
                name="lignes[__INDEX__][article_id]"
                required>
                <option value="">Sélectionner un article</option>
                @foreach($articles as $article)
                <option value="{{$article->id}}">{{$article->designation}}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">L'article est requis</div>
        </td>
        <td>
            <div class="input-group">
                <select class="form-select select-modal" name="lignes[__INDEX__][unite_mesure_id]" required>
                    <option value="">Unité de mesure</option>
                    @foreach($uniteMesures as $unite)
                    <option value="{{$unite->id}}">{{$unite->libelle_unite}}</option>
                    @endforeach
                </select>
            </div>
            <div class="invalid-feedback">L'unité de mesure est requise</div>
        </td>
        <td>
            <input type="number"
                class="form-control text-end"
                name="lignes[__INDEX__][qte]"
                placeholder="0.00"
                required
                min="0.01"
                step="0.01"
                onchange="updateMontant(lignes[__INDEX__][qte])">
            <div class="invalid-feedback">La quantité est requise</div>
        </td>
        <td>
            <input type="number"
                class="form-control text-end"
                name="lignes[__INDEX__][pu]"
                required
                placeholder="0.00"
                min="0"
                step="0.01"
                onchange="updateMontant(__INDEX__)">
            <div class="invalid-feedback">Le prix unitaire est requis</div>
        </td>
        <td>
            <input type="number"
                class="form-control text-end"
                name="lignes[__INDEX__][montant]"
                required
                placeholder="0.00"
                min="0"
                max="100"
                step="0.01"
                readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-ligne">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push("scripts")
<script>
    function editDestockage(destockage) {

        console.log("destockage detail:", destockage)

        const articles = json_encode("{{$articles}}")
        const depots = json_encode("{{$depots}}")
        const uniteMesures = json_encode("{{$uniteMesures}}")

        console.log("articles :", articles)

        // Remplir les informations de base
        $('#editDestockageCode').text(`Code : ${destockage.code}`);
        $('#editDestockageReference').text(destockage.reference);

        $('#destockageClient').text(`Client : ${destockage.client?.raison_sociale}`);
        $("#observation").html(destockage.Observation)
        $('#editDateOp').val(destockage.date_op);

        $("#editDestockageLignesContainer").html(
            destockage.lignes?.forEach(ligne => {
                `
                <tr class="ligne-facture">
                    <td>
                        <select class="form-select select-modal"
                            name="lignes[${ligne.id}][article_id]"
                            required>
                            <option value="">Sélectionner un article</option>
                            ${articles.map((article)=>
                                `<option ${article.id==ligne.article_id?'selected':''} value="${article.id}">${article.designation}</option>`
                            ).join('')}
                        </select>
                        <div class="invalid-feedback">L'article est requis</div>
                    </td>
                    <td>
                        <div class="input-group">
                            <select class="form-select select-modal" name="lignes[${ligne.id}][unite_mesure_id]" required>
                                <option value="">Unité de mesure</option>
                                ${uniteMesures.map((unite)=>
                                    `<option ${unite.id==ligne.unite_mesure_id?'selected':''} value="${unite.id}">${unite.libelle_unite}</option>`
                                ).json('')}
                            </select>
                        </div>
                        <div class="invalid-feedback">L'unité de mesure est requise</div>
                    </td>
                    <td>
                        <input type="number"
                            class="form-control text-end"
                            name="lignes[${ligne.id}][qte]"
                            placeholder="0.00"
                            required
                            min="0.01"
                            step="0.01"
                            value="${ligne.qte}"
                            onchange="editUpdateMontant(lignes[${ligne.id}][qte])">
                        <div class="invalid-feedback">La quantité est requise</div>
                    </td>
                    <td>
                        <input type="number"
                            class="form-control text-end"
                            name="lignes[${ligne.id}][pu]"
                            required
                            placeholder="0.00"
                            min="0"
                            step="0.01"
                            value="${ligne.pu}"
                            onchange="editUpdateMontant(lignes[${ligne.id}][pu])">
                        <div class="invalid-feedback">Le prix unitaire est requis</div>
                    </td>
                    <td>
                        <input type="number"
                            class="form-control text-end"
                            name="lignes[${ligne.id}][montant]"
                            required
                            placeholder="0.00"
                            min="0"
                            max="100"
                            step="0.01"
                            value="${ligne.montant}"
                            readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm edit-remove-ligne">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
                `
            })
        )

        $('#editDestockageModal').modal('show');
    }
</script>
@endpush

@push("scripts")
<script>
    let editLigneIndex = destockage.lignes[destockage.lignes.lenght - 1]?.id + 1; // ✅ Variable globale, persiste entre les appels

    // Initialiser select2 sur une ligne spécifique
    function editInitSelect2OnLigne($ligne) {
        $ligne.find(".select-modal").select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addDestockageModal'),
        });
    }

    // Ajouter une ligne
    function editAddLigne() {
        try {
            const template = $("#editLigneDestockageTemplate").html();
            const newLine = template.replace(/__INDEX__/g, editLigneIndex); // ✅ Remplace avec le bon index
            const $newLine = $(newLine);

            $newLine.hide();
            $("#addDestockageLignesContainer").append($newLine); // ✅ Append $newLine, pas template
            $newLine.fadeIn(300);

            editInitSelect2OnLigne($newLine); // ✅ Init select2 sur la nouvelle ligne

            // ✅ Brancher le bouton supprimer
            $newLine.find(".remove-ligne").on("click", function() {
                $(this).closest("tr").fadeOut(300, function() {
                    $(this).remove();
                    updateTotalGeneral();
                });
            });

            // ✅ Brancher les inputs pour le calcul automatique
            $newLine.find("input[name$='[qte]'], input[name$='[pu]']").on("input", function() {
                editUpdateMontant($newLine);
            });

            editLigneIndex++; // ✅ Incrémenter après usage
        } catch (error) {
            console.error("Erreur lors de l'ajout de ligne:", error);
        }
    }

    // ✅ Calcul du montant d'une ligne
    function updateMontant($ligne) {
        const qte = parseFloat($ligne.find("input[name$='[qte]']").val()) || 0;
        const pu = parseFloat($ligne.find("input[name$='[pu]']").val()) || 0;
        const montant = qte * pu;

        $ligne.find("input[name$='[montant]']").val(montant.toFixed(2));
        updateTotalGeneral();
    }

    // ✅ Calcul du total général (optionnel mais utile)
    function updateTotalGeneral() {
        let total = 0;
        $("#addDestockageLignesContainer tr").each(function() {
            const montant = parseFloat($(this).find("input[name$='[montant]']").val()) || 0;
            total += montant;
        });
        // Affichez le total où vous voulez, ex:
        // $("#totalGeneral").text(total.toFixed(2));
        console.log("Total général :", total.toFixed(2));
    }

    // Initialisation — une ligne vide au démarrage
    editAddLigne();
</script>
@endpush