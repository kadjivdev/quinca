@extends('layouts.rapport.stock')

@section('title', 'Tableau de bord des ventes')
@section('content')
    @include('pages.rapports.stocks.header-stock')



    {{-- Filtres et Table --}}
    <div class="row g-3">
        {{-- Filtres --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="row g-2">
                        {{-- Filtre Magasin --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small mb-1">Magasin</label>
                            <select class="form-select form-select-sm" id="depotFilter" onchange="filterValorisation()">
                                <option value="">Tous les dépôts</option>
                                @foreach ($depots as $depot)
                                    <option value="{{ $depot->id }}">{{ $depot->libelle_depot }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filtre Famille Article --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small mb-1">Famille Articles</label>
                            <select class="form-select form-select-sm" id="familleFilter" onchange="filterValorisation()">
                                <option value="">Toutes les familles</option>
                                @foreach ($familles as $famille)
                                    <option value="{{ $famille->id }}">{{ $famille->libelle }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filtre Statut Stock --}}
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Statut Stock</label>
                            <div class="btn-group btn-group-sm w-100">
                                <input type="radio" class="btn-check" name="stockStatus" id="all" checked>
                                <label class="btn btn-outline-secondary" for="all">Tous</label>

                                <input type="radio" class="btn-check" name="stockStatus" id="normal">
                                <label class="btn btn-outline-secondary" for="normal">Normal</label>

                                <input type="radio" class="btn-check" name="stockStatus" id="alert">
                                <label class="btn btn-outline-secondary" for="alert">Alerte</label>

                                <input type="radio" class="btn-check" name="stockStatus" id="critique">
                                <label class="btn btn-outline-secondary" for="critique">Critique</label>
                            </div>
                        </div>

                        {{-- Bouton Reset --}}
                        <div class="col-md-2">
                            <label class="form-label d-none d-md-block small mb-1">&nbsp;</label>
                            <button class="btn btn-secondary btn-sm w-100" onclick="resetFilters()">
                                <i class="fas fa-redo me-1"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table de valorisation --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="valorisationTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Code Article</th>
                                <th>Désignation</th>
                                <th>Magasin</th>
                                <th class="text-end">Stock Actuel</th>
                                <th class="text-end">CUMP</th>
                                <th class="text-end">Valeur Stock</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($valorisations as $val)
                                <tr>
                                    <td class="text-nowrap">
                                        <span class="numero-document">{{ $val->article->code_article }}</span>
                                    </td>
                                    <td>{{ $val->article->designation }}</td>
                                    <td>{{ $val->depot->libelle_depot }}</td>
                                    <td class="text-end">
                                        {{ number_format($val->quantite, 2) }}
                                        <small class="text-muted">{{ $val->article->unite->libelle ?? 'Unité' }}</small>
                                    </td>
                                    <td class="text-end">{{ number_format($val->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-end fw-medium">{{ number_format($val->valeur_totale, 0, ',', ' ') }}
                                        FCFA</td>
                                    <td class="text-center">
                                        @php
                                            $statusClass = match ($val->article->getStockStatus()) {
                                                'critique' => 'bg-danger',
                                                'alerte' => 'bg-warning',
                                                'surplus' => 'bg-info',
                                                default => 'bg-success',
                                            };
                                            $statusText = match ($val->article->getStockStatus()) {
                                                'critique' => 'Critique',
                                                'alerte' => 'Alerte',
                                                'surplus' => 'Surplus',
                                                default => 'Normal',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-light-primary btn-icon"
                                                onclick="showArticleDetails({{ $val->article_id }}, {{ $val->depot_id }})"
                                                title="Voir historique" data-bs-toggle="tooltip">
                                                <i class="fas fa-history"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light-info btn-icon ms-1"
                                                onclick="printFicheStock({{ $val->article_id }}, {{ $val->depot_id }})"
                                                title="Imprimer fiche de stock">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="empty-state">
                                            <i class="fas fa-boxes fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Aucune donnée de valorisation disponible</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('pages.rapports.stocks.modal-history')

@endsection
@push('styles')
    <style>
        /* Les styles sont conservés des fichiers existants */
        .header-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            background: linear-gradient(145deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-primary-rgb), 0.05));
            position: relative;
        }

        .icon-wrapper {
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .header-icon:hover .icon-wrapper {
            transform: scale(1.15) rotate(15deg);
        }

        .icon-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0) 70%);
            border-radius: inherit;
            animation: pulse 2s infinite;
            z-index: 1;
        }

        /* Ajout des styles spécifiques pour la valorisation */
        .btn-group-sm .btn-outline-secondary {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-check:checked+.btn-outline-secondary {
            background-color: var(--bs-secondary);
            color: white;
        }

        .numero-document {
            font-family: 'Monaco', 'Consolas', monospace;
            color: #2c3e50;
            font-weight: 500;
            padding-left: 0.5rem;
            display: inline-block;
            border-left: 3px solid rgba(var(--bs-primary-rgb), 0.3);
        }

        /* Styles pour les boutons d'action */
        .btn-light-primary,
        .btn-light-info {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            border: none;
            transition: all 0.2s ease;
        }

        .btn-light-primary:hover,
        .btn-light-info:hover {
            transform: translateY(-1px);
        }

        .btn-light-primary {
            color: var(--bs-primary);
        }

        .btn-light-info {
            color: var(--bs-info);
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }
    </style>
@endpush
@push('scripts')
    <script>
        // Initialisation lors du chargement du document
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser les tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Initialiser les select2 si disponible
            if (typeof $.fn.select2 !== 'undefined') {
                $('#depotFilter').select2({
                    placeholder: 'Sélectionner un magasin',
                    allowClear: true,
                    width: '100%'
                });

                $('#familleFilter').select2({
                    placeholder: 'Sélectionner une famille',
                    allowClear: true,
                    width: '100%'
                });
            }

            // Initialiser DataTables si disponible
            if (typeof $.fn.DataTable !== 'undefined') {
                $('#valorisationTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                    },
                    pageLength: 25,
                    order: [
                        [5, 'desc']
                    ], // Trier par valeur stock par défaut
                    responsive: true,
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex"f>>rtip',
                    columnDefs: [{
                            targets: [3, 4, 5], // Colonnes numériques
                            className: 'text-end'
                        },
                        {
                            targets: [6], // Colonne statut
                            className: 'text-center'
                        },
                        {
                            targets: [7], // Colonne actions
                            orderable: false
                        }
                    ]
                });
            }
        });

        // Fonction de filtrage principale
        function filterValorisation() {
            const depot = document.getElementById('depotFilter').value;
            const famille = document.getElementById('familleFilter').value;
            const statut = document.querySelector('input[name="stockStatus"]:checked').id;

            // Afficher l'indicateur de chargement
            showLoading();

            // Construire l'URL avec les paramètres de filtrage
            const params = new URLSearchParams({
                depot_id: depot,
                famille_id: famille,
                stock_status: statut
            });

            // Faire la requête AJAX
            fetch(`${window.location.pathname}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    updateTableData(data);
                    updateStatistics(data.statistics);
                    hideLoading();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showError('Une erreur est survenue lors du filtrage des données');
                    hideLoading();
                });
        }

        // Mettre à jour les données du tableau
        function updateTableData(data) {
            const table = document.querySelector('#valorisationTable tbody');
            table.innerHTML = '';

            if (data.valorisations.length === 0) {
                table.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="empty-state">
                        <i class="fas fa-boxes fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Aucune donnée de valorisation disponible</p>
                    </div>
                </td>
            </tr>
        `;
                return;
            }

            data.valorisations.forEach(val => {
                const statusClass = getStatusClass(val.article.stock_status);
                const statusText = getStatusText(val.article.stock_status);

                const row = `
            <tr>
                <td class="text-nowrap">
                    <span class="numero-document">${val.article.code_article}</span>
                </td>
                <td>${val.article.designation}</td>
                <td>${val.depot.libelle_depot}</td>
                <td class="text-end">
                    ${formatNumber(val.quantite)}
                    <small class="text-muted">${val.article.unite?.libelle || 'Unité'}</small>
                </td>
                <td class="text-end">${formatCurrency(val.prix_unitaire)}</td>
                <td class="text-end fw-medium">${formatCurrency(val.valeur_totale)}</td>
                <td class="text-center">
                    <span class="badge ${statusClass}">${statusText}</span>
                </td>
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-light-primary btn-icon"
                                onclick="showArticleDetails(${val.article_id}, ${val.depot_id})"
                                title="Voir historique"
                                data-bs-toggle="tooltip">
                            <i class="fas fa-history"></i>
                        </button>
                        <button class="btn btn-sm btn-light-info btn-icon ms-1"
                                onclick="printFicheStock(${val.article_id}, ${val.depot_id})"
                                title="Imprimer fiche de stock">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
                table.innerHTML += row;
            });

            // Réinitialiser les tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        }

        // Mettre à jour les statistiques
        function updateStatistics(stats) {
            document.getElementById('totalValeur').textContent = formatCurrency(stats.total_valeur);
            document.getElementById('totalArticles').textContent = stats.total_articles;
            document.getElementById('stocksCritiques').textContent = stats.stocks_critiques;
            document.getElementById('depotsActifs').textContent = stats.depots_actifs;
        }

        // Fonction d'export
        function exportValorisation() {
            const depot = document.getElementById('depotFilter').value;
            const famille = document.getElementById('familleFilter').value;
            const statut = document.querySelector('input[name="stockStatus"]:checked').id;

            const params = new URLSearchParams({
                depot_id: depot,
                famille_id: famille,
                stock_status: statut
            });

            window.location.href = `/rapports/stock/valorisation/export?${params.toString()}`;
        }

        // Imprimer la fiche de stock
        function printFicheStock(articleId, depotId) {
            window.open(`/rapports/stock/valorisation/fiche-stock/${articleId}/${depotId}`, '_blank');
        }

        // Réinitialiser les filtres
        function resetFilters() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('#depotFilter').val(null).trigger('change');
                $('#familleFilter').val(null).trigger('change');
            } else {
                document.getElementById('depotFilter').value = '';
                document.getElementById('familleFilter').value = '';
            }

            document.getElementById('all').checked = true;
            filterValorisation();
        }

        // Fonctions utilitaires
        function showLoading() {
            // Ajouter un indicateur de chargement si nécessaire
            document.body.style.cursor = 'wait';
        }

        function hideLoading() {
            document.body.style.cursor = 'default';
        }

        function showError(message) {
            // Afficher une notification d'erreur (utiliser votre système de notification préféré)
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: message
                });
            } else {
                alert(message);
            }
        }

        function getStatusClass(status) {
            return {
                'critique': 'bg-danger',
                'alerte': 'bg-warning',
                'surplus': 'bg-info',
                'normal': 'bg-success'
            } [status] || 'bg-secondary';
        }

        function getStatusText(status) {
            return {
                'critique': 'Critique',
                'alerte': 'Alerte',
                'surplus': 'Surplus',
                'normal': 'Normal'
            } [status] || 'Inconnu';
        }

        function formatNumber(number) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        }

        function formatCurrency(number) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number) + ' FCFA';
        }


        function showArticleDetails(articleId, depotId) {
            if (!articleId || !depotId) {
                showError('Données invalides pour l\'historique');
                return;
            }

            fetch(`/rapports/stock/valorisation/article-history/${articleId}/${depotId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.json();
                })
                .then(response => {
                    if (!response.success) throw new Error(response.message || 'Erreur lors du chargement');

                    const data = response.data;
                    const tbody = document.getElementById('historyTableBody');

                    // Mettre à jour les informations d'en-tête
                    document.getElementById('historyArticleCode').textContent = data.article.code_article;
                    document.getElementById('historyArticleDesignation').textContent = data.article.designation;
                    document.getElementById('historyDepot').textContent = data.depot.libelle_depot;

                    // Vider le tableau
                    tbody.innerHTML = '';

                    // Vérifier si nous avons des mouvements
                    if (data.mouvements && data.mouvements.length > 0) {
                        data.mouvements.forEach(item => {
                            tbody.innerHTML += `
                    <tr class="${item.type === 'ENTREE' ? 'table-success' : 'table-danger'}">
                        <td>${item.date}</td>
                        <td>
                            <span class="badge bg-${item.type === 'ENTREE' ? 'success' : 'danger'}">
                                ${item.type}
                            </span>
                        </td>
                        <td class="text-end">${formatNumber(item.quantite)}</td>
                        <td class="text-end">${formatCurrency(item.prix_unitaire)}</td>
                        <td class="text-end fw-medium">${formatCurrency(item.valeur_totale)}</td>
                        <td>${item.reference || '-'}</td>
                    </tr>
                `;
                        });
                    } else {
                        tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-3">Aucun mouvement trouvé</td>
                </tr>
            `;
                    }

                    // Afficher le modal
                    const modal = new bootstrap.Modal(document.getElementById('articleHistoryModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showError('Impossible de charger l\'historique');
                });
        }

        // Fonctions utilitaires
        function formatNumber(number) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(number);
        }

        function formatCurrency(number) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number) + ' FCFA';
        }


        // Fonction de rafraîchissement
        function refreshPage() {
            const refreshBtn = document.querySelector('.btn-light-secondary');
            refreshBtn.classList.add('refreshing');
            refreshBtn.disabled = true;

            setTimeout(() => {
                window.location.reload();
            }, 500);
        }
    </script>
@endpush
