<script>
    let reglementId = null;

    function showReglement(id) {
        reglementId = id;

        // Afficher le modal avec loader
        const modal = new bootstrap.Modal(document.getElementById('showReglementModal'));
        modal.show();

        // Afficher le loader, cacher les détails
        document.getElementById('loaderContainer').classList.remove('d-none');
        document.getElementById('reglementDetails').classList.add('d-none');

        // Afficher le toast de chargement
        const loadingToast = Toast.fire({
            title: 'Chargement des données...',
            icon: 'info',
            showConfirmButton: false,
            timer: false,
            timerProgressBar: true
        });

        // Charger les données
        fetch(`${apiUrl}/vente/reglement/${id}/details`)
            .then(response => response.json())
            .then(response => {
                if (!response.success) {
                    throw new Error(response.message);
                }

                const data = response.data;

                // Informations de base
                document.getElementById('showNumero').textContent = data.numero;
                document.getElementById('showDate').textContent = formatDate(data.date_reglement);

                // Client et avatar
                const clientName = data.facture.client.raison_sociale;
                document.getElementById('showClient').textContent = clientName;
                document.getElementById('showClientAvatar').textContent = getInitials(clientName);
                document.getElementById('showContact').textContent = data.facture.client.telephone || 'N/A';

                // Facture
                document.getElementById('showFacture').textContent = data.facture.numero;
                document.getElementById('showDateFacture').textContent = formatDate(data.facture.date_facture);

                // Paiement
                document.getElementById('showMode').textContent = formatMode(data.type_reglement);
                document.getElementById('showMontant').textContent = formatMontant(data.montant) + ' F';

                // Référence conditionnelle
                toggleBlock('referenceBlock', 'showReference', data.reference_preuve);

                // Banque conditionnelle
                toggleBlock('banqueBlock', 'showBanque', data.banque);

                // Statut et infos
                document.getElementById('showStatut').innerHTML = getStatusBadge(data.statut);
                document.getElementById('showCreatedBy').textContent = data.created_by.name;

                // Validation conditionnelle
                toggleBlock('validatedByBlock', 'showValidatedBy',
                    data.validated_by ? data.validated_by.name : null);

                // Cacher le loader, montrer les détails
                document.getElementById('loaderContainer').classList.add('d-none');
                document.getElementById('reglementDetails').classList.remove('d-none');

                // Fermer le toast de chargement et montrer le succès
                loadingToast.close();
                Toast.fire({
                    title: 'Données chargées avec succès',
                    icon: 'success',
                    timer: 1500
                });
            })
            .catch(error => {
                console.error('Erreur:', error);
                loadingToast.close();
                Toast.fire({
                    title: error.message || 'Erreur lors du chargement',
                    icon: 'error'
                });
                modal.hide();
            });
    }

    // Utilitaires
    function toggleBlock(blockId, contentId, value) {
        const block = document.getElementById(blockId);
        if (value) {
            block.style.display = 'block';
            document.getElementById(contentId).textContent = value;
        } else {
            block.style.display = 'none';
        }
    }

    function getInitials(name) {
        return name
            .split(' ')
            .map(word => word[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function formatMode(mode) {
        const modes = {
            'espece': 'Espèces',
            'cheque': 'Chèque',
            'virement': 'Virement',
            'carte_bancaire': 'Carte Bancaire',
            'MoMo': 'Mobile Money',
            'Flooz': 'Flooz',
            'Celtis_Pay': 'Celtis Pay'
        };
        return modes[mode] || mode;
    }

    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR').format(montant);
    }

    function getStatusBadge(statut) {
        const badges = {
            'brouillon': '<span class="badge bg-warning bg-opacity-10 text-warning px-3">Brouillon</span>',
            'validee': '<span class="badge bg-success bg-opacity-10 text-success px-3">Validé</span>',
            'annulee': '<span class="badge bg-danger bg-opacity-10 text-danger px-3">Annulé</span>'
        };
        return badges[statut] || '<span class="badge bg-secondary">Inconnu</span>';
    }
    </script>
