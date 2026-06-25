@extends('tpl.portal')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Mes commandes</h1>
        <a href="{{ route('portal.orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle commande
        </a>
    </div>

    @if(session('success'))
    <div style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; color: #065f46; display: flex; align-items: center; gap: 0.6rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Type</th>
                        <th>Articles</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                @php
                    $statusMap = [
                        'pending_payment'  => ['label' => 'Paiement en attente', 'class' => 'badge-warning'],
                        'draft'            => ['label' => 'Brouillon',           'class' => 'badge-secondary'],
                        'confirmed'        => ['label' => 'Confirmée',           'class' => 'badge-primary'],
                        'completed'        => ['label' => 'Livrée',             'class' => 'badge-success'],
                        'cancelled'        => ['label' => 'Annulée',            'class' => 'badge-danger'],
                        'pending_approval' => ['label' => 'En attente valid.',  'class' => 'badge-warning'],
                    ];
                    $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'badge-secondary'];
                @endphp
                <tr>
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>
                        @if($order->order_type === 'credit')
                        <span class="badge badge-primary">Crédit</span>
                        @else
                        <span class="badge badge-success">Comptant</span>
                        @endif
                    </td>
                    <td>{{ $order->items->count() }} article(s)</td>
                    <td>{{ number_format($order->total_amount, 0, ',', ' ') }} GNF</td>
                    <td><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('portal.orders.show', $order->id) }}" class="btn btn-sm btn-outline">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($order->order_type === 'cash' && in_array($order->status, ['draft', 'pending_payment']))
                        <form action="{{ route('portal.djomy.order.checkout.initiate', $order->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary" style="margin-left:.35rem;">
                                <i class="fas fa-redo"></i> Relancer
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--gray);">
                        <i class="fas fa-shopping-cart" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.3;"></i>
                        Aucune commande
                        <div style="margin-top: 0.75rem;">
                            <a href="{{ route('portal.orders.create') }}" class="btn btn-sm btn-primary">Passer une commande</a>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($orders, 'hasPages') && $orders->hasPages())
        <div class="card-footer" style="padding: 1rem 1.25rem;">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
