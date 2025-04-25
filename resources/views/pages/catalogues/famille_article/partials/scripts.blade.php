<script>
    var apiUrl = "{{ config('app.url_ajax') }}";
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('familleArticleForm');
    const modal = document.getElementById('addFamilleModal');

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
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

        try {
            const formData = new FormData(this);
            formData.set('statut', document.getElementById('familleActifCheck').checked ? '1' : '0');

            const response = await fetch(apiUrl+'/catalogue/famille-articles', {  // URL mise à jour
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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

    // Génération automatique du code lors de l'ouverture du modal
    modal?.addEventListener('show.bs.modal', function() {
        form.reset();
        form.classList.remove('was-validated');
        const code = generateFamilleCode();

         // Vérifier si le code existe déjà via AJAX
         fetch("{{ route('check.famille.code') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ code_famille: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    console.warn("Code déjà utilisé, génération d'un nouveau code...");
                    // Régénérer le code si déjà existant
                    generateAndValidateCode();
                } else {
                    console.log("Code unique, utilisation en cours...");
                    form.querySelector('[name="code_famille"]').value = code;
                }
            })
            .catch(error => {
                console.error("Erreur lors de la vérification du code : ", error);
            });
            // Réinitialiser le switch à actif par défaut
            document.getElementById('statusSwitch').checked = true;

        form.querySelector('[name="code_famille"]').value = code;
    });

    // Fonction pour régénérer et vérifier
    function generateAndValidateCode() {
        const newCode = generateFamilleCode();
        fetch("{{ route('check.famille.code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ code_famille: newCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                generateAndValidateCode(); // Répète jusqu'à avoir un code unique
            } else {
                form.querySelector('[name="code_famille"]').value = newCode;
            }
        });
    }
});

// Fonction d'édition
async function editFamilleArticle(id) {
    try {
        Toast.fire({
            icon: 'info',
            title: 'Chargement...',
            timer: 1000,
            showConfirmButton: false
        });

        const response = await fetch(`${apiUrl}/catalogue/famille-articles/${id}/edit`, {  // URL mise à jour
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
            const editModal = document.getElementById('editFamilleModal');
            const editForm = document.getElementById('editFamilleForm');

            editForm.action = `/catalogue/famille-articles/${id}`;  // URL mise à jour

            // Remplir les champs
            editForm.querySelector('[name="code_famille"]').value = result.data.code_famille;
            editForm.querySelector('[name="libelle_famille"]').value = result.data.libelle_famille;
            // editForm.querySelector('[name="description"]').value = result.data.description || '';
            // editForm.querySelector('[name="methode_valorisation"]').value = result.data.methode_valorisation;
            // editForm.querySelector('[name="famille_parent_id"]').value = result.data.famille_parent_id || '';
            // editForm.querySelector('[name="statut"]').checked = result.data.statut;

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

document.getElementById('editFamilleForm')?.addEventListener('submit', async function(e) {
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
        // const switchElement = document.getElementById('editStatusSwitch');
        // formData.set('actif', switchElement.checked ? '1' : '0');

        const response = await fetch(apiUrl+this.action.split('1080')[1], {
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
            const editModal = document.getElementById('editFamilleModal');
            bootstrap.Modal.getInstance(editModal).hide();

            // Message de succès
            Toast.fire({
                icon: 'success',
                title: result.message || 'Famille mise à jour avec succès'
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
            title: error.message || 'Erreur lors de la mise à jour de la Famille'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer les modifications';
    }
});

// Fonction de suppression
async function deleteFamilleArticle(id) {
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
                    const response = await fetch(`${apiUrl}/catalogue/famille-articles/${id}`, {  // URL mise à jour
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
                title: result.value.message || 'Famille supprimée avec succès'
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

// Fonction de changement de statut
async function toggleFamilleStatus(id) {
    try{
        const result = await Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Voulez-vous modifier le statut de cette famille ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, modifier',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const response = await fetch(`${apiUrl}/catalogue/famille-articles/${id}/toggle-status`, {  // URL mise à jour
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

// Fonction utilitaire pour générer un code
function generateFamilleCode() {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code;
}
</script>
