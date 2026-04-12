@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Paiements</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Paiements</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
    @endif

    {{-- Filtres --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.index') }}">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 1rem; align-items: flex-end;">
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
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Méthode</label>
                        <select name="method" class="form-input">
                            <option value="">Toutes</option>
                            <option value="cash"         {{ request('method') === 'cash'         ? 'selected' : '' }}>Espèces</option>
                            <option value="transfer"     {{ request('method') === 'transfer'     ? 'selected' : '' }}>Virement</option>
                            <option value="mobile_money" {{ request('method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="card"         {{ request('method') === 'card'         ? 'selected' : '' }}>Carte</option>
                            <option value="other"        {{ request('method') === 'other'        ? 'selected' : '' }}>Autre</option>
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
                        @if(request()->hasAny(['customer_id', 'method', 'date_from', 'date_to']))
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline btn-sm">
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
                        <th>Date</th>
                        <th>Client</th>
                        <th>Commande</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Référence</th>
                        <th>Enregistré par</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($payments as $payment)
                    @php
                        $methodLabels = [
                            'cash'         => ['Espèces',      'success'],
                            'transfer'     => ['Virement',     'blue'],
                            'mobile_money' => ['Mobile Money', 'purple'],
                            'card'         => ['Carte',        'blue'],
                            'other'        => ['Autre',        ''],
                        ];
                        [$methodLabel, $methodClass] = $methodLabels[$payment->method] ?? [$payment->method, ''];
                    @endphp
                    <tr>
                        <td style="white-space: nowrap; font-size: 0.85rem;">
                            {{ $payment->payment_date->format('d/m/Y') }}
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $payment->customer->displayName() }}</div>
                        </td>
                        <td>
                            @if($payment->order)
                            <a href="{{ route('admin.orders.show', $payment->order) }}" style="color: var(--primary); font-weight: 600;">
                                {{ $payment->order->order_number }}
                            </a>
                            @else
                            <span style="color: var(--gray);">—</span>
                            @endif
                        </td>
                        <td style="font-weight: 700; color: var(--primary);">
                            {{ number_format($payment->amount, 0, ',', ' ') }} GNF
                        </td>
                        <td>
                            <span class="badge {{ $methodClass }}">{{ $methodLabel }}</span>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--gray);">
                            {{ $payment->reference ?? '—' }}
                        </td>
                        <td style="font-size: 0.85rem; color: var(--gray);">
                            {{ $payment->creator?->name ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucun paiement trouvé
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($payments->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
                {{ $payments->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
