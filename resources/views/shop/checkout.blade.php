@extends('shop.layouts.app')

@section('title', 'Paiement & livraison — CAURISHOP')

@section('content')
@php
    $c = $customer;
    $first = old('first_name', $c->first_name ?? '');
    $last  = old('last_name', $c->last_name ?? '');
    $phone = old('phone', $c->phone ?? '');
@endphp
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('shop.cart.index') }}">Panier</a> › <span class="crumb-current">Paiement</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">Paiement &amp; livraison</h1>
  </div>
</div>

<form method="POST" action="{{ route('shop.checkout.store') }}">
  @csrf
<div class="container-xl py-4">
  <div class="row g-4">

    <div class="col-lg-8 d-flex flex-column gap-3">

      <!-- 1. Livraison -->
      <div class="panel p-4">
        <div class="step-title"><span class="step-num">1</span> Informations de livraison</div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Prénom</label><input name="first_name" value="{{ $first }}" class="form-control" placeholder="Aïssatou"></div>
          <div class="col-md-6"><label class="form-label">Nom</label><input name="last_name" value="{{ $last }}" class="form-control" placeholder="Diallo"></div>
          <div class="col-md-6"><label class="form-label">Téléphone</label><input name="phone" value="{{ $phone }}" class="form-control" placeholder="+224 6 00 00 00 00"></div>
          <div class="col-12"><label class="form-label">Adresse / quartier</label><input name="address" value="{{ old('address') }}" class="form-control" placeholder="Ex : Almamya, Rue KA 020, en face de la pharmacie"></div>
          <div class="col-md-6"><label class="form-label">Ville / Région</label>
            <select name="city" class="form-select">
              @foreach (['Conakry','Kindia','Boké','Labé','Mamou','Faranah','Kankan','N\'Zérékoré'] as $ville)
                <option value="{{ $ville }}" @selected(old('city') === $ville)>{{ $ville }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <!-- 2. Mode de livraison -->
      <div class="panel p-4">
        <div class="step-title"><span class="step-num">2</span> Mode de livraison</div>
        @foreach ($deliveries as $key => $d)
          <label class="choice-card d-flex align-items-center gap-3">
            <input class="form-check-input m-0 js-delivery" type="radio" name="delivery_method" value="{{ $key }}" data-cost="{{ $d['fee'] }}" @checked($loop->first)>
            <span class="fs-4">{{ $key === 'express' ? '⚡' : '🚚' }}</span>
            <span class="flex-grow-1">
              <span class="d-block fw-bold">{{ $d['label'] }}</span>
              <span class="d-block small text-muted">{{ $key === 'express' ? 'Le jour même à Conakry (avant 12h)' : 'Conakry 24h · régions 2-4 jours' }}</span>
            </span>
            <span class="fw-bold text-brand">{{ $d['fee'] === 0 ? 'Offerte' : \App\Support\Money::gnf($d['fee']) }}</span>
          </label>
        @endforeach
      </div>

      <!-- 3. Paiement -->
      <div class="panel p-4">
        <div class="step-title"><span class="step-num">3</span> Moyen de paiement</div>
        <div class="row g-2">
          @foreach ($methods as $method)
            <div class="col-md-6"><label class="choice-card d-flex align-items-center gap-3 mb-0 h-100">
              <input class="form-check-input m-0 js-payment" type="radio" name="payment_method" value="{{ $method->key() }}" data-phone="{{ $method->requiresPhone() ? '1' : '0' }}" @checked($loop->first)>
              <span class="pay-chip">{{ $method->icon() }}</span><span class="fw-bold small">{{ $method->label() }}</span>
            </label></div>
          @endforeach
        </div>
        <div class="pay-hint mt-3">
          <div id="payPhoneWrap">
            <div class="fw-bold text-brand small mb-2">Un code de confirmation vous sera envoyé sur votre numéro Mobile Money.</div>
            <input class="form-control" name="payment_phone" value="{{ old('payment_phone') }}" id="payPhone" placeholder="Numéro Mobile Money (+224 …)">
          </div>
        </div>
      </div>
    </div>

    <!-- Récapitulatif -->
    <aside class="col-lg-4">
      <div class="panel p-4 sticky-lg-top summary-sticky">
        <div class="fs-5 fw-bold text-ink mb-3">Votre commande</div>
        @foreach ($summary['items'] as $item)
          @php $cover = $item['product']->coverUrl(); @endphp
          <div class="d-flex align-items-center gap-3 mb-3">
            <span class="ck-thumb"><span>🛍️</span>@if ($cover)<img class="mini__img" src="{{ $cover }}" alt="{{ $item['product']->name }}" loading="lazy" onerror="this.remove()">@endif</span>
            <span class="flex-grow-1 small text-ink">{{ $item['product']->name }} <span class="text-muted">×{{ $item['quantity'] }}</span></span>
            <span class="fw-bold small text-brand text-nowrap">@gnf($item['line_total'])</span>
          </div>
        @endforeach
        <hr class="my-3">
        <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Sous-total</span><span>@gnf($summary['subtotal'])</span></div>
        <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Livraison</span><span id="ck-delivery">Offerte</span></div>
        @if ($summary['discount'] > 0)
          <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Remise ({{ $summary['promo'] }})</span><span class="text-brand">− @gnf($summary['discount'])</span></div>
        @endif
        <div class="summary-total d-flex justify-content-between align-items-baseline">
          <span class="fw-bold">Total à payer</span>
          <span class="total-amount" id="ck-total">@gnf($summary['total'])</span>
        </div>
        <button type="submit" class="btn btn-amber w-100 btn-lg mt-3">Confirmer &amp; payer</button>
        <div class="text-center small text-muted mt-3">🔒 Transaction sécurisée &amp; cryptée</div>
      </div>
    </aside>
  </div>
</div>
</form>
@endsection

@push('scripts')
<script>
(function () {
  var base = {{ (int) $summary['total'] }};
  var fmt = function (n) { return n.toLocaleString('fr-FR').replace(/ /g, ' ') + ' GNF'; };
  var ckDeliv = document.getElementById('ck-delivery');
  var ckTotal = document.getElementById('ck-total');

  function refresh() {
    var chosen = document.querySelector('.js-delivery:checked');
    var cost = chosen ? parseInt(chosen.dataset.cost, 10) : 0;
    if (ckDeliv) ckDeliv.textContent = cost === 0 ? 'Offerte' : fmt(cost);
    if (ckTotal) ckTotal.textContent = fmt(base + cost);
  }
  document.querySelectorAll('.js-delivery').forEach(function (r) { r.addEventListener('change', refresh); });

  var phoneWrap = document.getElementById('payPhoneWrap');
  function refreshPay() {
    var chosen = document.querySelector('.js-payment:checked');
    if (phoneWrap) phoneWrap.style.display = (chosen && chosen.dataset.phone === '1') ? '' : 'none';
  }
  document.querySelectorAll('.js-payment').forEach(function (r) { r.addEventListener('change', refreshPay); });

  refresh();
  refreshPay();
})();
</script>
@endpush
