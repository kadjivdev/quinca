@extends('layouts.rapport.facture')

@section('title', 'Rapport des Mouvements de Stock')
@section('content')
<div class="container-fluid">
    <br>

    <!-- Filtres -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <input type="hidden" name="depot_id" value="{{ $selectedDepot->id }}">

                <div class="col-md-4">
                    <label class="form-label">Période</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_debut">
                        <span class="input-group-text">au</span>
                        <input type="date" class="form-control" name="date_fin">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Entrées du mois</h6>
                    <h2 class="mb-0">{{ $stats['entrees']['nombre'] }}</h2>
                    <small>Valeur: {{ number_format($stats['entrees']['valeur'], 0, ',', ' ') }} FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Sorties du mois</h6>
                    <h2 class="mb-0">{{ $stats['sorties']['nombre'] }}</h2>
                    <small>Valeur: {{ number_format($stats['sorties']['valeur'], 0, ',', ' ') }} FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Stock Actuel</h6>
                    <h2 class="mb-0">{{ $stats['stock_actuel']['articles'] }} articles</h2>
                    <small>Valeur: {{ number_format($stats['stock_actuel']['valeur_totale'], 0, ',', ' ') }}
                        FCFA</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Articles Critiques</h6>
                    <h2 class="mb-0">{{ count($stats['articles_critiques']) }}</h2>
                    <small>En alerte ou sous minimum</small>
                </div>
            </div>
        </div>
    </div>

    <!--  -->
    <div class="row d-flex justify-content-center">
        <div class="col-6">
            <div class="border rounded shadow-sm p-2">
                <p class="text-center">Ce panel est devenu fonctionnel à la date du <em class="text-danger"> 13/05/2026</em> | Les qte de sortie sont devenue juste à partir du 02/07/2026</p>
                <form action="{{route('rapports.mouvement-stock')}}" method="get">
                    <select name="depot_id" value="{{old('depot_id')}}" class="form-control">
                        <option value="">Choisissez un dépôt ...</option>
                        @foreach($depots as $depot)
                        <option value="{{$depot->id}}">{{$depot->libelle_depot}}</option>
                        @endforeach
                    </select>
                    <select name="statut_id" value="{{old('statut_id')}}" class="form-control my-1">
                        <option value="">Choisissez un statut ...</option>
                        <option value="ENTREE">Entrée</option>
                        <option value="SORTIE">Sortie</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-success w-100 my-2">Filtrer</button>
                </form>
            </div>
        </div>
    </div>
    <br>

    <!-- Table des mouvements -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Mouvements de Stock</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="example1" class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Inseré le</th>
                            <th>Date de modification du stock de l'article dans le depot</th>
                            <th>Date</th>
                            <th>Inseré par:</th>
                            <th>Type</th>
                            <th>Article</th>
                            <th>Depôt</th>
                            <th>Unité</th>
                            <th class="text-end">Quantité</th>
                            <th class="text-end">Prix Unitaire</th>
                            <th>Document</th>
                            <th>Commentaire</th>
                            <th>Utilisateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mouvements as $mouvement)
                        <tr>
                            <td class="text-monospace">{{$mouvement->id}} | {{ $mouvement->code }}</td>
                            <td>{{ $mouvement->created_at->format('d/m/Y') }}</td>
                            <td>{{ $mouvement->stock_depot_updated_at->format('d/m/Y') }}</td>
                            <td>{{ $mouvement->date_mouvement->format('d/m/Y') }}</td>
                            <td><span class="badge bg-light border rounded text-dark"> {{ $mouvement->user?->name }} </span></td>
                            <td>
                                @switch($mouvement->type_mouvement)
                                @case('ENTREE')
                                <span class="badge bg-success">Entrée</span>
                                @break

                                @case('SORTIE')
                                <span class="badge bg-warning">Sortie</span>
                                @break

                                @case('TRANSFERT')
                                <span class="badge bg-info">Transfert</span>
                                @break

                                @default
                                <span class="badge bg-secondary">Ajustement</span>
                                @endswitch
                            </td>
                            <td>{{ $mouvement->article?->designation }} | {{$mouvement->article?->code_article}}</td>
                            <td>{{ $mouvement->depot?->libelle_depot }}</td>
                            <td>{{ $mouvement->article?->uniteMesure?->libelle_unite }}</td>
                            <td class="text-end"> {{ number_format($mouvement->quantite, 2, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($mouvement->prix_unitaire, 0, ',', ' ') }} FCFA
                            <td>{{ $mouvement->document_type }} {{ $mouvement->document_id }}</td>
                            <td>
                                <textarea class="form-control">{{$mouvement->notes?? '---'}}</textarea>
                            </td>
                            <td>{{ $mouvement->user?->name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .text-monospace {
        font-family: 'Monaco', 'Consolas', monospace;
    }

    .table-responsive {
        min-height: 300px;
    }

    .badge {
        font-size: 85%;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const inputs = form.querySelectorAll('input, select');

        inputs.forEach(input => {
            input.addEventListener('change', function() {
                refreshData();
            });
        });
    });

    function refreshData() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);

        window.location.href = `{{ route('rapports.stock.mouvements') }}?${params.toString()}`;
    }

    function exportStock() {
        const depot_id = document.querySelector('input[name="depot_id"]').value;
        window.location.href = `{{ route('rapports.stock.export') }}?depot_id=${depot_id}`;
    }

    function printStock() {
        const depot_id = document.querySelector('input[name="depot_id"]').value;
        window.open(`{{ route('rapports.stock.print') }}?depot_id=${depot_id}`, '_blank');
    }

    function exportMouvements() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        window.location.href = `{{ route('rapports.stock.export-mouvements') }}?${params.toString()}`;
    }


    // datatable
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["pdf", "print", "csv", "excel"],
        "order": [
            [0, 'desc']
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