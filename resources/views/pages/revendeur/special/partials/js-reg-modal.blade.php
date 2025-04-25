<script>
    document.addEventListener('DOMContentLoaded', function() {
        const montantInput = document.getElementById('montantReglement');
        if (!montantInput) return;

        montantInput.addEventListener('input', function() {
            const montantSaisi = parseFloat(this.value) || 0;
            const montantRestant = parseFloat(document.getElementById('montantRestant').getAttribute(
                'data-montant')) || 0;
            const nouveauSolde = montantRestant - montantSaisi;

            const nouveauSoldeElement = document.getElementById('nouveauSolde');
            const soldeContainer = document.getElementById('soldeContainer');

            // Mettre à jour le montant
            nouveauSoldeElement.textContent = formatMontant(Math.abs(nouveauSolde));

            // Mettre à jour le style et le message
            if (nouveauSolde < 0) {
                nouveauSoldeElement.className = 'mb-0 mt-2 text-success';
                updateSoldeMessage('Montant à rembourser au client');
            } else if (nouveauSolde > 0) {
                nouveauSoldeElement.className = 'mb-0 mt-2 text-danger';
                updateSoldeMessage('Reste à payer');
            } else {
                nouveauSoldeElement.className = 'mb-0 mt-2 text-primary';
                updateSoldeMessage('Facture soldée');
            }
        });
    });

    // Fonction pour mettre à jour le message du solde
    function updateSoldeMessage(message) {
        const messageElement = document.getElementById('soldeMessage');
        if (messageElement) {
            messageElement.textContent = message;
        } else {
            const nouveauSoldeElement = document.getElementById('nouveauSolde');
            const messageDiv = document.createElement('div');
            messageDiv.id = 'soldeMessage';
            messageDiv.className = 'small text-muted mt-1';
            messageDiv.textContent = message;

            // Remplacer l'ancien message s'il existe
            const existingMessage = nouveauSoldeElement.nextElementSibling;
            if (existingMessage && existingMessage.classList.contains('small')) {
                existingMessage.remove();
            }

            nouveauSoldeElement.parentNode.insertBefore(messageDiv, nouveauSoldeElement.nextSibling);
        }
    }

    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XOF',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(montant).replace('XOF', 'FCFA');
    }

    function addPayment(factureId) {
        initReglementModal();

        Swal.fire({
            title: 'Chargement...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`${apiUrl}/ventes-speciales/factures/${factureId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(response => {
                if (response.status === 'error') {
                    throw new Error(response.message);
                }

                Swal.close();

                const data = response.data;
                const facture = data.facture;

                // Remplir les informations de base
                document.getElementById('factureClientId').value = factureId;
                document.getElementById('numeroFacture').textContent = facture.numero;
                document.getElementById('clientName').textContent = facture.client.raison_sociale;

                // Remplir les montants
                document.getElementById('montantTTC').textContent = data.montantTTC + ' FCFA';
                document.getElementById('montantPaye').textContent = data.montantRegle + ' FCFA';
                document.getElementById('montantRestant').textContent = data.montantRestant + ' FCFA';

                // Stocker le montant restant pour les calculs
                const montantRestant = parseFloat(data.montantRestant.replace(/\s/g, '').replace(',', '.'));
                document.getElementById('montantRestant').setAttribute('data-montant', montantRestant);

                // Initialiser le nouveau solde
                document.getElementById('nouveauSolde').textContent = data.montantRestant + ' FCFA';

                // Configurer l'input du montant
                const inputMontant = document.querySelector('input[name="montant"]');
                inputMontant.max = montantRestant;
                inputMontant.placeholder = `Maximum ${data.montantRestant} FCFA`;

                // Afficher le modal
                const reglementModal = new bootstrap.Modal(document.getElementById('addReglementModal'));
                reglementModal.show();
            })
            .catch(error => {
                console.error('Erreur:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.message || 'Impossible de charger les détails de la facture',
                    confirmButtonText: 'Fermer'
                });
            });
    }

    // Fonction pour initialiser le modal
    function initReglementModal() {
        const form = document.getElementById('addReglementForm');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        // Cacher tous les champs conditionnels
        ['banqueField', 'referenceField', 'dateEcheanceField'].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.style.display = 'none';
                const input = field.querySelector('input');
                if (input) {
                    input.required = false;
                    input.value = '';
                }
            }
        });
    }

    // Validation du formulaire
    document.getElementById('addReglementForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const formData = new FormData(this);

        Swal.fire({
            title: 'Enregistrement...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`${apiUrl}/ventes-speciales/reglement/store`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fermer le modal
                    bootstrap.Modal.getInstance(document.getElementById('addReglementModal')).hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Le règlement a été enregistré avec succès',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Rafraîchir la page
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Une erreur est survenue');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.message || 'Une erreur est survenue lors de l\'enregistrement',
                    confirmButtonText: 'Fermer'
                });
            });
    });
</script>
