@extends('layouts.rapport.stock')

@section('title', 'Tableau de bord des ventes')
@section('content')

    {{-- Modern Dashboard Header --}}
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">Analyse de la Rotation des Stocks</h1>
                <div class="period-badge">
                    <span class="period-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    {{ $dateDebut->format('d/m/Y') }} - {{ $dateFin->format('d/m/Y') }}
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-refresh" onclick="refreshPage()">
                    <i class="fas fa-sync-alt"></i>
                    <span>Actualiser</span>
                </button>
                <button class="btn-export" onclick="exportRotation()">
                    <i class="fas fa-file-export"></i>
                    <span>Exporter</span>
                </button>
            </div>
        </div>

        {{-- Analytics Cards --}}
        <div class="analytics-grid">
            <div class="analytics-card high-rotation">
                <div class="card-icon">
                    <i class="fas fa-forward"></i>
                </div>
                <div class="card-content">
                    <h3>{{ $stats['articles_rotation_forte'] }}</h3>
                    <p>Rotation Forte</p>
                </div>
                <div class="card-trend positive">
                    <div class="trend-indicator"></div>
                </div>
            </div>

            <div class="analytics-card low-rotation">
                <div class="card-icon">
                    <i class="fas fa-backward"></i>
                </div>
                <div class="card-content">
                    <h3>{{ $stats['articles_rotation_faible'] }}</h3>
                    <p>Rotation Faible</p>
                </div>
                <div class="card-trend warning">
                    <div class="trend-indicator"></div>
                </div>
            </div>

            <div class="analytics-card dormant">
                <div class="card-icon">
                    <i class="fas fa-pause"></i>
                </div>
                <div class="card-content">
                    <h3>{{ $stats['articles_dormants'] }}</h3>
                    <p>Articles Dormants</p>
                </div>
                <div class="card-trend negative">
                    <div class="trend-indicator"></div>
                </div>
            </div>

            <div class="analytics-card coverage">
                <div class="card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-content">
                    <h3>{{ number_format($stats['couverture_moyenne'], 0) }}</h3>
                    <p>Jours de Couverture</p>
                </div>
                <div class="card-trend neutral">
                    <div class="trend-indicator"></div>
                </div>
            </div>
        </div>

        {{-- Modern Filter Section --}}
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

                <div class="filter-group date-range">
                    <label class="filter-label">Période d'analyse</label>
                    <div class="date-inputs">
                        <input type="date" class="filter-date" name="date_debut"
                            value="{{ $dateDebut->format('Y-m-d') }}">
                        <span class="date-separator">à</span>
                        <input type="date" class="filter-date" name="date_fin" value="{{ $dateFin->format('Y-m-d') }}">
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Type Rotation</label>
                    <select class="filter-select" name="type_rotation" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        <option value="forte">Forte rotation</option>
                        <option value="normale">Rotation normale</option>
                        <option value="faible">Faible rotation</option>
                        <option value="dormant">Articles dormants</option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                        <span>Rechercher</span>
                    </button>
                    <button type="button" class="btn-reset" onclick="resetFilters()">
                        <i class="fas fa-redo"></i>
                        <span>Réinitialiser</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Modern Data Table --}}
        <div class="data-table-container">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Magasin</th>
                        <th class="text-right">Stock Actuel</th>
                        <th class="text-right">Stock Moyen</th>
                        <th class="text-right">Sorties</th>
                        <th class="text-right">Taux Rotation</th>
                        <th class="text-right">Couverture</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rotations as $rotation)
                        <tr class="table-row">
                            <td class="article-cell">
                                <div class="article-code">{{ $rotation['article']->code_article }}</div>
                                <div class="article-name">{{ $rotation['article']->designation }}</div>
                            </td>
                            <td>{{ $rotation['depot']->libelle_depot }}</td>
                            <td class="text-right">{{ number_format($rotation['stock_actuel'], 2) }}</td>
                            <td class="text-right">{{ number_format($rotation['stock_moyen'], 2) }}</td>
                            <td class="text-right">{{ number_format($rotation['total_sorties'], 2) }}</td>
                            <td class="text-right">
                                <span
                                    class="rotation-rate {{ $rotation['taux_rotation'] >= 3 ? 'high' : ($rotation['taux_rotation'] < 1 ? 'low' : 'medium') }}">
                                    {{ number_format($rotation['taux_rotation'], 2) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="coverage-value">
                                    {{ number_format($rotation['couverture_stock'], 0) }}
                                    <span class="unit">jours</span>
                                </div>
                            </td>
                            <td>
                                <div class="status-badges">
                                    @php
                                        $statusClass = match ($rotation['statut_rotation']) {
                                            'forte' => 'status-high',
                                            'normale' => 'status-normal',
                                            'faible' => 'status-low',
                                            default => 'status-dormant',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ ucfirst($rotation['statut_rotation']) }}
                                    </span>
                                    @if ($rotation['alerte'] !== 'normal')
                                        <span
                                            class="alert-badge {{ $rotation['alerte'] === 'critique' ? 'critical' : 'warning' }}">
                                            {{ ucfirst($rotation['alerte']) }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        onclick="showRotationDetails({{ $rotation['article']->id }}, {{ $rotation['depot']->id }})"
                                        class="btn-action details">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <button
                                        onclick="showMouvements({{ $rotation['article']->id }}, {{ $rotation['depot']->id }})"
                                        class="btn-action movements">
                                        <i class="fas fa-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-chart-line"></i>
                                    <p>Aucune donnée de rotation disponible</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modern Modal --}}
    <div class="modal fade" id="rotationDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de Rotation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="details-header">
                        <div class="details-info">
                            <div class="info-group">
                                <h6>Article</h6>
                                <div id="detailsArticleCode" class="info-primary"></div>
                                <div id="detailsArticleDesignation" class="info-secondary"></div>
                            </div>
                            <div class="info-group">
                                <h6>Magasin</h6>
                                <div id="detailsDepot" class="info-primary"></div>
                            </div>
                        </div>
                    </div>

                    <div class="details-content">
                        <div class="chart-container">
                            <div id="evolutionChart"></div>
                        </div>

                        <div class="metrics-grid">
                            <div class="metric-card">
                                <div class="metric-icon rotation">
                                    <i class="fas fa-sync"></i>
                                </div>
                                <div class="metric-content">
                                    <h6>Taux de Rotation</h6>
                                    <div id="detailsTauxRotation" class="metric-value">0</div>
                                    <p class="metric-description">
                                        Renouvellement du stock
                                    </p>
                                </div>
                            </div>

                            <div class="metric-card">
                                <div class="metric-icon duration">
                                    <i class="fas fa-hourglass"></i>
                                </div>
                                <div class="metric-content">
                                    <h6>Durée de Stockage</h6>
                                    <div id="detailsDureeStockage" class="metric-value">0</div>
                                    <p class="metric-description">
                                        Temps moyen en stock
                                    </p>
                                </div>
                            </div>

                            <div class="metric-card">
                                <div class="metric-icon coverage">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="metric-content">
                                    <h6>Couverture Stock</h6>
                                    <div id="detailsCouverture" class="metric-value">0</div>
                                    <p class="metric-description">
                                        Jours de consommation
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    /* Modern Dashboard Styles */
    .dashboard-container {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 1rem;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .dashboard-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a1f36;
        margin: 0;
    }

    .period-badge {
        display: inline-flex;
        align-items: center;
        background: #e9ecef;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        color: #495057;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .period-icon {
        margin-right: 0.5rem;
        color: #4c6ef5;
    }

    .header-actions {
        display: flex;
        gap: 1rem;
    }

    /* Analytics Cards */
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .analytics-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
    }

    .analytics-card:hover {
        transform: translateY(-2px);
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.25rem;
    }

    .high-rotation .card-icon {
        background: rgba(76, 175, 80, 0.1);
        color: #4caf50;
    }

    .low-rotation .card-icon {
        background: rgba(255, 152, 0, 0.1);
        color: #ff9800;
    }

    .dormant .card-icon {
        background: rgba(244, 67, 54, 0.1);
        color: #f44336;
    }

    .coverage .card-icon {
        background: rgba(33, 150, 243, 0.1);
        color: #2196f3;
    }

    /* Suite des styles pour les cartes analytiques */
    .card-content h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: #1a1f36;
    }

    .card-content p {
        margin: 0.25rem 0 0;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .card-trend {
        margin-left: auto;
        padding-left: 1rem;
    }

    .trend-indicator {
        width: 60px;
        height: 24px;
        position: relative;
        overflow: hidden;
    }

    /* Filter Section Styles */
    .filters-section {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
    }

    .filter-select,
    .filter-date {
        height: 2.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0 1rem;
        font-size: 0.875rem;
        color: #1f2937;
        background-color: #fff;
        transition: border-color 0.15s ease;
    }

    .filter-select:hover,
    .filter-date:hover {
        border-color: #d1d5db;
    }

    .filter-select:focus,
    .filter-date:focus {
        border-color: #4f46e5;
        outline: none;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
    }

    .date-range {
        grid-column: span 2;
    }

    .date-inputs {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .date-separator {
        color: #6b7280;
        font-size: 0.875rem;
    }

    .filter-actions {
        display: flex;
        gap: 1rem;
    }

    /* Buttons Styles */
    .btn-search,
    .btn-reset,
    .btn-refresh,
    .btn-export {
        height: 2.5rem;
        padding: 0 1.25rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-search {
        background: #4f46e5;
        color: white;
    }

    .btn-search:hover {
        background: #4338ca;
    }

    .btn-reset {
        background: #f3f4f6;
        color: #4b5563;
    }

    .btn-reset:hover {
        background: #e5e7eb;
    }

    .btn-refresh,
    .btn-export {
        background: white;
        border: 1px solid #e5e7eb;
    }

    .btn-refresh:hover,
    .btn-export:hover {
        background: #f9fafb;
    }

    /* Table Styles */
    .data-table-container {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead {
        background: #f9fafb;
    }

    .modern-table th {
        padding: 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .modern-table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    .table-row:hover {
        background: #f9fafb;
    }

    .article-cell {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .article-code {
        font-weight: 600;
        color: #1f2937;
    }

    .article-name {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* Status Badges */
    .status-badges {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .status-badge,
    .alert-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-high {
        background: rgba(76, 175, 80, 0.1);
        color: #2e7d32;
    }

    .status-normal {
        background: rgba(33, 150, 243, 0.1);
        color: #1976d2;
    }

    .status-low {
        background: rgba(255, 152, 0, 0.1);
        color: #f57c00;
    }

    .status-dormant {
        background: rgba(244, 67, 54, 0.1);
        color: #d32f2f;
    }

    .alert-badge.critical {
        background: rgba(244, 67, 54, 0.1);
        color: #d32f2f;
    }

    .alert-badge.warning {
        background: rgba(255, 152, 0, 0.1);
        color: #f57c00;
    }

    /* Modal Styles */
    .modern-modal {
        border-radius: 1rem;
        overflow: hidden;
    }

    .modal-header {
        background: #f9fafb;
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }

    .details-header {
        background: #f9fafb;
        padding: 1.5rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .details-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .info-group h6 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .info-primary {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
    }

    .info-secondary {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    /* Metrics Grid */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .metric-card {
        background: #f9fafb;
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .metric-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .metric-icon.rotation {
        background: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .metric-icon.duration {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .metric-icon.coverage {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .metric-content h6 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #4b5563;
        margin: 0 0 0.5rem;
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .metric-description {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    /* Empty State */
    .empty-state {
        padding: 3rem;
        text-align: center;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .analytics-grid {
            grid-template-columns: 1fr;
        }

        .filters-grid {
            grid-template-columns: 1fr;
        }

        .date-range {
            grid-column: auto;
        }

        .metrics-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
