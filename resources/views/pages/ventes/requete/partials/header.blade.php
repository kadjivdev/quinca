<div class="page-header mb-4">
    <div class="container-fluid p-0">
        {{-- En-tête principal --}}
        <div class="row align-items-center mb-4">
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div>

                    </div>
                </div>
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-light px-3 d-inline-flex align-items-center" onclick="location.reload()">
                    <i class="fas fa-sync-alt me-2"></i>
                    Actualiser
                </button>

                @can("requetes.create")
                <button type="button"
                    class="btn btn-primary px-3 d-inline-flex align-items-center"
                    data-bs-toggle="modal"
                    data-bs-target="#addRequeteModal">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle requête
                </button>
                @endcan
            </div>
        </div>

        <!-- GESTION DES ERREURES -->
        <div class="row justify-content-center d-flex">
            <div class="col-md-6">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if (session()->has('success'))
                <div class="alert alert-success">
                    {{session()->get("success") }}
                </div>
                @endif

                @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{session()->get("error") }}
                </div>
                @endif
            </div>
        </div>
        <!-- FIN GESTION DES ERREURES -->
    </div>
</div>
<link href="{{ asset('css/theme/header.css') }}" rel="stylesheet">

<style>
    .page-header {
        margin-bottom: 2rem;
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header-icon .icon-wrapper {
        transition: transform 0.3s ease;
    }

    .header-icon:hover .icon-wrapper {
        transform: scale(1.1);
    }

    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
    }

    .badge {
        padding: 0.5rem 0.75rem;
    }

    .btn {
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn i {
        transition: transform 0.3s ease;
    }

    .btn:active i {
        transform: scale(0.9);
    }
</style>

<script>
    // Animation de rafraîchissement
    function refreshList() {
        const icon = document.querySelector('.fa-sync-alt');
        icon.classList.add('fa-spin');

        // Appel AJAX pour rafraîchir les données
        $.ajax({
            url: '{{ route("vente.reglement.refresh") }}',
            type: 'GET',
            success: function(response) {
                // Mettre à jour le contenu
                $('#reglementsList').html(response.html);

                // Mettre à jour les statistiques
                updateStats(response.stats);

                // Notification
                Toast.fire({
                    icon: 'success',
                    title: 'Liste actualisée'
                });
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors de l\'actualisation'
                });
            },
            complete: function() {
                // Arrêter l'animation après un délai
                setTimeout(() => {
                    icon.classList.remove('fa-spin');
                }, 500);
            }
        });
    }

    // Fonction pour mettre à jour les statistiques
    function updateStats(stats) {
        // Mettre à jour chaque statistique
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                if (key.includes('montant')) {
                    element.textContent = new Intl.NumberFormat('fr-FR').format(stats[key]) + ' F';
                } else {
                    element.textContent = stats[key];
                }
            }
        });
    }
</script>