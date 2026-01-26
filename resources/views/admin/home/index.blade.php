@extends('tpl.admin')
@section('content')
<div class="content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <div class="page-breadcrumb">
            <span>CAURISHOP</span>
            <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
            <span>Dashboard</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Entreprises</div>
                <div class="stat-value">52</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>+8% ce mois</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Employés Actifs</div>
                <div class="stat-value">2,847</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>+12% ce mois</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Commandes</div>
                <div class="stat-value">387</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>+23% ce mois</span>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Revenus</div>
                <div class="stat-value">127M</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>+15% ce mois</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Commandes Récentes</h3>
            <button class="btn btn-outline btn-sm">
                <i class="fas fa-download"></i>
                Exporter
            </button>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employé</th>
                        <th>Entreprise</th>
                        <th>Produit</th>
                        <th>Montant</th>
                        <th>Plan</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>#EC-00127</td>
                        <td>
                            <div style="font-weight: 600;">Mamadou Diallo</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">mamadou@orange.gn</div>
                        </td>
                        <td>Orange Guinée</td>
                        <td>iPhone 15 Pro Max</td>
                        <td>4,200,000 GNF</td>
                        <td>12 mois</td>
                        <td><span class="badge success">Approuvée</span></td>
                        <td>10 Jan 2025</td>
                        <td>
                            <button class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#EC-00126</td>
                        <td>
                            <div style="font-weight: 600;">Fatoumata Bah</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">fbah@cbg.gn</div>
                        </td>
                        <td>CBG Mining</td>
                        <td>MacBook Pro 14"</td>
                        <td>7,500,000 GNF</td>
                        <td>12 mois</td>
                        <td><span class="badge warning">En attente</span></td>
                        <td>10 Jan 2025</td>
                        <td>
                            <button class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#EC-00125</td>
                        <td>
                            <div style="font-weight: 600;">Alpha Condé</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">aconde@ecobank.gn</div>
                        </td>
                        <td>Ecobank Guinée</td>
                        <td>Samsung Galaxy S24</td>
                        <td>3,800,000 GNF</td>
                        <td>6 mois</td>
                        <td><span class="badge success">Approuvée</span></td>
                        <td>09 Jan 2025</td>
                        <td>
                            <button class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#EC-00124</td>
                        <td>
                            <div style="font-weight: 600;">Aissatou Barry</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">abarry@bollore.gn</div>
                        </td>
                        <td>Bolloré Transport</td>
                        <td>Dell XPS 13 Plus</td>
                        <td>5,200,000 GNF</td>
                        <td>12 mois</td>
                        <td><span class="badge danger">Rejetée</span></td>
                        <td>09 Jan 2025</td>
                        <td>
                            <button class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>#EC-00123</td>
                        <td>
                            <div style="font-weight: 600;">Ibrahima Sow</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">isow@orange.gn</div>
                        </td>
                        <td>Orange Guinée</td>
                        <td>AirPods Pro 2</td>
                        <td>1,450,000 GNF</td>
                        <td>3 mois</td>
                        <td><span class="badge success">Approuvée</span></td>
                        <td>08 Jan 2025</td>
                        <td>
                            <button class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Évolution des Ventes</h3>
                <button class="btn btn-outline btn-sm">
                    <i class="fas fa-calendar"></i>
                    Ce mois
                </button>
            </div>
            <div class="card-body">
                <div class="chart-placeholder">
                    <div style="text-align: center;">
                        <i class="fas fa-chart-area" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                        <div>Graphique des ventes</div>
                        <div style="font-size: 0.8rem; margin-top: 0.5rem;">Intégrer Chart.js ou ApexCharts</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Produits</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: var(--light); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-apple" style="color: var(--primary);"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.9rem;">iPhone 15 Pro</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">127 ventes</div>
                        </div>
                        <div style="font-weight: 700; color: var(--primary);">42%</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: var(--light); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-laptop" style="color: var(--success);"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.9rem;">MacBook Pro</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">89 ventes</div>
                        </div>
                        <div style="font-weight: 700; color: var(--success);">29%</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: var(--light); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-android" style="color: var(--warning);"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.9rem;">Galaxy S24</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">63 ventes</div>
                        </div>
                        <div style="font-weight: 700; color: var(--warning);">21%</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 40px; height: 40px; background: var(--light); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-headphones" style="color: var(--danger);"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 0.9rem;">AirPods Pro</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">24 ventes</div>
                        </div>
                        <div style="font-weight: 700; color: var(--danger);">8%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
