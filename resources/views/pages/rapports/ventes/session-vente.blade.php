@extends('layouts.rapport.facture')
@section('title', 'Rapport de Session')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <header class="bg-white shadow-sm rounded-3 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-cash-register text-primary me-2"></i>Rapport de Session
            </h1>
        </div>
    </header>

    <!-- Session Selector Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form id="sessionForm" class="row g-3 align-items-end" action="{{ route('vente.sessions.rapport') }}" method="GET">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-dark">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>Session
                    </label>
                    <select name="session_id" id="session_id" class="form-select select2">
                        <option value="">Session courante</option>
                        @foreach($sessions as $s)
                        <option value="{{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>
                            Session #{{ $s->id }} - Ouverte le: {{ $s->date_ouverture->format('d/m/Y H:i') }} -Fermée le : {{ $s->date_fermeture?->format('d/m/Y H:i') }} - Par: {{$s->utilisateur->name}} | Statut: ({{$s->statut}})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">
                        <i class="fas fa-calendar me-2"></i>Date début
                    </label>
                    <input type="date" class="form-control" name="date_debut"
                        value="{{ $dateDebut->format('Y-m-d') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-dark">
                        <i class="fas fa-calendar me-2"></i>Date fin
                    </label>
                    <input type="date" class="form-control" name="date_fin"
                        value="{{ $dateFin->format('Y-m-d') }}">
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <!-- Solde Initial -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Solde Initial</h6>
                            <h3 class="mb-0">{{ number_format($session->montant_ouverture, 0, ',', ' ') }} F</h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-money-bill text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Encaissements -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Total Encaissements</h6>
                            <h3 class="mb-0">{{ number_format($session->factures->sum('montant_ttc')-$session->factures->sum('montant_remise'), 0, ',', ' ') }} F</h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-cash-register text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Solde Théorique</h6>
                            <h3 class="mb-0">{{ number_format($session->solde_theorique, 0, ',', ' ') }} F</h3>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-calculator text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($session->statut === 'fermee')
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted fw-normal mb-2">Écart</h6>
                            <h3 class="mb-0 {{ $session->ecart >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $session->ecart >= 0 ? '+' : '' }}{{ number_format($session->ecart, 0, ',', ' ') }} F
                            </h3>
                        </div>
                        <div class="rounded-circle bg-{{ $session->ecart >= 0 ? 'success' : 'danger' }} bg-opacity-10 p-3">
                            <i class="fas fa-balance-scale text-{{ $session->ecart >= 0 ? 'success' : 'danger' }} fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif -->
    </div>

    <div class="row g-4">
        <!-- Liste des factures -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice text-primary me-2"></i>
                            Factures <span class="badge bg-primary ms-2">{{ $session->factures->count() }}</span>
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="example1">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Caisse/Session</th>
                                    <th>Client</th>
                                    <th class="text-end">Montant TTC</th>
                                    <th class="text-end">Insére le</th>
                                    <th class="text-end">Statut</th>
                                    <th class="text-end">Réglé</th>
                                    <th class="text-center">Etat</th>
                                    <th class="text-end">Insére par</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($session->factures as $facture)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-file-alt text-muted me-2"></i>
                                            {{ $facture->numero }}
                                        </span>
                                    </td>
                                    <td>
                                        caisse: {{$facture->sessionCaisse->caisse?->libelle}}/
                                        Ouverture: {{$facture->sessionCaisse?->montant_ouverture}} -
                                        Fermeture: {{$facture->sessionCaisse->montant_fermeture??00}}
                                    </td>
                                    <td>
                                        <i class="fas fa-user text-muted me-2"></i>
                                        {{ $facture->client->raison_sociale }}
                                    </td>
                                    <td class="text-end">{{ number_format($facture->montant_ttc-$facture->montant_remise, 0, ',', ' ') }} F</td>
                                    <td class="text-end">{{ $facture->created_at }} </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $facture->validated_by ? 'success' : 'warning' }} rounded-pill">
                                            <i class="fas fa-{{ $facture->est_solde ? 'check-circle' : 'clock' }} me-1"></i>
                                            {{ $facture->validated_by ? 'Vaildée' : 'Pas validée' }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($facture->montant_regle, 0, ',', ' ') }} F</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $facture->est_solde ? 'success' : 'warning' }} rounded-pill">
                                            <i class="fas fa-{{ $facture->est_solde ? 'check-circle' : 'clock' }} me-1"></i>
                                            {{ $facture->est_solde ? 'Soldée' : 'En cours' }}
                                        </span>
                                    </td>
                                    <td class="text-end"> <span class="badge bg-light text-dark border"> {{ $facture->createdBy->name }}</span> </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .select2-container--bootstrap4 .select2-selection--single {
        height: 38px;
        line-height: 1.5;
        padding: 0.375rem 0.75rem;
    }

    .card {
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }

    .table> :not(caption)>*>* {
        padding: 1rem 1rem;
    }

    .rounded-circle {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Configuration Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Sélectionnez une session',
            language: 'fr'
        });

        // Configuration DataTables commune
        const dataTableConfig = {
            language: {
                url: '/js/datatables-fr.json'
            },
            pageLength: 10,
            order: [
                [0, 'desc']
            ],
            responsive: true
        };

        // Gestion du formulaire de filtrage
        $('#sessionForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const searchParams = new URLSearchParams(formData);

            window.location.href = `{{ route('vente.sessions.rapport') }}?${searchParams.toString()}`;
        });
    });

    // DATATABLE
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [4, 'desc']
        ],
        "pageLength": 15,
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