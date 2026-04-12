<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAURISHOP - Espace Entreprise</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('assets/admin/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/components.css') }}" rel="stylesheet">
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('company.dashboard') }}" class="logo">CAURISHOP</a>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-label">Principal</div>
            <a href="{{ route('company.dashboard') }}" class="menu-item {{ request()->routeIs('company.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Gestion</div>
            <a href="{{ route('company.orders.index') }}" class="menu-item {{ request()->routeIs('company.orders.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>Commandes</span>
                @php
                    $pendingCount = 0;
                    if (auth()->user()->company) {
                        $cIds = \App\Models\Customer::where('company_id', auth()->user()->company->id)->pluck('id');
                        $pendingCount = \App\Models\Order::whereIn('customer_id', $cIds)
                            ->where('status', \App\Models\Order::STATUS_PENDING_APPROVAL)->count();
                    }
                @endphp
                @if($pendingCount > 0)
                <span class="menu-badge" style="background: var(--warning, #f59e0b); color: #fff;">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('company.employees.index') }}" class="menu-item {{ request()->routeIs('company.employees.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Employés</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Compte</div>
            <a href="{{ route('company.profile') }}" class="menu-item {{ request()->routeIs('company.profile*') ? 'active' : '' }}">
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
                    <a href="{{ route('company.profile') }}" class="dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        Mon Profil
                    </a>
                    <a href="{{ route('company.profile') }}#password" class="dropdown-item" onclick="sessionStorage.setItem('profileTab','password')">
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
