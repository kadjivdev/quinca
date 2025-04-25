<!-- Modal -->
<div class="modal fade" id="inventairesModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fs-5" id="">Dépôt : <span class="badge bg-warning depot-title"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-bottom-0 text-nowrap py-3">N°</th>
                            <th class="border-bottom-0">Date Inventaire</th>
                            <th class="border-bottom-0">Effectué par</th>
                            <th class="border-bottom-0">Détails inventaire</th>
                        </tr>
                    </thead>
                    <tbody class="inventaires-body">
                        <!-- geré par du JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push("scripts")
<script type="text/javascript">
    
    // SHow inventaires
    function showInventories(depot) {
        $(".depot-title").html(depot.libelle_depot + " (" + depot.code_depot + ")")
        console.log(depot.inventaires, depot.inventaires.length)
        $(".inventaires-body").empty();

        let content = ``

        if (depot.inventaires.length > 0) {
            let rows = ``
            let inventDetails = ``

            depot.inventaires.forEach((inventaire, index) => {
                let details = ``
                inventDetails = inventaire.details.forEach(detail => {
                    details += `
                                <li class="bg-warning p-2" style="list-style-type: none">
                                    <strong class="badge  d-block text-dark"> Qte réelle: <strong class="bg-white rounded p-1">${detail.qte_reel}</strong> ; Qte stock: ${detail.qte_stock}; Fait le : <strong class="bg-white rounded p-1">${dateFormated(detail.created_at)}</strong> </strong>
                                </li>
                                <hr>
                    `
                });

                rows += `
                    <tr>
                        <td class="text-nowrap py-3">
                            <span class="badge bg-light text-dark numero-bl me-2">${index+1}</span>
                        </td>
                        <td><span class="badge bg-light text-dark">${dateFormated(inventaire.date_inventaire)}</span></td>
                        <td class="text-center"><span class="badge bg-light text-dark"> ${inventaire.auteur.name} </span></td>
                        <td class="border p-2">
                            <ul class="m-0" style="width:100%;height:100px!important;overflow-y:scroll;">
                                ${details}
                            </ul>
                        </td>
                    </tr>
                `
            });

            content = rows;
        } else {
            content = `<p class="text-center">Aucun inventaires!</p>`
        }

        $(".inventaires-body").append(content)
    }

    // formatage de date
    function dateFormated(date) {
        const _date = new Date(date);
        const options = {
            year: "numeric",
            month: "long",
            day: "numeric"
        };
        return _date.toLocaleDateString("fr", options);

    }
</script>
@endpush