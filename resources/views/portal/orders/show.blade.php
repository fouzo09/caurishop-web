@extends('tpl.portal')

@section('content')
<div class="page-content">
    <div class="page-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('portal.orders.index') }}" style="color: var(--gray); text-decoration: none;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">{{ $order->order_number }}</h1>
                <p style="color: var(--gray); margin-top: 0.25rem; font-size: 0.9rem;">
                    Créée le {{ $order->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 8px; color: #065f46; display: flex; align-items: center; gap: 0.6rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @php
        $statusMap = [
            'pending_payment'  => ['label' => 'Paiement en attente',    'class' => 'badge-warning',   'msg' => 'Votre commande est en attente de paiement.'],
            'draft'            => ['label' => 'Brouillon',              'class' => 'badge-secondary', 'msg' => null],
            'confirmed'        => ['label' => 'Confirmée',              'class' => 'badge-primary',   'msg' => null],
            'completed'        => ['label' => 'Livrée',                 'class' => 'badge-success',   'msg' => null],
            'cancelled'        => ['label' => 'Annulée',                'class' => 'badge-danger',    'msg' => 'Cette commande a été annulée.'],
            'pending_approval' => ['label' => 'En attente de validation','class' => 'badge-warning',  'msg' => 'Votre commande est en attente de validation par votre responsable.'],
        ];
        $s = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => 'badge-secondary', 'msg' => null];
    @endphp

    @if($s['msg'])
    <div style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 8px; color: #92400e; display: flex; align-items: center; gap: 0.6rem;">
        <i class="fas fa-info-circle"></i> {{ $s['msg'] }}
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

        {{-- Articles --}}
        <div>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Articles commandés</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produit</th>
                                <th>Variante</th>
                                <th>Prix unitaire</th>
                                <th>Qté</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? '—' }}</td>
                            <td>{{ $item->variant?->name ?? '—' }}</td>
                            <td>{{ number_format($item->unit_price, 0, ',', ' ') }} GNF</td>
                            <td>{{ $item->quantity }}</td>
                            <td><strong>{{ number_format($item->line_total, 0, ',', ' ') }} GNF</strong></td>
                        </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 600;">Total :</td>
                                <td><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} GNF</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($order->creditPlan)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Mon échéancier</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.25rem;">
                        <div style="text-align: center; padding: 0.75rem; background: var(--light); border-radius: 8px;">
                            <div style="font-size: 0.8rem; color: var(--gray);">Acompte versé</div>
                            <div style="font-weight: 700; margin-top: 0.25rem;">{{ number_format($order->creditPlan->down_payment_amount, 0, ',', ' ') }} GNF</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background: var(--light); border-radius: 8px;">
                            <div style="font-size: 0.8rem; color: var(--gray);">Restant dû</div>
                            <div style="font-weight: 700; margin-top: 0.25rem; color: #ef4444;">{{ number_format($order->creditPlan->outstanding_amount, 0, ',', ' ') }} GNF</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background: var(--light); border-radius: 8px;">
                            <div style="font-size: 0.8rem; color: var(--gray);">Mensualités</div>
                            <div style="font-weight: 700; margin-top: 0.25rem;">{{ $order->creditPlan->installments_count }}</div>
                        </div>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Échéance</th>
                                <th>Montant dû</th>
                                <th>Payé</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->creditPlan->installments as $inst)
                        @php
                            $iMap = [
                                'pending' => ['label' => 'En attente', 'class' => 'badge-secondary'],
                                'partial' => ['label' => 'Partiel',    'class' => 'badge-warning'],
                                'paid'    => ['label' => 'Payé',       'class' => 'badge-success'],
                                'late'    => ['label' => 'En retard',  'class' => 'badge-danger'],
                            ];
                            $is = $iMap[$inst->status] ?? ['label' => $inst->status, 'class' => 'badge-secondary'];
                        @endphp
                        <tr>
                            <td>{{ $inst->installment_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($inst->due_date)->format('d/m/Y') }}</td>
                            <td>{{ number_format($inst->amount_due, 0, ',', ' ') }} GNF</td>
                            <td>{{ number_format($inst->amount_paid, 0, ',', ' ') }} GNF</td>
                            <td><span class="badge {{ $is['class'] }}">{{ $is['label'] }}</span></td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Infos latérales --}}
        <div class="card" style="height: fit-content;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Informations</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <div style="font-size: 0.78rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em;">Statut</div>
                        <div style="margin-top: 0.35rem;"><span class="badge {{ $s['class'] }}">{{ $s['label'] }}</span></div>
                    </div>
                    <div>
                        <div style="font-size: 0.78rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em;">Type</div>
                        <div style="margin-top: 0.35rem; font-weight: 600;">
                            {{ $order->order_type === 'credit' ? 'Crédit' : 'Comptant' }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.78rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em;">Montant total</div>
                        <div style="margin-top: 0.35rem; font-weight: 700; font-size: 1.1rem; color: var(--primary);">
                            {{ number_format($order->total_amount, 0, ',', ' ') }} GNF
                        </div>
                    </div>
                    @if($order->down_payment > 0)
                    <div>
                        <div style="font-size: 0.78rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em;">Acompte</div>
                        <div style="margin-top: 0.35rem; font-weight: 600;">
                            {{ number_format($order->down_payment, 0, ',', ' ') }} GNF
                        </div>
                    </div>
                    @endif
                    @if($order->confirmed_at)
                    <div>
                        <div style="font-size: 0.78rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em;">Confirmée le</div>
                        <div style="margin-top: 0.35rem; font-weight: 600;">
                            {{ $order->confirmed_at->format('d/m/Y') }}
                        </div>
                    </div>
                    @endif
                    @if($order->delivered_at)
                    <div>
                        <div style="font-size: 0.78rem; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em;">Livrée le</div>
                        <div style="margin-top: 0.35rem; font-weight: 600; color: #10b981;">
                            {{ $order->delivered_at->format('d/m/Y') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
