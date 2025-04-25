<div id="articlesBlock">
    @forelse($articles as $article)
    <div class="col-md-4 col-lg-3 article-item">
        <div class="card article-card h-100">
            <div class="badge stock-badge {{ $article->getStockStatus() }}">
                {{ ucfirst($article->getStockStatus()) }}
            </div>

            <div class="card-body">
                <h5 class="card-title mb-1">{{ $article->designation }}</h5>
                <p class="card-text text-muted small mb-2">{{ $article->code_article }}</p>

                <div class="mb-3">
                    <span class="badge bg-info">{{ $article->famille->nom }}</span>
                    @if(!$article->stockable)
                    <span class="badge bg-secondary">Non stockable</span>
                    @endif
                </div>

                @if($article->stockable)
                <div class="progress mb-2" style="height: 5px;">
                    <div class="progress-bar" role="progressbar"
                        style="width: {{ $article->stock_maximum ? ($article->stock_actuel / $article->stock_maximum) * 100 : 0 }}%"></div>
                </div>
                <p class="card-text small mb-0">
                    Stock: {{ $article->stock_actuel }} / {{ $article->stock_maximum }}
                </p>
                @endif
            </div>

            <div class="card-footer bg-transparent border-top-0">
                <div class="btn-group w-100">
                    <button type="button" class="btn btn-outline-primary btn-sm"
                        onclick="editArticle({{ $article->id }})">
                        <i class="bi bi-pencil me-1"></i>Modifier
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm"
                        onclick="updateStock({{ $article->id }})">
                        <i class="bi bi-box me-1"></i>Stock
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>Aucun article trouvé
        </div>
    </div>
    @endforelse
</div>

@if($articles->hasPages())
<div class="card-footer border-0 py-3">
    {{ $articles->links() }}
</div>
@endif

@push('scripts')
<script>
    var searchResultInitial = <?php echo json_encode($articles); ?>;
    var articlesInitiales = searchResultInitial.data

    const filtrage = (data) => {
        let newContent;
        data.forEach(article => {
            newContent += `
            <div class="col-md-4 col-lg-3 article-item">
                <div class="card article-card h-100">
                    <div class="badge stock-badge 0">
                        0
                    </div>

                    <div class="card-body">
                        <h5 class="card-title mb-1"> ${article.designation} </h5>
                        <p class="card-text text-muted small mb-2">${article.code_article}</p>

                        <div class="mb-3">
                            <span class="badge bg-info">${article.famille.nom}</span>

                            ${!article.stockable && "<span class='badge bg-secondary'>Non stockable</span>"}
                        </div>

                        ${ article.stockable &&      
                                `<div class="progress mb-2" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: ${ article.stock_maximum ? (article.stock_actuel / article.stock_maximum) * 100 : 0 }%"></div>
                                        </div>
                                        <p class="card-text small mb-0">
                                            Stock:  ${ article.stock_actuel } / ${ article.stock_maximum }
                                        </p>`
                            
                        }
                    </div>

                    <div class="card-footer bg-transparent border-top-0">
                        <div class="btn-group w-100">
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="editArticle(${article.id })">
                                <i class="bi bi-pencil me-1"></i>Modifier
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="updateStock(${article.id })">
                                <i class="bi bi-box me-1"></i>Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            `
        });

        $("#articlesBlock").append(newContent)
    }

    const HandleSearch = (text) => {
        $("#articlesBlock").empty()
        if (text.trim()) {
            const results = articlesInitiales.filter((item) => Object.values(item.designation).join('').toLocaleLowerCase().includes(text.toLocaleLowerCase()))
            filtrage(results)
        } else {
            filtrage(articlesInitiales)
        }
    }

    $('#searchArticle').on("change", function(e) {
        console.log("...testing")
        HandleSearch(e.target.value)
    })
</script>
@endpush