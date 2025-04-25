document.addEventListener('DOMContentLoaded', function() {
    // Fonctions utilitaires
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(montant);
    }

    function extractMontant(montantFormate) {
        if (typeof montantFormate === 'string') {
            return parseFloat(montantFormate.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
        }
        return montantFormate || 0;
    }

    // Fonction de mise à jour des totaux
    function updateTotaux() {
        let totalHT = 0;
        
        // Calcul du total HT
        document.querySelectorAll('.ligne-facture').forEach(ligne => {
            const totalLigneElement = ligne.querySelector('.total-ligne');
            console.log('Élément ligne trouvé:', {
                element: totalLigneElement,
                valeur: totalLigneElement.value
            });
            
            const montantLigne = extractMontant(totalLigneElement.value);
            console.log('Montant extrait:', montantLigne);
            totalHT += montantLigne;
        });

        // Récupération des taux
        const tauxTva = {{ $tauxTva }};
        const clientSelect = document.querySelector('select[name="client_id"]');
        const selectedOption = clientSelect.selectedOptions[0];
        const tauxAib = selectedOption ? parseFloat(selectedOption.dataset.tauxAib) : 0;

        console.log('Taux récupérés:', {
            tva: tauxTva,
            aib: tauxAib
        });

        // Calcul des montants
        const montantTva = (totalHT * tauxTva) / 100;
        const montantAib = (totalHT * tauxAib) / 100;
        const montantTTC = totalHT + montantTva + montantAib;

        console.log('Montants calculés:', {
            ht: totalHT,
            tva: montantTva,
            aib: montantAib,
            ttc: montantTTC
        });

        // Mise à jour de l'affichage
        document.getElementById('totalHT').textContent = formatMontant(totalHT) + ' FCFA';
        document.getElementById('totalTVA').textContent = formatMontant(montantTva) + ' FCFA';
        document.getElementById('totalAIB').textContent = formatMontant(montantAib) + ' FCFA';
        document.getElementById('totalTTC').textContent = formatMontant(montantTTC) + ' FCFA';
    }

    // Écouteur pour le changement de client
    document.querySelector('select[name="client_id"]').addEventListener('change', function() {
        const selectedOption = this.selectedOptions[0];
        if (selectedOption) {
            console.log('Client sélectionné:', {
                id: selectedOption.value,
                nom: selectedOption.text,
                tauxAib: selectedOption.dataset.tauxAib
            });
        }
        updateTotaux();
    });

    // Écouteur pour les modifications des lignes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantite-input') || 
            e.target.classList.contains('remise-input') || 
            e.target.classList.contains('prix-input') ||
            e.target.classList.contains('total-ligne')) {
            console.log('Modification détectée sur:', e.target.className);
            updateTotaux();
        }
    });

    // Calcul du montant restant
    document.getElementById('montantRegle').addEventListener('input', function() {
        const montantTTC = extractMontant(document.getElementById('totalTTC').textContent);
        const montantRegle = parseFloat(this.value) || 0;
        const restant = montantRegle - montantTTC;
        
        console.log('Calcul du restant:', {
            ttc: montantTTC,
            regle: montantRegle,
            restant: restant
        });

        const montantRestantElement = document.getElementById('montantRestant');
        const messageRestantElement = document.getElementById('messageRestant');

        montantRestantElement.value = formatMontant(Math.abs(restant));

        if (restant > 0) {
            messageRestantElement.textContent = "À rendre au client";
            messageRestantElement.className = "text-success";
        } else if (restant < 0) {
            messageRestantElement.textContent = "Dû par le client";
            messageRestantElement.className = "text-danger";
        } else {
            messageRestantElement.textContent = "Compte exact";
            messageRestantElement.className = "text-primary";
        }
    });

    // Mise à jour lors de l'ajout d'une nouvelle ligne
    document.getElementById('btnAddLigne').addEventListener('click', function() {
        console.log('Nouvelle ligne ajoutée');
        setTimeout(updateTotaux, 100);
    });

    // Mise à jour lors de la modification des tarifs
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('select2-tarifs')) {
            console.log('Tarif modifié');
            setTimeout(updateTotaux, 100);
        }
    });
});