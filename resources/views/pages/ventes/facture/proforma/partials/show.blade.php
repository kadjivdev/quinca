@extends('layouts.ventes.facture')
@section('content')
<div class="content">
    <div class="page-header mb-4">
        <div class="container-fluid p-0">
            {{-- Header du modal avec un nouveau design --}}
            <div class="modal-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-file-invoice fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Détails Proforma du <strong class="reference badge bg-dark">{{$devis->reference}}</strong> </h5>
                        <p class="text-muted small mb-0">Remplissez les informations ci-dessous pour créer une nouvelle
                            facture</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--  -->
            <div class="row">
                <div class="col-12">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex text-right" style="justify-content: end;">
                <a href="{{route('proforma.create')}}" class="btn btn-md btn-primary"><i class="bi bi-arrow-left-circle-fill"></i> Retour</a>
            </div>

            <form action="{{route('proforma.update',$devis->id)}}" method="POST" class="needs-validation" novalidate>
                @csrf
                @method("PATCH")
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
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Client</label>
                                            <div class="input-group">
                                                <select class="form-select ___select2" name="client_id" required>
                                                    <option> {{ $devis->client->raison_sociale }} </option>
                                                </select>
                                            </div>
                                            <div class="invalid-feedback">Le client est requis</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-medium required">Date facture</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">
                                                    <i class="fas fa-calendar-alt text-primary"></i>
                                                </span>

                                                <span class="bagde bg-dark text-white">{{$devis->date_devis}}</span>
                                            </div>
                                            <div class="invalid-feedback">La date est requise</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Section articles --}}
                        <div class="col-12">
                            <div class="card border border-light-subtle">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="editableTable" class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Article</th>
                                                    <th>Quantité</th>
                                                    <th>Prix Unit</th>
                                                    <th>Unité de mesure</th>
                                                    <th>Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody id="updateArticlesRows">
                                                @foreach($devis->details as $detail)
                                                <tr>
                                                    <td>{{$detail->article->designation}}<input type="hidden" required name="articles[]" value="{{$detail->article->id}}"></td>
                                                    <td>{{$detail->qte_cmde}} <input type="hidden" required name="qte_cdes[]" value="{{$detail->qte_cmde}}"> </td>
                                                    <td>{{$detail->prix_unit}} <input type="hidden" required name="prixUnits[]" value="{{$detail->prix_unit}}"> </td>
                                                    <td>{{$detail->mesureunit->libelle_unite}} <input type="hidden" required name="unites[]" value="{{$detail->mesureunit->id}}"> </td>
                                                    <td>{{$detail->qte_cmde*$detail->prix_unit}}<input type="hidden" required name="montants[]" value="{{$detail->qte_cmde*$detail->prix_unit}}"> </td>
                                                </tr>
                                                @endforeach
                                                <!-- Les lignes seront ajoutées ici -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push("scripts")
    <script>
        $(".___select2").select2({
            theme: 'bootstrap-5',
            width: '100%',
            // dropdownParent: updateFactureProformaModal,
        })
    </script>
    @endpush
    @endsection
</div>