<style>
    /* common-styles.css */

/* Variables */
:root {
    --primary-color: #FFA500;
    --primary-hover: #FF8C00;
    --secondary-color: #4A5568;
    --success-color: #48BB78;
    --danger-color: #F56565;
    --warning-color: #ECC94B;
    --info-color: #4299E1;
    --background-color: #F7FAFC;
    --text-color: #2D3748;
    --border-radius: 0.75rem;
    --transition-duration: 0.3s;
}

/* Animations globales */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { transform: translateX(-20px); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Styles de base */
body {
    background-color: var(--background-color);
    color: var(--text-color);
}

/* Cards personnalisées */
.k-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all var(--transition-duration) ease;
}

.k-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Boutons personnalisés */
.k-btn {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all var(--transition-duration) ease;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.k-btn-primary {
    background: linear-gradient(to right, var(--primary-color), var(--primary-hover));
    color: white;
}

.k-btn-primary:hover {
    background: linear-gradient(to right, var(--primary-hover), var(--primary-hover));
    transform: translateY(-1px);
}

/* Formulaires stylisés */
.k-form-control {
    border-radius: 0.5rem;
    border: 2px solid #E2E8F0;
    padding: 0.5rem 1rem;
    transition: all var(--transition-duration) ease;
}

.k-form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.2);
}

/* Select2 personnalisé */
.select2-container--bootstrap-5 .select2-selection {
    border-radius: 0.5rem !important;
    border: 2px solid #E2E8F0 !important;
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: var(--primary-color) !important;
    box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.2) !important;
}

/* Badges personnalisés */
.k-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.k-badge-primary {
    background-color: rgba(255, 165, 0, 0.1);
    color: var(--primary-color);
}

/* Tables responsives */
.k-table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Scrollbar personnalisée */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #F1F1F1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

/* Modals personnalisés */
.k-modal .modal-content {
    border: none;
    border-radius: var(--border-radius);
}

.k-modal .modal-header {
    background: linear-gradient(to right, var(--primary-color), var(--primary-hover));
    color: white;
    border-top-left-radius: var(--border-radius);
    border-top-right-radius: var(--border-radius);
}

/* Utilitaires d'espacement */
.k-space-y > * + * {
    margin-top: 1rem;
}

.k-space-x > * + * {
    margin-left: 1rem;
}

/* Effets de survol */
.hover-scale {
    transition: transform var(--transition-duration) ease;
}

.hover-scale:hover {
    transform: scale(1.02);
}

/* États de chargement */
.k-loading {
    position: relative;
    pointer-events: none;
}

.k-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .k-card {
        margin: 0.5rem 0;
    }

    .k-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
