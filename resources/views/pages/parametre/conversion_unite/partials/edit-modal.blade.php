{{-- Modal d'ajout de conversion --}}
<div class="modal fade" id="editConversionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            {{-- Header du modal --}}
            <div class="modal-header bg-light border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exchange-alt fs-4 text-primary me-2"></i>
                    <h5 class="modal-title fw-bold">Nouvelle Conversion</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editConversionForm" method="POST" action="" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Unités de conversion --}}
                        <div class="col-12">
                            <div class="conversion-units-selector p-3 bg-light rounded">
                                <div class="row align-items-center text-center">
                                    {{-- Unité source --}}
                                    <div class="col">
                                        <label class="form-label fw-semibold required">Unité Source</label>
                                        <select class="form-select" name="unite_source_id" id="editUniteSourceMod" required>
                                            <option value="">Sélectionner...</option>
                                            @foreach($unitesMesure as $unite)
                                                <option value="{{ $unite->id }}">
                                                    {{ $unite->code_unite }} - {{ $unite->libelle_unite }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Veuillez sélectionner l'unité source
                                        </div>
                                    </div>

                                    {{-- Icône de conversion --}}
                                    <div class="col-auto">
                                        <div class="conversion-arrow">
                                            <i class="fas fa-arrow-right fa-lg text-primary"></i>
                                        </div>
                                    </div>

                                    {{-- Unité destination --}}
                                    <div class="col">
                                        <label class="form-label fw-semibold required">Unité Destination</label>
                                        <select class="form-select" name="unite_dest_id" id="editUniteDestMod" required>
                                            <option value="">Sélectionner...</option>
                                            @foreach($unitesMesure as $unite)
                                                <option value="{{ $unite->id }}">
                                                    {{ $unite->code_unite }} - {{ $unite->libelle_unite }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            Veuillez sélectionner l'unité de destination
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Coefficient de conversion --}}
                        <div class="col-12">
                            <div class="conversion-coefficient p-3 bg-light rounded">
                                <label class="form-label fw-semibold required mb-3">Coefficient de conversion</label>
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="coefficient-value">1</span>
                                        <span class="unite-source-label text-muted"></span>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-equals text-muted"></i>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="coefficient" id="coefficientMod"
                                                   step="0.0001" min="0.0001" required
                                                   placeholder="Entrez le coefficient">
                                            <span class="input-group-text unite-dest-label text-muted"></span>
                                        </div>
                                        <div class="invalid-feedback">
                                            Le coefficient doit être supérieur à 0
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Exemple : Si 1 KG = 1000 G, entrez 1000 comme coefficient
                                </small>
                            </div>
                        </div>

                        {{-- Section Articles --}}
                        <div class="col-12">
                            <div class="article-list-container p-3 bg-light rounded">
                                <label>Article concerné</label>
                                <ul id="articleList" class="list-group">
                                    <!-- Les articles seront injectés ici dynamiquement -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>

                <input type="hidden" name="conversion_type" value="articles">
                <input type="hidden" name="article_id" >
            </form>
        </div>
    </div>
</div>

<style>
.conversion-units-selector,
.conversion-coefficient {
    background-color: rgba(0, 0, 0, 0.02) !important;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.conversion-arrow {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

.coefficient-value {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--bs-primary);
}

.required:after {
    content: " *";
    color: var(--bs-danger);
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.conversion-arrow i {
    animation: pulse 2s infinite;
}

.unite-source-label,
.unite-dest-label {
    font-weight: 500;
}

.article-selector .card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.article-selector .famille-header {
    cursor: pointer;
    transition: all 0.2s ease;
}

.article-selector .article-item {
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.article-selector .article-item:hover {
    background-color: rgba(0,0,0,0.02);
}

.articles-container {
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.2) transparent;
}

.articles-container::-webkit-scrollbar {
    width: 6px;
}

.articles-container::-webkit-scrollbar-track {
    background: transparent;
}

.articles-container::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,0.2);
    border-radius: 20px;
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('articleSearch');
    const articleItems = document.querySelectorAll('.article-item');
    const familleGroups = document.querySelectorAll('.famille-group');
    const selectedCountElement = document.querySelector('.selected-count small');

    // Fonction pour mettre à jour le compteur d'articles sélectionnés
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.article-checkbox:checked').length;
        selectedCountElement.textContent = `${selectedCount} article(s) sélectionné(s)`;
    }

    // Fonction pour filtrer les articles
    function filterArticles(searchTerm) {
        searchTerm = searchTerm.toLowerCase().trim();

        familleGroups.forEach(familleGroup => {
            let hasVisibleArticles = false;
            const articles = familleGroup.querySelectorAll('.article-item');

            articles.forEach(article => {
                const searchData = article.getAttribute('data-search');
                const matches = searchData.includes(searchTerm);
                article.style.display = matches ? '' : 'none';
                if (matches) hasVisibleArticles = true;
            });

            // Afficher/cacher le groupe de famille en fonction des résultats
            familleGroup.style.display = hasVisibleArticles ? '' : 'none';
        });
    }

    // Événement de saisie dans la barre de recherche
    searchInput.addEventListener('input', function(e) {
        filterArticles(e.target.value);
    });

    // Événement de sélection "Tout sélectionner" par famille
    document.querySelectorAll('.select-all-famille').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const familleId = this.getAttribute('data-famille');
            const familleArticles = document.querySelectorAll(`.article-checkbox[data-famille="${familleId}"]`);

            familleArticles.forEach(articleCheckbox => {
                const articleItem = articleCheckbox.closest('.article-item');
                if (articleItem.style.display !== 'none') {
                    articleCheckbox.checked = this.checked;
                }
            });

            updateSelectedCount();
        });
    });

    // Événement de sélection d'articles individuels
    document.querySelectorAll('.article-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Réinitialiser la recherche lorsque le modal est fermé
    const modal = document.getElementById('addConversionModal');
    modal.addEventListener('hidden.bs.modal', function() {
        searchInput.value = '';
        filterArticles('');
        document.querySelectorAll('.article-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.select-all-famille').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    });

    // Initialisation du compteur
    updateSelectedCount();
});
</script>
