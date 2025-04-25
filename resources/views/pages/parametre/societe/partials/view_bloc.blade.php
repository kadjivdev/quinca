{{-- view_bloc.blade.php --}}
<div class="col-12 col-lg-4">
    <div class="card border-0 shadow-lg h-100 position-relative overflow-hidden hover-card">
        <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-modern opacity-5"></div>

        <div class="card-body p-4">
            {{-- Section Logo avec Animation améliorée --}}
            <div class="text-center position-relative mb-4">
                <div class="company-logo-wrapper mb-3">
                    @php
                        $hasLogo = !empty($configuration->logo_path);
                        $logoUrl = $hasLogo
                            ? asset('storage/' . $configuration->logo_path)
                            : asset('images/default-company-logo.png');
                    @endphp
                    <div class="logo-container">
                        <div class="logo-background-modern"></div>
                        @if(!$hasLogo)
                            <div class="default-logo-placeholder">
                                <i class="fas fa-building"></i>
                            </div>
                        @endif
                        <img src="{{ $logoUrl }}"
                             alt="Logo Entreprise"
                             class="company-logo animate-fade-in {{ !$hasLogo ? 'd-none' : '' }}"
                             id="previewLogo">
                             <div class="logo-overlay-modern" id="logoOverlay">
                                <label for="logo" class="btn btn-floating-modern me-2 btn-dark" title="Mettre à jour le logo">
                                    <i class="uil uil-sync"></i>
                                </label>
                                @if($hasLogo)
                                    <button type="button" class="btn btn-floating-modern btn-danger" onclick="deleteLogo()" title="Supprimer le logo">
                                        <i class="uil uil-trash-alt"></i>
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

            {{-- Reste du code inchangé... --}}
        </div>
    </div>
</div>

@push('styles')
<style>
:root {
    --primary-gradient: linear-gradient(135deg, #4361ee, #4895ef);
    --secondary-gradient: linear-gradient(135deg, #3f37c9, #4cc9f0);
    --danger-gradient: linear-gradient(135deg, #ef4444, #dc2626);
    --card-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
    --border-radius: 16px;
    --logo-size: 200px;
}

/* Styles existants... */

/* Logo Styles Refaits */
.company-logo-wrapper {
    width: var(--logo-size);
    height: var(--logo-size);
    margin: 0 auto;
    position: relative;
    perspective: 1000px;
    border-radius: var(--border-radius);
    background: white;
    box-shadow: var(--card-shadow);
}

.logo-container {
    width: 100%;
    height: 100%;
    border-radius: var(--border-radius);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.default-logo-placeholder {
    position: absolute;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: #e2e8f0;
    background: #f8fafc;
    border-radius: var(--border-radius);
}

.company-logo {
    width: var(--logo-size);
    height: var(--logo-size);
    object-fit: contain;
    padding: 1rem;
    transition: var(--transition);
}

.logo-overlay-modern {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
    padding: 2rem 1rem 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
    opacity: 0;
    transform: translateY(20px);
    transition: var(--transition);
}

.logo-container:hover .logo-overlay-modern {
    opacity: 1;
    transform: translateY(0);
}

/* Boutons flottants modernisés */
.btn-floating-modern {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
    background: white;
    border: none;
    color: #4361ee;
}

.btn-floating-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.btn-floating-modern.btn-danger {
    color: #dc2626;
}

.btn-floating-modern.btn-danger:hover {
    background: #fee2e2;
}

/* Animation améliorée pour les icônes */
.fa-pulse-hover {
    transition: var(--transition);
}

.btn-floating-modern:hover .fa-pulse-hover {
    transform: rotate(360deg);
}

/* Animations */
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* État vide modernisé */
.info-text:empty::after,
.info-text:contains('Non défini') {
    content: 'Non défini';
    color: #94a3b8;
    font-style: italic;
    font-weight: 400;
}

/* Responsive Design amélioré */
@media (max-width: 768px) {
    :root {
        --logo-size: 160px;
    }

    .company-logo-wrapper {
        margin-bottom: 1.5rem;
    }

    .default-logo-placeholder {
        font-size: 3rem;
    }
}

.btn-floating-modern i {
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

/* Animation pour l'icône de synchronisation */
.btn-floating-modern:hover .uil-sync {
    animation: rotate 1s linear infinite;
}

@keyframes rotate {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Couleur de l'icône de suppression */
.btn-floating-modern.btn-danger i {
    color: #dc2626;
}

.btn-floating-modern.btn-danger:hover i {
    color: #ef44
</style>
@endpush
