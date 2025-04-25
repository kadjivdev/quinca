@extends('layouts.ventes.facture')
@section('content')
<div class="content">
    <div class="page-header mb-4">
        <div class="container-fluid p-0">
            {{-- En-tête principal --}}
            <div class="row align-items-center mb-4">
                <div class="col-auto me-auto">
                    <div class="d-flex align-items-center gap-3" style="justify-content: space-between;">
                        <div class="header-icon d-flex">
                            <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-file-invoice fs-4 text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">{{ date("Y-m-d") }}</div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0 fw-bold">Factures proforma</h5>
                                </div>
                            </div>
                        </div>
                        <div class="">
                            @can('facture.proformas.create')
                            <div class="">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#addFactureProformaModal" class="btn btn-primary float-end"> + Ajouter un proforma</a>
                            </div>
                            @endcan
                        </div>
                    </div>
                </div>
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

    <div class="row g-3 list mt-3" id="stockEntriesList">
        {{-- Table des factures --}}
        <div class="col-12">
            <div class="card border-0 p-3 shadow-sm">
                <div class="table-responsive">
                    <table id="example1" class="table table-hover align-middle mb-0" id="facturesTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-bottom-0">N°</th>
                                <th class="border-bottom-0">Date proforma</th>
                                <th class="border-bottom-0">Référence</th>
                                <th class="border-bottom-0">Auteur</th>
                                <th class="border-bottom-0">Client</th>
                                <th class="border-bottom-0">Statut</th>
                                <th class="border-bottom-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devis as $item)
                            <tr>
                                <td>{{ $loop->index ++}} </td>
                                <td>{{ $item->date_devis->locale('fr_FR')->isoFormat('ll') }}</td>
                                <td><span class="badge bg-dark"> {{ $item->reference }}</span> </td>
                                <td>{{ $item->redacteur->name }}</td>
                                <td>{{ $item->client->raison_sociale }}</td>
                                <td>
                                    <span class="badge rounded-pill @if($item->statut=='Valide') bg-success @else text-bg-warning @endif">{{ $item->statut }}</span>
                                </td>

                                <td>
                                    @can('facture.proformas.details')

                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-gear"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li>
                                                <a  href="{{route('proforma.show',$item->id)}}" class="dropdown-item"
                                                    ata-bs-toggle="tooltip" data-bs-placement="left"
                                                    data-bs-title="Voir détails"> Détails du ProForma </a>
                                            </li>


                                            @if($item->statut!='Valide')
                                            @can("facture.proformas.edit")
                                            <li>
                                                <a  href="{{route('proforma.edit',$item->id)}}" class="dropdown-item"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    data-bs-title="Generer la proforma"> Modifier </a>
                                            </li>
                                            @endcan

                                            @can("facture.proformas.delete")
                                            <li>
                                                <form
                                                    class="form-inline" method="POST"
                                                    action="{{ route('proforma.destroy', $item->id) }}"
                                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette ProForma ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item"
                                                        data-bs-toggle="tooltip" data-bs-placement="left"
                                                        data-bs-title="Supprimer">Supprimer la ProForma</button>
                                                </form>
                                            </li>
                                            @endcan
                                            @can("facture.proformas.validate")
                                            <li>
                                                <a  href="{{ route('validate-proforma', $item->id) }}" class="dropdown-item"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    data-bs-title="Validation">Valider la proforma </a>
                                            </li>
                                            @endcan

                                            @endif

                                            <li>
                                                <a  href="{{ route('generate-proforma', $item->id) }}" class="dropdown-item"
                                                    data-bs-toggle="tooltip" data-bs-placement="left"
                                                    data-bs-title="Generer la proforma"> Générer la ProForma </a>
                                            </li>
                                        </ul>
                                    </div>

                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include("pages.ventes.facture.proforma.partials.add-modal")
@endsection

@push("scripts")

<!-- DATATABLE -->
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