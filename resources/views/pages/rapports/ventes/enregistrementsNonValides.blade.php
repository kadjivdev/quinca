<!-- resources/views/pages/rapports/ventes/vente-journaliere.blade.php -->
@extends('layouts.rapport.facture')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <!-- En-tête avec filtres -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form action="{{ route('rapports._enregistrementsNonValides') }}" method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Date du rapport <span class="badge bg-dark">m/d/Y</span> </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-calendar"></i>
                                </span>
                                <input type="date"
                                    name="date"
                                    class="form-control"
                                    value="{{ request('date', now()->format('Y-m-d')) }}"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sync-alt me-2"></i>Actualiser
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Message d'alerte -->
            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Tableau des ventes -->
            <div class="card shadow-sm">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Rapport des enregistrements du {{ Carbon\Carbon::parse(request('date', now()))->format('d/m/Y') }}</h5>
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-excel me-2"></i>Exporter
                    </button>
                </div>
                <br>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="example1" class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>N°</th>
                                    <th>Date Écriture <span class="badge bg-dark">m/d/Y</span></th>
                                    <th>Date vente <span class="badge bg-dark">m/d/Y</span></th>
                                    <th>Référence</th>
                                    <th>Type vente</th>
                                    <th>Statut vente</th>
                                    <th>Catégorie vente</th>
                                    <th>Client</th>
                                    <th class="text-end">Montant TTC</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ventes as $vente)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $vente['date_ecriture'] }}</td>
                                    <td>{{ $vente['date_vente'] }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $vente['reference'] }}</span>
                                    </td>
                                    <td>
                                        @if($vente['type_vente'] === 'Comptant')
                                        <span class="badge bg-success">Comptant</span>
                                        @else
                                        <span class="badge bg-danger">Crédit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $vente['statut'] }} </span>
                                    </td>
                                    <td>{{ $vente['categorie_vente'] }}</td>
                                    <td>{{ $vente['client'] }}</td>
                                    <td class="text-end">
                                        {{ number_format($vente['montant_ttc'], 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-sm btn-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailsModal{{ $vente['id'] }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-info-circle me-2"></i>Aucune vente pour cette date
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="8" class="text-end">Total Global:</td>
                                    <td class="text-end">{{ number_format($totaux['total_global'], 0, ',', ' ') }} FCFA</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="text-end">Total Comptant:</td>
                                    <td class="text-end">{{ number_format($totaux['total_comptant'], 0, ',', ' ') }} FCFA</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="text-end">Total Crédit:</td>
                                    <td class="text-end">{{ number_format($totaux['total_credit'], 0, ',', ' ') }} FCFA</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals pour les détails -->
@foreach($ventes as $vente)
<div class="modal fade" id="detailsModal{{ $vente['id'] }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la facture {{ $vente['reference'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-end">Quantité</th>
                            <th class="text-end">Prix unitaire</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vente['lignes'] as $ligne)
                        <tr>
                            <td>{{ $ligne['produit'] }}</td>
                            <td class="text-end">{{ number_format($ligne['quantite'], 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($ligne['prix_unitaire'], 0, ',', ' ') }} FCFA</td>
                            <td class="text-end">{{ number_format($ligne['total'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Total TTC:</td>
                            <td class="text-end">{{ number_format($vente['montant_ttc'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Montant Réglé:</td>
                            <td class="text-end">{{ number_format($vente['montant_regle'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Reste à Payer:</td>
                            <td class="text-end">{{ number_format($vente['reste_a_payer'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
    .table> :not(caption)>*>* {
        padding: 1rem 1rem;
        background-color: transparent;
    }

    .table>thead {
        background-color: #f8f9fa;
    }

    .badge {
        padding: 0.5em 0.75em;
    }

    .card {
        border: none;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.querySelector('.btn-outline-primary');
        if (exportBtn) {
            exportBtn.addEventListener('click', function() {
                const date = document.querySelector('input[name="date"]').value;
                window.location.href = `/rapports/ventes-journalier/export?date=${date}`;
            });
        }
    });
</script>

<script>
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [0, 'asc']
        ],
        "pageLength": 15,
        // "columnDefs": [{
        //         "targets": 2,
        //         "orderable": false
        //     },
        //     {
        //         "targets": 0,
        //         "orderable": false
        //     }
        // ],
        language: {
            "emptyTable": "Aucune donnée disponible dans le tableau",
            "lengthMenu": "Afficher _MENU_ éléments",
            "loadingRecords": "Chargement...",
            "processing": "Traitement...",
            "zeroRecords": "Aucun élément correspondant trouvé",
            "paginate": {
                "first": "Premier",
                "last": "Dernier",
                "previous": "Précédent",
                "next": "Suiv"
            },
            "aria": {
                "sortAscending": ": activer pour trier la colonne par ordre croissant",
                "sortDescending": ": activer pour trier la colonne par ordre décroissant"
            },
            "select": {
                "rows": {
                    "_": "%d lignes sélectionnées",
                    "1": "1 ligne sélectionnée"
                },
                "cells": {
                    "1": "1 cellule sélectionnée",
                    "_": "%d cellules sélectionnées"
                },
                "columns": {
                    "1": "1 colonne sélectionnée",
                    "_": "%d colonnes sélectionnées"
                }
            },
            "autoFill": {
                "cancel": "Annuler",
                "fill": "Remplir toutes les cellules avec <i>%d<\/i>",
                "fillHorizontal": "Remplir les cellules horizontalement",
                "fillVertical": "Remplir les cellules verticalement"
            },
            "searchBuilder": {
                "conditions": {
                    "date": {
                        "after": "Après le",
                        "before": "Avant le",
                        "between": "Entre",
                        "empty": "Vide",
                        "equals": "Egal à",
                        "not": "Différent de",
                        "notBetween": "Pas entre",
                        "notEmpty": "Non vide"
                    },
                    "number": {
                        "between": "Entre",
                        "empty": "Vide",
                        "equals": "Egal à",
                        "gt": "Supérieur à",
                        "gte": "Supérieur ou égal à",
                        "lt": "Inférieur à",
                        "lte": "Inférieur ou égal à",
                        "not": "Différent de",
                        "notBetween": "Pas entre",
                        "notEmpty": "Non vide"
                    },
                    "string": {
                        "contains": "Contient",
                        "empty": "Vide",
                        "endsWith": "Se termine par",
                        "equals": "Egal à",
                        "not": "Différent de",
                        "notEmpty": "Non vide",
                        "startsWith": "Commence par"
                    },
                    "array": {
                        "equals": "Egal à",
                        "empty": "Vide",
                        "contains": "Contient",
                        "not": "Différent de",
                        "notEmpty": "Non vide",
                        "without": "Sans"
                    }
                },
                "add": "Ajouter une condition",
                "button": {
                    "0": "Recherche avancée",
                    "_": "Recherche avancée (%d)"
                },
                "clearAll": "Effacer tout",
                "condition": "Condition",
                "data": "Donnée",
                "deleteTitle": "Supprimer la règle de filtrage",
                "logicAnd": "Et",
                "logicOr": "Ou",
                "title": {
                    "0": "Recherche avancée",
                    "_": "Recherche avancée (%d)"
                },
                "value": "Valeur"
            },
            "searchPanes": {
                "clearMessage": "Effacer tout",
                "count": "{total}",
                "title": "Filtres actifs - %d",
                "collapse": {
                    "0": "Volet de recherche",
                    "_": "Volet de recherche (%d)"
                },
                "countFiltered": "{shown} ({total})",
                "emptyPanes": "Pas de volet de recherche",
                "loadMessage": "Chargement du volet de recherche..."
            },
            "buttons": {
                "copyKeys": "Appuyer sur ctrl ou u2318 + C pour copier les données du tableau dans votre presse-papier.",
                "collection": "Collection",
                "colvis": "Visibilité colonnes",
                "colvisRestore": "Rétablir visibilité",
                "copy": "Copier",
                "copySuccess": {
                    "1": "1 ligne copiée dans le presse-papier",
                    "_": "%ds lignes copiées dans le presse-papier"
                },
                "copyTitle": "Copier dans le presse-papier",
                "csv": "CSV",
                "excel": "Excel",
                "pageLength": {
                    "-1": "Afficher toutes les lignes",
                    "_": "Afficher %d lignes"
                },
                "pdf": "PDF",
                "print": "Imprimer"
            },
            "decimal": ",",
            "info": "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
            "infoEmpty": "Affichage de 0 à 0 sur 0 éléments",
            "infoThousands": ".",
            "search": "Rechercher:",
            "thousands": ".",
            "infoFiltered": "(filtrés depuis un total de _MAX_ éléments)",
            "datetime": {
                "previous": "Précédent",
                "next": "Suivant",
                "hours": "Heures",
                "minutes": "Minutes",
                "seconds": "Secondes",
                "unknown": "-",
                "amPm": [
                    "am",
                    "pm"
                ],
                "months": [
                    "Janvier",
                    "Fevrier",
                    "Mars",
                    "Avril",
                    "Mai",
                    "Juin",
                    "Juillet",
                    "Aout",
                    "Septembre",
                    "Octobre",
                    "Novembre",
                    "Decembre"
                ],
                "weekdays": [
                    "Dim",
                    "Lun",
                    "Mar",
                    "Mer",
                    "Jeu",
                    "Ven",
                    "Sam"
                ]
            },
            "editor": {
                "close": "Fermer",
                "create": {
                    "button": "Nouveaux",
                    "title": "Créer une nouvelle entrée",
                    "submit": "Envoyer"
                },
                "edit": {
                    "button": "Editer",
                    "title": "Editer Entrée",
                    "submit": "Modifier"
                },
                "remove": {
                    "button": "Supprimer",
                    "title": "Supprimer",
                    "submit": "Supprimer",
                    "confirm": {
                        "1": "etes-vous sure de vouloir supprimer 1 ligne?",
                        "_": "etes-vous sure de vouloir supprimer %d lignes?"
                    }
                },
                "error": {
                    "system": "Une erreur système s'est produite"
                },
                "multi": {
                    "title": "Valeurs Multiples",
                    "restore": "Rétablir Modification",
                    "noMulti": "Ce champ peut être édité individuellement, mais ne fait pas partie d'un groupe. ",
                    "info": "Les éléments sélectionnés contiennent différentes valeurs pour ce champ. Pour  modifier et "
                }
            }
        },
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
</script>
@endpush