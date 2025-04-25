<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializePointVenteForm();
    });

    function initializePointVenteForm() {
        const form = document.getElementById('pointVenteForm');
        const modal = document.getElementById('addPointVenteModal');

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
                // Gestion explicite de la checkbox actif
                formData.set('actif', document.getElementById('statusSwitch').checked ? '1' : '0');

                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Erreur lors de la création');
                }

                if (result.success) {
                    // Fermer le modal
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }

                    // Message de succès
                    Toast.fire({
                        icon: 'success',
                        title: result.message || 'Point de vente créé avec succès'
                    });

                    // Recharger la page après un délai
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Gérer les erreurs de validation
                    if (result.errors) {
                        let errorMessage = Object.values(result.errors)
                            .flat()
                            .join('\n');
                        throw new Error(errorMessage);
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

        // Gestion de l'ouverture du modal
        modal.addEventListener('show.bs.modal', function() {
            form.reset();
            form.classList.remove('was-validated');
            const code = generatePVCode();

            // Vérifier si le code existe déjà via AJAX
            fetch("{{ route('check.point_vente.code') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code_depot: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    console.warn("Code déjà utilisé, génération d'un nouveau code...");
                    // Régénérer le code si déjà existant
                    generateAndValidateCode();
                } else {
                    console.log("Code unique, utilisation en cours...");
                    form.querySelector('[name="code_pv"]').value = code;
                }
            })
            .catch(error => {
                console.error("Erreur lors de la vérification du code : ", error);
            });
        });
    }

    // Fonction pour régénérer et vérifier
    function generateAndValidateCode() {
        const newCode = generateDepotCode();
        fetch("{{ route('check.point_vente.code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ code_pv: newCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                generateAndValidateCode(); // Répète jusqu'à avoir un code unique
            } else {
                form.querySelector('[name="code_pv"]').value = newCode;
            }
        });
    }

    // Fonction de génération de code PV
    function generatePVCode() {
        const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return Array.from({
            length: 6
        }, () => chars.charAt(Math.floor(Math.random() * chars.length))).join('');
    }

    async function editPointVente(id) {
        try {
            // Afficher un indicateur de chargement
            Toast.fire({
                icon: 'info',
                title: 'Chargement...',
                timer: 1000,
                showConfirmButton: false
            });

            const response = await fetch(`${apiUrl}/parametres/points-vente/${id}/edit`, {
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
                const editModal = document.getElementById('editPointVenteModal');
                const editForm = document.getElementById('editPointVenteForm');

                // Mettre à jour l'action du formulaire
                editForm.action = `${apiUrl}/parametres/points-vente/${id}`;

                // Remplir les champs du formulaire
                editForm.querySelector('[name="code_pv"]').value = result.data.code_pv;
                editForm.querySelector('[name="nom_pv"]').value = result.data.nom_pv;
                editForm.querySelector('[name="adresse_pv"]').value = result.data.adresse_pv || '';
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

    // Gestion de la soumission du formulaire d'édition
    document.getElementById('editPointVenteForm')?.addEventListener('submit', async function(e) {
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
            formData.set('actif', this.querySelector('[name="actif"]').checked ? '1' : '0');

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
                const editModal = document.getElementById('editPointVenteModal');
                bootstrap.Modal.getInstance(editModal).hide();

                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Point de vente mis à jour avec succès'
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
                title: error.message || 'Erreur lors de la mise à jour'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer les modifications';
        }
    });

    // Réinitialisation du formulaire lors de la fermeture du modal
    document.getElementById('editPointVenteModal')?.addEventListener('hidden.bs.modal', function() {
        const editForm = document.getElementById('editPointVenteForm');
        editForm.reset();
        editForm.classList.remove('was-validated');
    });


    async function deletePointVente(id) {
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
                        const response = await fetch(`${apiUrl}/parametres/points-vente/${id}`, {
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
                    title: result.value.message || 'Point de vente supprimé avec succès'
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

    async function toggleStatus(id) {
        try {
            const result = await Swal.fire({
                title: 'Confirmation !',
                text: "Êtes-vous sure de vouloir changer le statut",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, Changer',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch(`${apiUrl}/parametres/points-vente/${id}/toggle-status`, {
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



    //     try {
    //         const result = Swal.fire({
    //         title: 'Êtes-vous sûr ?',
    //         text: "Voulez-vous modifier le statut de ce point de vente ?",
    //         icon: 'warning',
    //         showCancelButton: true,
    //         confirmButtonColor: '#FF9B00',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Oui, modifier',
    //         cancelButtonText: 'Annuler'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
                
    //         }
    //     });
    // }

    async function deletePointVente(id) {
        try {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#FF9B00',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch(`${apiUrl}/parametres/points-vente/${id}`, {
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
                    title: result.value.message || 'Point de vente supprimé avec succès'
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
</script>
