@extends('tpl.portal')

@section('content')
<div class="page-content" style="max-width:560px;margin:0 auto;">
    <div class="page-header">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <a href="{{ route('portal.orders.index') }}" style="color:var(--gray);text-decoration:none;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Paiement de la commande</h1>
        </div>
    </div>

    @if(session('error'))
    <div class="alert danger" style="margin-bottom:1rem;">
        <i class="fas fa-exclamation-triangle"></i> <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Récapitulatif commande --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-shopping-bag"></i> {{ $order->order_number }}</h3>
            <span style="font-size:.8rem;color:var(--gray);">{{ $order->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="card-body" style="padding:0;">
            <table class="table" style="margin:0;">
                <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product?->name ?? '—' }}
                        @if($item->variant)
                        <small style="color:var(--gray);"> · {{ $item->variant->name }}</small>
                        @endif
                    </td>
                    <td style="text-align:right;white-space:nowrap;">{{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', ' ') }} GNF</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:var(--light);">
                        <td style="font-weight:700;text-align:right;">Total à payer</td>
                        <td style="font-weight:800;font-size:1.15rem;color:var(--primary);text-align:right;white-space:nowrap;">
                            {{ number_format($order->total_amount, 0, ',', ' ') }} GNF
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Djomy card --}}
    <div class="card" style="overflow:hidden;">
        {{-- Orange header with real logo --}}
        <div style="background:linear-gradient(135deg,#FF7A00,#FF9736);padding:1.5rem;display:flex;align-items:center;justify-content:center;">
            <img src="{{ asset('assets/djomy-logo.svg') }}" alt="Djomy" style="height:40px;filter:brightness(0) invert(1);">
        </div>

        <div class="card-body" style="padding:1.5rem;">
            <p style="font-size:.88rem;color:var(--gray);margin-bottom:1.5rem;text-align:center;line-height:1.6;">
                Vous allez être redirigé vers le portail Djomy pour finaliser votre paiement.
                La commande sera confirmée automatiquement dès réception du paiement.
            </p>

            {{-- Payment methods icons --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
                <span style="font-size:.75rem;font-weight:600;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;">Modes acceptés :</span>
                <span style="background:#FF7A00;color:#fff;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:4px;">Orange Money</span>
                <span style="background:#FFCC00;color:#333;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:4px;">MTN MoMo</span>
                <span style="background:#1A1A2E;color:#fff;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:4px;">Carte</span>
            </div>

            <form action="{{ route('portal.djomy.order.checkout.initiate', $order) }}" method="POST" id="djomyOrderForm">
                @csrf
                <div style="display:flex;gap:.75rem;justify-content:space-between;align-items:center;">
                    <a href="{{ route('portal.orders.index') }}" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary" id="btnPayOrder"
                            style="background:linear-gradient(135deg,#FF7A00,#FF9736);border:none;font-weight:700;font-size:1rem;padding:.65rem 1.5rem;">
                        <i class="fas fa-lock" style="margin-right:.4rem;"></i>
                        Payer {{ number_format($order->total_amount, 0, ',', ' ') }} GNF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('djomyOrderForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnPayOrder');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirection…';
});
</script>
@endsection
