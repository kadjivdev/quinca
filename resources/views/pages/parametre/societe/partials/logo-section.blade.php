{{-- Section Logo dans view_bloc.blade.php --}}
<div class="text-center position-relative mb-4">
    <div class="company-logo-wrapper mb-3">
        @php
            $logoUrl = $configuration->logo_path
                ? asset('storage/' . $configuration->logo_path)
                : asset('images/default-company-logo.png');
        @endphp
        <div class="logo-container">
            <div class="logo-background-modern"></div>
            <img src="{{ $logoUrl }}"
                 alt="Logo Entreprise"
                 class="company-logo animate-fade-in"
                 id="previewLogo">
            <div class="logo-overlay-modern" id="logoOverlay">
                <label for="logo" class="btn btn-light btn-floating-modern me-2" title="Changer le logo">
                    <span class="material-icons">add_a_photo</span>
                </label>
                @if($configuration->logo_path)
                <button type="button" class="btn btn-light btn-floating-modern delete-logo-btn" onclick="deleteLogo()" title="Supprimer le logo">
                    <span class="material-icons text-danger">delete</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <input type="file"
           id="logo"
           name="logo"
           accept="image/*"
           class="d-none">

    <h3 class="fw-bold text-gradient mb-1 animate-text">
        {{ $configuration->nom_societe ?? 'Nom de l\'entreprise' }}
    </h3>
    <p class="text-muted small fw-medium">{{ $configuration->forme_juridique ?? '' }}</p>
</div>

@push('styles')
<style>
/* Styles existants... */

/* Taille fixe pour le logo */
.company-logo-wrapper {
    width: 200px; /* Taille fixe */
    height: 200px; /* Taille fixe */
    margin: 0 auto;
    position: relative;
}

.company-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 1rem;
    border-radius: var(--border-radius);
    background: white;
    box-shadow: var(--card-shadow);
}

/* Style amélioré pour les boutons */
.btn-floating-modern {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-floating-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.btn-floating-modern .material-icons {
    font-size: 20px;
}

/* Overlay amélioré */
.logo-overlay-modern {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    padding: 1rem;
    border-radius: 0 0 var(--border-radius) var(--border-radius);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.logo-container:hover .logo-overlay-modern {
    opacity: 1;
    transform: translateY(0);
}

/* Style pour l'image */
.company-logo {
    max-width: 100%;
    max-height: 100%;
    width: 200px; /* Taille fixe */
    height: 200px; /* Taille fixe */
    object-fit: contain;
    border-radius: var(--border-radius);
}
</style>
<style>
    /* Ajoutez ces styles spécifiques pour le logo */
    .company-logo-wrapper {
        width: 200px !important; /* Taille fixe */
        height: 200px !important; /* Taille fixe */
        margin: 0 auto;
        position: relative;
    }

    .company-logo {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;
        padding: 1rem;
        border-radius: var(--border-radius);
        background: white;
        box-shadow: var(--card-shadow);
    }

    /* Assurez-vous que le conteneur du logo respecte également la taille fixe */
    .logo-container {
        width: 100%;
        height: 100%;
        border-radius: var(--border-radius);
        position: relative;
        overflow: hidden;
    }

    /* Style pour les icônes des boutons */
    .btn-floating-modern i {
        font-size: 1.2rem;
    }
    </style>
@endpush
