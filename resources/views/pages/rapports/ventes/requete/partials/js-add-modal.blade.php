<script>
    $(document).ready(function() {
        // filtres
        $('#filtre_depot_id').select2({
            width: '100%',
            placeholder: 'Sélectionner un depôt',
            allowClear: true
        });

        let isInModal = $('#addRequeteModal').length;
        let dropDownOrNot = (isInModal && {
            dropdownParent: $('#addRequeteModal .modal-content')
        })

        $("#article_id").select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner un article',
            width: '100%',
            ...dropDownOrNot
        })

        $("#depot_id").select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner un dépôt',
            width: '100%',
            ...dropDownOrNot
        })

        $("#unite_mesure_id").select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner une unité de mesure',
            width: '100%',
            ...dropDownOrNot
        })

        $('#filtre_article_id').select2({
            width: '100%',
            placeholder: 'Sélectionner un article',
            allowClear: true
        });


        // 
        let oldArticleId = "{{ old('depot_id',$requete->article_id??'') }}";
        let oldDepotId = "{{ old('depot_id',$requete->depot_id??'') }}";
        let oldMesureId = "{{ old('unite_mesure_id',$requete->unite_mesure_id??'') }}";

        $('#article_id').on('select2:select', function(e) {
            // alert(e.params.data.id); // ou e.params.data.text selon ce que tu veux
            const selected = $(this).find(':selected');
            const unites = selected.data('unites');
            const depots = selected.data('depots');

            // gestion du select des depots
            $("#depot_id").empty()
            let depotsOptions = '<option value="">Choisir le dépôt </option>'
            depots.forEach(depot => {
                let selected = (oldDepotId == depot.id) ? 'selected' : '';
                depotsOptions += `<option value="${depot.id}" ${selected}>
                            ${depot.libelle_depot }-(${depot.code_depot})
                        </option>`
            });
            $("#depot_id").append(depotsOptions)

            // gestion du select des unites
            $("#unite_mesure_id").empty()
            let unitesOptions = '<option value="">Choisir une unité </option>'
            unites.forEach(unite => {
                let selected = (oldMesureId == unite.id) ? 'selected' : '';
                unitesOptions += `<option value="${unite.id}" ${selected}>
                            ${unite.text }
                        </option>`
            });
            $("#unite_mesure_id").append(unitesOptions)


            // console
            // console.log(`Les unités concernées : ${JSON.stringify(unites)}`)
            // console.log(`Les dépôts concernés : ${JSON.stringify(depots)}`)
        });
    });
</script>