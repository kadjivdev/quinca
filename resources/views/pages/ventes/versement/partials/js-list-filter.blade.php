<script>
    // client-list.js

$(document).ready(function() {
    // Initialisation des filtres select2
    $('.select2-filter').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Gestion des filtres
    function filterClients() {
        const filters = {
            categorie: $('#categorieFilter').val(),
            ville: $('#villeFilter').val(),
            statut: $('#statutFilter').val(),
            search: $('#searchFilter').val(),
            avec_credit: $('#creditFilter').val()
        };

        $.ajax({
            url: window.location.href,
            type: 'GET',
            data: filters,
            success: function(response) {
                if (typeof response === 'string') {
                    const newContent = $(response).find('#clientsTable').html();
                    $('#clientsTable').html(newContent);

                    const newStats = $(response).find('#statsContainer').html();
                    if (newStats) {
                        $('#statsContainer').html(newStats);
                    }
                } else if (response.html) {
                    $('#clientsTable').html(response.html);
                    if (response.stats) {
                        updateStats(response.stats);
                    }
                }
                initTooltips();
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du rafraîchissement de la liste'
                });
            }
        });
    }

    // Attachement des événements aux filtres
    $('.filter-control').on('change', filterClients);
    $('#searchFilter').on('keyup', debounce(filterClients, 500));

    // Rafraîchissement de la liste
    function refreshList() {
        filterClients();
    }
});

// Fonction debounce pour la recherche
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialisation des tooltips
function initTooltips() {
    $('[data-bs-toggle="tooltip"]').tooltip();
}
</script>