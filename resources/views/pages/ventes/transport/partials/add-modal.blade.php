<div class="modal fade" id="addTransportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header du modal --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-money-bill-wave fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Nouveau transport</h5>
                        <p class="text-muted small mb-0" id="factureInfo"></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="row p-3" action="#" id="addTransportForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <div class="col-6 mb-3">
                    <label for="montant">Montant</label>
                    <input type="number" class="form-control" name="montant" id="montant" required>
                </div>

                <div class="col-6 mb-3">
                    <label for="date_op">Date</label>
                    <input type="date" class="form-control" name="date_op" id="date_op" required>
                </div>

                <div class="col-12 mb-3">
                    <label for="client_id">Client</label>
                    <select required name="client_id" id="client_id" class="select2 form-select">
                        <option value="">Choisir l'article </option>
                        @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->raison_sociale }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mb-3">
                    <label for="observation">Observation</label>
                    <textarea class="form-control" name="observation" id="observation"></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" id="btnSaveTransport" class="w-50 btn btn-sm btn-primary">Enregistrer</button>
                    <div class="loader"></div>
                </div>
            </form>
        </div>
    </div>
</div>
<link href="{{ asset('css/theme/modal.css') }}" rel="stylesheet">
<style>
    .required:after {
        content: " *";
        color: red;
    }

    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f8f9fa;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
</style>