<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('addVehiculeForm');
        const modal = document.getElementById('addVehiculeModal');

        // Gestion de la soumission du formulaire de création
        form?.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

            try {
                const formData = new FormData(this);

                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }

                    Toast.fire({
                        icon: 'success',
                        title: result.message
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (result.errors) {
                        let errorMessage = '';
                        Object.values(result.errors).forEach(errors => {
                            errorMessage += errors.join('\n') + '\n';
                        });
                        throw new Error(errorMessage || 'Erreur de validation');
                    } else {
                        throw new Error(result.message || 'Une erreur est survenue');
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                Toast.fire({
                    icon: 'error',
                    title: error.message || 'Une erreur est survenue lors de la création'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
            }
        });
    });

    // Fonction d'édition
    async function editVehicule(id) {
        try {
            Toast.fire({
                icon: 'info',
                title: 'Chargement...',
                timer: 1000,
                showConfirmButton: false
            });

            const response = await fetch(`vehicules/${id}/edit`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des données');
            }

            const result = await response.json();

            console.log(result)

            if (result.success) {
                const editModal = document.getElementById('editVehiculeModal');
                const editForm = document.getElementById('editVehiculeForm');

                // Mettre à jour l'action du formulaire
                editForm.action = `vehicules/${id}`; // Changement ici

                editForm.querySelector('[name="matricule"]').value = result.data.matricule;
                editForm.querySelector('[name="actif"]').checked = result.data.statut;

                const modal = new bootstrap.Modal(editModal);
                modal.show();
            } else {
                throw new Error(result.message || 'Erreur lors du chargement des données');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des données',
                timer: 3000
            });
        }
    }

    // Dans votre scripts.blade.php
    document.getElementById('editVehiculeForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mise à jour...';

        try {
            const formData = new FormData(this);
            formData.append('_method', 'PUT');

            // Correction ici pour la gestion du switch
            const switchElement = document.getElementById('statusSwitchMod');
            formData.set('actif', switchElement.checked ? '1' : '0');

            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Fermer le modal
                const editModal = document.getElementById('editVehiculeModal');
                bootstrap.Modal.getInstance(editModal).hide();

                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Vehicle mis à jour avec succès'
                });

                // Recharger la page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(result.message || 'Erreur lors de la mise à jour');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors de la mise à jour de la caisse'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer les modifications';
        }
    });

    // Fonction de suppression
    async function deleteVehicule(id) {
        try {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette suppression ne pourra pas être annulée !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch(`vehicules/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        if (!data.success) {
                            throw new Error(data.message || 'Erreur lors de la suppression');
                        }
                        return data;
                    } catch (error) {
                        Swal.showValidationMessage(`Erreur: ${error.message}`);
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed) {
                Toast.fire({
                    icon: 'success',
                    title: result.value.message || 'Vehicule supprimé avec succès'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Une erreur est survenue lors de la suppression'
            });
        }
    }

    // Import de fournisseurs
    async function importFournisseurs(formElement) {
        try {
            const formData = new FormData(formElement);
            const response = await fetch('/fournisseurs/import', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                Toast.fire({
                    icon: 'success',
                    title: result.message
                });

                if (result.errors && result.errors.length) {
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Rapport d\'import',
                            html: `
                                <div class="text-start">
                                    <p>Total traité: ${result.details.total}</p>
                                    <p>Importés: ${result.details.imported}</p>
                                    <p>Ignorés: ${result.details.skipped}</p>
                                    ${result.errors.length ? '<p>Erreurs:</p><ul>' + result.errors.map(err => '<li>' + err + '</li>').join('') + '</ul>' : ''}
                                </div>
                            `,
                            icon: 'info'
                        });
                    }, 1500);
                }

                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            } else {
                throw new Error(result.message || 'Erreur lors de l\'import');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Une erreur est survenue lors de l\'import'
            });
        }
    }

    // Fonction utilitaire pour générer un code
    function generateFournisseurCode() {
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let code = '';
        for (let i = 0; i < 6; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }
</script>
