<script>
    var apiUrl = "{{ config('app.url_ajax') }}";
    // =========================================
    // Déclaration des fonctions principales
    // =========================================

    /**
     * Fonction de validation d'une programmation
     */
    function validateProgrammation(id) {
        console.log('Validation appelée pour ID:', id);

        Swal.fire({
            title: 'Confirmer la validation',
            text: 'Êtes-vous sûr de vouloir valider cette programmation ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const loadingAlert = Swal.fire({
                    title: 'Validation en cours...',
                    text: 'Veuillez patienter...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                axios.post(`/programmations/${id}/validate`)
                    .then(response => {
                        if (response.data.success) {
                            loadingAlert.close();
                            Swal.fire({
                                icon: 'success',
                                title: 'Validation réussie !',
                                text: 'La programmation a été validée avec succès.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(response.data.message || 'Erreur lors de la validation');
                        }
                    })
                    .catch(error => {
                        loadingAlert.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: error.response?.data?.message ||
                                'Une erreur est survenue lors de la validation.',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }

    /**
     * Initialisation des composants d'interface
     */
    function initializeUI() {
        // Tooltips Bootstrap
        initializeTooltips();

        // Popovers Bootstrap
        initializePopovers();

        // Select2
        initializeSelect2();

        // Datepickers
        initializeDatepickers();

        // Inputs numériques
        initializeNumericInputs();

        // Défilement fluide
        initializeSmoothScroll();

        // Datatables
        initializeDatatables();

        // Gestionnaire de fichiers
        initializeFileHandlers();

        // Éditeurs de texte
        initializeRichTextEditors();

        // Images lazy loading
        initializeLazyLoading();

        // Masques de saisie
        initializeInputMasks();

        // Validation de formulaire
        initializeFormValidation();
    }

    // =========================================
    // Fonctions d'initialisation spécifiques
    // =========================================

    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                boundary: document.body
            });
        });
    }

    function initializePopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    function initializeSelect2() {
        $('.select2-standard').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Sélectionner...',
            allowClear: true
        });
    }

    function initializeDatepickers() {
        // $('.datepicker').flatpickr({
        //     locale: 'fr',
        //     dateFormat: 'd/m/Y',
        //     altFormat: 'Y-m-d',
        //     altInput: true,
        //     allowInput: true,
        //     theme: 'light'
        // });
    }

    function initializeNumericInputs() {
        $('.number-input').each(function() {
            new Cleave(this, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalScale: 2,
                numeralPositiveOnly: true
            });
        });
    }

    function initializeSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    function initializeDatatables() {
        $('.datatable').each(function() {
            const table = $(this);
            const options = {
                language: {
                    url: '/assets/js/datatables-fr.json'
                },
                pageLength: table.data('page-length') || 10,
                responsive: true,
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fas fa-download me-1"></i> Exporter',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-1"></i> Excel',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: ':visible:not(.no-export)'
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: ':visible:not(.no-export)'
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-1"></i> Imprimer',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: ':visible:not(.no-export)'
                            }
                        }
                    ],
                    className: 'btn btn-secondary'
                }]
            };

            const specificOptions = table.data('options') || {};
            const mergedOptions = {
                ...options,
                ...specificOptions
            };

            table.DataTable(mergedOptions);
        });
    }

    function initializeFileHandlers() {
        $('.custom-file-input').on('change', function() {
            const fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    }

    function initializeRichTextEditors() {
        $('.rich-text-editor').each(function() {
            const editor = $(this);
            const options = {
                placeholder: editor.data('placeholder') || 'Commencez à écrire...',
                modules: {
                    toolbar: [
                        [{
                            'header': [1, 2, 3, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        ['link'],
                        ['clean']
                    ]
                },
                theme: 'snow'
            };

            new Quill(editor[0], options);
        });
    }

    function initializeLazyLoading() {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }

    function initializeInputMasks() {
        $('.phone-mask').each(function() {
            new Cleave(this, {
                phone: true,
                phoneRegionCode: 'FR'
            });
        });
    }

    function initializeFormValidation() {
        $('form.needs-validation').each(function() {
            const form = $(this);

            form.on('submit', function(event) {
                if (!this.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    const firstError = form.find(':invalid').first();
                    if (firstError.length) {
                        $('html, body').animate({
                            scrollTop: firstError.offset().top - 100
                        }, 500);
                    }
                }

                form.addClass('was-validated');
            });
        });
    }

    // =========================================
    // Écouteurs d'événements
    // =========================================

    // Initialisation au chargement du document
    $(document).ready(function() {
        initializeUI();

        // Écouteur pour les boutons de validation
        $(document).on('click', '.validate-btn', function() {
            const id = $(this).data('id');
            console.log('Click détecté sur le bouton de validation:', id);
            validateProgrammation(id);
        });
    });

    // Réinitialisation après les mises à jour dynamiques
    document.addEventListener('componentUpdated', function(e) {
        if (e.detail && e.detail.container) {
            initializeUI();
        }
    });

    // Gestionnaire pour les modals dynamiques
    document.body.addEventListener('show.bs.modal', function(e) {
        const modal = e.target;
        setTimeout(() => {
            initializeUI();

            const firstInput = modal.querySelector('input:not([readonly]), select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    });

    async function editProgrammation(id) {
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

            const response = await fetch(`${apiUrl}/achat/programmations/${id}/edit`, {  // URL mise à jour
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
            // console.log(result);

            if (result.success) {
                const editModal = document.getElementById('editProgrammationModal');
                const editForm = document.getElementById('editProgrammationForm');

                editForm.action = `/achat/programmations/${id}`;  // URL mise à jour

                // Remplir les champs
                $("#lignesContainerMod").html('');
                editForm.querySelector('[name="code"]').value = result.data.code;
                editForm.querySelector('[name="date_programmation"]').value = moment(result.data.date_programmation).format('YYYY-MM-DD');
                editForm.querySelector('[name="fournisseur_id"]').value = result.data.fournisseur_id;
                result.data.lignes.forEach(ligne => {
                    // Obtenez le contenu du template
                    const template = document.getElementById('ligneProgrammationTemplate');
                    const clone = template.content.cloneNode(true);

                    // Insérez les données dans les champs du clone
                    const selectArticles = clone.querySelector('.select2-articles');
                    const inputQuantite = clone.querySelector('input[name="quantites[]"]');
                    const selectUnites = clone.querySelector('select[name="unites[]"]');

                    // Marquer l'article sélectionné
                    const articleOption = selectArticles.querySelector(`option[value="${ligne.article_id}"]`);
                    if (articleOption) {
                        articleOption.selected = true;
                    }

                    // Ajouter la quantité
                    inputQuantite.value = ligne.quantite;

                    // Marquer l'unité sélectionnée
                    const uniteOption = selectUnites.querySelector(`option[value="${ligne.unite_mesure_id}"]`);
                    if (uniteOption) {
                        uniteOption.selected = true;
                    }

                    // Ajoutez la ligne au conteneur
                    document.getElementById('lignesContainerMod').appendChild(clone);

                    // Initialisez Select2 pour le champ des articles
                    // $(selectArticles).select2({
                    //     theme: 'bootstrap-5',
                    //     width: '100%'
                    // });
                });                
                
                editForm.querySelector('[name="commentaire"]').value = result.data.commentaire;

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
</script>


<script>
    function validateProgrammation(id) {
        console.log('Validation appelée pour ID:', id); // Log de débogage

        Swal.fire({
            title: 'Confirmer la validation',
            text: 'Êtes-vous sûr de vouloir valider cette programmation ? Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, valider',
            cancelButtonText: 'Annuler',
            reverseButtons: true
        }).then((result) => {
            console.log('Réponse SweetAlert:', result); // Log de débogage

            if (result.isConfirmed) {
                // Afficher un loader pendant la validation
                const loadingAlert = Swal.fire({
                    title: 'Validation en cours...',
                    text: 'Veuillez patienter...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Appel AJAX avec Axios
                axios.post(`${apiUrl}/achat/programmations/${id}/validate`)
                    .then(response => {
                        console.log('Réponse serveur:', response); // Log de débogage

                        if (response.data.success) {
                            loadingAlert.close();
                            Swal.fire({
                                icon: 'success',
                                title: 'Validation réussie !',
                                text: 'La programmation a été validée avec succès.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(response.data.message || 'Erreur lors de la validation');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error); // Log de débogage

                        loadingAlert.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: error.response?.data?.message ||
                                'Une erreur est survenue lors de la validation.',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }
</script>

<script>
    // Fonction principale pour afficher une programmation
    function showProgrammation(id) {
        // Réinitialiser le modal
        resetModal();

        // Afficher l'indicateur de chargement
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

        // Charger les données
        $.ajax({
            url: `${apiUrl}/achat/programmations/${id}/edit`,
            method: 'GET',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    const programmation = response.data;
                    fillProgrammationDetails(programmation);
                    $('#showProgrammationModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.close();
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors du chargement des données'
                });
            }
        });
    }

    // Fonction pour remplir les détails de la programmation
    function fillProgrammationDetails(programmation) {
        // Informations générales
        $('#programmationCode').text(`Code : ${programmation.code}`);
        $('#dateProgrammation').text(moment(programmation.date_programmation).format('DD/MM/YYYY'));
        $('#pointVente').text(programmation.point_vente.nom_pv);
        $('#fournisseur').text(programmation.fournisseur.raison_sociale);

        // console.log(programmation)

        // Statut
        const statutBadge = programmation.rejected_at
        ? '<span class="badge bg-danger bg-opacity-10 text-danger"><i class="fas fa-minus-circle"></i> Rejetée</span>'
        : programmation.validated_at
            ? '<span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i> Validée</span>'
            : '<span class="badge bg-warning bg-opacity-10 text-warning"><i class="fas fa-hourglass-half"></i> En attente</span>';

        $('#statut').html(statutBadge);

        // Commentaire
        $('#commentaire').text(programmation.commentaire || 'Aucun commentaire');

        // Vider et remplir le tableau des lignes
        $('#lignesDetails').empty();
        programmation.lignes.forEach(ligne => {
            const row = `
                <tr>
                    <td>${ligne.article.code_article}</td>
                    <td>${ligne.article.designation}</td>
                    <td class="text-end">${parseFloat(ligne.quantite).toLocaleString('fr-FR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</td>
                    <td>${ligne.unite_mesure.libelle_unite}</td>
                    <td>${ligne.observation || ''}</td>
                </tr>
            `;
            $('#lignesDetails').append(row);
        });
    }

    // Fonction pour réinitialiser le modal
    function resetModal() {
        $('#lignesDetails').empty();
        $('#commentaire').text('');
        $('#programmationCode').text('Code : ');
        $('#dateProgrammation').text('');
        $('#pointVente').text('');
        $('#fournisseur').text('');
        $('#statut').html('');
    }

    // Fonctions d'export
    function printProgrammation() {
        window.print();
    }

    function exportPDF() {
        Toast.fire({
            icon: 'info',
            title: 'Export PDF en cours de développement'
        });
    }

    function exportExcel() {
        Toast.fire({
            icon: 'info',
            title: 'Export Excel en cours de développement'
        });
    }

    // Initialisation des tooltips au chargement de la page
    $(document).ready(function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    async function deleteProgrammation(id) {
        try {
            const result = await Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: "Cette suppression ne pourra pas être annulée !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await fetch(`${apiUrl}/achat/programmations/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        // Log pour debug
                        // console.log('Response:', data);

                        if (!data.success) {
                            throw new Error(data.message || data.error ||
                                'Erreur lors de la suppression');
                        }
                        return data;
                    } catch (error) {
                        console.error('Delete Error:', error);
                        Swal.showValidationMessage(
                            `Erreur: ${error.message}`
                        );
                        throw error;
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            });

            if (result.isConfirmed && result.value) {
                // Message de succès
                Toast.fire({
                    icon: 'success',
                    title: result.value.message || 'Programmation supprimée avec succès'
                });

                // Recharger la page après un court délai
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } catch (error) {
            console.error('Global Error:', error);
            Toast.fire({
                icon: 'error',
                title: error.message || 'Une erreur est survenue lors de la suppression'
            });
        }
    }
</script>
