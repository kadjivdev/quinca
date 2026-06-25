<script>
    function showDestockage(destockage) {

        console.log("destockage detail:", destockage)

        // Remplir les informations de base
        $('#destockageClient').text(`Client : ${destockage.client?.raison_sociale}`);
        $('#destockageCode').text(`Code : ${destockage.code}`);
        $('#destockageReference').text(destockage.reference);
        $("#observation").html(destockage.Observation)
        $('#destockageDate').text(destockage.date_op);


        $("#showDestockageLignesContainer").empty()
        let rows = ''
        if (destockage.lignes.length == 0) {
            rows = "Aucun article"
        }

        destockage.lignes?.forEach(ligne => {
            rows += `
            <tr class="ligne-facture">
                <td>
                    <select class="form-select select-modal">
                        <option value="">${ligne.article?.designation??'---'}</option>
                    </select>
                </td>
                <td>
                    <div class="input-group">
                        <select class="form-select select-modal">
                            <option value="">${ligne.unite_mesure?.libelle_unite}</option>
                        </select>
                    </div>
                </td>
                <td>
                    <input type="number"
                        class="form-control text-end"
                        readonly
                        value="${ligne.qte}">
                </td>
                <td>
                    <input type="number"
                        class="form-control text-end"
                        readonly
                        value="${ligne.pu}">
                </td>
                <td>
                    <input type="number"
                        class="form-control text-end"
                        readonly
                        value="${ligne.montant}">
                </td>
            </tr>
            `
        });

        $("#showDestockageLignesContainer").append(rows)

        $('#showDestockageModal').modal('show');
    }
</script>