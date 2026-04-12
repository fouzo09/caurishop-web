<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAURISHOP - Mon Espace</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/admin/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/components.css') }}" rel="stylesheet">
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('portal.dashboard') }}" class="logo">CAURISHOP</a>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-label">Principal</div>
            <a href="{{ route('portal.dashboard') }}" class="menu-item {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Catalogue</div>
            <a href="{{ route('portal.products.index') }}" class="menu-item {{ request()->routeIs('portal.products.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>Produits</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Mes achats</div>
            <a href="{{ route('portal.orders.index') }}" class="menu-item {{ request()->routeIs('portal.orders.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>Mes Commandes</span>
            </a>
            <a href="{{ route('portal.payments.index') }}" class="menu-item {{ request()->routeIs('portal.payments.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Mes Paiements</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Compte</div>
            <a href="{{ route('portal.profile') }}" class="menu-item {{ request()->routeIs('portal.profile*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i>
                <span>Mon Profil</span>
            </a>
        </div>
    </nav>
</aside>

<main class="main-content">
    <header class="header">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Rechercher...">
        </div>

        <div class="header-actions">
            <div class="user-menu" id="userMenuTrigger" onclick="toggleUserDropdown()">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text);">{{ auth()->user()->name }}</div>
                </div>
                <i class="fas fa-chevron-down" id="userMenuChevron" style="color:var(--text-light);font-size:11px;transition:transform 0.2s;"></i>
            </div>

            <div id="userDropdown" class="user-dropdown">
                <div class="user-dropdown-header">
                    <div>{{ auth()->user()->name }}</div>
                    <div>{{ auth()->user()->email }}</div>
                </div>
                <div class="user-dropdown-body">
                    <a href="{{ route('portal.profile') }}" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        Mon Profil
                    </a>
                    <a href="{{ route('portal.profile') }}#password" class="dropdown-item" onclick="sessionStorage.setItem('profileTab','password')">
                        <i class="fas fa-lock"></i>
                        Changer le mot de passe
                    </a>
                </div>
                <div class="user-dropdown-footer">
                    <form action="{{ route('logout') }}" method="GET" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item logout-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; font-size: inherit; font-family: inherit;">
                            <i class="fas fa-sign-out-alt"></i>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    @yield('content')
</main>

<style>
    .header-actions { position: relative; }
</style>

<script>
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        const chevron  = document.getElementById('userMenuChevron');
        dropdown.classList.toggle('open');
        chevron.style.transform = dropdown.classList.contains('open') ? 'rotate(180deg)' : '';
    }
    document.addEventListener('click', function(e) {
        const trigger  = document.getElementById('userMenuTrigger');
        const dropdown = document.getElementById('userDropdown');
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
            document.getElementById('userMenuChevron').style.transform = '';
        }
    });
</script>
</body>
</html>
