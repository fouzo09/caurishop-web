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
            <a href="#" class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
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
                <span class="menu-badge">8</span>
            </a>
            <a href="{{ route('admin.customers.index') }}" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Clients</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="menu-item">
                <i class="fas fa-box"></i>
                <span>Produits</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Commandes</span>
                <span class="menu-badge">12</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Finances</div>
            <a href="#" class="menu-item">
                <i class="fas fa-wallet"></i>
                <span>Paiements</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-file-invoice"></i>
                <span>Échéanciers</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-chart-pie"></i>
                <span>Transactions</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Utilisateurs</div>
            <a href="{{ route('admin.users.index') }}" class="menu-item">
                <i class="fas fa-user-cog"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="" class="menu-item">
                <i class="fas fa-user-tag"></i>
                <span>Rôles</span>
            </a>
            <a href="" class="menu-item">
                <i class="fas fa-key"></i>
                <span>Permissions</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Configuration</div>
            <a href="#" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a href="#" class="menu-item">
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
            <button class="icon-btn">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>

            <button class="icon-btn">
                <i class="fas fa-envelope"></i>
                <span class="notification-badge">7</span>
            </button>

            <div class="user-menu">
                <div class="user-avatar">AD</div>
                <div>
                    <div style="font-weight: 600; font-size: 0.9rem;">Admin</div>
                    <div style="font-size: 0.75rem; color: var(--gray);">Super Admin</div>
                </div>
                <i class="fas fa-chevron-down" style="color: var(--gray); font-size: 0.8rem;"></i>
            </div>
        </div>
    </header>

    @yield('content')
</main>
</body>
</html>
