@extends('tpl.portal')

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Bonjour, {{ $user->name }} 👋</h1>
            <p style="color: var(--gray); margin-top: 0.25rem; font-size: 0.9rem;">
                {{ $user->company?->name ?? 'Mon espace' }}
            </p>
        </div>
        <a href="{{ route('portal.orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Passer une commande
        </a>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <div class="stat-label">Mes commandes</div>
                <div class="stat-value">{{ $stats['orders_total'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-amber"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">En attente</div>
                <div class="stat-value">{{ $stats['orders_pending'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Paiements en retard</div>
                <div class="stat-value">{{ $stats['late_payments'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon-green"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <div class="stat-label">Produits disponibles</div>
                <div class="stat-value">{{ $stats['products_count'] }}</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">

        {{-- Commandes récentes --}}
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title"><i class="fas fa-history"></i> Mes dernières commandes</h3>
                <a href="{{ route('portal.orders.index') }}" style="font-size: 0.85rem; color: var(--primary);">Voir tout</a>
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
                        <div style="font-size: 0.8rem; color: var(--gray);">{{ $order->created_at->format('d/m/Y') }}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                        <a href="{{ route('portal.orders.show', $order->id) }}" style="color: var(--primary); font-size: 0.85rem;">Voir</a>
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray);">
                    <i class="fas fa-shopping-cart" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                    Aucune commande
                    <div style="margin-top: 0.75rem;">
                        <a href="{{ route('portal.orders.create') }}" class="btn btn-sm btn-primary">Passer une commande</a>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Prochaines échéances --}}
        <div class="card">
            <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Prochaines échéances</h3>
                <a href="{{ route('portal.payments.index') }}" style="font-size: 0.85rem; color: var(--primary);">Voir tout</a>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($upcomingInstallments as $inst)
                @php
                    $isLate = \Carbon\Carbon::parse($inst->due_date)->isPast();
                @endphp
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">
                            {{ $inst->creditPlan?->order?->order_number ?? '—' }}
                            <span style="font-weight: normal; color: var(--gray);">· Échéance #{{ $inst->installment_number }}</span>
                        </div>
                        <div style="font-size: 0.8rem; color: {{ $isLate ? '#ef4444' : 'var(--gray)' }};">
                            <i class="fas fa-{{ $isLate ? 'exclamation-circle' : 'calendar' }}"></i>
                            {{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }}
                            @if($isLate) <strong>(En retard)</strong> @endif
                        </div>
                    </div>
                    <div style="font-weight: 700; color: {{ $isLate ? '#ef4444' : 'var(--dark)' }};">
                        {{ number_format($inst->amount_due - $inst->amount_paid, 0, ',', ' ') }} GNF
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray);">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981; display: block; margin-bottom: 0.5rem;"></i>
                    Aucune échéance à venir
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
