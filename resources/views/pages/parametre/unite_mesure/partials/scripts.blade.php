<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('uniteMesureForm');
        const modal = document.getElementById('addUniteMesureModal');

        form?.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

            try {
                const formData = new FormData(this);
                // Gestion explicite des checkboxes
                formData.set('statut', document.getElementById('uniteActifCheck').checked ? '1' : '0');
                // formData.set('unite_base', document.getElementById('uniteBaseCheck').checked ? '1' : '0');

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
                    } else {
                        $(modal).modal('hide');
                    }

                    Toast.fire({
                        icon: 'success',
                        title: result.message || 'Unité de mesure créée avec succès'
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

        // Génération du code à l'ouverture du modal d'ajout
        modal?.addEventListener('show.bs.modal', function() {
            form.reset();
            form.classList.remove('was-validated');
            const code = generateUniteCode();

            // Vérifier si le code existe déjà via AJAX
            fetch("{{ route('check.unite.code') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code_unite: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    console.warn("Code déjà utilisé, génération d'un nouveau code...");
                    // Régénérer le code si déjà existant
                    generateAndValidateCode();
                } else {
                    console.log("Code unique, utilisation en cours...");
                    form.querySelector('[name="code_unite"]').value = code;
                }
            })
            .catch(error => {
                console.error("Erreur lors de la vérification du code : ", error);
            });
        });
    });

    // Fonction pour régénérer et vérifier
    function generateAndValidateCode() {
        const newCode = generateUniteCode();
        fetch("{{ route('check.unite.code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ code_unite: newCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                generateAndValidateCode(); // Répète jusqu'à avoir un code unique
            } else {
                form.querySelector('[name="code_unite"]').value = newCode;
            }
        });
    }

    // Génération du code unité
    function generateUniteCode() {
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        let code = '';
        for (let i = 0; i < 3; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return code;
    }

    // Edition d'une unité de mesure
    async function editUniteMesure(id) {
        try {
            Toast.fire({
                icon: 'info',
                title: 'Chargement...',
                timer: 1000,
                showConfirmButton: false
            });

            const response = await fetch(`/parametres/unites-mesure/${id}/edit`, {
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
                const editModal = document.getElementById('editUniteMesureModal');
                const editForm = document.getElementById('editUniteMesureForm');

                editForm.action = `/parametres/unites-mesure/${id}`;

                // Remplir les champs
                editForm.querySelector('[name="code_unite"]').value = result.data.code_unite;
                editForm.querySelector('[name="libelle_unite"]').value = result.data.libelle_unite;
                editForm.querySelector('[name="description"]').value = result.data.description || '';
                // editForm.querySelector('#editUniteBase').checked = result.data.unite_base;
                editForm.querySelector('#editUniteActif').checked = result.data.statut;

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

    // Soumission du formulaire d'édition
    document.getElementById('editUniteMesureForm')?.addEventListener('submit', async function(e) {
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

            // Gestion des checkboxes
            formData.set('statut', this.querySelector('#editUniteActif').checked ? '1' : '0');
            // formData.set('unite_base', this.querySelector('#editUniteBase').checked ? '1' : '0');

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
                const editModal = document.getElementById('editUniteMesureModal');
                bootstrap.Modal.getInstance(editModal).hide();

                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Unité de mesure mise à jour avec succès'
                });

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
                title: error.message || 'Erreur lors de la mise à jour'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer les modifications';
        }
    });

    // Réinitialisation du formulaire d'édition
    document.getElementById('editUniteMesureModal')?.addEventListener('hidden.bs.modal', function() {
        const editForm = document.getElementById('editUniteMesureForm');
        editForm.reset();
        editForm.classList.remove('was-validated');
    });

    // Suppression d'une unité de mesure
    async function deleteUniteMesure(id) {
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
                        const response = await fetch(`/parametres/unites-mesure/${id}`, {
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
                    title: result.value.message || 'Unité de mesure supprimée avec succès'
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

    // Fonction pour basculer le statut d'une unité
    async function toggleUniteStatus(id) {
        try {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Voulez-vous modifier le statut de cette unité ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, modifier',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch(`/parametres/unites-mesure/${id}/toggle-status`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Statut mis à jour avec succès'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(result.message || 'Annulé');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors de la mise à jour du statut'
            });
        }
    }
    </script>
