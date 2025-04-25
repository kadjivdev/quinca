@extends('layouts.securite.Login')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center py-5">
            <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5">
                <!-- Card Container avec effet d'angle amélioré -->
                <div class="position-relative login-card-wrapper">
                    <!-- Fond décoratif incliné avec animation -->
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-warning decorative-bg"
                         style="transform: rotate(-3deg); z-index: 0; border-radius: 15px;"></div>

                    <!-- Carte principale -->
                    <div class="card shadow-lg border-0 position-relative main-card" style="z-index: 1;">
                        <div class="card-body p-4 p-md-5">
                            <!-- Logo ADJIV -->
                            <div class="text-center mb-4 logo-container">
                                <img src="{{ asset('assets/img/logos/kadjiv.png') }}"
                                alt="ADJIV Logo"
                                class="img-fluid mb-3 logo-image"
                                style="max-width: 150px; height: auto;">
                            </div>

                            <!-- Messages de succès -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show animated fadeIn" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="text-center mb-4 animated fadeIn">
                                <h3 class="fw-bold text-dark mb-2">Connexion Quincaillerie</h3>
                                <p class="text-muted">Accédez à votre compte</p>
                            </div>

                            <form method="POST" class="needs-validation animated fadeInUp"  novalidate id="loginForm">
                                @csrf
                                <!-- Champ username avec animation -->
                                <div class="form-floating mb-4 input-group-hover">
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                           id="username" name="username" placeholder="email@example.com"
                                           value="{{ old('username') }}" required autofocus>
                                    <label for="username">
                                        <i class="fas fa-user me-2"></i>Email ou téléphone
                                    </label>
                                    @error('username')
                                        <div class="invalid-feedback animated fadeIn">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Champ mot de passe avec animation -->
                                <div class="form-floating mb-4 position-relative input-group-hover">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" placeholder="Mot de passe" required>
                                    <label for="password">
                                        <i class="fas fa-key me-2"></i>Mot de passe
                                    </label>
                                    <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y me-2 password-toggle"
                                            data-password-toggle>
                                        <i class="far fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback animated fadeIn">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Bouton de connexion avec animation -->
                                <button type="submit" class="btn btn-warning w-100 py-3 mb-3 text-dark fw-bold login-btn">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes floating {
        0% { transform: translateY(0px) rotate(-3deg); }
        50% { transform: translateY(-10px) rotate(-3deg); }
        100% { transform: translateY(0px) rotate(-3deg); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    /* Classes d'animation */
    .animated {
        animation-duration: 0.8s;
        animation-fill-mode: both;
    }

    .fadeIn {
        animation-name: fadeIn;
    }

    .fadeInUp {
        animation-name: fadeInUp;
    }

    /* Styles améliorés */
    .login-card-wrapper {
        transition: transform 0.3s ease;
    }

    .login-card-wrapper:hover {
        transform: translateY(-5px);
    }

    .decorative-bg {
        animation: floating 6s ease-in-out infinite;
        background: linear-gradient(135deg, #ffd700, #ffa500);
    }

    .main-card {
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .logo-image {
        animation: pulse 2s infinite;
        transition: transform 0.3s ease;
    }

    .logo-image:hover {
        transform: scale(1.1);
    }

    .input-group-hover {
        transition: all 0.3s ease;
    }

    .input-group-hover:hover {
        transform: translateX(5px);
    }

    .form-control {
        border-radius: 10px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #ffc107;
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.3);
        transform: scale(1.02);
    }

    .password-toggle {
        opacity: 0.6;
        transition: all 0.3s ease;
    }

    .password-toggle:hover {
        opacity: 1;
        transform: translateY(-50%) scale(1.1);
    }

    .login-btn {
        border-radius: 10px;
        background: linear-gradient(135deg, #ffd700, #ffa500);
        border: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .login-btn:before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: 0.5s;
    }

    .login-btn:hover:before {
        left: 100%;
    }

    .login-btn:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
    }

    /* Animation du fond */
    .min-h-screen {
        background: linear-gradient(-45deg, #f8f9fa, #e9ecef, #dee2e6, #f8f9fa);
        background-size: 400% 400%;
        animation: gradientAnimation 15s ease infinite;
    }

    /* Shake Animation pour la validation */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    .shake {
        animation: shake 0.6s cubic-bezier(.36,.07,.19,.97) both;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée des éléments
    const elements = document.querySelectorAll('.card, .form-floating, .btn');
    elements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.animation = `fadeInUp 0.5s ease forwards ${index * 0.1}s`;
    });

    // Gestion des erreurs avec SweetAlert amélioré
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Erreur de connexion',
            text: "{{ $errors->first() }}",
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc107',
            showClass: {
                popup: 'animated fadeInDown faster'
            },
            hideClass: {
                popup: 'animated fadeOutUp faster'
            }
        });
    @endif

    // Gestion des messages de succès
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Succès',
            text: "{{ session('success') }}",
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc107',
            showClass: {
                popup: 'animated fadeInDown faster'
            },
            hideClass: {
                popup: 'animated fadeOutUp faster'
            }
        });
    @endif

    // Toggle du mot de passe avec animation
    const togglePassword = document.querySelector('[data-password-toggle]');
    const passwordInput = document.querySelector('#password');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');

            // Animation de l'icône
            icon.style.transform = 'scale(1.2)';
            setTimeout(() => icon.style.transform = 'scale(1)', 200);
        });
    }

    // Validation du formulaire améliorée
    const form = document.querySelector('form.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                // Animation de secousse pour les champs invalides
                const invalidInputs = form.querySelectorAll(':invalid');
                invalidInputs.forEach(input => {
                    input.classList.add('shake');
                    setTimeout(() => input.classList.remove('shake'), 600);
                });
            }
            form.classList.add('was-validated');
        });
    }

    // Ajout d'effets de survol sur les champs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.closest('.form-floating').style.transform = 'scale(1.02)';
        });
        input.addEventListener('blur', () => {
            input.closest('.form-floating').style.transform = 'scale(1)';
        });
    });
});
</script>
@endpush
@endsection
