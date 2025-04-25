<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('depotForm');
        const modal = document.getElementById('addDepotModal');

        form.addEventListener('submit', async function(e) {
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
                // Gestion explicite des checkboxes
                formData.set('actif', document.getElementById('depotActifCheck').checked ? '1' :
                    '0');
                formData.set('depot_principal', document.getElementById('depotPrincipalCheck')
                    .checked ? '1' : '0');

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
                    // Fermer le modal en utilisant Bootstrap directement
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    } else {
                        // Alternative si l'instance n'est pas trouvée
                        $(modal).modal('hide');
                    }

                    // Afficher le message de succès
                    Toast.fire({
                        icon: 'success',
                        title: result.message
                    });

                    // Recharger la page après un délai
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Gérer les erreurs de validation
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
                    title: error.message ||
                        'Une erreur est survenue lors de la création du magasin'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
            }
        });

        // Gestion de l'ouverture du modal d'ajout
        modal.addEventListener('show.bs.modal', function() {
            form.reset();
            form.classList.remove('was-validated');
            const code = generateDepotCode();

            // Vérifier si le code existe déjà via AJAX
            fetch("{{ route('check.depot.code') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        code_depot: code
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        console.warn("Code déjà utilisé, génération d'un nouveau code...");
                        // Régénérer le code si déjà existant
                        generateAndValidateCode();
                    } else {
                        console.log("Code unique, utilisation en cours...");
                        form.querySelector('[name="code_depot"]').value = code;
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la vérification du code : ", error);
                });
        });
    });
    
    // Fonction pour régénérer et vérifier
    function generateAndValidateCode() {
        const newCode = generateDepotCode();
        fetch("{{ route('check.depot.code') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    code_depot: newCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    generateAndValidateCode(); // Répète jusqu'à avoir un code unique
                } else {
                    form.querySelector('[name="code_depot"]').value = newCode;
                }
            });
    }


    // Fonction pour générer un code de magasin
    function generateDepotCode() {
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let code = '';
        for (let i = 0; i < 6; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }

    // Fonction pour éditer un magasin
    async function editDepot(id) {
        try {
            // Afficher un indicateur de chargement
            Toast.fire({
                icon: 'info',
                title: 'Chargement...',
                timer: 1000,
                showConfirmButton: false
            });

            const response = await fetch(`${apiUrl}/parametres/depots/${id}/edit`, {
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

            if (result.success) {
                // Récupérer le modal et le formulaire
                const editModal = document.getElementById('editDepotModal');
                const editForm = document.getElementById('editDepotForm');

                // Mettre à jour l'action du formulaire
                editForm.action = `${apiUrl}/parametres/depots/${id}`;

                // Remplir les champs du formulaire
                editForm.querySelector('[name="code_depot"]').value = result.data.code_depot;
                editForm.querySelector('[name="libelle_depot"]').value = result.data.libelle_depot;
                editForm.querySelector('[name="adresse_depot"]').value = result.data.adresse_depot || '';
                editForm.querySelector('[name="tel_depot"]').value = result.data.tel_depot || '';
                editForm.querySelector('[name="type_depot_id"]').value = result.data.type_depot_id;
                editForm.querySelector('[name="depot_principal"]').checked = result.data.depot_principal;
                editForm.querySelector('[name="actif"]').checked = result.data.actif;
                editForm.querySelector('[name="point_de_vente_id"]').value = result.data.point_de_vente_id;

                // Ouvrir le modal
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

    // Gestion de la soumission du formulaire d'édition
    document.getElementById('editDepotForm')?.addEventListener('submit', async function(e) {
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

            // Gestion explicite des checkboxes
            formData.set('actif', this.querySelector('[name="actif"]').checked ? '1' : '0');
            formData.set('depot_principal', this.querySelector('[name="depot_principal"]').checked ? '1' :
                '0');

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
                const editModal = document.getElementById('editDepotModal');
                bootstrap.Modal.getInstance(editModal).hide();

                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Magasin mis à jour avec succès'
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
                title: error.message || 'Erreur lors de la mise à jour du magasin'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer les modifications';
        }
    });

    // Réinitialisation du formulaire lors de la fermeture du modal
    document.getElementById('editDepotModal')?.addEventListener('hidden.bs.modal', function() {
        const editForm = document.getElementById('editDepotForm');
        editForm.reset();
        editForm.classList.remove('was-validated');
    });

    // Fonction pour supprimer un magasin
    async function deleteDepot(id) {
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
                        const response = await fetch(`${apiUrl}/parametres/depots/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        if (!data.success) {
                            throw new Error(data.message || 'Erreur lors de la suppression');
                        }
                        return data;
                    } catch (error) {
                        Swal.showValidationMessage(
                            `Erreur: ${error.message}`
                        );
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed) {
                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.value.message || 'Magasin supprimé avec succès'
                });

                // Recharger la page après un court délai
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

    // Fonction pour changer le type de magasin
    function updateTypeDepotUI(selectElement) {
        const isPointVente = selectElement.value === 'POINT_VENTE';
        const depotPrincipalCheckbox = document.querySelector('[name="depot_principal"]');

        if (isPointVente) {
            depotPrincipalCheckbox.checked = false;
            depotPrincipalCheckbox.disabled = true;
        } else {
            depotPrincipalCheckbox.disabled = false;
        }
    }

    // Ajouter des écouteurs d'événements pour les sélecteurs de type de magasin
    document.querySelectorAll('select[name="type_depot_id"]').forEach(select => {
        select.addEventListener('change', function() {
            updateTypeDepotUI(this);
        });
    });

    async function toggleDepotStatus(id) {
        try {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Voulez-vous modifier le statut de ce point de vente ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF9B00',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, modifier',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch(`${apiUrl}/parametres/depots/${id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();
                        if (!data.success) {
                            throw new Error(data.message || 'Erreur lors de la modification');
                        }
                        return data;
                    } catch (error) {
                        Swal.showValidationMessage(
                            `Erreur: ${error.message}`
                        );
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed) {
                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.value.message || 'Statut modifié avec succès'
                });

                // Recharger la page après un court délai
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Une erreur est survenue lors de la modification du statut'
            });
        }
    }
</script>
