{{-- Modal Historique Article --}}
<div class="modal fade" id="articleHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historique des Mouvements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="article-info mb-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-2">Article</h6>
                            <p class="mb-1">
                                <span class="fw-medium" id="historyArticleCode"></span>
                                <span class="text-muted" id="historyArticleDesignation"></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Magasin</h6>
                            <p class="mb-1" id="historyDepot"></p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="historyTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th class="text-end">Quantité</th>
                                <th class="text-end">P.U.</th>
                                <th class="text-end">Valeur</th>
                                <th>Référence</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <!-- Rempli dynamiquement -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="printHistory()">
                    <i class="fas fa-print me-1"></i>
                    Imprimer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showArticleDetails(articleId, depotId) {
        fetch(`/stock/valorisation/article-history/${articleId}/${depotId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const history = data.data;
                    const tbody = document.getElementById('historyTableBody');
                    tbody.innerHTML = '';

                    history.forEach(item => {
                        const row = `
                        <tr class="${item.type === 'ENTREE' ? 'table-success' : 'table-danger'}">
                            <td>${item.date}</td>
                            <td>
                                <span class="badge bg-${item.type === 'ENTREE' ? 'success' : 'danger'}">
                                    ${item.type}
                                </span>
                            </td>
                            <td class="text-end">
                                ${Number(item.quantite).toLocaleString('fr-FR', {minimumFractionDigits: 2})}
                            </td>
                            <td class="text-end">
                                ${Number(item.prix_unitaire).toLocaleString('fr-FR', {minimumFractionDigits: 2})} FCFA
                            </td>
                            <td class="text-end fw-medium">
                                ${Number(item.valeur_totale).toLocaleString('fr-FR', {minimumFractionDigits: 2})} FCFA
                            </td>
                            <td>${item.reference || '-'}</td>
                        </tr>
                    `;
                        tbody.innerHTML += row;
                    });

                    // Afficher le modal
                    const modal = new bootstrap.Modal(document.getElementById('articleHistoryModal'));
                    modal.show();
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                // Afficher une notification d'erreur
            });
    }

    function printHistory() {
        // Implémenter la logique d'impression
    }
</script>
