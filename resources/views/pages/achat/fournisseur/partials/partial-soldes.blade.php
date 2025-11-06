
<!-- TABLE -->
<div class="table-responsive bg-white p-2 shadow-sm">
    <h4 class="text-center"><span class="badge bg-light borded rounded text-success"> Soldes partiels</span> des fournisseurs</h4>
    <table class="table table-hover" id="example1">
        <thead>
            <tr>
                <th>Code</th>
                <th>Raison sociale</th>
                <th>Phone/Adresse</th>
                <th class="text-center">Payé au fournisseur</th>
                <th class="text-center">Factures</th>
                <th class="text-center">Règlement</th>
                <th class="text-center">Avances</th>
                <th class="text-center">Accompte</th>
                <th class="text-center">Solde Partiel</th>
                <!-- <th class="text-center">Action</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($fournisseurs as $fournisseur)
            <tr>
                <td><span class="badge bg-light text-dark">{{$fournisseur->id}} {{ $fournisseur->code_fournisseur }}</span></td>
                <td><span class="badge bg-light text-dark"> {{ $fournisseur->raison_sociale }}</span></td>
                <td> <span class="badge bg-light text-dark"> {{ $fournisseur->telephone }}/{{ $fournisseur->adresse }}</span></td>
                <td><span class="badge bg-light text-dark text-center"> {{ number_format($fournisseur->totalAppro, 2, ',', ' ') }}</span></td>
                <td><span class="badge bg-light text-dark text-center"> {{ number_format($fournisseur->factureAchatAmount, 2, ',', ' ') }}</span></td>
                <td><span class="badge bg-light text-warning text-center"> {{ number_format($fournisseur->reglementsAmount, 2, ',', ' ') }}</span></td>
                <td><span class="badge bg-light text-warning text-center"> {{ number_format($fournisseur->avancesAmount, 2, ',', ' ') }}</span></td>
                <td><span class="badge bg-light text-warning text-center"> {{ number_format($fournisseur->accompteAmount, 2, ',', ' ') }}</span></td>
                <td><span class="badge @if($fournisseur->reste_solde>0) bg-success @elseif($fournisseur->reste_solde<0) bg-danger @else bg-light text-dark @endif  text-center"> {{ number_format($fournisseur->reste_solde, 2, ',', ' ') }}</span></td>
                
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@push('scripts')
<script type="text/javascript">
    const apiUrl = "{{ env('APP_URL') }}";

    // Make showFactures globally accessible
    async function showFactures(fournisseurId) {
        const facturesModal = new bootstrap.Modal(document.getElementById('showFournisseurFacturesModal'));

        Swal.fire({
            title: 'Chargement...',
            text: 'Récupération des données du fournisseur',
            showConfirmButton: false,
            footer: `<div class="spinner-border" role="status">
                                    <span class="visually-hidden">En cours de traitement ...</span>
                                </div>`
        });

        $.ajax({
            url: `${apiUrl}/achat/fournisseurs/details/${fournisseurId}/factures`,
            method: 'GET',
            success: function(response) {
                Swal.close(); // Close the loading alert

                if (response.success) {
                    const fournisseur = response.fournisseur;
                    const factures = fournisseur.factures;

                    // Met à jour le nom du fournisseur dans le modal
                    $('#fournisseurName').text(`${fournisseur.raison_sociale} (${fournisseur.code_fournisseur})`);

                    // Génère le HTML des factures
                    let facturesHtml = '';
                    if (!factures || factures.length === 0) {
                        facturesHtml = '<tr><td colspan="6" class="text-center">Aucune facture trouvée.</td></tr>';
                    } else {
                        factures.forEach(facture => {
                            facturesHtml += `
                                <tr>
                                    <td>${facture.code ?? ''}</td>
                                    <td>${facture.created_at ? new Date(facture.created_at).toLocaleDateString() : ''}</td>
                                    <td>${facture.date_facture ? new Date(facture.date_facture).toLocaleDateString() : ''}</td>
                                    <td>${facture.bon_commande?.code ?? ''}</td>
                                    <td class="text-end">${facture.montant_ht ? facture.montant_ht.toFixed(2).replace('.', ',') : ''}</td>
                                    <td class="text-end">${facture.montant_ttc ? facture.montant_ttc.toFixed(2).replace('.', ',') : ''}</td>
                                </tr>
                            `;
                        });
                    }

                    // Injecte le HTML dans le tableau
                    $('#facturesTableBody').html(facturesHtml);

                    // let facturesHtml = '';

                    // $('#fournisseurName').text(`${fournisseur.raison_sociale} - (${fournisseur.code_fournisseur})`);
                    // $('#factureClientCode').text(fournisseur.code_fournisseur);

                    // if (factures.length === 0) {
                    //     facturesHtml = '<tr><td colspan="5" class="text-center">Aucune facture trouvée.</td></tr>';
                    // } else {
                    //     factures.forEach(facture => {
                    //         facturesHtml += `
                    //             <tr>
                    //                 <td>${facture.numero}</td>
                    //                 <td>${new Date(facture.created_at).toLocaleDateString()}</td>
                    //                 <td>${new Date(facture.date_facture).toLocaleDateString()}</td>
                    //                 <td> <span>${new Date(facture.bon_commande?.code).toLocaleDateString()}</span> </td>
                    //                 <td class="text-end">${facture.montant_ht.toFixed(2).replace('.', ',')}</td>
                    //                 <td class="text-end">${facture.montant_ttc.toFixed(2).replace('.', ',')}</td>
                    //             </tr>
                    //         `;
                    //     });
                    // }

                    // $('#facturesTableBody').html('facturesHtml');
                    facturesModal.show();
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du chargement des factures'
                    });
                }
            },
            error: function() {
                Swal.close(); // Close the loading alert
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des factures'
                });
            }
        });

    }


    // Make showReglements globally accessible
    async function showReglements(fournisseurId) {
        const reglementsModal = new bootstrap.Modal(document.getElementById('showFournisseurReglementsModal'));

        Swal.fire({
            title: 'Chargement...',
            text: 'Récupération des données du fournisseur',
            showConfirmButton: false,
            footer: `<div class="spinner-border" role="status">
                                    <span class="visually-hidden">En cours de traitement ...</span>
                                </div>`
        });

        $.ajax({
            url: `${apiUrl}/achat/fournisseurs/details/${fournisseurId}/reglements`,
            method: 'GET',
            success: function(response) {
                Swal.close(); // Close the loading alert

                if (response.success) {
                    // alert("Success ....")
                    const fournisseur = response.fournisseur;
                    const reglements = response.reglements;


                    // Affiche toutes les infos reçues dans la console
                    // Assurez-vous que la console du navigateur est ouverte pour voir ces logs
                    console.log("Réponse complète :", response);
                    console.log("Fournisseur :", fournisseur);

                    // Met à jour le nom du fournisseur dans le modal
                    $('#rgl_fournisseurName').text(`${fournisseur.raison_sociale} (${fournisseur.code_fournisseur})`);
                    // Génère le HTML des règlements
                    let reglementsHtml = '';
                    if (!reglements || reglements.length === 0) {
                        reglementsHtml = '<tr><td colspan="6" class="text-center">Aucun règlement trouvé.</td></tr>';
                    } else {
                        reglements.forEach(reglement => {
                            reglementsHtml += `
                                <tr>
                                    <td>${reglement.code}</td>
                                    <td>${reglement.date_reglement ? new Date(reglement.date_reglement).toLocaleDateString() : ''}</td>
                                    <td>${reglement.mode_reglement}</td>
                                    <td>${reglement.facture?.code ?? ''}</td>
                                    <td class="text-end">${reglement.montant_reglement} FCFA</td>
                                </tr>
                            `;
                        });
                    }

                    // Injecte le HTML dans le tableau
                    $('#reglementsTableBody').html(reglementsHtml);

                    reglementsModal.show();
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du chargement des reglements'
                    });
                }
            },
            error: function() {
                Swal.close(); // Close the loading alert
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des factures'
                });
            }
        });

    }

    // Make showAccomptes globally accessible
    async function showAccomptes(fournisseurId) {
        const accomptesModal = new bootstrap.Modal(document.getElementById('showFournisseurAccomptesModal'));

        Swal.fire({
            title: 'Chargement...',
            text: 'Récupération des données du fournisseur',
            showConfirmButton: false,
            footer: `<div class="spinner-border" role="status">
                                    <span class="visually-hidden">En cours de traitement ...</span>
                                </div>`
        });

        $.ajax({
            url: `${apiUrl}/achat/fournisseurs/details/${fournisseurId}/accomptes`,
            method: 'GET',
            success: function(response) {
                Swal.close(); // Close the loading alert

                if (response.success) {
                    // alert("Success ....")
                    const fournisseur = response.fournisseur;
                    const accomptes = fournisseur.accomptes;


                    // Affiche toutes les infos reçues dans la console
                    // Assurez-vous que la console du navigateur est ouverte pour voir ces logs
                    console.log("Réponse complète :", response);
                    console.log("Fournisseur :", fournisseur);

                    // Met à jour le nom du fournisseur dans le modal
                    $('#acc_fournisseurName').text(`${fournisseur.raison_sociale} (${fournisseur.code_fournisseur})`);
                    // Génère le HTML des règlements
                    let accomptesHtml = '';
                    if (!accomptes || accomptes.length === 0) {
                        accomptesHtml = '<tr><td colspan="6" class="text-center">Aucun règlement trouvé.</td></tr>';
                    } else {
                        accomptes.forEach(accompte => {
                            accomptesHtml += `
                                <tr>
                                    <td>${accompte.reference}</td>
                                    <td>${accompte.created_at ? new Date(accompte.created_at).toLocaleDateString() : ''}</td>
                                    <td>${accompte.montant}</td>
                                    <td>${accompte.statut}</td>
                                    <td>${accompte.requete_id?'Requete':''} ${accompte.transport_id?'Transport':''} ${(!accompte.requete_id && !accompte.transport_id)?'---':'' } </td>
                                </tr>
                            `;
                        });
                    }

                    // Injecte le HTML dans le tableau
                    $('#accomptesTableBody').html(accomptesHtml);

                    accomptesModal.show();
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du chargement des accomptes'
                    });
                }
            },
            error: function() {
                Swal.close(); // Close the loading alert
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des accomptes'
                });
            }
        });

    }

    $(document).ready(function() {
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
    })
</script>
@endpush