<script>
    // Function to validate a payment
    function validateReglement(id) {
        Swal.fire({
            title: 'Valider le règlement',
            text: 'Êtes-vous sûr de vouloir valider ce règlement ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Validation en cours...',
                    html: 'Veuillez patienter...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `${apiUrl}/achat/reglements/${id}/validate`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Validation réussie',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: xhr.responseJSON?.message || 'Erreur lors de la validation'
                        });
                    }
                });
            }
        });
    }

    // Function to delete a payment
    function deleteReglement(id) {
        Swal.fire({
            title: 'Supprimer le règlement',
            text: 'Êtes-vous sûr de vouloir supprimer ce règlement ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${apiUrl}/achat/reglements/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Suppression réussie',
                                text: 'Le règlement a été supprimé avec succès',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: xhr.responseJSON?.message || 'Erreur lors de la suppression'
                        });
                    }
                });
            }
        });
    }

    // Function to show payment details
    function showReglement(id) {
        Swal.fire({
            title: 'Chargement...',
            text: 'Veuillez patienter...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Récupérer les données du règlement
        $.ajax({
            url: `${apiUrl}/achat/reglements/${id}`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const reglement = response.data;

                    // Mettre à jour les champs du modal
                    $('#reglementCode').text(`Code : ${reglement.code}`);
                    $('#factureCode').text(reglement.facture.code);
                    $('#fournisseurNom').text(reglement.facture.fournisseur.raison_sociale);
                    $('#dateReglement').text(new Date(reglement.date_reglement).toLocaleDateString('fr-FR'));
                    $('#modeReglementShow').text(reglement.mode_reglement);
                    $('#referenceReglementShow').text(reglement.reference_reglement || '-');
                    $('#montantReglementShow').text(new Intl.NumberFormat('fr-FR').format(reglement.montant_reglement) + ' FCFA');
                    $('#statutReglement').html(reglement.validated_at ?
                        '<span class="badge bg-success">Validé</span>' :
                        '<span class="badge bg-warning">En attente</span>');
                    $('#referenceDocumentShow').text(reglement.reference_document || '-');
                    $('#commentaire').text(reglement.commentaire || 'Aucun commentaire');

                    // Informations complémentaires
                    $('#createdBy').text(reglement.creator?.name || '-');
                    $('#createdAt').text(new Date(reglement.created_at).toLocaleString('fr-FR'));
                    $('#validatedBy').text(reglement.validator?.name || '-');

                    // Fermer le loader Swal et ouvrir le modal
                    Swal.close();
                    $('#detailReglementModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: xhr.responseJSON?.message || 'Une erreur est survenue lors du chargement des données'
                });
            }
        });
    }
    $('#detailReglementModal').on('hidden.bs.modal', function() {
        $('[data-bs-toggle="tooltip"]').tooltip('dispose');
    });

    // Function to print payment
    function printReglement(id) {
        window.open(`${apiUrl}/achat/reglements/${id}/print`, '_blank');
    }

    // Function to filter by date
    function filterByDate(period) {
        window.location.href = `${apiUrl}/achat/reglements?period=${period}`;
    }

    // Function to filter by payment mode
    function filterByMode(mode) {
        window.location.href = `${apiUrl}/achat/reglements?mode=${mode}`;
    }

    // Function to refresh page
    function refreshPage() {
        const refreshBtn = document.querySelector('.btn-light-secondary');
        refreshBtn.classList.add('refreshing');
        refreshBtn.disabled = true;

        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    async function editReglement(id) {
        try {
            Swal.fire({
                title: 'Chargement...',
                text: 'Veuillez patienter...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(`${apiUrl}/achat/reglements/${id}`, { // URL mise à jour
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors du chargement des données');
            }

            const result = await response.json();
            console.log(result.data);

            if (result.success) {
                const editModal = document.getElementById('editReglementModal');
                const editForm = document.getElementById('editReglementForm');

                const data = result.data;

                editForm.action = `${apiUrl}/achat/reglements/${id}`; // URL mise à jour

                $("#codeReg").html(data.code);
                $("[name='facture_fournisseur_id']").val(data.facture_fournisseur_id);
                $("#factureSelectMod").html(data.facture.code);

                $('#codeReglementMod').val(data.code);
                $('#referenceDocumentMod').val(data.reference_document);
                $("#montantRestantMod").html(result.montant_restant)

                // $('#fournisseurMod').text(data.fournisseur.raison_sociale);
                $("[name='date_reglement']").val(data.date_reglement.split('T')[0]);

                // Marquer le mode de reglement
                const modeList = $("#modeReglementMod");
                const modeOption = modeList.find(`option[value="${data.mode_reglement}"]`);
                if (modeOption.length > 0) {
                    modeOption.prop("selected", true);
                }

                $('#montantReglementMod').val(data.montant_reglement);
                $("[name='commentaire']").val(data.commentaire);

                const modal = new bootstrap.Modal(editModal);
                modal.show();
                Swal.close();
            } else {
                throw new Error(result.message || 'Erreur lors du chargement des données');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Erreur lors du chargement des données',
                timer: 3000
            });
        }
    }

    $('#editReglementForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $("#editReglementForm").serialize();
        console.log($(this).attr('action'))

        $.ajax({
            url: $(this).attr('action'),
            method: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editConversionModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: 'Règlement modifié avec succès'
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors de la modification du Règlement'
                });
            }
        });

        $(this).addClass('was-validated');
    });
</script>