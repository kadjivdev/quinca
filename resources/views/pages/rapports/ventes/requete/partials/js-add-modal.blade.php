<script>
    $(document).ready(function() {
        // filtres (inchangés)
        $('#filtre_depot_id').select2({
            width: '100%',
            placeholder: 'Sélectionner un depôt',
            allowClear: true
        });
        $('#filtre_article_id').select2({
            width: '100%',
            placeholder: 'Sélectionner un article',
            allowClear: true
        });

        const $modal = $('#addRequeteModal');
        const $container = $('#lignesContainer');
        const dropdownParent = $modal.length ? $modal.find('.modal-content') : null;
        let ligneIndex = 0;

        function initSelect2($select) {
            const opts = {
                theme: 'bootstrap-5',
                placeholder: $select.data('placeholder'),
                width: '100%'
            };
            if (dropdownParent) opts.dropdownParent = dropdownParent;
            $select.select2(opts);
        }

        // Remplit dépôts + unité selon l'article sélectionné dans la ligne
        function populateLigne($ligne, depotId = '', uniteId = '') {
            const $selected = $ligne.find('.article-select option:selected');
            const $depot = $ligne.find('.depot-select').empty();
            const $unite = $ligne.find('.unite-select').empty();

            if (!$selected.val()) return;

            const article = $selected.data('article');
            const depots = $selected.data('depots') || [];

            console.log(`article selected :`, article)

            $depot.append('<option value="">Choisir le dépôt</option>');
            depots.forEach(d => {
                $depot.append(new Option(`${d.libelle_depot}-(${d.code_depot})`, d.id, false, String(d.id) === String(depotId)));
            });

            if (article.unite_mesure) {
                const u = article.unite_mesure;
                $unite.append(new Option(u.libelle_unite, u.id, true, true));
            }

            $depot.trigger('change.select2');
            $unite.trigger('change.select2');
        }

        function updateRemoveButtons() {
            const only = $container.children('.ligne-requete').length === 1;
            $container.find('.btn-remove-ligne').prop('disabled', only);
        }

        function addLigne(data = {}) {
            const html = $('#ligneTemplate').html().replaceAll('__INDEX__', ligneIndex++);
            const $ligne = $(html).appendTo($container);

            $ligne.find('select').each(function() {
                initSelect2($(this));
            });

            // Réhydratation après erreur de validation (old input)
            if (data.article_id) {
                $ligne.find('.article-select').val(data.article_id).trigger('change.select2');
                populateLigne($ligne, data.depot_id, data.unite_mesure_id);
                $ligne.find('.quantite-input').val(data.quantite ?? '');
            }

            updateRemoveButtons();
        }

        // Événements délégués (fonctionnent pour les lignes ajoutées dynamiquement)
        $container.on('change', '.article-select', function() {
            populateLigne($(this).closest('.ligne-requete'));
        });

        $container.on('click', '.btn-remove-ligne', function() {
            $(this).closest('.ligne-requete').find('select').select2('destroy');
            $(this).closest('.ligne-requete').remove();
            updateRemoveButtons();
        });

        $('#btnAddLigne').on('click', () => addLigne());

        // Initialisation : lignes old() ou une ligne vide
        const oldLignes = @json(old('lignes', []));
        if (Object.keys(oldLignes).length) {
            Object.values(oldLignes).forEach(l => addLigne(l));
        } else {
            addLigne();
        }

        // Si tu veux repartir de zéro à chaque ouverture du modal
        $modal.on('hidden.bs.modal', function() {
            $container.find('select').select2('destroy');
            $container.empty();
            ligneIndex = 0;
            addLigne();
        });
    });
</script>