<div class="modal fade" id="importFamilleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-import fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Importer des Familles d'Articles</h5>
                        <p class="text-muted small mb-0">Format accepté : xlsx, xls</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('catalogue.famille-article.import') }}" method="POST" id="importFamilleForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <a href="{{ route('catalogue.famille-article.template.download') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-download me-2"></i>Télécharger le modèle
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium required">Fichier Excel</label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls" required>
                        <div class="form-text">
                            Assurez-vous que votre fichier respecte le format du modèle
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading fw-bold mb-2">
                            <i class="fas fa-info-circle me-2"></i>Instructions
                        </h6>
                        <ul class="mb-0 ps-3">
                            <li>Téléchargez et utilisez le modèle fourni</li>
                            <li>Remplissez les données sans modifier la structure</li>
                            <li>Les champs marqués d'un * sont obligatoires</li>
                            <li>Vérifiez vos données avant l'import</li>
                        </ul>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-file-import me-2"></i>Importer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
