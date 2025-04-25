@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoInput = document.getElementById('logo');
    const previewLogo = document.getElementById('previewLogo');
    const form = document.getElementById('configurationForm');
    const defaultLogoUrl = "{{ asset('images/default-company-logo.png') }}";
    const currentLogoUrl = "{{ $Societe->logo_path ? asset('storage/' . $Societe->logo_path) : asset('images/default-company-logo.png') }}";

    // Configuration des messages Swal
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Fonction pour afficher les notifications
    function showNotification(type, message) {
        Toast.fire({
            icon: type,
            title: message
        });
    }

    // Gestion du formulaire principal
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Enregistrement en cours...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                Swal.close();

                if (result.success) {
                    showNotification('success', result.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('\n');
                        showNotification('error', errorMessages);
                    } else {
                        showNotification('error', result.message || 'Une erreur est survenue');
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                Swal.close();
                showNotification('error', 'Une erreur inattendue est survenue');
            }
        });
    }

    // Gestion du logo
    if (logoInput) {
        logoInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (file) {
                try {
                    if (!file.type.match('image.*')) {
                        throw new Error('Le fichier doit être une image');
                    }
                    if (file.size > 2 * 1024 * 1024) {
                        throw new Error('L\'image ne doit pas dépasser 2Mo');
                    }

                    Swal.fire({
                        title: 'Chargement du logo...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData();
                    formData.append('logo', file);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    const response = await fetch(`${apiUrl}/parametres/configuration/update-logo`, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();
                    Swal.close();

                    if (result.success) {
                        // Prévisualisation avec animation
                        previewLogo.style.opacity = '0';
                        setTimeout(() => {
                            previewLogo.src = result.data.logo_path;
                            previewLogo.style.opacity = '1';
                        }, 300);

                        showNotification('success', result.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        throw new Error(result.message || 'Erreur lors de la mise à jour du logo');
                    }
                } catch (error) {
                    Swal.close();
                    showNotification('error', error.message);
                    // Restaurer l'ancien logo
                    if (previewLogo) {
                        previewLogo.src = currentLogoUrl;
                    }
                }
            }
        });
    }

    // Fonction de suppression du logo
    window.deleteLogo = async function() {
        try {
            const result = await Swal.fire({
                title: 'Supprimer le logo ?',
                text: "Cette action ne peut pas être annulée",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });

            if (result.isConfirmed) {
                const response = await fetch(`${apiUrl}/parametres/configuration/logo`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Animation de suppression
                    if (previewLogo) {
                        previewLogo.style.opacity = '0';
                        setTimeout(() => {
                            previewLogo.src = defaultLogoUrl;
                            previewLogo.style.opacity = '1';
                        }, 300);
                    }

                    showNotification('success', data.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message);
                }
            }
        } catch (error) {
            console.error('Erreur:', error);
            showNotification('error', 'Erreur lors de la suppression du logo');
        }
    }
});
</script>
@endpush
