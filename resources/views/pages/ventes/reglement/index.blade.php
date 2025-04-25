@extends('layouts.ventes.reglement')

@push('styles')
<style>
    /* Z-index fixes */
    .modal-backdrop {
        z-index: 1040 !important;
    }

    .modal {
        z-index: 1050 !important;
    }

    /* Select2 en dehors du modal */
    .main-content .select2-container {
        z-index: 1000 !important;
    }

    .main-content .select2-dropdown {
        z-index: 1001 !important;
    }

    /* Select2 dans le modal */
    .modal .select2-container {
        z-index: 2000 !important;
    }

    .modal .select2-dropdown {
        z-index: 2001 !important;
    }

    /* Select2 styling */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #dee2e6;
    }

    /* Pour masquer les select2 quand le modal est ouvert */
    .modal-open .main-content .select2-container {
        display: none;
    }
</style>
@endpush

@section('content')

<div class="content">
    @include('pages.ventes.reglement.partials.header')
    <div class="row g-3 list mt-3" id="stockEntriesList">
        @include('pages.ventes.reglement.partials.list')
    </div>
</div>

@include('pages.ventes.reglement.partials.add-modal')
@include('pages.ventes.reglement.partials.show-modal')
@include('pages.ventes.reglement.partials.edit-modal')

@include('pages.ventes.reglement.partials.factures')

@endsection
@push('scripts')

@include('pages.ventes.reglement.partials.js-validate')
@include('pages.ventes.reglement.partials.js-add-modal')
@include('pages.ventes.reglement.partials.js-show-modal')
@include('pages.ventes.reglement.partials.js-cancel-modal')
@include('pages.ventes.reglement.partials.js-edit-modal')
@include('pages.ventes.reglement.partials.js-validate-modal')
@include('pages.ventes.reglement.partials.js-delete-modal')
@include('pages.ventes.reglement.partials.js-load-line-facture')

<script>
    // Fonction pour rafraîchir la liste
    function refreshList() {
        const filters = {
            client_id: $('#clientFilter').val(),
            facture_id: $('#factureFilter').val(),
            type_reglement: $('#typeFilter').val(),
            statut: $('#statutFilter').val(),
            date_debut: $('#dateDebut').val(),
            date_fin: $('#dateFin').val()
        };

        $.get({
            // Changer ceci
            url: '{{ route("vente.reglement.refresh") }}', // Noter le changement de 'reglements' à 'reglement'
            data: filters,
            success: function(response) {
                $('#reglementsTable').html(response.html);
                updateStats(response.stats);
                // Réinitialiser les tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du rafraîchissement de la liste'
                });
            }
        });
    }

    // Fonction pour mettre à jour les statistiques
    function updateStats(stats) {
        $('#totalMois').text(formatMontant(stats.total_mois));
        $('#totalReglements').text(formatMontant(stats.total_reglements));
        $('#reglementsEnAttente').text(stats.reglements_en_attente);
        $('#montantEnAttente').text(formatMontant(stats.montant_en_attente));
    }

    // Initialisation des filtres Select2
    $(document).ready(function() {
        $('.select2-clients').select2({
            theme: 'bootstrap-5',
            placeholder: 'Tous les clients'
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Select2 pour les filtres (hors modal)
        $('.main-content .select2-clients').select2({
            theme: 'bootstrap-5',
            placeholder: 'Tous les clients',
            width: '100%'
        });

        // Fonction pour rafraîchir la liste
        function refreshList() {
            const filters = {
                client_id: $('#clientFilter').val(),
                facture_id: $('#factureFilter').val(),
                type_reglement: $('#typeFilter').val(),
                statut: $('#statutFilter').val(),
                date_debut: $('#dateDebut').val(),
                date_fin: $('#dateFin').val()
            };

            $.get({
                url: '{{ route("vente.reglement.refresh") }}',
                data: filters,
                success: function(response) {
                    $('#reglementsTable').html(response.html);
                    updateStats(response.stats);
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                error: function() {
                    Toast.fire({
                        icon: 'error',
                        title: 'Erreur lors du rafraîchissement de la liste'
                    });
                }
            });
        }

        // Fonction pour mettre à jour les statistiques
        function updateStats(stats) {
            $('#totalMois').text(formatMontant(stats.total_mois));
            $('#totalReglements').text(formatMontant(stats.total_reglements));
            $('#reglementsEnAttente').text(stats.reglements_en_attente);
            $('#montantEnAttente').text(formatMontant(stats.montant_en_attente));
        }
    });
</script>

@endpush