<script>
    // Constantes pour les URLs
const SESSION_CAISSE_URL = {
    store: '/vente/sessions',
};

// Initialisation lors du chargement de la page
$(document).ready(function() {
    // Soumission du formulaire
    $('#addSessionCaisseForm').on('submit', function(event) {
        event.preventDefault();
        ouvrirSession(event);
    });

    // Reset du formulaire lors de la fermeture du modal
    $('#addSessionCaisseModal').on('hidden.bs.modal', function () {
        resetForm();
    });

    // Validation du montant d'ouverture (nombres positifs uniquement)
    $('#montant_ouverture').on('input', function() {
        let value = $(this).val();
        if (value < 0) {
            $(this).val(0);
        }
    });
});

/**
 * Fonction principale d'ouverture de session
 */
function ouvrirSession(event) {
    event.preventDefault();

    // Validation basique côté client
    if (!validateForm()) {
        return;
    }

    // Désactivation du bouton et affichage du spinner
    toggleSubmitButton(true);

    // Récupération et envoi des données
    const formData = new FormData($('#addSessionCaisseForm')[0]);

    $.ajax({
        url: SESSION_CAISSE_URL.store,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: handleSuccess,
        error: handleError,
        complete: function() {
            toggleSubmitButton(false);
        }
    });
}

/**
 * Validation du formulaire côté client
 */
function validateForm() {
    const montantOuverture = $('#montant_ouverture').val();

    if (!montantOuverture || montantOuverture <= 0) {
        showError('Le montant d\'ouverture doit être supérieur à 0');
        return false;
    }

    return true;
}

/**
 * Gestion du succès de la requête
 */
function handleSuccess(response) {
    if (response.success) {
        Swal.fire({
            title: 'Succès !',
            text: response.message || 'Session de caisse ouverte avec succès',
            icon: 'success',
            confirmButtonColor: '#3085d6'
        }).then(() => {
            $('#addSessionCaisseModal').modal('hide');

            // Redirection ou rechargement
            if (response.redirect) {
                window.location.href = response.redirect;
            } else {
                window.location.reload();
            }
        });
    } else {
        showError(response.message || 'Une erreur inattendue est survenue');
    }
}

/**
 * Gestion des erreurs de la requête
 */
function handleError(xhr) {
    let errorMessage = 'Une erreur est survenue';

    if (xhr.responseJSON) {
        if (xhr.responseJSON.errors) {
            // Rassembler toutes les erreurs de validation
            errorMessage = Object.values(xhr.responseJSON.errors)
                .flat()
                .join('\n');
        } else if (xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
    }

    showError(errorMessage);
}

/**
 * Affichage d'une erreur avec SweetAlert2
 */
function showError(message) {
    Swal.fire({
        title: 'Erreur !',
        html: message.replace(/\n/g, '<br>'),
        icon: 'error',
        confirmButtonColor: '#3085d6'
    });
}

/**
 * Active/Désactive le bouton de soumission
 */
function toggleSubmitButton(disabled = true) {
    const btn = $('#saveSessionBtn');
    btn.prop('disabled', disabled);

    if (disabled) {
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Traitement...');
    } else {
        btn.html('<i class="fas fa-check-circle me-1"></i> Ouvrir la session');
    }
}

/**
 * Réinitialisation du formulaire
 */
function resetForm() {
    const form = $('#addSessionCaisseForm')[0];
    form.reset();
    toggleSubmitButton(false);
}


function fermerSession(sessionId) {
    Swal.fire({
        title: 'Fermeture de session',
        html: `
            <form id="fermetureForm" class="text-left">
                <div class="mb-3">
                    <label class="form-label">Montant de fermeture <span class="text-danger">*</span></label>
                    <input type="number" id="montant_fermeture" class="form-control" required min="0" step="100">
                </div>
                <div class="mb-3">
                    <label class="form-label">Comptage des billets et pièces</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" class="form-control mb-2" placeholder="Qté billets 10000"
                                   onchange="calculerTotal()" data-valeur="10000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control mb-2" placeholder="Qté billets 5000"
                                   onchange="calculerTotal()" data-valeur="5000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control mb-2" placeholder="Qté billets 2000"
                                   onchange="calculerTotal()" data-valeur="2000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control mb-2" placeholder="Qté billets 1000"
                                   onchange="calculerTotal()" data-valeur="1000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control" placeholder="Qté pièces 500"
                                   onchange="calculerTotal()" data-valeur="500" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" class="form-control" placeholder="Qté pièces 100"
                                   onchange="calculerTotal()" data-valeur="100" min="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observations</label>
                    <textarea id="observations_fermeture" class="form-control" rows="3"></textarea>
                </div>
            </form>
        `,
        confirmButtonText: 'Fermer la session',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        focusConfirm: false,
        preConfirm: () => {
            // Récupération des valeurs
            const montantFermeture = document.getElementById('montant_fermeture').value;
            const observationsFermeture = document.getElementById('observations_fermeture').value;

            // Récupération des quantités
            const quantites = {};
            document.querySelectorAll('#fermetureForm input[data-valeur]').forEach(input => {
                const valeur = input.getAttribute('data-valeur');
                quantites[valeur] = parseInt(input.value || 0);
            });

            // Validation basique
            if (!montantFermeture || montantFermeture <= 0) {
                Swal.showValidationMessage('Le montant de fermeture est requis et doit être supérieur à 0');
                return false;
            }

            return {
                montant_fermeture: montantFermeture,
                observations_fermeture: observationsFermeture,
                quantites: quantites
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Envoi de la requête AJAX
            $.ajax({
                url: `/vente/sessions/${sessionId}/fermer`,
                type: 'POST',
                data: {
                    ...result.value,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Erreur', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let message = 'Une erreur est survenue';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire('Erreur', message, 'error');
                }
            });
        }
    });
}

</script>
