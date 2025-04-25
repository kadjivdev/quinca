<div class="modal fade" id="updateFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-edit fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Modifier la Facture <span id="factureNumber"></span></h5>
                        <p class="text-muted small mb-0">Modifiez les informations de la facture</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="updateFactureForm" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="facture_id" id="factureId">

                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Informations Générales -->
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations Générales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Date facture</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_facture"
                                                    id="updateDateFacture" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Client</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-user text-primary"></i>
                                                </span>
                                                <select class="form-select" name="client_id" id="updateClientId"
                                                    required>
                                                    <option value="">Sélectionner un client</option>
                                                    @foreach ($clients as $client)
                                                        <option value="{{ $client->id }}"
                                                            data-taux-aib="{{ $client->taux_aib }}">
                                                            {{ $client->raison_sociale }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-medium required">Échéance</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-clock text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_echeance"
                                                    id="updateDateEcheance" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Articles -->
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i>Articles
                                    </h6>
                                    <button type="button" class="btn btn-warning btn-sm" id="btnUpdateAddLigne">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 30%">Article</th>
                                                    <th style="width: 20%">Tarif</th>
                                                    <th style="width: 15%">Quantité</th>
                                                    <th style="width: 10%">Remise (%)</th>
                                                    <th style="width: 25%">Total HT</th>
                                                    <th style="width: 5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="updateLignesContainer">
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total HT</td>
                                                    <td class="text-end fw-bold" id="updateTotalHT">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">TVA</td>
                                                    <td class="text-end fw-bold" id="updateTotalTVA">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">AIB</td>
                                                    <td class="text-end fw-bold" id="updateTotalAIB">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td colspan="4" class="text-end fw-bold">Total TTC</td>
                                                    <td class="text-end fw-bold" id="updateTotalTTC">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observations -->
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Observations
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="observations" id="updateObservations" rows="3"
                                        placeholder="Observations éventuelles"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="fas fa-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template pour une nouvelle ligne dans le formulaire de mise à jour -->
<template id="updateLigneFactureTemplate">
    <tr class="ligne-facture">
        <td>
            <select class="form-select select2-articles" name="lignes[__INDEX__][article_id]" required>
                <option value="">Sélectionner un article</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control text-end select2-tarifs"
                name="lignes[__INDEX__][tarification_id]" placeholder="0.00" required min="0.01" step="0.01">
            <div class="invalid-feedback">Le prix est requis</div>
        </td>
        <td>
            <div class="input-group">
                <input type="number" class="" name="lignes[__INDEX__][quantite]"
                    placeholder="0.00" required min="0.01" step="0.01">
                <select class="form-select unite-select" name="lignes[__INDEX__][unite_vente_id]" hidden required>
                    {{-- <option value="">Unité</option> --}}
                </select>
            </div>
        </td>
        <td>
            <input type="number" class="form-control text-end remise-input" name="lignes[__INDEX__][taux_remise]"
                min="0" max="100" step="0.01">
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">FCFA</span>
                <input type="text" class="form-control text-end total-ligne" readonly value="0">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-ligne">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize update modal functionality
        const updateFactureModal = document.getElementById('updateFactureModal');
        const updateForm = document.getElementById('updateFactureForm');

        // Function to load facture data into update modal
        window.loadFactureData = async function(factureId) {
            try {
                const response = await fetch(`/revendeurs/factures/${factureId}`);
                const data = await response.json();

                if (data.status === 'success') {
                    const facture = data.data.facture;

                    // Informations de base
                    document.getElementById('factureId').value = facture.id;
                    document.getElementById('factureNumber').textContent = facture.numero;
                    document.getElementById('updateDateFacture').value = facture.date_facture.split(
                        'T')[0];
                    document.getElementById('updateClientId').value = facture.client_id;
                    document.getElementById('updateDateEcheance').value = facture.date_echeance.split(
                        'T')[0];
                    document.getElementById('updateObservations').value = facture.observations || '';

                    // Mise à jour du select client
                    const clientSelect = document.getElementById('updateClientId');
                    $(clientSelect).val(facture.client_id).trigger('change');

                    // Vider le conteneur des lignes
                    const container = document.getElementById('updateLignesContainer');
                    container.innerHTML = '';

                    // Ajouter chaque ligne de facture
                    for (let i = 0; i < facture.lignes.length; i++) {
                        const ligne = facture.lignes[i];

                        // Créer nouvelle ligne
                        const newLine = createUpdateLigneFature(i);
                        container.appendChild(newLine);
                        const lineEl = container.lastElementChild;

                        // Configurer Select2 pour l'article
                        const articleSelect = lineEl.querySelector('[name$="[article_id]"]');
                        $(articleSelect).select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Sélectionner un article'
                        }).append(new Option(ligne.article.designation, ligne.article.id, true,
                            true)).trigger('change');

                        // Configurer Select2 pour le tarif
                        const tarifSelect = lineEl.querySelector('[name$="[tarification_id]"]');
                        // $(tarifSelect).select2({
                        //     theme: 'bootstrap-5',
                        //     placeholder: 'Sélectionner un tarif'
                        // });

                        // Charger les tarifs pour cet article
                        const tarifsResponse = await fetch(`${apiUrl}/revendeurs/factures/articles/${ligne.article.id}/tarifs`);
                        const tarifsData = await tarifsResponse.json();

                        if (tarifsData.status === 'success') {
                            tarifsData.data.tarifs.forEach(tarif => {
                                const option = new Option(tarif.text, tarif.id, false, tarif
                                    .id == ligne.tarification_id);
                                $(tarifSelect).append(option);
                            });
                            $(tarifSelect).val(ligne.tarification_id).trigger('change');
                        }

                        // Charger les unités pour cet article
                        const unitesResponse = await fetch(`${apiUrl}/revendeurs/factures/articles/${ligne.article.id}/unites`);
                        const unitesData = await unitesResponse.json();

                        if (unitesData.status === 'success') {
                            const uniteSelect = lineEl.querySelector('[name$="[unite_vente_id]"]');
                            unitesData.data.unites.forEach(unite => {
                                const option = new Option(unite.text, unite.id, false, unite
                                    .id == ligne.unite_vente_id);
                                $(uniteSelect).append(option);
                            });
                            $(uniteSelect).val(ligne.unite_vente_id).trigger('change');
                            uniteSelect.hidden = false;
                        }

                        // Définir les autres valeurs
                        lineEl.querySelector('[name$="[quantite]"]').value = ligne.quantite;
                        lineEl.querySelector('[name$="[tarification_id]"]').value = ligne.prix_unitaire_ht;
                        lineEl.querySelector('[name$="[taux_remise]"]').value = ligne.taux_remise || 0;

                        // Mettre à jour le total de la ligne
                        const totalInput = lineEl.querySelector('.total-ligne');
                        totalInput.value = parseFloat(ligne.montant_ht).toFixed(2);
                    }

                    // Mettre à jour les totaux
                    updateTotals(facture);

                    // Afficher le modal
                    const modal = new bootstrap.Modal(document.getElementById('updateFactureModal'));
                    modal.show();
                }
            } catch (error) {
                console.error('Error loading facture:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les données de la facture'
                });
            }
        };

        // Handle form submission
        updateForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const factureId = formData.get('facture_id');

            try {
                const response = await fetch(`${apiUrl}/revendeurs/factures/${factureId}/update`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: 'Facture mise à jour avec succès'
                    });

                    // Close modal and refresh page
                    bootstrap.Modal.getInstance(updateFactureModal).hide();
                    window.location.reload();
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Erreur lors de la mise à jour'
                    });
                }
            } catch (error) {
                console.error('Error updating facture:', error);
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors de la mise à jour'
                });
            }
        });

        // Add line button handler
        document.getElementById('btnUpdateAddLigne').addEventListener('click', function() {
            const container = document.getElementById('updateLignesContainer');
            const index = container.children.length;
            container.appendChild(createUpdateLigneFature(index));
            initializeSelect2();
        });
    });

    function createUpdateLigneFature(index) {
        const template = document.getElementById('updateLigneFactureTemplate');
        const clone = template.content.cloneNode(true);

        // Update all name attributes with correct index
        clone.querySelectorAll('[name*="__INDEX__"]').forEach(el => {
            el.name = el.name.replace('__INDEX__', index);
        });

        return clone;
    }

    function updateTotals(facture) {
        const rows = document.querySelectorAll('#updateLignesContainer .ligne-facture');
        let totalHT = 0;
        let tauxTVA = parseFloat(document.querySelector('[name="taux_tva"]')?.value || 18);
        let tauxAIB = parseFloat(document.querySelector('#updateClientId option:checked')?.dataset?.tauxAib || 0);

        rows.forEach(row => {
            const montantHT = parseFloat(row.querySelector('.total-ligne').value || 0);
            totalHT += montantHT;
        });

        const montantTVA = facture.montant_tva > 0 ?(totalHT * tauxTVA) / 100 : 0;
        const montantAIB = (totalHT * tauxAIB) / 100;
        const totalTTC = totalHT + montantTVA + montantAIB;

        // Mise à jour des affichages
        document.getElementById('updateTotalHT').textContent = `${totalHT.toFixed(2)} FCFA`;
        document.getElementById('updateTotalTVA').textContent = `${montantTVA.toFixed(2)} FCFA`;
        document.getElementById('updateTotalAIB').textContent = `${montantAIB.toFixed(2)} FCFA`;
        document.getElementById('updateTotalTTC').textContent = `${totalTTC.toFixed(2)} FCFA`;
    }



    // Re-use existing initialization functions
    function initializeSelect2() {
        // Initialize select2 for articles and tarifs
        $('.select2-articles').select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner un article',
            ajax: {
                url: `${apiUrl}/revendeur/articles/search`,
                dataType: 'json',
                delay: 250,
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        $('.select2-tarifs').select2({
            theme: 'bootstrap-5',
            placeholder: 'Sélectionner un tarif'
        });
    }

    function editFacture(id) {
        try {
            // Afficher un indicateur de chargement
            Swal.fire({
                title: 'Chargement...',
                html: '<div class="spinner-border text-primary" role="status"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            // Charger les données de la facture
            loadFactureData(id)
                .then(() => {
                    Swal.close();
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de charger les données de la facture'
                    });
                });
        } catch (error) {
            console.error('Erreur:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue'
            });
        }
    }
</script>
