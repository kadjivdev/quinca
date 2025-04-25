<!-- Modal pour les détails de la facture -->
<div class="modal fade" id="showFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0">
            <!-- Header du modal -->
            <div class="modal-header position-relative py-3 px-4 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="feature-icon-small d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fs-4 rounded-3 p-2 me-3">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-semibold mb-0">Facture <span id="numeroFacture" class="badge bg-primary ms-2"></span></h5>
                        <p class="text-muted small mb-0">Émise le <span id="dateFacture" class="fw-medium"></span></p>
                    </div>
                </div>
                <button type="button" class="btn-close position-absolute top-50 end-0 translate-middle-y me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Corps du modal -->
            <div class="modal-body p-4">
                <!-- Loader -->
                <div id="factureLoader" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="text-muted mt-3">Chargement des détails de la facture...</p>
                </div>

                <!-- Contenu de la facture -->
                <div id="factureDetails"></div>
            </div>

            <!-- Footer du modal -->
            <div class="modal-footer justify-content-between bg-light py-3 px-4 border-top">
                <div>
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Fermer
                    </button>
                </div>
                {{-- <div class="btn-group">
                    <a href="{{ route('vente.facture.print', $facture) }}" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-print"></i> Imprimer
                    </a>
                    <button type="button" class="btn btn-white" onclick="printFacture()">
                        <i class="fas fa-print me-2"></i>Imprimer
                    </button>
                    <button type="button" class="btn btn-primary" onclick="downloadFacture()">
                        <i class="fas fa-download me-2"></i>Télécharger
                    </button>
                </div> --}}
            </div>
        </div>
    </div>
</div>
@include('pages.ventes.facture.partials.js-show-modal')

<style>
/* Styles pour le modal */
.modal-content {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 1rem;
}

.modal-header {
    background: linear-gradient(to right, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.01));
}

.feature-icon-small {
    width: 48px;
    height: 48px;
    transition: transform 0.2s;
}

.feature-icon-small:hover {
    transform: scale(1.1);
}

/* Styles pour les cartes d'information */
.info-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 0.75rem;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
}

/* Styles pour les badges et étiquettes */
.badge {
    padding: 0.5em 0.75em;
    font-weight: 500;
}

.form-label {
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    color: var(--bs-gray-600);
}

/* Styles pour les tableaux */
.table-facture {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

.table-facture th {
    background-color: var(--bs-gray-100);
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-facture th,
.table-facture td {
    padding: 1rem;
    border: 1px solid var(--bs-gray-200);
}

/* Styles pour les totaux */
.total-section {
    background-color: var(--bs-gray-100);
    border-radius: 0.5rem;
    padding: 1rem;
}

.total-line {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px dashed var(--bs-gray-300);
}

.total-line:last-child {
    border-bottom: none;
}

/* Styles pour les boutons */
.btn-white {
    background-color: #fff;
    border: 1px solid var(--bs-gray-300);
}

.btn-white:hover {
    background-color: var(--bs-gray-100);
}

/* Animation du loader */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: slideIn 0.3s ease forwards;
}

/* Styles pour la version imprimable */
@media print {
    .modal {
        position: absolute;
        left: 0;
        top: 0;
        margin: 0;
        padding: 0;
        overflow: visible!important;
    }
}
</style>
