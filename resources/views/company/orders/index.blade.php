@extends('tpl.company')

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Commandes</h1>
            <p style="color: var(--gray); margin-top: 0.25rem; font-size: 0.9rem;">{{ $company->name }}</p>
        </div>
        <a href="{{ route('company.orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle commande
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; color: #065f46; display: flex; align-items: center; gap: 0.6rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert" style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; color: #991b1b; display: flex; align-items: center; gap: 0.6rem;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Filtres --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            @php
                $statuses = [
                    ''                 => 'Toutes',
                    'pending_approval' => 'En attente',
                    'draft'            => 'Brouillon',
                    'confirmed'        => 'Confirmée',
                    'completed'        => 'Livrée',
                    'cancelled'        => 'Annulée',
                ];
            @endphp
            @foreach($statuses as $val => $label)
            <a href="{{ route('company.orders.index', $val ? ['status' => $val] : []) }}"
               style="padding: 0.4rem 0.85rem; border-radius: 20px; font-size: 0.85rem; text-decoration: none; transition: all 0.15s;
                      {{ $status === $val ? 'background: var(--primary); color: #fff;' : 'background: var(--light); color: var(--dark);' }}">
                {{ $label }}
                @if($val === 'pending_approval' && $pending > 0)
                <span style="background: #f59e0b; color: #fff; border-radius: 9999px; padding: 0.1rem 0.4rem; font-size: 0.72rem; margin-left: 0.2rem;">{{ $pending }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client / Employé</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
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
                <tr>
                    <td><strong>{{ $order->order_number }}</strong></td>
                    <td>{{ $order->customer?->first_name }} {{ $order->customer?->last_name }}</td>
                    <td>
                        @if($order->order_type === 'credit')
                        <span class="badge badge-primary">Crédit</span>
                        @else
                        <span class="badge badge-success">Comptant</span>
                        @endif
                    </td>
                    <td>{{ number_format($order->total_amount, 0, ',', ' ') }} GNF</td>
                    <td><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('company.orders.show', $order->id) }}" class="btn btn-sm btn-outline">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($order->status === 'pending_approval')
                        <form action="{{ route('company.orders.approve', $order->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" title="Approuver">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form action="{{ route('company.orders.reject', $order->id) }}" method="POST" style="display: inline;"
                              onsubmit="return confirm('Rejeter cette commande ?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" title="Rejeter">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray);">Aucune commande trouvée</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="card-footer" style="padding: 1rem 1.25rem;">
            {{ $orders->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
