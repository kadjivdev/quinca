<script>
    // Constantes pour les URLs
const SESSION_CAISSE_URL = {
    store: '/vente/sessions',
    fermer: (id) => `/vente/sessions/${id}/fermer`,
    comptage: (id) => `/vente/sessions/${id}/comptage`,
    stats: (id) => `/vente/sessions/${id}/stats`,
    rapport: (id) => `/vente/sessions/${id}/rapport`,
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
    $('#addReferenceRecuForm').on('submit', function(event) {
        event.preventDefault();
        encaisserProcess(event);
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

function encaisserProcess(event) {
    event.preventDefault();

    toggleSubmitButton(true);

    const formData = $("#addReferenceRecuForm").serialize();
    const id = $("#facture_id").val();

    try {
        $.ajax({
            url: `${apiUrl}/vente/sessions/vente/${id}/encaisser`,
            type: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Vente encaissée avec succès',
                });

                setTimeout(() => {
                    window.location.reload(); 
                }, 1500);
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Une erreur est survenue lors de l\'encaissement.';
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: message,
                });
            },
            complete: function() {
                toggleSubmitButton(false);
            }
        });
        
    } catch (error) {
        Swal.fire({
            title: 'Erreur !',
            text: 'Erreur survenue',
            icon: 'error',
            confirmButtonColor: '#3085d6'
        });
    }

    
}

function validateForm() {
    if (!$('#latitude').val() || !$('#longitude').val()) {
        showError('La position géographique est requise pour ouvrir une session');
        return false;
    }
    return true;
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
    const btn = $('#saveReferenceRecuBtn');
    btn.prop('disabled', disabled);
    btn.html(disabled ?
        '<i class="fas fa-spinner fa-spin me-1"></i> Traitement...' :
        '<i class="fas fa-check-circle me-1"></i> Valider'
    );
}

async function encaisser(id){
    try {
        const result = await Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Voulez-vous encaisser cette vente ? Cette opération est irréversible !",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, encaisser',
            cancelButtonText: 'Annuler',
        });

        if (result.isConfirmed) {
            $("#facture_id").val(id);
            

            $("#addReferenceRecuModal").modal('show');
        }
    } catch (error) {
        console.error('Erreur:', error);
        Toast.fire({
            icon: 'error',
            title: error.message || 'Une erreur est survenue lors de l\'encaissement'
        });
    }

}
    
function deleteFacture(id) {
    Swal.fire({
        title: 'Êtes-vous sûr?',
        text: "Cette action est irréversible!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer!',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            // Récupérer le token CSRF
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // Envoyer la requête de suppression
            fetch(`${apiUrl}/vente/factures/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire(
                        'Supprimé!',
                        data.message,
                        'success'
                    ).then(() => {
                        // Recharger la page ou mettre à jour la liste
                        window.location.reload();
                    });
                } else {
                    Swal.fire(
                        'Erreur!',
                        data.message,
                        'error'
                    );
                }
            })
            .catch(error => {
                Swal.fire(
                    'Erreur!',
                    'Une erreur est survenue lors de la suppression',
                    'error'
                );
                console.error('Erreur:', error);
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    let selectedUrlA4 = "";
    let selectedUrlA5 = "";

    document.querySelectorAll(".print-bon").forEach(button => {
        button.addEventListener("click", function () {
            selectedUrlA4 = this.getAttribute("data-url-a4");
            selectedUrlA5 = this.getAttribute("data-url-a5");
        });
    });

    document.getElementById("btn-print-a4").addEventListener("click", function () {
        if (selectedUrlA4) {
            window.open(selectedUrlA4, "_blank");
        }
    });

    document.getElementById("btn-print-a5").addEventListener("click", function () {
        if (selectedUrlA5) {
            window.open(selectedUrlA5, "_blank");
        }
    });
});

</script>
