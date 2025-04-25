<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header text-white border-0 py-3" style="background-color: #FFB800;">
                <div class="d-flex align-items-center">
                    <div class="modal-title-icon me-3">
                        <i class="fas fa-shield-alt fa-lg"></i>
                    </div>
                    <h5 class="modal-title fw-semibold mb-0">Nouveau Rôle</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addRoleForm" action="javascript:void(0)">
                @csrf
                <div class="modal-body p-4">
                    <!-- Alert Info -->
                    <div class="alert bg-warning-soft border-0 mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-warning me-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                Les champs marqués d'un astérisque (<span class="text-danger">*</span>) sont obligatoires
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Information du Rôle -->
                        <div class="col-12 mb-2">
                            <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: #FFB800;">
                                <i class="fas fa-info-circle me-2"></i>Information du Rôle
                            </h6>
                        </div>

                        <div class="col-12">
                            <label for="name" class="form-label fw-medium required">Nom du rôle</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">
                                    <i class="fas fa-shield-alt" style="color: #FFB800;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0"
                                       id="name" name="name" placeholder="Entrez le nom du rôle" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Permissions -->
                        <div class="col-12 mt-4 mb-2">
                            <h6 class="fw-bold mb-3 border-bottom pb-2" style="color: #FFB800;">
                                <i class="fas fa-key me-2"></i>Permissions
                            </h6>
                        </div>

                        <!-- Barre de recherche -->
                        <div class="col-12 mb-3">
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">
                                    <i class="fas fa-search" style="color: #FFB800;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-0"
                                       id="searchPermissions"
                                       placeholder="Rechercher des permissions...">
                            </div>
                        </div>

                        <!-- Groupes de permissions -->
                        <div class="col-12">
                            <div class="row g-4">
                                @foreach($permissions->groupBy('group_name') as $groupName => $groupPermissions)
                                <div class="col-md-6 permission-group">
                                    <div class="card border shadow-sm h-100">
                                        <div class="card-header bg-light border-bottom py-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-folder me-2" style="color: #FFB800;"></i>
                                                    <h6 class="card-title mb-0 fw-bold">{{ $groupName }}</h6>
                                                </div>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-light select-all-group" data-group="{{ $groupName }}">
                                                        <i class="fas fa-check-square me-1"></i>Tout
                                                    </button>
                                                    <button type="button" class="btn btn-light deselect-all-group" data-group="{{ $groupName }}">
                                                        <i class="fas fa-square me-1"></i>Aucun
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="border-0" style="width: 70%">Permission</th>
                                                            <th class="border-0 text-end">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($groupPermissions as $permission)
                                                        <tr class="permission-row">
                                                            <td>
                                                                <div class="form-check permission-item">
                                                                    <input type="checkbox"
                                                                           class="form-check-input permission-checkbox"
                                                                           name="permissions[]"
                                                                           value="{{ $permission->name }}"
                                                                           id="perm_{{ $permission->id }}"
                                                                           data-group="{{ $groupName }}">
                                                                    <label class="form-check-label permission-label"
                                                                           for="perm_{{ $permission->id }}">
                                                                        {{ $permission->description }}
                                                                    </label>
                                                                </div>
                                                            </td>
                                                            <td class="text-end text-muted small">
                                                                <code>{{ $permission->name }}</code>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 bg-light p-3">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annulerss
                    </button>
                    <button type="submit" class="btn px-4 text-white" style="background-color: #FFB800;">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-warning-soft {
    background-color: rgba(255, 184, 0, 0.1);
}

.modal-content {
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.form-control, .form-select {
    padding: 0.6rem 1rem;
    border-radius: 0.375rem;
}

.form-control:focus, .form-select:focus {
    border-color: #FFB800;
    box-shadow: 0 0 0 0.25rem rgba(255, 184, 0, 0.25);
}

.input-group-text {
    padding: 0.6rem 1rem;
    background-color: #f8f9fa;
}

.required:after {
    content: ' *';
    color: #dc3545;
    font-weight: bold;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
}

.permission-item {
    margin: 0;
}

.permission-checkbox:checked + .permission-label {
    color: #FFB800;
    font-weight: 500;
}

code {
    font-size: 0.75rem;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.2rem;
}

.table-light {
    background-color: rgba(255, 184, 0, 0.05);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    opacity: 0.9;
}

.permission-row:hover {
    background-color: rgba(255, 184, 0, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche instantanée
    const searchInput = document.getElementById('searchPermissions');
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.permission-row').forEach(row => {
            const permissionText = row.textContent.toLowerCase();
            if (permissionText.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Masquer/afficher les groupes vides
        document.querySelectorAll('.permission-group').forEach(group => {
            const visibleRows = group.querySelectorAll('.permission-row[style=""]').length;
            if (visibleRows === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = '';
            }
        });
    });

    // Sélection/Désélection par groupe
    document.querySelectorAll('.select-all-group').forEach(btn => {
        btn.addEventListener('click', function() {
            const group = this.dataset.group;
            document.querySelectorAll(`input[type="checkbox"][data-group="${group}"]`)
                .forEach(cb => cb.checked = true);
        });
    });

    document.querySelectorAll('.deselect-all-group').forEach(btn => {
        btn.addEventListener('click', function() {
            const group = this.dataset.group;
            document.querySelectorAll(`input[type="checkbox"][data-group="${group}"]`)
                .forEach(cb => cb.checked = false);
        });
    });
});
</script>
