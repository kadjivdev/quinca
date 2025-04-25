<script>
    // Initialisation des variables globales
    let factureModal;

    // Initialisation au chargement du document
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le modal Bootstrap
        factureModal = new bootstrap.Modal(document.getElementById('showFactureModal'));

        // Initialiser les tooltips
        initTooltips();
    });

    // Fonction principale pour afficher la facture
    function showFacture(id) {
        // Vérification de l'ID
        if (!id) {
            showError('ID de facture invalide');
            return;
        }

        // Afficher l'animation de chargement
        Swal.fire({
            title: 'Chargement...',
            html: 'Récupération des détails de la facture',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Faire la requête AJAX
        $.ajax({
            url: `${apiUrl}/vente/factures/${id}`,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Fermer le loader
                Swal.close();

                if (response.status === 'success') {
                    // Mettre à jour le contenu du modal
                    updateModalContent(response.data);

                    // Afficher le modal
                    factureModal.show();
                } else {
                    showError('Erreur lors du chargement des détails');
                }
            },
            error: function(xhr) {
                Swal.close();
                showError('Erreur de communication avec le serveur');
                console.error('Erreur AJAX:', xhr);
            }
        });
    }

    // Fonction pour mettre à jour le contenu du modal
    function updateModalContent(data) {
        const facture = data.facture;

        // En-tête modal
        $('#numeroFacture').text(facture.numero);
        $('#dateFacture').text(data.dateFacture);

        // Contenu HTML
        let contenuHtml = `
       <div class="row g-4">
           <!-- Section Client -->
           <div class="col-md-6">
               <div class="card border-0 shadow-sm h-100">
                   <div class="card-body">
                       <h6 class="fw-bold mb-3">
                           <i class="fas fa-user-circle text-primary me-2"></i>
                           Information Client
                       </h6>
                       <div class="mb-3">
                           <label class="text-muted small mb-1">Raison Sociale</label>
                           <p class="fw-medium mb-0">${facture.client.raison_sociale}</p>
                       </div>
                       <div class="mb-3">
                           <label class="text-muted small mb-1">Contact</label>
                           <p class="fw-medium mb-0">${facture.client.telephone || 'Non renseigné'}</p>
                       </div>
                       <div class="mb-0">
                           <label class="text-muted small mb-1">AIB Client</label>
                           <p class="fw-medium mb-0">${data.tauxAIB || 0}%</p>
                       </div>
                   </div>
               </div>
           </div>

           <!-- Section Détails -->
           <div class="col-md-6">
               <div class="card border-0 shadow-sm h-100">
                   <div class="card-body">
                       <h6 class="fw-bold mb-3">
                           <i class="fas fa-info-circle text-primary me-2"></i>
                           Détails Facture
                       </h6>
                       <div class="row g-3">
                           <div class="col-6">
                               <label class="text-muted small mb-1">Statut</label>
                               <div>${getStatusBadge(facture.statut)}</div>
                           </div>
                           <div class="col-6">
                               <label class="text-muted small mb-1">Date Échéance</label>
                               <p class="fw-medium mb-0">${data.dateEcheance}</p>
                           </div>
                       </div>
                   </div>
               </div>
           </div>

           <!-- Section Taux -->
           <div class="col-12">
               <div class="card border-0 shadow-sm">
                   <div class="card-body">
                       <h6 class="fw-bold mb-3">
                           <i class="fas fa-percentage text-primary me-2"></i>
                           Taux Appliqués
                       </h6>
                       <div class="row g-3">
                           <div class="col-md-4">
                               <div class="p-3 rounded bg-light">
                                   <small class="text-muted d-block">TVA</small>
                                   <span class="fw-bold">${data.tauxTVA}%</span>
                               </div>
                           </div>
                           <div class="col-md-4">
                               <div class="p-3 rounded bg-light">
                                   <small class="text-muted d-block">AIB</small>
                                   <span class="fw-bold">${data.tauxAIB}%</span>
                                   <i class="fas fa-info-circle ms-2"
                                      data-bs-toggle="tooltip"
                                      title="Acompte sur Impôt sur les Bénéfices"></i>
                               </div>
                           </div>
                           <div class="col-md-4">
                               <div class="p-3 rounded bg-light">
                                   <small class="text-muted d-block">Remise Moyenne</small>
                                   <span class="fw-bold">${getMoyenneRemise(facture.lignes)}%</span>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>
           </div>

           <!-- Section Articles -->
           <div class="col-12">
               <div class="card border-0 shadow-sm">
                   <div class="card-body">
                       <h6 class="fw-bold mb-3">
                           <i class="fas fa-shopping-cart text-primary me-2"></i>
                           Articles
                       </h6>
                       <div class="table-responsive">
                           <table class="table table-bordered table-hover mb-0">
                               <thead class="bg-light">
                                   <tr>
                                       <th>Article</th>
                                       <th>Dépôt</th>
                                       <th class="text-end">Prix Unit. HT</th>
                                       <th class="text-center">Quantité</th>
                                       <th class="text-end">Remise</th>
                                       <th class="text-end">Total HT</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   ${generateLignesFacture(facture.lignes)}
                               </tbody>
                               <tfoot class="bg-light">
                                   ${generateTotaux(data)}
                               </tfoot>
                           </table>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   `;

        $('#factureDetails').html(contenuHtml);
        initTooltips();
    }

    // Fonction pour générer le badge de statut
    function getStatusBadge(statut) {
        const statusClasses = {
            'brouillon': 'bg-warning text-warning',
            'validee': 'bg-success text-success',
            'partiellement_payee': 'bg-info text-info',
            'payee': 'bg-success text-success',
            'annulee': 'bg-danger text-danger'
        };

        const statusLabels = {
            'brouillon': 'Brouillon',
            'validee': 'Validée',
            'partiellement_payee': 'Partiellement payée',
            'payee': 'Payée',
            'annulee': 'Annulée'
        };

        const className = statusClasses[statut] || 'bg-secondary';
        const label = statusLabels[statut] || 'Indéfini';

        return `<span class="badge ${className} bg-opacity-10 ">${label}</span>`;
    }

    // Fonction pour générer les lignes de facture
    function generateLignesFacture(lignes) {
        return lignes.map(ligne => `
            <tr>
                <td>${ligne.article.designation}</td>
                <td>${ligne.facturedepot.libelle_depot}</td>
                <td class="text-end">${formatMontant(ligne.prix_unitaire_ht)} FCFA</td>
                <td class="text-center">${formatQuantite(ligne.quantite)} ${ligne.unite_vente.libelle_unite}</td>
                <td class="text-end">${formatTaux(ligne.taux_remise)}%</td>
                <td class="text-end">${formatMontant(ligne.montant_ht)} FCFA</td>
            </tr>
        `).join('');
    }

    // Fonction pour générer les totaux
    function generateTotaux(data) {
        return `
       <tr>
           <td colspan="5" class="text-end fw-bold">Total HT</td>
           <td class="text-end fw-bold">${data.montantHT} FCFA</td>
       </tr>
       <tr>
           <td colspan="5" class="text-end fw-bold">Remise Globale</td>
           <td class="text-end text-danger fw-bold">-${formatMontant(data.facture.montant_remise)} FCFA</td>
       </tr>
       <tr>
           <td colspan="5" class="text-end fw-bold">Total HT après remise</td>
           <td class="text-end fw-bold">${formatMontant(data.facture.montant_ht_apres_remise)} FCFA</td>
       </tr>
       <tr>
           <td colspan="5" class="text-end fw-bold">TVA (${data.tauxTVA}%)</td>
           <td class="text-end fw-bold">${data.montantTVA} FCFA</td>
       </tr>
       <tr>
           <td colspan="5" class="text-end fw-bold">
               AIB (${data.tauxAIB}%)
               <i class="fas fa-info-circle ms-2" data-bs-toggle="tooltip" title="Acompte sur Impôt sur les Bénéfices"></i>
           </td>
           <td class="text-end fw-bold">${formatMontant(data.facture.montant_aib)} FCFA</td>
       </tr>
       <tr class="table-active">
           <td colspan="5" class="text-end fw-bold">Total TTC</td>
           <td class="text-end fw-bold">${data.montantTTC} FCFA</td>
       </tr>
       <tr>
           <td colspan="5" class="text-end fw-bold">Montant Réglé</td>
           <td class="text-end text-success fw-bold">${data.montantRegle} FCFA</td>
       </tr>
       <tr>
           <td colspan="5" class="text-end fw-bold">Reste à Payer</td>
           <td class="text-end text-danger fw-bold">${data.montantRestant} FCFA</td>
       </tr>
   `;
    }


    function getMoyenneRemise(lignes) {
        if (!lignes?.length) return '0.00';
        const total = lignes.reduce((sum, l) => sum + (l.taux_remise || 0), 0);
        return formatTaux(total / lignes.length);
    }


    // Fonctions utilitaires
    function formatMontant(montant) {
        return new Intl.NumberFormat('fr-FR').format(montant);
    }

    function formatQuantite(quantite) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(quantite);
    }

    function formatTaux(taux) {
        return new Intl.NumberFormat('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(taux);
    }

    // Fonction pour afficher les erreurs
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    // Initialisation des tooltips
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
</script>