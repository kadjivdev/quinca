<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des formulaires
    const addForm = document.getElementById('addTarificationForm');
    const editForm = document.getElementById('editTarificationForm');
    const addModal = document.getElementById('addTarificationModal');
    const editModal = document.getElementById('editTarificationModal');

    // Gestion du formulaire d'ajout
    addForm?.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!addForm.checkValidity()) {
            e.stopPropagation();
            addForm.classList.add('was-validated');
            return;
        }

        const submitBtn = addForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

        try {
            const formData = new FormData(this);
            formData.set('statut', document.getElementById('addStatutTarif').checked ? '1' : '0');

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
                const bsModal = bootstrap.Modal.getInstance(addModal);
                bsModal.hide();

                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Tarification créée avec succès'
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

    // Gestion du formulaire d'édition
    editForm?.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!editForm.checkValidity()) {
            e.stopPropagation();
            editForm.classList.add('was-validated');
            return;
        }

        const submitBtn = editForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mise à jour...';

        try {
            const formData = new FormData(this);
            formData.append('_method', 'PUT');
            formData.set('statut', document.getElementById('editStatutTarif').checked ? '1' : '0');

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
                const bsModal = bootstrap.Modal.getInstance(editModal);
                bsModal.hide();

                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Tarification mise à jour avec succès'
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

    // Fonction d'édition de tarification
    window.editTarification = async function(id) {
        try {
            // Afficher un indicateur de chargement
            Toast.fire({
                icon: 'info',
                title: 'Chargement...',
                timer: 1000,
                showConfirmButton: false
            });

            const response = await fetch(`/catalogue/tarifications/${id}/edit`, {
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
                // Mise à jour du formulaire
                const editForm = document.getElementById('editTarificationForm');
                editForm.action = `/catalogue/tarifications/${id}`;

                // Mise à jour des informations de l'article
                document.getElementById('editCodeArticle').textContent = result.data.article.code_article;
                document.getElementById('editTypeTarif').textContent = result.data.type_tarif.libelle_type_tarif;

                // Mise à jour des champs du formulaire
                editForm.querySelector('input[name="prix"]').value = result.data.prix;
                document.getElementById('editStatutTarif').checked = result.data.statut;

                // Ouverture du modal
                const bsModal = new bootstrap.Modal(document.getElementById('editTarificationModal'));
                bsModal.show();
            } else {
                throw new Error(result.message || 'Erreur lors du chargement des données');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des données'
            });
        }
    };

    // Fonction de suppression
    window.deleteTarification = async function(id) {
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
                        const response = await fetch(`/catalogue/tarifications/${id}`, {
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
                    title: result.value.message || 'Tarification supprimée avec succès'
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
    };

    // Fonction pour basculer le statut
    window.toggleTarificationStatus = async function(id) {
        try {
            const response = await fetch(`/catalogue/tarifications/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (result.success) {
                Toast.fire({
                    icon: 'success',
                    title: result.message || 'Statut mis à jour avec succès'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(result.message || 'Erreur lors de la mise à jour du statut');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors de la mise à jour du statut'
            });
        }
    };

    // Réinitialisation des formulaires à la fermeture des modaux
    addModal?.addEventListener('hidden.bs.modal', function() {
        addForm.reset();
        addForm.classList.remove('was-validated');
    });

    editModal?.addEventListener('hidden.bs.modal', function() {
        editForm.reset();
        editForm.classList.remove('was-validated');
    });

    // Fonction de rafraîchissement de la page
    window.refreshPage = function() {
        const refreshBtn = document.querySelector('.btn-light-secondary');
        if (refreshBtn) {
            refreshBtn.classList.add('refreshing');
            refreshBtn.disabled = true;
        }

        setTimeout(() => {
            window.location.reload();
        }, 500);
    };
});


// Filtrage des tarifications
function filterTarifications() {
    const articleFilter = document.getElementById('articleFilter').value;
    const familleFilter = document.getElementById('familleFilter').value;
    const rows = document.querySelectorAll('#tarificationsTable tbody tr');

    rows.forEach(row => {
        const articleId = row.dataset.article;
        const familleId = row.dataset.famille;
        let show = true;

        if (articleFilter && articleId != articleFilter) show = false;
        if (familleFilter && familleId != familleFilter) show = false;

        row.style.display = show ? '' : 'none';
    });
}

// Réinitialisation des filtres
function resetFilters() {
    document.getElementById('articleFilter').value = '';
    document.getElementById('familleFilter').value = '';
    filterTarifications();
}

// Afficher le modal d'ajout de tarification
function showAddTarificationModal(articleId, typeTarifId) {
    // Préremplir les champs du modal d'ajout
    const addForm = document.getElementById('addTarificationForm');
    if (addForm) {
        addForm.querySelector('[name="article_id"]').value = articleId;
        addForm.querySelector('[name="type_tarif_id"]').value = typeTarifId;
    }

    // Ouvrir le modal
    const modal = new bootstrap.Modal(document.getElementById('addTarificationModal'));
    modal.show();
}


// Voir toutes les tarifications d'un article
function showAllTarifications(articleId) {
    fetch(`/catalogue/tarifications/by-article/${articleId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Construire le HTML pour afficher les tarifications
                let html = '<div class="table-responsive"><table class="table">';
                html += '<thead><tr><th>Type</th><th>Prix</th><th>Statut</th></tr></thead><tbody>';

                result.data.tarifications.forEach(tarif => {
                    html += `
                        <tr>
                            <td>${tarif.type_tarif.libelle_type_tarif}</td>
                            <td>${tarif.prix}</td>
                            <td>${tarif.statut ? 'Actif' : 'Inactif'}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';

                // Afficher dans un modal de détails
                Swal.fire({
                    title: `Tarifications - ${result.data.article.code_article}`,
                    html: html,
                    width: '600px',
                    showCloseButton: true,
                    showConfirmButton: false
                });
            }
        });
}

// Toggle du statut d'une tarification
async function toggleTarificationStatus(id) {
    try {
        const response = await fetch(`/catalogue/tarifications/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            Toast.fire({
                icon: 'success',
                title: 'Statut mis à jour avec succès'
            });

            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (error) {
        Toast.fire({
            icon: 'error',
            title: 'Erreur lors de la mise à jour du statut'
        });
    }
}

// Fonction pour afficher tous les tarifs d'un article
function showAllTarifications(articleId) {
    // Afficher un indicateur de chargement
    Toast.fire({
        icon: 'info',
        title: 'Chargement des tarifs...',
        timer: 1000,
        showConfirmButton: false
    });

    fetch(`/catalogue/tarifications/by-article/${articleId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                // Construire le contenu HTML pour le modal
                let html = `
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type de Tarif</th>
                                    <th class="text-end">Prix</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                result.data.forEach(tarif => {
                    html += `
                        <tr>
                            <td>${tarif.type_tarif.libelle_type_tarif}</td>
                            <td class="text-end">${new Intl.NumberFormat('fr-FR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(tarif.prix)} FCFA</td>
                            <td class="text-center">
                                <span class="badge ${tarif.statut ? 'bg-success' : 'bg-danger'}">
                                    ${tarif.statut ? 'Actif' : 'Inactif'}
                                </span>
                            </td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                Swal.fire({
                    title: `Tarifs de l'article ${result.article.code_article}`,
                    html: html,
                    width: 800,
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        container: 'tarifs-modal',
                        popup: 'shadow-lg',
                        header: 'border-bottom',
                        content: 'p-0'
                    }
                });
            } else {
                throw new Error(result.message || 'Erreur lors du chargement');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des tarifs'
            });
        });
}

// Fonction pour modifier tous les tarifs d'un article
function showEditAllTarificationsModal(articleId) {
    Toast.fire({
        icon: 'info',
        title: 'Chargement des tarifs...',
        timer: 1000,
        showConfirmButton: false
    });

    fetch(`/catalogue/tarifications/by-article/${articleId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                let html = `
                    <form id="editAllTarifsForm">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type de Tarif</th>
                                        <th>Prix actuel</th>
                                        <th>Nouveau prix</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                result.data.forEach(tarif => {
                    html += `
                        <tr>
                            <td>${tarif.type_tarif.libelle_type_tarif}</td>
                            <td class="text-end">${new Intl.NumberFormat('fr-FR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(tarif.prix)} FCFA</td>
                            <td>
                                <div class="input-group">
                                    <input type="number"
                                           class="form-control"
                                           name="prix[${tarif.id}]"
                                           value="${tarif.prix}"
                                           step="0.01"
                                           min="0">
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </form>
                `;

                Swal.fire({
                    title: `Modifier les tarifs - ${result.article.code_article}`,
                    html: html,
                    width: 800,
                    showCancelButton: true,
                    confirmButtonText: 'Enregistrer',
                    cancelButtonText: 'Annuler',
                    showLoaderOnConfirm: true,
                    customClass: {
                        container: 'tarifs-modal',
                        popup: 'shadow-lg',
                        header: 'border-bottom',
                        content: 'p-0'
                    },
                    preConfirm: async () => {
                        try {
                            const form = document.getElementById('editAllTarifsForm');
                            const formData = new FormData(form);

                            const response = await fetch(`/catalogue/tarifications/${articleId}/update-all`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: formData
                            });

                            const data = await response.json();
                            if (!data.success) {
                                throw new Error(data.message);
                            }
                            return data;
                        } catch (error) {
                            Swal.showValidationMessage(error.message);
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Toast.fire({
                            icon: 'success',
                            title: 'Tarifs mis à jour avec succès'
                        });
                        setTimeout(() => window.location.reload(), 1500);
                    }
                });
            } else {
                throw new Error(result.message || 'Erreur lors du chargement');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des tarifs'
            });
        });
}
</script>
