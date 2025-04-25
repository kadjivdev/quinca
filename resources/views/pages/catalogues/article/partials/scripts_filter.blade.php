<script>
    // Module de gestion des filtres et de la vue
    const ArticleListManager = {
        init: function() {
            this.initSelectors();
            this.initEvents();
            this.restoreView();
        },

        // Sélecteurs
        initSelectors: function() {
            this.selectors = {
                list: '#articlesList',
                viewButtons: '[data-view]',
                articleItems: '.article-item',
                articleCards: '.article-card',
                filterFamille: '#filterFamille',
                filterStock: '#filterStock',
                searchInput: '#searchArticle'
            };
        },

        // Initialisation des événements
        initEvents: function() {
            // Changement de vue (grille/liste)
            $(this.selectors.viewButtons).on('click', (e) => {
                const view = $(e.currentTarget).data('view');
                this.changeView(view);
            });

            // Filtres
            $(`${this.selectors.filterFamille}, ${this.selectors.filterStock}`).on('change', () => {
                this.applyFilters();
            });

            // Recherche avec debounce
            let searchTimer;
            $(this.selectors.searchInput).on('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => this.applyFilters(), 300);
            });
        },

        // Changement de vue
        changeView: function(viewType) {
            $(this.selectors.viewButtons).removeClass('active');
            $(`[data-view="${viewType}"]`).addClass('active');

            if (viewType === 'list') {
                $(this.selectors.articleItems).removeClass('col-md-4 col-lg-3').addClass('col-12');
                $(this.selectors.articleCards).addClass('flex-row');
            } else {
                $(this.selectors.articleItems).removeClass('col-12').addClass('col-md-4 col-lg-3');
                $(this.selectors.articleCards).removeClass('flex-row');
            }

            localStorage.setItem('articlesView', viewType);
        },

        // Application des filtres
        applyFilters: function() {
            const filters = {
                famille: $(this.selectors.filterFamille).val(),
                stock: $(this.selectors.filterStock).val(),
                search: $(this.selectors.searchInput).val()
            };

            $(this.selectors.list).addClass('loading');

            $.ajax({
                url: "{{ route('articles.filter') }}",
                type: 'GET',
                data: filters,
                success: (response) => {
                    $(this.selectors.list).html(response);
                    const currentView = localStorage.getItem('articlesView') || 'grid';
                    this.changeView(currentView);
                },
                error: (xhr) => {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du filtrage des articles'
                    });
                    console.error('Erreur de filtrage:', xhr);
                },
                complete: () => {
                    $(this.selectors.list).removeClass('loading');
                }
            });
        },

        // Restauration de la vue précédente
        restoreView: function() {
            const savedView = localStorage.getItem('articlesView') || 'grid';
            this.changeView(savedView);
        }
    };

    // Initialisation au chargement de la page
    $(document).ready(function() {
        ArticleListManager.init();
    });
    </script>
