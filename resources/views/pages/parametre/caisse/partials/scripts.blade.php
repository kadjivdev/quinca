<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeCaisseForm();
    });

    function initializeCaisseForm() {
        const form = document.getElementById('caisseForm');
        const modal = document.getElementById('addCaisseModal');

        if (!form || !modal) return;

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
                // Correction pour la gestion du switch
                const switchElement = document.getElementById('statusSwitch');
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
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }

                    // Message de succès
                    Toast.fire({
                        icon: 'success',
                        title: result.message || 'Caisse créée avec succès'
                    });

                    // Recharger la page après un délai
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(result.message || 'Une erreur est survenue');
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

        // Gestion de l'ouverture du modal
        modal.addEventListener('show.bs.modal', function() {
            form.reset();
            form.classList.remove('was-validated');
            const code = generateCaisseCode();

            // Vérifier si le code existe déjà via AJAX
            fetch("{{ route('check.caisse.code') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code_caisse: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    console.warn("Code déjà utilisé, génération d'un nouveau code...");
                    // Régénérer le code si déjà existant
                    generateAndValidateCode();
                } else {
                    console.log("Code unique, utilisation en cours...");
                    form.querySelector('[name="code_caisse"]').value = code;
                }
            })
            .catch(error => {
                console.error("Erreur lors de la vérification du code : ", error);
            });
            // Réinitialiser le switch à actif par défaut
            document.getElementById('statusSwitch').checked = true;
        });
    }

    // Fonction pour régénérer et vérifier
    function generateAndValidateCode() {
        const newCode = generateCaisseCode();
        fetch("{{ route('check.caisse.code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ code_caisse: newCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                generateAndValidateCode(); // Répète jusqu'à avoir un code unique
            } else {
                form.querySelector('[name="code_caisse"]').value = newCode;
            }
        });
    }

    // Fonction de génération de code caisse
    function generateCaisseCode() {
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }

    async function editCaisse(id) {
        try {
            // Afficher un indicateur de chargement
            Toast.fire({
                icon: 'info',
                title: 'Chargement...',
                timer: 1000,
                showConfirmButton: false
            });

            const response = await fetch(`${apiUrl}/parametres/caisses/${id}/edit`, { // Changement ici
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
                const editModal = document.getElementById('editCaisseModal');
                const editForm = document.getElementById('editCaisseForm');

                // Mettre à jour l'action du formulaire
                editForm.action = `${apiUrl}/parametres/caisses/${id}`; // Changement ici

                // Remplir les champs du formulaire
                editForm.querySelector('[name="code_caisse"]').value = result.data.code_caisse;
                editForm.querySelector('[name="libelle"]').value = result.data.libelle;
                editForm.querySelector('[name="point_de_vente_id"]').value = result.data.point_vente_id;
                editForm.querySelector('[name="actif"]').checked = result.data.actif;

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



    // Dans votre scripts.blade.php
    document.getElementById('editCaisseForm')?.addEventListener('submit', async function(e) {
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
            const switchElement = document.getElementById('editStatusSwitch');
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
                const editModal = document.getElementById('editCaisseModal');
                bootstrap.Modal.getInstance(editModal).hide();

                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Caisse mise à jour avec succès'
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
    // Définir la fonction globalement
    window.editCaisse = function(id) {
        try {
            // Afficher un indicateur de chargement
            Toast.fire({
                icon: 'info',
                title: 'Chargement...',
                timer: 1000,
                showConfirmButton: false
            });

            fetch(`${apiUrl}/parametres/caisses/${id}/edit`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur lors du chargement des données');
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        // Récupérer le modal et le formulaire
                        const editModal = document.getElementById('editCaisseModal');
                        const editForm = document.getElementById('editCaisseForm');

                        // Mettre à jour l'action du formulaire
                        editForm.action = `${apiUrl}/parametres/caisses/${id}`;

                        // Remplir les champs du formulaire
                        editForm.querySelector('[name="code_caisse"]').value = result.data.code_caisse;
                        editForm.querySelector('[name="libelle"]').value = result.data.libelle;
                        editForm.querySelector('[name="point_vente_id"]').value = result.data.point_de_vente_id;
                        editForm.querySelector('[name="actif"]').checked = result.data.actif;

                        // Ouvrir le modal
                        const modal = new bootstrap.Modal(editModal);
                        modal.show();
                    } else {
                        throw new Error(result.message || 'Erreur lors du chargement des données');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    Toast.fire({
                        icon: 'error',
                        title: error.message || 'Erreur lors du chargement des données',
                        timer: 3000
                    });
                });
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des données',
                timer: 3000
            });
        }
    }

    async function deleteCaisse(id) {
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
                        const response = await fetch(`${apiUrl}/parametres/caisses/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        // Log pour debug
                        console.log('Response:', data);

                        if (!data.success) {
                            throw new Error(data.message || data.error ||
                                'Erreur lors de la suppression');
                        }
                        return data;
                    } catch (error) {
                        console.error('Delete Error:', error);
                        Swal.showValidationMessage(
                            `Erreur: ${error.message}`
                        );
                        throw error;
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed && result.value) {
                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.value.message || 'Caisse supprimée avec succès'
                });

                // Recharger la page après un court délai
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            console.error('Global Error:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Une erreur est survenue lors de la suppression'
            });
        }
    }

    async function toggleCaisseStatus(id) {
        try {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Voulez-vous modifier le statut de cette caisse ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, modifier',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch(`${apiUrl}/parametres/caisses/${id}/toggle-status`, {
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
                            throw new Error(data.message || 'Erreur lors de la modification du statut');
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
