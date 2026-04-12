@extends('tpl.admin')
@section('content')
<div class="content">

    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Dashboard</span>
            </div>
        </div>
        <div style="font-size: 0.85rem; color: var(--gray);">
            <i class="fas fa-calendar"></i>
            {{ now()->isoFormat('dddd D MMMM YYYY') }}
        </div>
    </div>

    {{-- ── KPI CARDS ─────────────────────────────────────────────── --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-building"></i></div>
            <div class="stat-info">
                <div class="stat-label">Entreprises</div>
                <div class="stat-value">{{ number_format($stats['companies']) }}</div>
                <div class="stat-trend" style="color: var(--gray); font-size: 0.78rem;">
                    {{ $stats['companies_active'] }} actives
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-label">Clients</div>
                <div class="stat-value">{{ number_format($stats['customers']) }}</div>
                <div class="stat-trend" style="color: var(--gray); font-size: 0.78rem;">
                    {{ $stats['customers_active'] }} actifs
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <div class="stat-label">Commandes ce mois</div>
                <div class="stat-value">{{ number_format($stats['orders_month']) }}</div>
                <div class="stat-trend" style="color: var(--gray); font-size: 0.78rem;">
                    {{ $stats['orders_pending'] }} en attente
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
                <div class="stat-label">Revenus ce mois</div>
                <div class="stat-value" style="font-size: 1.3rem;">{{ number_format($stats['revenue_month'], 0, ',', ' ') }} GNF</div>
                <div class="stat-trend" style="color: var(--gray); font-size: 0.78rem;">
                    Total : {{ number_format($stats['revenue_total'], 0, ',', ' ') }} GNF
                </div>
            </div>
        </div>
    </div>

    {{-- ── ALERTES RAPIDES ──────────────────────────────────────────── --}}
    @if($stats['installments_late'] > 0 || $stats['orders_pending'] > 0)
    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        @if($stats['installments_late'] > 0)
        <a href="{{ route('admin.installments.index') }}"
           style="display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 1rem; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); border-radius: 8px; text-decoration: none; color: var(--danger); font-size: 0.88rem; font-weight: 600;">
            <i class="fas fa-exclamation-circle"></i>
            {{ $stats['installments_late'] }} échéance(s) en retard
        </a>
        @endif
        @if($stats['orders_pending'] > 0)
        <a href="{{ route('admin.orders.index') }}"
           style="display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 1rem; background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); border-radius: 8px; text-decoration: none; color: var(--warning); font-size: 0.88rem; font-weight: 600;">
            <i class="fas fa-clock"></i>
            {{ $stats['orders_pending'] }} commande(s) en attente de confirmation
        </a>
        @endif
    </div>
    @endif

    {{-- ── LIGNE PRINCIPALE ─────────────────────────────────────────── --}}
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; margin-bottom: 1.5rem;">

        {{-- Commandes récentes --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Commandes récentes</h3>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
                    Voir tout <i class="fas fa-arrow-right" style="margin-left: 0.3rem;"></i>
                </a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container" style="margin: 0; border-radius: 0;">
                    <table>
                        <thead>
                        <tr>
                            <th>Commande</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td style="font-weight: 600; font-family: monospace;">{{ $order->order_number }}</td>
                            <td>
                                <div style="font-weight: 600; font-size: 0.88rem;">
                                    {{ $order->customer?->first_name }} {{ $order->customer?->last_name }}
                                </div>
                                <div style="font-size: 0.78rem; color: var(--gray);">{{ $order->customer?->company?->name }}</div>
                            </td>
                            <td style="font-weight: 600; white-space: nowrap;">{{ number_format($order->total_amount, 0, ',', ' ') }} GNF</td>
                            <td>
                                @if($order->order_type === 'credit')
                                <span class="badge primary">Crédit</span>
                                @else
                                <span class="badge success">Cash</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusMap = [
                                        'draft'     => ['label' => 'Brouillon', 'class' => 'warning'],
                                        'confirmed' => ['label' => 'Confirmée', 'class' => 'primary'],
                                        'completed' => ['label' => 'Livrée',    'class' => 'success'],
                                        'cancelled' => ['label' => 'Annulée',   'class' => 'danger'],
                                    ];
                                    $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => ''];
                                @endphp
                                <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                            </td>
                            <td style="font-size: 0.82rem; color: var(--gray);">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--gray);">
                                <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                                Aucune commande
                            </td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Colonne droite --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">

            {{-- Statuts des commandes --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statuts des commandes</h3>
                </div>
                <div class="card-body">
                    @php
                        $statusDef = [
                            'draft'     => ['label' => 'Brouillon',  'color' => 'var(--warning)', 'icon' => 'fa-pencil'],
                            'confirmed' => ['label' => 'Confirmées', 'color' => 'var(--primary)', 'icon' => 'fa-check'],
                            'completed' => ['label' => 'Livrées',    'color' => 'var(--success)', 'icon' => 'fa-box'],
                            'cancelled' => ['label' => 'Annulées',   'color' => 'var(--danger)',  'icon' => 'fa-ban'],
                        ];
                        $total = array_sum($ordersByStatus) ?: 1;
                    @endphp
                    @foreach($statusDef as $key => $def)
                    @php $count = $ordersByStatus[$key] ?? 0; $pct = round($count / $total * 100); @endphp
                    <div style="margin-bottom: 0.9rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.3rem;">
                            <span style="display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fas {{ $def['icon'] }}" style="color: {{ $def['color'] }}; width: 14px;"></i>
                                {{ $def['label'] }}
                            </span>
                            <span style="font-weight: 700;">{{ $count }}</span>
                        </div>
                        <div style="height: 5px; background: var(--light); border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: {{ $pct }}%; background: {{ $def['color'] }}; border-radius: 3px; transition: width 1s ease;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Raccourcis --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions rapides</h3>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <a href="{{ route('admin.orders.create') }}" class="quick-action-btn" style="--qa-color: var(--primary);">
                            <i class="fas fa-plus-circle"></i>
                            <span>Nouvelle commande</span>
                        </a>
                        <a href="{{ route('admin.customers.create') }}" class="quick-action-btn" style="--qa-color: var(--success);">
                            <i class="fas fa-user-plus"></i>
                            <span>Nouveau client</span>
                        </a>
                        <a href="{{ route('admin.companies.create') }}" class="quick-action-btn" style="--qa-color: var(--warning);">
                            <i class="fas fa-building"></i>
                            <span>Nouvelle entreprise</span>
                        </a>
                        <a href="{{ route('admin.products.create') }}" class="quick-action-btn" style="--qa-color: #8B5CF6;">
                            <i class="fas fa-box"></i>
                            <span>Nouveau produit</span>
                        </a>
                        <a href="{{ route('admin.installments.index') }}" class="quick-action-btn" style="--qa-color: var(--danger); grid-column: span 2;">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Gérer les échéances</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── LIGNE BASSE ──────────────────────────────────────────────── --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

        {{-- Échéances à venir --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check" style="color: var(--primary);"></i>
                    Échéances dans 7 jours
                </h3>
                <a href="{{ route('admin.installments.index') }}" class="btn btn-outline btn-sm">Voir tout</a>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($upcomingInstallments as $inst)
                @php $customer = $inst->creditPlan?->order?->customer; @endphp
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);">
                    <div style="width: 38px; height: 38px; background: rgba(0,102,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-file-invoice" style="color: var(--primary); font-size: 0.85rem;"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $customer?->first_name }} {{ $customer?->last_name }}
                        </div>
                        <div style="font-size: 0.78rem; color: var(--gray);">
                            Échéance {{ $inst->installment_number }} — {{ $inst->due_date->format('d/m/Y') }}
                        </div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <div style="font-weight: 700; font-size: 0.88rem;">{{ number_format($inst->amount_due, 0, ',', ' ') }}</div>
                        <div style="font-size: 0.75rem; color: var(--gray);">GNF</div>
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray); font-size: 0.88rem;">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem; color: var(--success); display: block; margin-bottom: 0.5rem;"></i>
                    Aucune échéance dans les 7 prochains jours
                </div>
                @endforelse
            </div>
        </div>

        {{-- Échéances en retard --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle" style="color: var(--danger);"></i>
                    Échéances en retard
                    @if($stats['installments_late'] > 0)
                    <span class="badge danger" style="font-size: 0.75rem; margin-left: 0.3rem;">{{ $stats['installments_late'] }}</span>
                    @endif
                </h3>
                <a href="{{ route('admin.installments.index') }}" class="btn btn-outline btn-sm">Voir tout</a>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($lateInstallments as $inst)
                @php $customer = $inst->creditPlan?->order?->customer; @endphp
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);">
                    <div style="width: 38px; height: 38px; background: rgba(239,68,68,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-exclamation" style="color: var(--danger); font-size: 0.85rem;"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; font-size: 0.88rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $customer?->first_name }} {{ $customer?->last_name }}
                        </div>
                        <div style="font-size: 0.78rem; color: var(--danger);">
                            Due le {{ $inst->due_date->format('d/m/Y') }}
                            ({{ $inst->due_date->diffForHumans() }})
                        </div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <div style="font-weight: 700; font-size: 0.88rem; color: var(--danger);">{{ number_format($inst->amount_due, 0, ',', ' ') }}</div>
                        <div style="font-size: 0.75rem; color: var(--gray);">GNF</div>
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray); font-size: 0.88rem;">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem; color: var(--success); display: block; margin-bottom: 0.5rem;"></i>
                    Aucune échéance en retard
                </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ── TOP PRODUITS ─────────────────────────────────────────────── --}}
    @if($topProducts->isNotEmpty())
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Top produits vendus</h3>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">Voir tous les produits</a>
        </div>
        <div class="card-body">
            @php $maxQty = $topProducts->max('qty') ?: 1; @endphp
            <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                @foreach($topProducts as $i => $product)
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 26px; height: 26px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0;">
                        {{ $i + 1 }}
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.3rem;">
                            <span style="font-weight: 600; font-size: 0.88rem;">{{ $product->name }}</span>
                            <span style="font-size: 0.82rem; color: var(--gray);">{{ $product->qty }} vente(s) — {{ number_format($product->revenue, 0, ',', ' ') }} GNF</span>
                        </div>
                        <div style="height: 5px; background: var(--light); border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: {{ round($product->qty / $maxQty * 100) }}%; background: var(--primary); border-radius: 3px;"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>

<style>
    .quick-action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.4rem;
        padding: 0.85rem 0.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 0.78rem;
        font-weight: 600;
        text-align: center;
        color: var(--qa-color);
        background: color-mix(in srgb, var(--qa-color) 10%, transparent);
        border: 1px solid color-mix(in srgb, var(--qa-color) 20%, transparent);
        transition: all 0.2s;
    }

    .quick-action-btn:hover {
        background: color-mix(in srgb, var(--qa-color) 18%, transparent);
        transform: translateY(-1px);
    }

    .quick-action-btn i {
        font-size: 1.2rem;
    }
</style>
@endsection
