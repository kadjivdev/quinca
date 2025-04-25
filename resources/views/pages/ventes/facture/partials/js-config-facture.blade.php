// facture-config.js

'use strict';

// Configuration centralisée
const FactureConfig = {
    selectors: {
        modal: '#addFactureModal',
        form: '#addFactureForm',
        ligneContainer: '#lignesContainer',
        template: '#ligneFactureTemplate',
        addButton: '#btnAddLigne',
        totalHT: '#totalHT',
        totalTVA: '#totalTVA',
        totalTTC: '#totalTTC',
        totalAIB: '#totalAIB',
        clientSelect: '.select2-clients'
    },
    classes: {
        ligne: 'ligne-facture',
        quantiteInput: 'quantite-input',
        tarifSelect: 'select2-tarifs',
        uniteSelect: 'unite-select',
        remiseInput: 'remise-input',
        totalLigne: 'total-ligne'
    },
    routes: {
        articlesSearch: '/vente/factures/api/articles/search',
        getTarifs: (id) => `/vente/factures/articles/${id}/tarifs`,
        getUnites: (id) => `/vente/factures/articles/${id}/unites`,
        store: '/vente/factures/store'
    },
    select2Options: {
        theme: 'bootstrap-5',
        width: '100%',
        language: 'fr',
        dropdownParent: '#addFactureModal'
    },
    TVA: {
        rate: 18 // Taux fixe de 18%
    }
};

// Messages et configurations
const FactureMessages = {
    errors: {
        articleLoad: "Impossible de charger les détails de l'article",
        tarifLoad: "Impossible de charger les tarifs",
        uniteLoad: "Impossible de charger les unités de mesure",
        submission: "Erreur lors de l'enregistrement de la facture",
        validation: "Veuillez vérifier les champs du formulaire",
        network: "Erreur de connexion au serveur"
    },
    success: {
        created: "Facture créée avec succès",
        updated: "Facture mise à jour avec succès"
    },
    confirmations: {
        delete: "Êtes-vous sûr de vouloir supprimer cette ligne ?",
        cancel: "Êtes-vous sûr de vouloir annuler la saisie ?"
    }
};

// Cache pour les données
const ArticleCache = {
    _cache: new Map(),

    async get(type, id) {
        const key = `${type}_${id}`;
        return this._cache.get(key) || null;
    },

    set(type, id, data) {
        const key = `${type}_${id}`;
        this._cache.set(key, data);
    },

    clear() {
        this._cache.clear();
    }
};

// Utilitaires pour la facture
const FactureUtils = {
    roundNumber(number, decimals = 2) {
        return Number(Math.round(number + 'e' + decimals) + 'e-' + decimals);
    },

    parseNumber(value) {
        if (!value) return 0;
        const cleanValue = value.toString().replace(/\s/g, '').replace(',', '.');
        return parseFloat(cleanValue) || 0;
    },

    formatMoney(amount) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }
};

// Style supplémentaires pour l'UI
const styles = `
    .ligne-facture {
        transition: all 0.3s ease;
    }

    .ligne-facture.loading {
        opacity: 0.6;
        pointer-events: none;
        position: relative;
    }

    .ligne-facture.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
    }

    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.375rem;
        border-color: #ced4da;
    }

    .was-validated .select2-container--bootstrap-5 .select2-selection--single {
        border-color: #dc3545;
    }

    .was-validated .valid.select2-container--bootstrap-5 .select2-selection--single {
        border-color: #198754;
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #e9ecef;
        color: #1e2125;
    }

    .select2-result-article {
        padding: 8px;
    }

    .select2-result-article__code {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .select2-result-article__title {
        font-weight: 500;
        margin: 4px 0;
    }

    .select2-result-article__stock {
        font-size: 0.875rem;
        color: #198754;
    }

    .select2-dropdown {
        border-color: #ced4da;
    }

    .select2-search__field {
        border-radius: 0.25rem !important;
        border-color: #ced4da !important;
    }

    .select2-search__field:focus {
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }
`;

// Injection des styles
$('<style>').text(styles).appendTo('head');

// Export des constantes et utilitaires
window.FactureConfig = FactureConfig;
window.FactureMessages = FactureMessages;
window.ArticleCache = ArticleCache;
window.FactureUtils = FactureUtils;
