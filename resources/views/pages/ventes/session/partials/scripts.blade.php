<script>
    // Constantes pour les URLs
const SESSION_CAISSE_URL = {
    store: `${apiUrl}/vente/sessions`,
    fermer: (id) => `${apiUrl}/vente/sessions/${id}/fermer`,
    comptage: (id) => `${apiUrl}/vente/sessions/${id}/comptage`,
    stats: (id) => `${apiUrl}/vente/sessions/${id}/stats`,
    rapport: (id) => `${apiUrl}/vente/sessions/${id}/rapport`,
};

$(document).ready(function() {
    initializeFormHandlers();
    $('#addSessionCaisseModal').on('show.bs.modal', getLocation);
});

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                $('#latitude').val(position.coords.latitude);
                $('#longitude').val(position.coords.longitude);
            },
            function(error) {
                console.error('Erreur de géolocalisation:', error);
                showError('Impossible d\'obtenir votre position. Veuillez autoriser la géolocalisation.');
            }
        );
    } else {
        showError('La géolocalisation n\'est pas supportée par votre navigateur.');
    }
}

// Initialisation lors du chargement de la page
// $(document).ready(function() {
//     initializeFormHandlers();
// });

function initializeFormHandlers() {
    // Soumission du formulaire d'ouverture
    $('#addSessionCaisseForm').on('submit', function(event) {
        event.preventDefault();
        ouvrirSession(event);
    });

    // Reset du formulaire lors de la fermeture du modal
    $('#addSessionCaisseModal').on('hidden.bs.modal', function () {
        resetForm();
    });

    // Validation du montant d'ouverture
    $('#montant_ouverture').on('input', function() {
        let value = $(this).val();
        if (value < 0) {
            $(this).val(0);
        }
    });
}

function ouvrirSession(event) {
    event.preventDefault();

    if (!validateForm()) {
        return;
    }

    toggleSubmitButton(true);

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

function validateForm() {
    if (!$('#latitude').val() || !$('#longitude').val()) {
        // showError('La position géographique est requise pour ouvrir une session');
        // return false;
    }
    return true;
}

function fermerSession(sessionId) {
    if (!sessionId) {
        showError('ID de session invalide');
        return;
    }

    Swal.fire({
        title: 'Fermeture de session',
        html: `
            <form id="fermetureForm" class="text-left">
                <div class="mb-3">
                    <label class="form-label">Montant de fermeture <span class="text-danger">*</span></label>
                    <input type="number" id="montant_fermeture" name="montant_fermeture" class="form-control" required min="0" step="100">
                </div>
                <div class="mb-3">
                    <label class="form-label">Comptage des billets et pièces</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" name="quantites[10000]" class="form-control mb-2" placeholder="Qté billets 10000"
                                   onchange="calculerTotal()" data-valeur="10000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" name="quantites[5000]" class="form-control mb-2" placeholder="Qté billets 5000"
                                   onchange="calculerTotal()" data-valeur="5000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" name="quantites[2000]" class="form-control mb-2" placeholder="Qté billets 2000"
                                   onchange="calculerTotal()" data-valeur="2000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" name="quantites[1000]" class="form-control mb-2" placeholder="Qté billets 1000"
                                   onchange="calculerTotal()" data-valeur="1000" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" name="quantites[500]" class="form-control" placeholder="Qté pièces 500"
                                   onchange="calculerTotal()" data-valeur="500" min="0">
                        </div>
                        <div class="col-6">
                            <input type="number" name="quantites[100]" class="form-control" placeholder="Qté pièces 100"
                                   onchange="calculerTotal()" data-valeur="100" min="0">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observations</label>
                    <textarea id="observations_fermeture" name="observations_fermeture" class="form-control" rows="3"></textarea>
                </div>
            </form>
        `,
        confirmButtonText: 'Fermer la session',
        showCancelButton: true,
        cancelButtonText: 'Annuler',
        focusConfirm: false,
        preConfirm: () => {
            const form = document.getElementById('fermetureForm');
            const formData = new FormData(form);
            const data = {};
            const quantites = {};

            for(let [key, value] of formData.entries()) {
                if (key.startsWith('quantites[')) {
                    const valeur = key.match(/\[(\d+)\]/)[1];
                    quantites[valeur] = parseInt(value) || 0;
                } else {
                    data[key] = value;
                }
            }

            if (!data.montant_fermeture || data.montant_fermeture <= 0) {
                Swal.showValidationMessage('Le montant de fermeture est requis et doit être supérieur à 0');
                return false;
            }

            return {
                ...data,
                quantites: quantites
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: SESSION_CAISSE_URL.fermer(sessionId),
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ...result.value
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
                        showError(response.message);
                    }
                },
                error: handleError
            });
        }
    });
}

function calculerTotal() {
    let total = 0;
    document.querySelectorAll('#fermetureForm input[data-valeur]').forEach(input => {
        const valeur = parseInt(input.getAttribute('data-valeur'));
        const quantite = parseInt(input.value || 0);
        total += valeur * quantite;
    });

    document.getElementById('montant_fermeture').value = total;
}

function handleSuccess(response) {
    if (response.success) {
        Swal.fire({
            title: 'Succès !',
            text: response.message,
            icon: 'success',
            confirmButtonColor: '#3085d6'
        }).then(() => {
            $('#addSessionCaisseModal').modal('hide');
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

function handleError(xhr) {
    let errorMessage = 'Une erreur est survenue';

    if (xhr.responseJSON) {
        if (xhr.responseJSON.errors) {
            errorMessage = Object.values(xhr.responseJSON.errors)
                .flat()
                .join('\n');
        } else if (xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
    }

    showError(errorMessage);
}

function showError(message) {
    Swal.fire({
        title: 'Erreur !',
        html: message.replace(/\n/g, '<br>'),
        icon: 'error',
        confirmButtonColor: '#3085d6'
    });
}

function toggleSubmitButton(disabled = true) {
    const btn = $('#saveSessionBtn');
    btn.prop('disabled', disabled);
    btn.html(disabled ?
        '<i class="fas fa-spinner fa-spin me-1"></i> Traitement...' :
        '<i class="fas fa-check-circle me-1"></i> Ouvrir la session'
    );
}

function resetForm() {
    const form = $('#addSessionCaisseForm')[0];
    form.reset();
    toggleSubmitButton(false);
}
</script>
