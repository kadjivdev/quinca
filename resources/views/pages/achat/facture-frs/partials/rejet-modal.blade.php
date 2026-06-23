<div class="modal fade" id="rejetFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-clipboard-list fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Annulation de la facture</h5>
                        <p class="text-muted small mb-0">Choisissez les articles à rejeter</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="rejetFactureForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">

                        {{-- Section articles --}}
                        <div id="articlesSectionShow" class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i> Articles
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Référence</th>
                                                    <th>Désignation</th>
                                                    <th>Unité Base</th>
                                                    <th class="text-end">Unité</th>
                                                    <th class="text-end">Prix Unitaire</th>
                                                    <th class="text-end">Montant HT</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="facture_articles">
                                                <!-- Rempli dynamiquement -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section commentaire --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Motif d'annulation
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" id="motif_rejet" name="motif_rejet" rows="3" placeholder="Commentaire éventuel" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@push('scripts')
<script>
    $(document).ready(function() {
        // Soumission du formulaire
        $('#rejetFactureForm').on('submit', function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                rejetFacture($(this), this.action);
            }
            $(this).addClass('was-validated');
        });
    });

    // submission
    function rejetFacture($form, action) {
        const formData = $form.serialize();

        $.ajax({
            url: action,
            method: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Fermer le modal
                    $('#rejetFactureModal').modal('hide');

                    // Afficher le message de succès
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });

                    // Recharger la page après un court délai
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                console.log("erreure :", xhr.responseJSON?.message)
                Toast.fire({
                    icon: 'error',
                    title: xhr.responseJSON?.message || 'Erreur lors du rejet'
                });
            }
        });
    }

    // Fonction pour afficher les articles
    function loadArticles(lignes) {

        const tbody = $('#facture_articles');
        tbody.empty();

        lignes.forEach(ligne => {
            const checked = ligne.rejected ? 'checked' : '';
            tbody.append(`
                        <tr>
                            <td>${ligne.article?.code_article}</td>
                            <td>${ligne.article?.designation}</td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm text-end quantite"
                                    name="articles[${ligne?.article.id}][quantite_base]"
                                    value="${ligne?.quantite}"
                                    readonly>
                                    <span class="badge bg-light border text-dark">${ligne?.unite_mesure?.libelle_unite}</span>
                            </td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm text-end quantite"
                                    name="articles[${ligne.id}][quantite]"
                                    value="${ligne.quantite}"
                                    readonly>
                                    <span class="badge bg-light border text-dark">${ligne?.unite_mesure?.libelle_unite}</span>
                            </td>
                            <td class="text-end">
                                <input type="number" class="form-control form-control-sm text-end prix"
                                    name="articles[${ligne?.id}][prix_unitaire]"
                                    value="${ligne.prix_unitaire}"
                                    readonly>
                            </td>
                            <td class="text-end">
                                <span class="montant-ht">${(ligne.quantite * ligne.prix_unitaire).toFixed(2)}</span> FCFA
                                <input type="hidden" name="articles[${ligne.id}][montant_ht]"
                                    class="montant-ht-input"
                                    value="${(ligne.quantite * ligne.prix_unitaire).toFixed(2)}">
                                <input type="hidden" name="articles[${ligne.id}][unite_mesure_id]"
                                    value="${ligne.unite_mesure_id}">
                                <input type="hidden" name="articles[${ligne.id}][taux_tva]"
                                    value="0">
                                <input type="hidden" name="articles[${ligne.id}][taux_aib]"
                                    value="0">
                            </td>
                            <td class="text-end">
                                <input
                                    type="checkbox"
                                    name="articles[${ligne.id}][rejected]"
                                    ${checked}>
                            </td>
                        </tr>
                `);
        });

    }

    function initRejetFacture(facture) {

        Swal.fire({
            title: 'Confirmer le rejet',
            text: 'Êtes-vous sûr de vouloir rejeter cette facture ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, rejeter',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            console.log('Réponse SweetAlert:', result); // Log de débogage

            if (result.isConfirmed) {
                const rejetModal = document.getElementById('rejetFactureModal');
                const rejetForm = document.getElementById('rejetFactureForm');

                rejetForm.action = `/achat/factures/${facture?.id}/rejet`; // URL mise à jour

                // chargement des articles
                document.getElementById("motif_rejet").value = facture.motif_rejet
                
                console.log("Les lignes de la facture :", facture.lignes)
                loadArticles(facture.lignes)

                const modal = new bootstrap.Modal(rejetModal);
                modal.show();
                Swal.close();
            }
        });
    }
</script>
@endpush