@extends('layouts.rapport.stock')

@section('title', 'Rapport des Articles en Alerte')
@section('content')

    <div class="dashboard-container">
        {{-- Header Section --}}
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">Articles en Alerte de Stock</h1>
                <div class="period-badge">
                    <span class="period-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                    {{ now()->format('d/m/Y') }}
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-refresh" onclick="refreshPage()">
                    <i class="fas fa-sync-alt"></i>
                    <span>Actualiser</span>
                </button>
                <button class="btn-export" onclick="exportAlerts()">
                    <i class="fas fa-file-export"></i>
                    <span>Exporter</span>
                </button>
            </div>
        </div>

        {{-- Analytics Cards --}}
        <div class="analytics-grid">
            <div class="analytics-card critical">
                <div class="card-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="card-content">
                    <h3>{{ $stats['articles_critiques'] ?? 0 }}</h3>
                    <p>Stock Critique</p>
                </div>
            </div>

            <div class="analytics-card warning">
                <div class="card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="card-content">
                    <h3>{{ $stats['articles_alerte'] ?? 0 }}</h3>
                    <p>Stock en Alerte</p>
                </div>
            </div>

            <div class="analytics-card value">
                <div class="card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="card-content">
                    <h3>{{ number_format($stats['valeur_stock_alerte'] ?? 0, 2) }}</h3>
                    <p>Valeur Stock en Alerte</p>
                </div>
            </div>

            <div class="analytics-card reorder">
                <div class="card-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="card-content">
                    <h3>{{ $stats['articles_a_commander'] ?? 0 }}</h3>
                    <p>Articles à Commander</p>
                </div>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="filters-section">
            <form id="filterForm" class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label">Magasin</label>
                    <select class="filter-select" name="depot_id" onchange="this.form.submit()">
                        <option value="">Tous les dépôts</option>
                        @foreach ($depots as $depot)
                            <option value="{{ $depot->id }}">{{ $depot->libelle_depot }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Niveau d'Alerte</label>
                    <select class="filter-select" name="niveau_alerte" onchange="this.form.submit()">
                        <option value="">Tous les niveaux</option>
                        <option value="critique">Stock critique</option>
                        <option value="alerte">Stock en alerte</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Famille Article</label>
                    <select class="filter-select" name="famille_id" onchange="this.form.submit()">
                        <option value="">Toutes les familles</option>
                        @foreach ($familles as $famille)
                            <option value="{{ $famille->id }}">{{ $famille->libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="button" class="btn-reset" onclick="resetFilters()">
                        <i class="fas fa-redo"></i>
                        <span>Réinitialiser</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Table Section --}}
        <div class="data-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Magasin</th>
                        <th class="text-right">Stock Actuel</th>
                        <th class="text-right">Stock Min</th>
                        <th class="text-right">Stock Sécu</th>
                        <th class="text-right">Stock Max</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $article)
                        <tr class="table-row">
                            <td class="article-cell">
                                <div class="article-code">{{ $article->code_article }}</div>
                                <div class="article-name">{{ $article->designation }}</div>
                            </td>
                            <td>{{ $article->depot->libelle_depot }}</td>
                            <td class="text-right">
                                <span
                                    class="stock-value {{ $article->isStockCritique() ? 'text-danger' : ($article->isStockAlert() ? 'text-warning' : '') }}">
                                    {{ number_format($article->stock_actuel, 2) }}
                                </span>
                            </td>
                            <td class="text-right">{{ number_format($article->stock_minimum, 2) }}</td>
                            <td class="text-right">{{ number_format($article->stock_securite, 2) }}</td>
                            <td class="text-right">{{ number_format($article->stock_maximum, 2) }}</td>
                            <td>
                                <div class="status-badges">
                                    @if ($article->isStockCritique())
                                        <span class="status-badge status-critical">Critique</span>
                                    @elseif($article->isStockAlert())
                                        <span class="status-badge status-warning">Alerte</span>
                                    @endif
                                    @if ($article->canBeOrdered())
                                        <span class="alert-badge reorder">À commander</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="showStockHistory({{ $article->id }})" class="btn-action history">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button onclick="createOrder({{ $article->id }})" class="btn-action order">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Aucun article en alerte de stock</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Historique --}}
    <div class="modal fade" id="stockHistoryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Historique des Mouvements</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Content will be loaded dynamically --}}
                </div>
            </div>
        </div>
    </div>

@endsection

<style>
    /* Réutilisation des styles précédents avec ajouts spécifiques pour les alertes */
    .analytics-card.critical .card-icon {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .analytics-card.warning .card-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .analytics-card.value .card-icon {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .analytics-card.reorder .card-icon {
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .stock-value {
        font-weight: 600;
    }

    .stock-value.text-danger {
        color: #dc2626;
    }

    .stock-value.text-warning {
        color: #f59e0b;
    }

    .status-badge.status-critical {
        background: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .status-badge.status-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .alert-badge.reorder {
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .btn-action.order {
        color: #4f46e5;
        background: rgba(79, 70, 229, 0.1);
    }

    .btn-action.order:hover {
        background: rgba(79, 70, 229, 0.2);
    }

    /* Les autres styles restent identiques au fichier précédent */
</style>

<script>
    function showStockHistory(articleId) {
        // Implementation for showing stock history
    }

    function createOrder(articleId) {
        // Implementation for creating order
    }

    function exportAlerts() {
        // Implementation for exporting alerts
    }

    // Les autres fonctions JavaScript restent identiques
</script>
