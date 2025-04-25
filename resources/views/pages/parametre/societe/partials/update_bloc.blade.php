{{-- update_bloc.blade.php --}}
<div class="col-12 col-lg-8">
    <div class="card border-0 shadow-lg hover-card">
        <div class="card-header bg-white py-3 border-0">
            <ul class="nav nav-pills nav-tabs-modern card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#basic-info">
                        <i class="fas fa-building me-2"></i>Informations de base
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#legal-info">
                        <i class="fas fa-balance-scale me-2"></i>Informations légales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#contact-info">
                        <i class="fas fa-address-card me-2"></i>Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#billing-info">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Facturation
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content">
                {{-- Informations de base --}}
                <div class="tab-pane fade show active" id="basic-info">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="nom_societe" name="nom_societe"
                                       value="{{ $Societe->nom_societe }}" required>
                                <label for="nom_societe" class="required">Nom de l'entreprise</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="raison_sociale" name="raison_sociale"
                                       value="{{ $Societe->raison_sociale }}">
                                <label for="raison_sociale">Raison sociale</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="forme_juridique" name="forme_juridique" value="{{ $Societe->forme_juridique }}">
                                <label for="forme_juridique">Forme juridique</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control modern-input" id="description" name="description"
                                          style="height: 100px">{{ $Societe->description }}</textarea>
                                <label for="description">Description</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Informations légales --}}
                <div class="tab-pane fade" id="legal-info">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="rccm" name="rccm"
                                       value="{{ $Societe->rccm }}">
                                <label for="rccm">RCCM</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="ifu" name="ifu"
                                       value="{{ $Societe->ifu }}"
                                       pattern="[0-9]{13}" title="L'IFU doit contenir 13 chiffres">
                                <label for="ifu">IFU</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="rib" name="rib"
                                       value="{{ $Societe->rib }}">
                                <label for="rib">RIB</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Informations de contact --}}
                <div class="tab-pane fade" id="contact-info">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control modern-input" id="telephone_1" name="telephone_1"
                                       value="{{ $Societe->telephone_1 }}" required>
                                <label for="telephone_1" class="required">Téléphone 1</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control modern-input" id="telephone_2" name="telephone_2"
                                       value="{{ $Societe->telephone_2 }}">
                                <label for="telephone_2">Téléphone 2</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control modern-input" id="email" name="email"
                                       value="{{ $Societe->email }}">
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="ville" name="ville"
                                       value="{{ $Societe->ville }}">
                                <label for="ville">Ville</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control modern-input" id="adresse" name="adresse"
                                          style="height: 100px">{{ $Societe->adresse }}</textarea>
                                <label for="adresse">Adresse</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Nouveau: Informations de facturation --}}
                <div class="tab-pane fade" id="billing-info">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control modern-input" id="tva" name="parametres_supplementaires[facturation][tva]"
                                       value="{{ $Societe->tva ?? '18' }}" min="0" max="100" step="0.5">
                                <label for="tva">Taux de TVA (%)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control modern-input" id="devise" name="parametres_supplementaires[facturation][devise]"
                                       value="{{ $Societe->devise ?? 'FCFA' }}">
                                <label for="devise">Devise</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-modern px-4">
                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
:root {
    --primary-color: #4361ee;
    --primary-hover: #3a51d6;
    --transition: all 0.3s ease;
    --border-radius: 12px;
    --input-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Card Hover Effect */
.hover-card {
    transition: var(--transition);
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12) !important;
}

/* Modern Tabs */
.nav-tabs-modern {
    border: none;
    margin-bottom: -1px;
}

.nav-tabs-modern .nav-link {
    border: none;
    border-radius: var(--border-radius);
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    color: #64748b;
    transition: var(--transition);
}

.nav-tabs-modern .nav-link:hover {
    color: var(--primary-color);
    background: rgba(67, 97, 238, 0.05);
}

.nav-tabs-modern .nav-link.active {
    color: var(--primary-color);
    background: rgba(67, 97, 238, 0.1);
}

/* Modern Inputs */
.form-floating {
    position: relative;
}

.modern-input {
    border-radius: var(--border-radius);
    border: 2px solid #e2e8f0;
    padding: 1rem;
    height: calc(3.5rem + 2px);
    box-shadow: var(--input-shadow);
    transition: var(--transition);
}

.modern-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
}

textarea.modern-input {
    min-height: 100px;
}

.form-floating > label {
    padding: 1rem;
}

/* Modern Button */
.btn-modern {
    border-radius: var(--border-radius);
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: var(--transition);
}

.btn-primary {
    background: var(--primary-color);
    border: none;
}

.btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
}

/* Required Field Indicator */
.required::after {
    content: '*';
    color: #e53e3e;
    margin-left: 4px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .nav-tabs-modern .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .card-body {
        padding: 1rem !important;
    }
}
</style>
@endpush
