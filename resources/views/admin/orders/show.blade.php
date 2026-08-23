@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $order->order_number }}</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.orders.index') }}">Commandes</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ $order->order_number }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            @if($order->status === 'draft')
            <form action="{{ route('admin.orders.confirm', $order) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-check"></i> Confirmer
                </button>
            </form>
            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger);"
                    onclick="return confirm('Annuler cette commande ?')">
                    <i class="fas fa-times"></i> Annuler
                </button>
            </form>
            @elseif($order->status === 'confirmed')
            <form action="{{ route('admin.orders.deliver', $order) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-truck"></i> Marquer comme livrée
                </button>
            </form>
            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger);"
                    onclick="return confirm('Annuler cette commande ?')">
                    <i class="fas fa-times"></i> Annuler
                </button>
            </form>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

        {{-- Colonne principale --}}
        <div>
            {{-- Articles --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Articles commandés</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-container">
                        <table>
                            <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Prix unitaire</th>
                                <th>Qté</th>
                                <th>Total ligne</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $item->product->name }}</div>
                                    @if($item->variant)
                                    <div style="font-size: 0.8rem; color: var(--gray);">
                                        Variante: {{ $item->variant->name }}
                                    </div>
                                    @endif
                                    <div style="font-size: 0.8rem; color: var(--gray);">
                                        SKU: {{ $item->variant?->sku ?? $item->product->sku ?? '—' }}
                                    </div>
                                </td>
                                <td>{{ number_format($item->unit_price, 0, ',', ' ') }} GNF</td>
                                <td>{{ $item->quantity }}</td>
                                <td style="font-weight: 600;">{{ number_format($item->line_total, 0, ',', ' ') }} GNF</td>
                            </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr style="background: var(--light);">
                                <td colspan="3" style="text-align: right; font-weight: 600; padding: 0.75rem 1rem;">Total</td>
                                <td style="font-weight: 700; color: var(--primary); font-size: 1.1rem;">
                                    {{ number_format($order->total_amount, 0, ',', ' ') }} GNF
                                </td>
                            </tr>
                            @if($order->down_payment > 0)
                            <tr>
                                <td colspan="3" style="text-align: right; font-weight: 600; padding: 0.5rem 1rem;">Acompte versé</td>
                                <td style="color: var(--success);">- {{ number_format($order->down_payment, 0, ',', ' ') }} GNF</td>
                            </tr>
                            <tr style="background: var(--light);">
                                <td colspan="3" style="text-align: right; font-weight: 600; padding: 0.75rem 1rem;">Montant à financer</td>
                                <td style="font-weight: 700; color: #8B5CF6; font-size: 1.1rem;">
                                    {{ number_format($order->total_amount - $order->down_payment, 0, ',', ' ') }} GNF
                                </td>
                            </tr>
                            @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Échéancier (si crédit confirmé) --}}
            @if($order->isCredit() && $order->creditPlan)
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Échéancier</h3>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <span style="font-size: 0.85rem; color: var(--gray);">
                            {{ $order->creditPlan->installments_count }} mensualités ·
                            {{ number_format($order->creditPlan->outstanding_amount, 0, ',', ' ') }} GNF restants
                        </span>
                        @if($order->creditPlan->isClosed())
                        <span class="badge success">Soldé</span>
                        @else
                        <span class="badge warning">En cours</span>
                        @endif
                    </div>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-container">
                        <table>
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Échéance</th>
                                <th>Montant dû</th>
                                <th>Payé</th>
                                <th>Reste</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->creditPlan->installments->sortBy('installment_number') as $installment)
                            <tr>
                                <td style="color: var(--gray);">{{ $installment->installment_number }}</td>
                                <td>{{ $installment->due_date->format('d/m/Y') }}</td>
                                <td>{{ number_format($installment->amount_due, 0, ',', ' ') }} GNF</td>
                                <td style="color: var(--success);">{{ number_format($installment->amount_paid, 0, ',', ' ') }} GNF</td>
                                <td style="font-weight: 600;">{{ number_format($installment->remainingAmount(), 0, ',', ' ') }} GNF</td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'pending' => ['En attente', 'warning'],
                                            'partial' => ['Partiel',    'warning'],
                                            'paid'    => ['Payé',       'success'],
                                            'late'    => ['En retard',  'danger'],
                                        ];
                                        [$label, $class] = $statusMap[$installment->status] ?? [$installment->status, ''];
                                    @endphp
                                    <span class="badge {{ $class }}">{{ $label }}</span>
                                </td>
                                <td>
                                    @if($installment->status !== 'paid')
                                    <a href="{{ route('admin.installments.pay', $installment) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-money-bill-wave"></i> Payer
                                    </a>
                                    @else
                                    <span style="color: var(--gray);">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Panneau latéral --}}
        <div>
            {{-- Statut commande --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Statut</h3></div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Commande</span>
                            @php
                                $sMap = ['pending_payment'=>['Paiement en attente','warning'],'pending_approval'=>['En attente valid.','warning'],'draft'=>['Brouillon','secondary'],'confirmed'=>['Confirmée','success'],'completed'=>['Livrée','success'],'cancelled'=>['Annulée','danger']];
                                [$sl, $sc] = $sMap[$order->status] ?? [$order->status,''];
                            @endphp
                            <span class="badge {{ $sc }}">{{ $sl }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Paiement</span>
                            @if($order->isCredit())
                            <span class="badge" style="background: rgba(139,92,246,0.1); color:#8B5CF6;">Crédit</span>
                            @else
                            <span class="badge success">Comptant</span>
                            @endif
                        </div>
                        @if($order->confirmed_at)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Confirmée le</span>
                            <span style="font-size: 0.9rem;">{{ $order->confirmed_at->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($order->delivered_at)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Livrée le</span>
                            <span style="font-size: 0.9rem;">{{ $order->delivered_at->format('d/m/Y') }}</span>
                        </div>
                        @endif
                        @if($order->creator)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Créée par</span>
                            <span style="font-size: 0.9rem;">{{ $order->creator->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Client --}}
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header"><h3 class="card-title">Client</h3></div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="font-weight: 600;">{{ $order->customer->displayName() }}</div>
                        @if($order->customer->company)
                        <div style="font-size: 0.85rem; color: var(--gray);">
                            <i class="fas fa-building"></i> {{ $order->customer->company->name }}
                        </div>
                        @endif
                        @if($order->customer->phone)
                        <div style="font-size: 0.85rem; color: var(--gray);">
                            <i class="fas fa-phone"></i> {{ $order->customer->phone }}
                        </div>
                        @endif
                        @if($order->customer->email)
                        <div style="font-size: 0.85rem; color: var(--gray);">
                            <i class="fas fa-envelope"></i> {{ $order->customer->email }}
                        </div>
                        @endif
                        @php $available = $order->customer->availableCredit(); @endphp
                        @if(!is_null($available))
                        <div style="margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border);">
                            <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.25rem;">Crédit disponible</div>
                            <div style="font-weight: 600; color: {{ $available > 0 ? 'var(--success)' : 'var(--danger)' }};">
                                {{ number_format($available, 0, ',', ' ') }} GNF
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Résumé crédit --}}
            @if($order->isCredit())
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header"><h3 class="card-title">Crédit</h3></div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Mensualités</span>
                            <span style="font-weight: 600;">{{ $order->credit_installments_count }}x</span>
                        </div>
                        @if($order->down_payment > 0)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Acompte</span>
                            <span style="font-weight: 600; color: var(--success);">{{ number_format($order->down_payment, 0, ',', ' ') }} GNF</span>
                        </div>
                        @endif
                        @php $creditAmount = $order->total_amount - $order->down_payment; @endphp
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Montant financé</span>
                            <span style="font-weight: 600; color: #8B5CF6;">{{ number_format($creditAmount, 0, ',', ' ') }} GNF</span>
                        </div>
                        @if($order->credit_installments_count > 0)
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Mensualité</span>
                            <span style="font-weight: 600; color: var(--primary);">
                                {{ number_format($creditAmount / $order->credit_installments_count, 0, ',', ' ') }} GNF
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
