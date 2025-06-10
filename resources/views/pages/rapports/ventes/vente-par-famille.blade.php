@extends('layouts.rapport.facture')

@section('title', 'Rapport des ventes par client')

@section('styles')
<style>
    .stats-card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s;
    }

    .stats-card:hover {
        transform: translateY(-3px);
    }

    .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold">
            <i class="fas fa-layer-group me-2"></i>Rapport des ventes par famille
        </h2>
    </div>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="filterForm">
                        <div class="row mb-3">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>Période
                                </label>
                                <div class="input-group">
                                    <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
                                    <span class="input-group-text bg-light">au</span>
                                    <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted">
                                    <i class="fas fa-layer-group me-1"></i>Famille
                                </label>
                                <select name="famille_id" class="form-select select2">
                                    <option value="">Toutes les familles</option>
                                    @foreach($familles as $famille)
                                    <option value="{{ $famille->id }}" {{ $familleId == $famille->id ? 'selected' : '' }}>
                                        {{ $famille->code_famille }} - {{ $famille->libelle_famille }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>Filtrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total des ventes</h6>
                        <h3 class="mb-0">{{ number_format($articles->sum('qteTotalVendu'), 0, ',', ' ') }} F</h3>
                    </div>
                    <div class="stats-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Articles vendus</h6>
                        <h3 class="mb-0">{{ number_format($articles->count(), 0, ',', ' ') }}</h3>
                    </div>
                    <div class="stats-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-box fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Filtres -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted">
                        <i class="far fa-calendar-alt me-1"></i>Période
                    </label>
                    <div class="input-group">
                        <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
                        <span class="input-group-text bg-light">au</span>
                        <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted">
                        <i class="fas fa-layer-group me-1"></i>Famille
                    </label>
                    <select name="famille_id" class="form-select select2">
                        <option value="">Toutes les familles</option>
                        @foreach($familles as $famille)
                        <option value="{{ $famille->id }}" {{ $familleId == $famille->id ? 'selected' : '' }}>
                            {{ $famille->code_famille }} - {{ $famille->libelle_famille }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des résultats -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Détails par famille</h5>
                <button type="button" class="btn btn-success" id="exportExcel">
                    <i class="fas fa-file-excel me-1"></i>Exporter
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive bg-white p-2 shadow-sm">
                <table class="table table-hover" id="example1">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Article</th>
                            <th>Famille</th>
                            <th class="text-center">Quantité</th>
                            <th class="text-center">Unité</th>
                            <th class="text-center">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($articles as $article)
                        <tr>
                            <td>{{ $article->code_article }}</td>
                            <td>{{ $article->designation }}</td>
                            <td>{{ $article->famille->libelle_famille }}</td>
                            <td class="text-center">{{ number_format($article->qantiteBase,2,","," ") }}</td>
                            <td class="text-center">{{ $article->uniteMesure?->libelle_unite }}</td>
                            <td class="border p-1 m-0">
                                <ul class="mx-0" style="width:100%;height:100px!important;overflow-y:scroll;">
                                    @forelse($article->stocks as $stock)
                                    <li class="bg-warning rounded p-2" style="list-style-type: none">
                                        <h4 class="badge d-block text-dark border-bottom">Dépôt: {{$stock->depot->libelle_depot}}</h4>
                                        <span class="badge d-block d-flex text-dark">Qte vendue: {{number_format($stock->qteTotalVenduStock,2,'.','')}} ({{$stock->uniteMesure?->libelle_unite}})</span>
                                    </li>
                                    <hr>
                                    @empty
                                    <li class="text-center">Ce article n'est disponible dans aucun dépôt!</li>
                                    @endforelse
                                </ul>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            window.location.href = `{{ route('rapports.ventes-familles') }}?${new URLSearchParams(formData)}`;
        });
    });

    // datatable
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [0, 'asc']
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