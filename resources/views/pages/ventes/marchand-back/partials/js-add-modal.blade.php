// Fonction pour gérer l'affichage du montant restant
function updateMontantRestant() {
    const montantTTC = extractMontant(document.getElementById('totalTTC').textContent);
    const montantRegle = parseFloat(document.getElementById('montantRegle').value) || 0;
    const restant = montantRegle - montantTTC;

    const montantRestantElement = document.getElementById('montantRestant');
    const messageRestantElement = document.getElementById('messageRestant');

    // Formatage du montant
    const montantAffiche = formatMontant(Math.abs(restant));

    // Définition des classes et messages selon le montant
    let messageText, messageClass, montantClass;

    if (restant > 0) {
        messageText = "À rendre au client";
        messageClass = "text-success";
        montantClass = "text-success";
    } else if (restant < 0) {
        messageText = "Dû par le client";
        messageClass = "text-danger";
        montantClass = "text-danger";
    } else {
        messageText = "Compte exact";
        messageClass = "text-primary";
        montantClass = "text-primary";
    }

    // Mise à jour des éléments
    if (messageRestantElement) {
        messageRestantElement.textContent = messageText;
        // Supprime toutes les classes de couleur précédentes
        messageRestantElement.classList.remove('text-success', 'text-danger', 'text-primary');
        messageRestantElement.classList.add(messageClass);
    }

    if (montantRestantElement) {
        montantRestantElement.textContent = `${montantAffiche} FCFA`;
        // Supprime toutes les classes de couleur précédentes
        montantRestantElement.classList.remove('text-success', 'text-danger', 'text-primary');
        montantRestantElement.classList.add(montantClass);

        // Animation de changement
        montantRestantElement.classList.add('scale-animation');
        setTimeout(() => {
            montantRestantElement.classList.remove('scale-animation');
        }, 300);
    }
}

// Ajouter au code existant
document.addEventListener('DOMContentLoaded', function() {
    // Écouteur pour le montant réglé
    const montantRegleInput = document.getElementById('montantRegle');
    if (montantRegleInput) {
        montantRegleInput.addEventListener('input', function() {
            updateMontantRestant();
        });
    }

    // Mettre à jour le montant restant quand les totaux changent
    const originalUpdateTotaux = window.updateTotaux;
    window.updateTotaux = function() {
        originalUpdateTotaux();
        updateMontantRestant();
    };
});

// Ajouter le style pour l'animation
const style = document.createElement('style');
style.textContent = `
    .scale-animation {
        animation: scale 0.3s ease-in-out;
    }

    @keyframes scale {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    #montantRestant {
        transition: color 0.3s ease;
    }
`;
document.head.appendChild(style);
