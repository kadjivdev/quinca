<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Sélecteurs des éléments du formulaire
    const conversionForm = document.getElementById('conversionForm');
    const uniteSourceSelect = document.querySelector('select[name="unite_source_id"]');
    const uniteDestSelect = document.querySelector('select[name="unite_dest_id"]');
    const coefficientInput = document.querySelector('input[name="coefficient"]');
    const uniteSourceLabel = document.querySelector('.unite-source-label');
    const uniteDestLabel = document.querySelector('.unite-dest-label');

    // Fonction pour mettre à jour les labels des unités
    function updateUniteLabels() {
        const uniteSourceText = uniteSourceSelect.options[uniteSourceSelect.selectedIndex]?.text.split(' - ')[1] || '';
        const uniteDestText = uniteDestSelect.options[uniteDestSelect.selectedIndex]?.text.split(' - ')[1] || '';

        uniteSourceLabel.textContent = uniteSourceText;
        uniteDestLabel.textContent = uniteDestText;
    }

    // Événements pour mettre à jour les labels des unités
    uniteSourceSelect.addEventListener('change', updateUniteLabels);
    uniteDestSelect.addEventListener('change', updateUniteLabels);

    // Fonction pour valider la sélection des unités
    function validateUniteSelection() {
        const uniteSourceId = uniteSourceSelect.value;
        const uniteDestId = uniteDestSelect.value;

        if (uniteSourceId === uniteDestId && uniteSourceId !== '') {
            coefficientInput.value = '1';
            coefficientInput.setAttribute('readonly', true);
            return false;
        }

        coefficientInput.removeAttribute('readonly');
        return true;
    }

    // Événements pour la validation des unités
    uniteSourceSelect.addEventListener('change', validateUniteSelection);
    uniteDestSelect.addEventListener('change', validateUniteSelection);

    // Fonction pour soumettre le formulaire
    async function handleFormSubmit(e) {
        e.preventDefault();

        // Validation du formulaire Bootstrap
        if (!conversionForm.checkValidity()) {
            e.stopPropagation();
            conversionForm.classList.add('was-validated');
            return;
        }

        // Vérification si au moins une case "article_ids[]" est cochée
        const articleCheckboxes = conversionForm.querySelectorAll('.article-checkbox');
        const isAnyArticleSelected = Array.from(articleCheckboxes).some(checkbox => checkbox.checked);

        if (!isAnyArticleSelected) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention !',
                text: "Veuillez sélectionner au moins un article",
                confirmButtonColor: '#d33'
            });
            return;
        }

        try {
            const formData = new FormData(conversionForm);
            const response = await fetch(conversionForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Erreur lors de l\'enregistrement');
            }

            // Notification de succès avec SweetAlert2
            Swal.fire({
                icon: 'success',
                title: 'Succès !',
                text: result.message,
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Fermer le modal et recharger la page
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addConversionModal'));
                    modal.hide();
                    window.location.reload();
                }
            });

        } catch (error) {
            // Notification d'erreur avec SweetAlert2
            Swal.fire({
                icon: 'error',
                title: 'Erreur !',
                text: error.message,
                confirmButtonColor: '#d33'
            });
        }
    }

    // Validation personnalisée avant la soumission
    conversionForm.addEventListener('submit', handleFormSubmit);

    // Initialisation des labels des unités au chargement
    updateUniteLabels();

    // Gestion de la sélection multiple d'articles
    let selectedArticles = new Set();

    document.querySelectorAll('.article-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedArticles.add(this.value);
            } else {
                selectedArticles.delete(this.value);
            }

            // Mettre à jour le compteur
            updateSelectedCount();
        });
    });

    // Fonction pour réinitialiser le formulaire
    function resetForm() {
        conversionForm.reset();
        selectedArticles.clear();
        updateUniteLabels();
        updateSelectedCount();
        conversionForm.classList.remove('was-validated');
        document.querySelectorAll('.article-checkbox, .select-all-famille')
            .forEach(cb => cb.checked = false);
    }

    // Événement pour réinitialiser le formulaire à la fermeture du modal
    document.getElementById('addConversionModal').addEventListener('hidden.bs.modal', resetForm);

    // Validation en temps réel du coefficient
    coefficientInput.addEventListener('input', function() {
        if (this.value <= 0) {
            this.setCustomValidity('Le coefficient doit être supérieur à 0');
        } else {
            this.setCustomValidity('');
        }
    });

    // Empêcher la sélection de la même unité source et destination
    uniteDestSelect.addEventListener('change', function() {
        if (this.value === uniteSourceSelect.value && this.value !== '') {
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: 'La conversion vers la même unité aura un coefficient de 1',
                confirmButtonColor: '#3085d6'
            });
        }
    });
});

async function editConversion(id){
    try {
        Toast.fire({
            icon: 'info',
            title: 'Chargement...',
            timer: 1000,
            showConfirmButton: false
        });

        const response = await fetch(`${apiUrl}/parametres/conversions/${id}/edit`, {  // URL mise à jour
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
            const data = result.data;
            const editModal = document.getElementById('editConversionModal');
            const editForm = document.getElementById('editConversionForm');

            editForm.action = `${apiUrl}/parametres/conversions/${id}`;  // URL mise à jour

            // Marquer l'article sélectionné
            const sourceList = $("#editUniteSourceMod");
            const sourceOption = sourceList.find(`option[value="${data.unite_source_id}"]`);
            if (sourceOption.length > 0) {
                sourceOption.prop("selected", true);
            }

            const destList = $("#editUniteDestMod");
            const destOption = destList.find(`option[value="${data.unite_dest_id}"]`);
            if (destOption.length > 0) {
                destOption.prop("selected", true);
            }

            // Remplir les champs
            editForm.querySelector('[name="coefficient"]').value = parseFloat(data.coefficient).toFixed(2);

            const articlesContainer = document.getElementById("articleList");
            articlesContainer.innerHTML = "";
            const listItem = document.createElement("li");
            listItem.className = "list-group-item d-flex align-items-center justify-content-between";
            listItem.innerHTML = `
                <div>
                    <strong>${data.article.code_article}</strong>
                    <span class="text-muted ms-2">${data.article.designation}</span>
                </div>
            `;

            $("[name='article_id']").val(data.article.id);
            articlesContainer.appendChild(listItem);

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

$('#editConversionForm').on('submit', function(e) {
    e.preventDefault();

    if (this.checkValidity()) {
        const formData = $("#editConversionForm").serialize();

        $.ajax({
            url: $(this).attr('action'),
            method: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editConversionModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: 'Conversion modifiée avec succès'
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors de la modification de la conversion'
                });
            }
        });
    }

    $(this).addClass('was-validated');
});

async function toggleStatutConversion(id) {
    try{
        const result = await Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Voulez-vous modifier le statut de cette conversion ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, modifier',
            cancelButtonText: 'Annuler',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const response = await fetch(`${apiUrl}/parametres/conversions/${id}/toggle-status`, {  // URL mise à jour
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

async function deleteConversion(id) {
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
                    const response = await fetch(`${apiUrl}/parametres/conversions/${id}`, {  // URL mise à jour
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
                title: result.value.message || 'Conversion supprimée avec succès'
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
</script>
