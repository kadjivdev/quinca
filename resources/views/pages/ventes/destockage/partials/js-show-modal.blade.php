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
                    <span class="badge bg-light text-dark border rounded">${ligne.article?.designation??'---'}</span>
                </td>
                <td>
                    <span class="badge bg-light text-dark border rounded">${ligne.unite_mesure?.libelle_unite}</span>
                </td>
                <td>
                    <span class="badge bg-light text-dark border rounded">${ligne.qte}</span>
                </td>
                <td>
                    <span class="badge bg-light text-dark border rounded">${ligne.pu}</span>
                </td>
                <td>
                    <span class="badge bg-light text-dark border rounded">${ligne.montant}</span>
                </td>
            </tr>
            `
        });

        $("#showDestockageLignesContainer").append(rows)

        $('#showDestockageModal').modal('show');
    }
</script>