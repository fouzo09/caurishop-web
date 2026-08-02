@extends('shop.layouts.app')

@section('title', 'Payer ma commande — CAURISHOP')

@section('content')
<div class="breadcrumb-bar">
  <div class="container-xl d-flex align-items-center gap-2 py-2 px-3 flex-wrap">
    <a href="{{ route('shop.account.index') }}">Mon compte</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <a href="{{ route('shop.account.orders') }}">Mes commandes</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <span class="fw-semibold text-dark">{{ $order->order_number }}</span>
  </div>
</div>

<main class="container-xl py-4">
  <div class="row g-4 align-items-start justify-content-center">
    <div class="col-lg-7 col-xl-6">

      <div class="border rounded-3 p-4">
        <div class="d-flex align-items-center gap-3 mb-4">
          <span class="acct-stat__ic" style="width:52px;height:52px;font-size:22px"><i class="bi bi-wallet2"></i></span>
          <div>
            <h1 class="fw-bolder m-0" style="font-size:20px">Régler ma commande</h1>
            <div class="text-muted" style="font-size:13.5px">{{ $order->order_number }}</div>
          </div>
        </div>

        <div class="border rounded-3 overflow-hidden mb-4">
          @foreach ($order->items as $item)
            <div class="d-flex align-items-center gap-3 p-3 border-bottom">
              <span class="flex-grow-1" style="font-size:13.5px">
                {{ $item->product?->name }}@if ($item->variant)<span class="text-muted"> — {{ $item->variant->name }}</span>@endif
                <span class="text-muted">× {{ $item->quantity }}</span>
              </span>
              <span class="fw-semibold text-nowrap" style="font-size:13.5px">@gnf($item->line_total)</span>
            </div>
          @endforeach
        </div>

        <div class="spec-list mb-4">
          <div class="spec"><i class="bi bi-calendar3"></i><span class="spec__k">Date</span><span class="spec__v">{{ $order->created_at?->translatedFormat('d F Y') }}</span></div>
          <div class="spec"><i class="bi bi-telephone"></i><span class="spec__k">Numéro à débiter</span><span class="spec__v">{{ $customer->phone ?: 'Non renseigné' }}</span></div>
        </div>

        <div class="d-flex align-items-baseline justify-content-between border-top pt-3 mb-4">
          <span class="fw-bolder" style="font-size:16px">Total à payer</span>
          <span class="total-amount">@gnf($order->netTotal())</span>
        </div>

        @if (empty($customer->phone))
          <div class="empty-state border-0 py-4 mb-3">
            <i class="bi bi-telephone-x"></i>
            Renseignez votre numéro de téléphone avant de payer.
            <div class="mt-3"><a href="{{ route('shop.account.profile') }}" class="btn-brand btn-sm">Compléter mon profil</a></div>
          </div>
        @else
          <form method="POST" action="{{ route('portal.djomy.order.checkout.initiate', $order->id) }}">
            @csrf
            <button type="submit" class="btn-brand w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-shield-lock"></i>Payer @gnf($order->netTotal())
            </button>
          </form>

          <div class="d-flex gap-2 flex-wrap justify-content-center mt-3">
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/om.svg') }}" alt="Orange Money" height="18"></span>
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/momo.svg') }}" alt="MTN MoMo" height="18"></span>
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/card.svg') }}" alt="Carte bancaire" height="16"></span>
          </div>
          <div class="text-muted text-center mt-3" style="font-size:12px">
            <i class="bi bi-lock me-1"></i>Vous allez être redirigé vers la page sécurisée de notre partenaire de paiement.
          </div>
        @endif

        <div class="text-center mt-3">
          <a href="{{ route('shop.account.orders.show', $order->id) }}" class="text-brand fw-semibold" style="font-size:13.5px">← Retour à la commande</a>
        </div>
      </div>

    </div>
  </div>
</main>
@endsection
