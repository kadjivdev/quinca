<style>
    .article-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .article-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .article-photo {
        height: 150px;
        object-fit: cover;
        background-color: #f8f9fa;
    }

    .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .stock-normal { background-color: #28a745; }
    .stock-alerte { background-color: #ffc107; }
    .stock-critique { background-color: #dc3545; }
    .stock-surplus { background-color: #17a2b8; }

    .search-bar {
        border-radius: 20px;
        padding-left: 40px;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
    }

    .article-card {
    transition: all 0.3s ease-in-out;
}

.article-photo {
    transition: all 0.2s ease-in-out;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
}

.loading::before {
    content: 'Chargement...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
}
</style>

<style>
/* Styles pour la vue liste */
.article-card.flex-row {
    display: flex;
    align-items: center;
}

.article-card.flex-row .article-photo {
    width: 150px;
    height: 150px;
    object-fit: cover;
}

.article-card.flex-row .card-body {
    flex: 1;
    padding: 1rem;
}

/* Loader */
.loading {
    position: relative;
    min-height: 200px;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8) url('path-to-your-loader.gif') center no-repeat;
}

/* Badges de stock */
.stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 0.5em 1em;
}

.stock-badge.normal { background-color: #28a745; }
.stock-badge.alerte { background-color: #ffc107; }
.stock-badge.critique { background-color: #dc3545; }
.stock-badge.surplus { background-color: #17a2b8; }
</style>
