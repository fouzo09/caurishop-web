@extends('tpl.company')

@section('content')
<div class="page-content">

    {{-- Alerts --}}
    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; color: #065f46; display: flex; align-items: center; gap: 0.6rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="page-header">
        <div>
            <h1 class="page-title">Tableau de bord</h1>
            <p style="color: var(--gray); margin-top: 0.25rem; font-size: 0.9rem;">{{ $company->name }}</p>
        </div>
        <a href="{{ route('company.orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Passer une commande
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
        <div class="stat-card">
            <div class="stat-icon stat-icon-slate"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-label">Employés</div>
                <div class="stat-value">{{ $stats['employees'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-amber"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">En attente</div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total commandes</div>
                <div class="stat-value">{{ $stats['orders_total'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green"><i class="fas fa-calendar"></i></div>
            <div class="stat-info">
                <div class="stat-label">Ce mois</div>
                <div class="stat-value">{{ $stats['orders_month'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Retards</div>
                <div class="stat-value">{{ $stats['late_installments'] }}</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">

        {{-- Commandes en attente --}}
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title">
                    <i class="fas fa-clock" style="color: #f59e0b;"></i>
                    Commandes à valider
                    @if($stats['pending'] > 0)
                    <span class="badge" style="background: #f59e0b; color: #fff; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 9999px; margin-left: 0.4rem;">{{ $stats['pending'] }}</span>
                    @endif
                </h3>
                <a href="{{ route('company.orders.index', ['status' => 'pending_approval']) }}" style="font-size: 0.85rem; color: var(--primary);">Voir tout</a>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($pendingOrders as $order)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ $order->order_number }}</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">
                            {{ $order->customer?->first_name }} {{ $order->customer?->last_name }}
                            · {{ $order->items->count() }} article(s)
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 600; font-size: 0.9rem;">{{ number_format($order->total_amount, 0, ',', ' ') }} GNF</span>
                        <a href="{{ route('company.orders.show', $order->id) }}" class="btn btn-sm btn-primary">Examiner</a>
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray);">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem; display: block;"></i>
                    Aucune commande en attente
                </div>
                @endforelse
            </div>
        </div>

        {{-- Commandes récentes --}}
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title"><i class="fas fa-history"></i> Commandes récentes</h3>
                <a href="{{ route('company.orders.index') }}" style="font-size: 0.85rem; color: var(--primary);">Voir tout</a>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($recentOrders as $order)
                @php
                    $statusMap = [
                        'draft'            => ['label' => 'Brouillon',  'class' => 'badge-secondary'],
                        'confirmed'        => ['label' => 'Confirmée',  'class' => 'badge-primary'],
                        'completed'        => ['label' => 'Livrée',     'class' => 'badge-success'],
                        'cancelled'        => ['label' => 'Annulée',    'class' => 'badge-danger'],
                        'pending_approval' => ['label' => 'En attente', 'class' => 'badge-warning'],
                    ];
                    $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'badge-secondary'];
                @endphp
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">{{ $order->order_number }}</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">
                            {{ $order->customer?->first_name }} {{ $order->customer?->last_name }}
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                        <a href="{{ route('company.orders.show', $order->id) }}" style="color: var(--primary); font-size: 0.85rem;">Voir</a>
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray);">Aucune commande</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
