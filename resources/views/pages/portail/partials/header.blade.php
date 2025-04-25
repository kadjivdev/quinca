<div class="page-header">
    <div class="header-container">
        {{-- Section principale --}}
        <div class="header-content">
            {{-- Logo et titre --}}
            <div class="header-left">
                <div class="logo-wrapper">
                    <div class="logo-triangle"></div>
                </div>
                <div class="header-info">
                    <span class="header-date">{{ $date }}</span>
                    <h1 class="header-title">QuincaKadjiv</h1>
                </div>
            </div>

            {{-- Accueil utilisateur --}}
            <div class="header-right">
                <div class="user-greeting">
                    <span class="greeting-text">Bonjour,</span>
                    <span class="user-name">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    background: #ffffff;
    margin-bottom: 2rem;
    padding: 1.25rem;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.logo-wrapper {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 107, 0, 0.1);
    border-radius: 10px;
    position: relative;
    transition: transform 0.3s ease;
}

.logo-wrapper:hover {
    transform: translateY(-2px);
}

.logo-triangle {
    width: 0;
    height: 0;
    border-left: 12px solid transparent;
    border-right: 12px solid transparent;
    border-bottom: 20px solid #FF6B00;
    transform: rotate(180deg);
}

.header-info {
    display: flex;
    flex-direction: column;
}

.header-date {
    font-size: 0.8125rem;
    color: #94A3B8;
    letter-spacing: 0.03em;
    margin-bottom: 0.25rem;
}

.header-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1E293B;
    margin: 0;
    letter-spacing: -0.01em;
}

.header-right {
    padding-left: 1.5rem;
    border-left: 1px solid #E2E8F0;
}

.user-greeting {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.greeting-text {
    color: #64748B;
    font-size: 0.9375rem;
}

.user-name {
    color: #1E293B;
    font-weight: 600;
    font-size: 0.9375rem;
}

@media (max-width: 768px) {
    .page-header {
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .header-right {
        padding-left: 0;
        border-left: none;
        width: 100%;
    }

    .logo-wrapper {
        width: 36px;
        height: 36px;
    }

    .header-title {
        font-size: 1.125rem;
    }
}
</style>
