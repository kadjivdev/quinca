<div class="modal fade" id="addFactureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95%; width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal avec un nouveau design --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouvelle Facture</h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer une nouvelle
                            facture</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="#" method="POST" id="addFactureForm" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4">
                        {{-- Section informations générales --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2"></i>Informations Générales
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Date facture</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_facture" required
                                                    value="{{ date('Y-m-d') }}">
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Client</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-user text-primary"></i>
                                                </span>
                                                <select class="form-select" name="client_id" required>
                                                    <option value="">Sélectionner un client</option>
                                                    @foreach ($clients as $client)
                                                        <option value="{{ $client->id }}"
                                                            data-taux-aib="{{ $client->taux_aib }}">
                                                            {{ $client->raison_sociale }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le client est requis</div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Échéance</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-clock text-primary"></i>
                                                </span>
                                                <input type="date" class="form-control" name="date_echeance"
                                                    value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="invalid-feedback">La date d'échéance est requise</div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-medium required">Type de Facture</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-file-invoice text-primary"></i>
                                                </span>
                                                <select class="form-select" name="type_facture" id="type_facture"
                                                    required>
                                                    <option value="">Sélectionner le type</option>
                                                    <option value="simple">Simple</option>
                                                    <option value="normaliser">Normalisée</option>
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le type de facture est requis</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section articles --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-box me-2"></i>Articles
                                    </h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="btnAddLigne">
                                        <i class="fas fa-plus me-2"></i>Ajouter un article
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 30%">Article</th>
                                                    <th style="width: 15%">Quantité</th>
                                                    <th style="width: 20%">Prix</th>
                                                    <th style="width: 10%">Remise (%)</th>
                                                    <th style="width: 25%">Total HT</th>
                                                    <th style="width: 5%"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="lignesContainer">
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total HT</td>
                                                    <td class="text-end fw-bold" id="totalHT">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">TVA
                                                        ({{ $tauxTva }}%)</td>
                                                    <td class="text-end fw-bold" id="totalTVA">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">AIB</td>
                                                    <td class="text-end fw-bold" id="totalAIB">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                                <tr class="table-light">
                                                    <td colspan="4" class="text-end fw-bold">Total TTC</td>
                                                    <td class="text-end fw-bold" id="totalTTC">0,00 FCFA</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    {{-- <div class="col-md-3">
                                        <label class="form-label fw-medium required">Magasin</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="fas fa-user text-primary"></i>
                                            </span>
                                            <select class="form-select" name="client_id" required>
                                                <option value="">Sélectionner un magasin</option>
                                                @foreach ($depots as $depot)
                                                    <option value="{{ $depot->id }}">
                                                        {{ $depot->libelle_depot }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="invalid-feedback">Le magasin est requis</div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>

                        {{-- Section règlement --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-money-bill-wave me-2"></i>Règlement
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Montant réglé</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-money-bill text-primary"></i>
                                                </span>
                                                <input type="number" class="form-control" name="montant_regle"
                                                    id="montantRegle" required min="0" step="0.01">
                                                <span class="input-group-text">FCFA</span>
                                            </div>

                                            <div id="champsBancaires" class="row g-3 mt-0" style="display: none;">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Banque</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white">
                                                            <i class="fas fa-university text-primary"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="nom_banque"
                                                            id="nomBanque">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label fw-medium">Référence</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white">
                                                            <i class="fas fa-hashtag text-primary"></i>
                                                        </span>
                                                        <input type="text" class="form-control"
                                                            name="reference_bancaire" id="referenceBancaire">
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Moyen de règlement</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-credit-card text-primary"></i>
                                                </span>

                                                <select class="form-select" name="moyen_reglement"
                                                    id="moyenReglement" required>
                                                    <option value="">Sélectionner</option>
                                                    <option value="espece">Espèce</option>
                                                    <option value="cheque">Chèque</option>
                                                    <option value="virement">Virement</option>
                                                    <option value="carte_bancaire">Carte Bancaire</option>
                                                    <option value="MoMo">MoMo</option>
                                                    <option value="Flooz">Flooz</option>
                                                    <option value="Celtis_Pay">Celtis Pay</option>
                                                    <option value="Effet">Effet</option>
                                                    <option value="Avoir">Avoir</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mt-5">
                                                <div class="d-flex justify-content-end align-items-center">
                                                    <div class="text-end">
                                                        <h3 id="messageRestant" class="fs-6 text-muted mb-1">Montant à
                                                            régler</h3>
                                                        <h2 id="montantRestant" class="fs-3 fw-bold mb-0">0,00 FCFA
                                                        </h2>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>




                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section observations --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-comment-alt me-2"></i>Observations
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="observations" rows="3" placeholder="Observations éventuelles"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 py-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Template pour une nouvelle ligne --}}
<template id="ligneFactureTemplate">
    <tr class="ligne-facture">
        <td>
            <select class="form-select select2-articles" name="lignes[__INDEX__][article_id]" required>
                <option value="">Sélectionner un article</option>
            </select>
            <div class="invalid-feedback">L'article est requis</div>
        </td>
        <td>
            <div class="input-group">
                <input type="number" class="form-control text-end quantite-input" name="lignes[__INDEX__][quantite]"
                    placeholder="0.00" required min="0.01" step="0.01">
                <select class="form-select unite-select" name="lignes[__INDEX__][unite_vente_id]" hidden required>
                    {{-- <option value="">Unité</option> --}}
                </select>
            </div>
            <div class="invalid-feedback">La quantité est requise</div>
        </td>
        <td>
            <input type="number" class="form-control text-end select2-tarifs"
                name="lignes[__INDEX__][tarification_id]" placeholder="0.00" required min="0.01" step="0.01">
            <div class="invalid-feedback">Le prix est requis</div>
        </td>
        <td>
            <input type="number" class="form-control text-end remise-input" name="lignes[__INDEX__][taux_remise]"
                placeholder="0.00" min="0" max="100" step="0.01">
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

</script>
