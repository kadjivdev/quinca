<div class="modal fade" id="rejetProgrammationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-clipboard-list fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Rejet de Précommande</h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour rejeter la
                            programmation</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="rejetProgrammationForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">

                        {{-- Section commentaire --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Motif du rejet
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="motif_rejet" rows="3" placeholder="Commentaire éventuel"></textarea>
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
            $('#rejetProgrammationForm').on('submit', function(e) {
                e.preventDefault();
                if (this.checkValidity()) {
                    rejetProgrammation($(this), this.action);
                }
                $(this).addClass('was-validated');
            });
        });

        function rejetProgrammation($form, action) {
            const formData = $form.serialize();

            $.ajax({
                url: action,
                method: 'PUT',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Fermer le modal
                        $('#rejetProgrammationModal').modal('hide');

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
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du rejet'
                    });
                }
            });
        }

        function initRejetProgramation(id) {

            Swal.fire({
                title: 'Confirmer le rejet',
                text: 'Êtes-vous sûr de vouloir rejeter cette programmation ? Cette action est irréversible.',
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
                    const rejetModal = document.getElementById('rejetProgrammationModal');
                    const rejetForm = document.getElementById('rejetProgrammationForm');

                    rejetForm.action = `/achat/programmations/${id}/rejet`; // URL mise à jour

                    const modal = new bootstrap.Modal(rejetModal);
                    modal.show();
                    Swal.close();
                }
            });
        }
    </script>
@endpush
