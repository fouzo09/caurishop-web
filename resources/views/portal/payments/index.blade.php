@extends('tpl.portal')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Mes paiements</h1>
    </div>

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ number_format($stats['total_paid'], 0, ',', ' ') }} GNF</div>
                <div class="stat-label">Total payé</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['pending_count'] }}</div>
                <div class="stat-label">Échéances à venir</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">{{ $stats['late_count'] }}</div>
                <div class="stat-label">En retard</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

        {{-- Échéanciers --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Mes échéances</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($installments as $inst)
                @php
                    $iMap = [
                        'pending' => ['label' => 'En attente', 'class' => 'badge-secondary'],
                        'partial' => ['label' => 'Partiel',    'class' => 'badge-warning'],
                        'paid'    => ['label' => 'Payé',       'class' => 'badge-success'],
                        'late'    => ['label' => 'En retard',  'class' => 'badge-danger'],
                    ];
                    $is = $iMap[$inst->status] ?? ['label' => $inst->status, 'class' => 'badge-secondary'];
                    $isLate = $inst->status === 'late' || (\Carbon\Carbon::parse($inst->due_date)->isPast() && $inst->status === 'pending');
                @endphp
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">
                            {{ $inst->creditPlan?->order?->order_number ?? '—' }}
                            <span style="font-weight: normal; color: var(--gray);">#{{ $inst->installment_number }}</span>
                        </div>
                        <div style="font-size: 0.8rem; color: {{ $isLate ? '#ef4444' : 'var(--gray)' }};">
                            <i class="fas fa-calendar{{ $isLate ? '-times' : '' }}"></i>
                            {{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700; color: {{ $isLate ? '#ef4444' : 'var(--dark)' }};">
                            {{ number_format($inst->amount_due - $inst->amount_paid, 0, ',', ' ') }} GNF
                        </div>
                        <span class="badge {{ $is['class'] }}" style="font-size: 0.72rem;">{{ $is['label'] }}</span>
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray);">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981; display: block; margin-bottom: 0.5rem;"></i>
                    Aucune échéance
                </div>
                @endforelse
            </div>
            @if($installments instanceof \Illuminate\Pagination\LengthAwarePaginator && $installments->hasPages())
            <div class="card-footer" style="padding: 0.85rem 1.25rem;">
                {{ $installments->links() }}
            </div>
            @endif
        </div>

        {{-- Historique des paiements --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history"></i> Historique des paiements</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($payments as $payment)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-weight: 600; font-size: 0.9rem;">
                            {{ $payment->order?->order_number ?? '—' }}
                        </div>
                        <div style="font-size: 0.8rem; color: var(--gray);">
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}
                            @if($payment->method)
                            · {{ ucfirst($payment->method) }}
                            @endif
                        </div>
                    </div>
                    <div style="font-weight: 700; color: #10b981;">
                        +{{ number_format($payment->amount, 0, ',', ' ') }} GNF
                    </div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray);">
                    Aucun paiement enregistré
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
