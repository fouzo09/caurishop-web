<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAURISHOP - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/admin/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/components.css') }}" rel="stylesheet">
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="logo">CAURISHOP</a>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-label">Principal</div>
            <a href="{{ route('admin.dashboard') }}" class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Gestion</div>
            <a href="{{ route('admin.customers.index') }}" class="menu-item">
                <i class="fas fa-truck"></i>
                <span>Fournisseurs</span>
            </a>
            <a href="{{ route('admin.companies.index') }}" class="menu-item">
                <i class="fas fa-building"></i>
                <span>Entreprises</span>
                @php $pendingCompanies = app(\App\Services\Admin\CompanyService::class)->pendingCount(); @endphp
                @if($pendingCompanies > 0)
                <span class="menu-badge" style="background:var(--warning);">{{ $pendingCompanies }}</span>
                @endif
            </a>
            <a href="{{ route('admin.customers.index') }}" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clients</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i>
                <span>Catégories</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>Produits</span>
            </a>
            <a href="{{ route('admin.scraper.index') }}" class="menu-item {{ request()->routeIs('admin.scraper.*') ? 'active' : '' }}">
                <i class="fas fa-spider"></i>
                <span>Scraper</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="menu-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Commandes</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Finances</div>
            <a href="{{ route('admin.payments.index') }}" class="menu-item">
                <i class="fas fa-wallet"></i>
                <span>Paiements</span>
            </a>
            <a href="{{ route('admin.installments.index') }}" class="menu-item">
                <i class="fas fa-file-invoice"></i>
                <span>Échéanciers</span>
            </a>
            <a href="{{ route('admin.margins.index') }}" class="menu-item {{ request()->routeIs('admin.margins.*') ? 'active' : '' }}">
                <i class="fas fa-percentage"></i>
                <span>Marges</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Utilisateurs</div>
            <a href="{{ route('admin.users.index') }}" class="menu-item">
                <i class="fas fa-user-cog"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="{{ route('admin.roles.index') }}" class="menu-item">
                <i class="fas fa-user-tag"></i>
                <span>Rôles</span>
            </a>
            <a href="{{ route('admin.permissions.index') }}" class="menu-item">
                <i class="fas fa-key"></i>
                <span>Permissions</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Configuration</div>
            <a href="{{ route('admin.settings.index') }}" class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
            <a href="{{ route('admin.notifications.index') }}" class="menu-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                @php $unread = app(\App\Services\Admin\NotificationService::class)->unreadCount(auth()->id()); @endphp
                @if($unread > 0)
                <span class="menu-badge" style="background: var(--danger);">{{ $unread > 99 ? '99+' : $unread }}</span>
                @endif
            </a>
            <a href="{{ route('admin.security.index') }}" class="menu-item {{ request()->routeIs('admin.security.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Sécurité</span>
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
            <a href="{{ route('admin.notifications.index') }}" class="icon-btn" style="text-decoration: none; color: inherit;">
                <i class="fas fa-bell"></i>
                @if($unread > 0)
                <span class="notification-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
                @endif
            </a>


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
                    <a href="{{ route('admin.profile.show') }}" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        Mon Profil
                    </a>
                    <a href="{{ route('admin.profile.show') }}#password" class="dropdown-item" onclick="sessionStorage.setItem('profileTab','password')">
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

    // Fermer en cliquant ailleurs
    document.addEventListener('click', function(e) {
        const trigger  = document.getElementById('userMenuTrigger');
        const dropdown = document.getElementById('userDropdown');
        if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('open');
            document.getElementById('userMenuChevron').style.transform = '';
        }
    });

    // Ouvrir l'onglet mot de passe si demandé
    if (sessionStorage.getItem('profileTab') === 'password') {
        sessionStorage.removeItem('profileTab');
        // Exécuté sur la page profil via le script inline de la vue
    }
</script>
</body>
</html>
