// Constantes et variables globales
const ENDPOINTS = {
    GENERATE_CODE: apiUrl.length>0 ? `${apiUrl}/achat/programmations/generate-code` : `/achat/programmations/generate-code`,
    GET_ARTICLES: apiUrl.length>0 ? `${apiUrl}/achat/programmation/articles` : `/achat/programmation/articles`,
    SAVE_PROGRAMMATION: apiUrl.length>0 ? `${apiUrl}/achat/programmations` : `/achat/programmations`
};

let articlesList = [];

// Initialisation du module
class ProgrammationAchat {
    constructor() {
        this.initializeEventListeners();
        // this.generateCode();
        this.initializeSelect2();
    }

    // Initialisation des écouteurs d'événements
    initializeEventListeners() {
        // Événements pour le modal et le formulaire
        $('#addProgrammationModal').on('shown.bs.modal', () => this.onModalShow());
        $('#addProgrammationModal').on('hidden.bs.modal', () => this.onModalHide());
        $('#editProgrammationModal').on('shown.bs.modal', () => this.onModalShow());
        $('#editProgrammationModal').on('hidden.bs.modal', () => this.onModalHide());
        $('#addProgrammationForm').on('submit', (e) => this.handleSubmit(e));

        // Événements pour la gestion des articles
        $('#btnAddLigne').on('click', () => this.addNewLine());
        $('#fournisseurSelect').on('change', (e) => this.handleFournisseurChange(e));
        $(document).on('click', '.remove-ligne', (e) => this.removeLine(e));
        $(document).on('change', '.select2-articles', (e) => this.handleArticleChange(e));
    }

    // Initialisation de Select2
    initializeSelect2() {
        // $('#fournisseurSelect').select2({
        //     theme: 'bootstrap-5',
        //     width: '100%',
        //     placeholder: 'Sélectionner un fournisseur'
        // });
    }

    // Gestion de l'affichage du modal
    onModalShow() {
        this.generateCode();
        this.clearForm();
    }

    onModalHide() {
        this.clearForm();
        $('#addProgrammationForm').removeClass('was-validated');
    }

    // Génération du code
    generateCode() {
        $.ajax({
            url: ENDPOINTS.GENERATE_CODE,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    $('#code').val(response.code);
                }
            },
            error: (xhr) => {
                this.showError('Erreur lors de la génération du code');
            }
        });
    }

    // Gestion des articles
    handleFournisseurChange(event) {
        const fournisseurId = $(event.target).val();
        if (fournisseurId) {
            // this.loadArticles(fournisseurId);
        } else {
            // this.clearArticles();
        }
    }

    loadArticles(fournisseurId) {
        $.ajax({
            url: `${ENDPOINTS.GET_ARTICLES}/${fournisseurId}`,
            method: 'GET',
            success: (response) => {
                articlesList = response;
                this.updateArticlesOptions();
            },
            error: (xhr) => {
                this.showError('Erreur lors du chargement des articles');
            }
        });
    }

    updateArticlesOptions() {
        let options = '<option value="">Sélectionner un article</option>';
        articlesList.forEach(article => {
            options += `
                <option value="${article.id}"
                        data-unites='${JSON.stringify(article.unites)}'>
                    ${article.designation}
                </option>`;
        });
        $('.select2-articles').html(options).trigger('change');
    }

    handleArticleChange(event) {
        const $select = $(event.target);
        const $row = $select.closest('tr');
        const articleId = $select.val();
        const article = articlesList.find(a => a.id == articleId);

        if (article && article.unites) {
            let unitesOptions = '<option value="">Sélectionner une unité</option>';
            article.unites.forEach(unite => {
                unitesOptions += `<option value="${unite.id}">${unite.nom}</option>`;
            });
            $row.find('select[name="unites[]"]').html(unitesOptions);
        }
    }

    addNewLine() {
        const template = document.getElementById('ligneProgrammationTemplate');
        const clone = template.content.cloneNode(true);
        const $newLine = $(clone);

        $('#lignesContainer').append($newLine);

        // Initialiser Select2 sur la nouvelle ligne
        
        $('.select2-articles').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#addProgrammationModal')
        });

        // Pré-remplir les options d'articles si disponibles
        if (articlesList.length > 0) {
            this.updateArticlesOptions();
        }
    }

    removeLine(event) {
        $(event.target).closest('tr').remove();
    }

    // Gestion du formulaire
    clearForm() {
        $('#addProgrammationForm')[0].reset();
        $('#lignesContainer').empty();
        this.addNewLine(); // Ajouter une ligne vide par défaut
    }

    handleSubmit(event) {
        event.preventDefault();
        const form = event.target;


        if (form.checkValidity()) {
            this.saveProgrammation($(form));
        }

        $(form).addClass('was-validated');
    }

    saveProgrammation($form) {
        $.ajax({
            url: ENDPOINTS.SAVE_PROGRAMMATION,
            method: 'POST',
            data: $form.serialize(),
            success: (response) => {
                if (response.success) {
                    $('#addProgrammationModal').modal('hide');
                    this.showSuccess(response.message);
                    setTimeout(() => window.location.reload(), 1000);
                }
            },
            error: (xhr) => {
                this.showError('Erreur lors de l\'enregistrement');
                // console.error(xhr);
                console.log($form.serialize())
            }
        });
    }

    // Utilitaires
    showSuccess(message) {
        Toast.fire({
            icon: 'success',
            title: message
        });
    }

    showError(message) {
        Toast.fire({
            icon: 'error',
            title: message
        });
    }
}

// Initialisation au chargement du document
$(document).ready(() => {
    new ProgrammationAchat();
});
