@extends('tpl.portal')

@section('content')
<div class="page-content" style="max-width:520px;margin:0 auto;">
    <div class="page-header">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <a href="{{ route('portal.payments.index') }}" style="color:var(--gray);text-decoration:none;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">Paiement de l'échéance</h1>
        </div>
    </div>

    @if(session('error'))
    <div class="alert danger" style="margin-bottom:1rem;">
        <i class="fas fa-exclamation-triangle"></i> <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Récapitulatif échéance --}}
    <div class="card" style="margin-bottom:1.25rem;">
        <div class="card-body">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
                <div>
                    <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Commande</div>
                    <div style="font-weight:700;">{{ $installment->creditPlan->order->order_number }}</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Échéance</div>
                    <div style="font-weight:700;">#{{ $installment->installment_number }}</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Date limite</div>
                    <div style="font-weight:600;">{{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Montant dû</div>
                    <div style="font-weight:800;font-size:1.3rem;color:var(--primary);">
                        {{ number_format($installment->amount_due - $installment->amount_paid, 0, ',', ' ') }} GNF
                    </div>
                </div>
            </div>
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
                Vous allez être redirigé vers le portail Djomy pour finaliser votre paiement
                (Orange Money, MTN Mobile Money, carte bancaire…).
            </p>

            {{-- Payment methods icons --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
                <span style="font-size:.75rem;font-weight:600;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;">Modes acceptés :</span>
                <span style="background:#FF7A00;color:#fff;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:4px;">Orange Money</span>
                <span style="background:#FFCC00;color:#333;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:4px;">MTN MoMo</span>
                <span style="background:#1A1A2E;color:#fff;font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:4px;">Carte</span>
            </div>

            <form action="{{ route('portal.djomy.checkout.initiate', $installment) }}" method="POST" id="djomyForm">
                @csrf
                <div style="display:flex;gap:.75rem;justify-content:space-between;align-items:center;">
                    <a href="{{ route('portal.payments.index') }}" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary" id="btnPay"
                            style="background:linear-gradient(135deg,#FF7A00,#FF9736);border:none;font-weight:700;font-size:1rem;padding:.65rem 1.5rem;">
                        <i class="fas fa-lock" style="margin-right:.4rem;"></i>
                        Payer {{ number_format($installment->amount_due - $installment->amount_paid, 0, ',', ' ') }} GNF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('djomyForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnPay');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirection…';
});
</script>
@endsection
