@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Échéanciers</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Échéanciers</span>
            </div>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ array_sum($counts) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">En attente</div>
                <div class="stat-value">{{ ($counts['pending'] ?? 0) + ($counts['partial'] ?? 0) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: var(--danger);"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">En retard</div>
                <div class="stat-value">{{ $counts['late'] ?? 0 }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Payées</div>
                <div class="stat-value">{{ $counts['paid'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.installments.index') }}">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 1rem; align-items: flex-end;">
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Statut</label>
                        <select name="status" class="form-input">
                            <option value="">Tous</option>
                            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>En attente</option>
                            <option value="partial"  {{ request('status') === 'partial'  ? 'selected' : '' }}>Partiel</option>
                            <option value="late"     {{ request('status') === 'late'     ? 'selected' : '' }}>En retard</option>
                            <option value="paid"     {{ request('status') === 'paid'     ? 'selected' : '' }}>Payée</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Client</label>
                        <select name="customer_id" class="form-input">
                            <option value="">Tous</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->displayName() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Du</label>
                        <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Au</label>
                        <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
                    </div>
                    <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                        @if(request()->hasAny(['status', 'customer_id', 'date_from', 'date_to']))
                        <a href="{{ route('admin.installments.index') }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Commande</th>
                        <th>Échéance</th>
                        <th>Montant dû</th>
                        <th>Payé</th>
                        <th>Reste</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($installments as $installment)
                    @php
                        $order    = $installment->creditPlan->order;
                        $customer = $order->customer;
                        $remaining = (float)$installment->amount_due - (float)$installment->amount_paid;
                        $statusMap = [
                            'pending' => ['En attente', 'warning'],
                            'partial' => ['Partiel',    'warning'],
                            'paid'    => ['Payée',      'success'],
                            'late'    => ['En retard',  'danger'],
                        ];
                        [$label, $class] = $statusMap[$installment->status] ?? [$installment->status, ''];
                    @endphp
                    <tr>
                        <td style="color: var(--gray); font-size: 0.85rem;">{{ $installment->installment_number }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $customer->displayName() }}</div>
                            @if($customer->company)
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $customer->company->name }}</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" style="color: var(--primary); font-weight: 600;">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td style="white-space: nowrap; {{ $installment->status === 'late' ? 'color: var(--danger); font-weight: 600;' : '' }}">
                            {{ $installment->due_date->format('d/m/Y') }}
                        </td>
                        <td>{{ number_format($installment->amount_due, 0, ',', ' ') }} GNF</td>
                        <td style="color: var(--success);">{{ number_format($installment->amount_paid, 0, ',', ' ') }} GNF</td>
                        <td style="font-weight: 600;">{{ number_format(max(0, $remaining), 0, ',', ' ') }} GNF</td>
                        <td><span class="badge {{ $class }}">{{ $label }}</span></td>
                        <td>
                            @if($installment->status !== 'paid')
                            <a href="{{ route('admin.installments.pay', $installment) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-money-bill-wave"></i> Payer
                            </a>
                            @else
                            <span style="color: var(--gray); font-size: 0.85rem;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucune échéance trouvée
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($installments->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
                {{ $installments->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
