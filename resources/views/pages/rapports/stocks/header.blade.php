<div class="page-header">
    <div class="container-fluid p-0">
        <div class="row align-items-center">
            {{-- Section gauche --}}
            <div class="col-auto me-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="header-icon">
                        <div class="icon-wrapper">
                            <i class="fas fa-file-invoice fs-4 text-primary"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div>
                        <div class="header-pretitle">{{ $date }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="font-weight-bold">
                                <i class="fas fa-layer-group me-2"></i>Rapport des ventes par Client
                            </h2>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Section droite avec les boutons d'action --}}

        </div>

        {{-- Section statistiques rapides --}}

    </div>
</div>

<style>
.page-header {
    background: #fff;
    padding: 1.25rem 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}

.header-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.75rem;
    background: linear-gradient(145deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-primary-rgb), 0.05));
    position: relative;
}

.icon-wrapper {
    position: relative;
    z-index: 2;
    transition: transform 0.3s ease;
}

.header-icon:hover .icon-wrapper {
    transform: scale(1.15) rotate(15deg);
}

.icon-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(var(--bs-primary-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0) 70%);
    border-radius: inherit;
    animation: pulse 2s infinite;
    z-index: 1;
}

.stat-trend {
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.stat-trend i {
    font-size: 0.7rem;
}

.quick-stat-card {
    background: #fff;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    min-width: 240px;
}

.quick-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-color: rgba(var(--bs-primary-rgb), 0.1);
}

/* Animation pour l'icône de rafraîchissement */
@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(0.95);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0;
    }
}

/* Les autres styles restent les mêmes que dans votre exemple original */
</style>

<script>
function refreshPage() {
    const refreshBtn = document.querySelector('.btn-light-secondary');
    refreshBtn.classList.add('refreshing');
    refreshBtn.disabled = true;

    setTimeout(() => {
        window.location.reload();
    }, 500);
}
</script>
