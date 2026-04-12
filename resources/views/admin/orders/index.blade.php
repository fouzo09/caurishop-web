@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Commandes</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Commandes</span>
            </div>
        </div>
        <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nouvelle commande
        </a>
    </div>

    @if(session('success'))
    <div class="alert success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session('error'))
    <div class="alert danger"><i class="fas fa-times-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    {{-- Statistiques --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total</div>
                <div class="stat-value">{{ $orders->total() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">Brouillons</div>
                <div class="stat-value">{{ $orders->where('status', 'draft')->count() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Confirmées</div>
                <div class="stat-value">{{ $orders->where('status', 'confirmed')->count() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple" style="background: rgba(139,92,246,0.1); color:#8B5CF6;">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Livrées</div>
                <div class="stat-value">{{ $orders->where('status', 'completed')->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 1rem; align-items: flex-end;">
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Recherche</label>
                        <div style="position: relative;">
                            <i class="fas fa-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--gray); font-size: 0.85rem;"></i>
                            <input type="text" name="search" class="form-input" placeholder="N° commande..."
                                value="{{ request('search') }}" style="padding-left: 2.25rem;">
                        </div>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Statut</label>
                        <select name="status" class="form-input">
                            <option value="">Tous</option>
                            <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Brouillon</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Livrée</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 0.8rem; margin-bottom: 0.25rem;">Paiement</label>
                        <select name="order_type" class="form-input">
                            <option value="">Tous</option>
                            <option value="cash"   {{ request('order_type') === 'cash'   ? 'selected' : '' }}>Comptant</option>
                            <option value="credit" {{ request('order_type') === 'credit' ? 'selected' : '' }}>Crédit</option>
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
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem;">
                    @if(request()->hasAny(['search', 'status', 'order_type', 'customer_id']))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                    @endif
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
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
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Acompte</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" style="font-weight: 600; color: var(--primary);">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $order->customer->displayName() }}</div>
                            @if($order->customer->company)
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $order->customer->company->name }}</div>
                            @endif
                        </td>
                        <td>
                            @if($order->order_type === 'credit')
                            <span class="badge" style="background: rgba(139,92,246,0.1); color:#8B5CF6;">
                                <i class="fas fa-credit-card"></i> Crédit
                            </span>
                            @else
                            <span class="badge success">
                                <i class="fas fa-money-bill"></i> Comptant
                            </span>
                            @endif
                        </td>
                        <td style="font-weight: 600;">{{ number_format($order->total_amount, 0, ',', ' ') }} GNF</td>
                        <td>
                            @if($order->down_payment > 0)
                            <span style="color: var(--success); font-weight: 500;">
                                {{ number_format($order->down_payment, 0, ',', ' ') }} GNF
                            </span>
                            @else
                            <span style="color: var(--gray);">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusMap = [
                                    'draft'     => ['Brouillon', 'warning'],
                                    'confirmed' => ['Confirmée', 'success'],
                                    'completed' => ['Livrée',    'success'],
                                    'cancelled' => ['Annulée',   'danger'],
                                ];
                                [$label, $class] = $statusMap[$order->status] ?? [$order->status, ''];
                            @endphp
                            <span class="badge {{ $class }}">{{ $label }}</span>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--gray); white-space: nowrap;">
                            {{ $order->created_at->format('d/m/Y') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucune commande trouvée
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border);">
                {{ $orders->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
