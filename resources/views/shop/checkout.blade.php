@extends('shop.layouts.app')

@section('title', 'Paiement & livraison — CAURISHOP')

@section('content')
@php
    $c = $customer;
    $addresses = $c ? $c->addresses()->get() : collect();
    $default   = $addresses->firstWhere('is_default', true);

    // Pré-remplissage : ancienne saisie > adresse par défaut > profil client.
    $defaultParts = $default ? preg_split('/\s+/', trim($default->full_name), 2) : [];
    $first = old('first_name', $defaultParts[0] ?? $c?->first_name ?? '');
    $last  = old('last_name', $defaultParts[1] ?? $c?->last_name ?? '');
    $phone = old('phone', $default?->phone ?? $c?->phone ?? '');
    $addr  = old('address', $default?->address ?? $c?->address ?? '');
    $city  = old('city', $default?->city ?? 'Conakry');

    $cities = \App\Http\Controllers\Shop\AddressController::CITIES;
@endphp

<main class="container-xl py-4">
  <span class="fw-bolder" style="font-size:24px">Paiement &amp; livraison</span>

  <div class="d-flex align-items-center my-3 flex-wrap" style="font-size:13.5px">
    <span class="d-flex align-items-center gap-2 text-brand fw-bold"><span class="step-done">1</span>Livraison</span>
    <span class="mx-3 step-line step-line--done"></span>
    <span class="d-flex align-items-center gap-2 text-brand fw-bold"><span class="step-done">2</span>Paiement</span>
    <span class="mx-3 step-line"></span>
    <span class="d-flex align-items-center gap-2 text-muted"><span class="step-todo">3</span>Confirmation</span>
  </div>

  <form method="POST" action="{{ route('shop.checkout.store') }}">
    @csrf
    <div class="row g-4 align-items-start">

      <div class="col-lg-8 d-flex flex-column gap-4">

        {{-- 1. Adresse de livraison --}}
        <div class="border rounded-3 p-4">
          <div class="d-flex align-items-baseline justify-content-between mb-3">
            <span class="fw-bolder" style="font-size:16px">Adresse de livraison</span>
            <a href="{{ route('shop.account.addresses') }}" class="section-link">Gérer mes adresses</a>
          </div>

          @if ($addresses->isNotEmpty())
            <div class="d-flex flex-column gap-2 mb-3" data-pay-group id="savedAddresses">
              @foreach ($addresses as $a)
                <label class="pay-option{{ $a->is_default ? ' selected' : '' }}"
                       data-name="{{ $a->full_name }}" data-phone="{{ $a->phone }}"
                       data-city="{{ $a->city }}" data-address="{{ $a->address }}">
                  <input type="radio" name="saved_address" value="{{ $a->id }}" class="form-check-input m-0" @checked($a->is_default)>
                  <span class="flex-grow-1">
                    <span class="d-block fw-semibold">{{ $a->label ?: $a->full_name }}</span>
                    <span class="d-block text-muted" style="font-size:12.5px">{{ $a->inline() }} · {{ $a->phone }}</span>
                  </span>
                  @if ($a->is_default)<span class="addr-badge">Par défaut</span>@endif
                </label>
              @endforeach
            </div>
          @endif

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Prénom</label>
              <input name="first_name" id="ck-first" value="{{ $first }}" class="form-control @error('first_name') is-invalid @enderror" placeholder="Aïssatou">
              @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input name="last_name" id="ck-last" value="{{ $last }}" class="form-control @error('last_name') is-invalid @enderror" placeholder="Diallo">
              @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Téléphone</label>
              <input name="phone" id="ck-phone" value="{{ $phone }}" class="form-control @error('phone') is-invalid @enderror" placeholder="+224 6 00 00 00 00">
              @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Préfecture / région</label>
              <select name="city" id="ck-city" class="form-select @error('city') is-invalid @enderror">
                @foreach ($cities as $ville)
                  <option value="{{ $ville }}" @selected($city === $ville)>{{ $ville }}</option>
                @endforeach
              </select>
              @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <label class="form-label">Quartier / repère</label>
              <input name="address" id="ck-address" value="{{ $addr }}" class="form-control @error('address') is-invalid @enderror" placeholder="Ex. Almamya, rue KA 020, en face de la pharmacie">
              @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="d-flex gap-3 mt-3 flex-wrap" data-pay-group>
            @foreach ($deliveries as $key => $d)
              <label class="pay-option flex-fill{{ $loop->first ? ' selected' : '' }}">
                <input type="radio" name="delivery_method" value="{{ $key }}" class="form-check-input m-0 js-delivery" data-cost="{{ $d['fee'] }}" @checked($loop->first)>
                <i class="bi {{ $key === 'express' ? 'bi-lightning-charge' : 'bi-truck' }} text-brand fs-6"></i>
                <span class="flex-grow-1">
                  <span class="d-block fw-semibold">{{ $d['label'] }}</span>
                  <span class="d-block text-muted" style="font-size:12.5px">{{ $key === 'express' ? 'Le jour même à Conakry (avant 12h)' : 'Conakry 24h · régions 2-4 jours' }}</span>
                </span>
                <span class="fw-bold" style="color:{{ $d['fee'] === 0 ? 'var(--green)' : 'inherit' }}">{{ $d['fee'] === 0 ? 'Offerte' : \App\Support\Money::gnf($d['fee']) }}</span>
              </label>
            @endforeach
          </div>
        </div>

        {{-- 2. Moyen de paiement --}}
        <div class="border rounded-3 p-4">
          <div class="fw-bolder mb-3" style="font-size:16px">Moyen de paiement</div>

          <div class="d-flex flex-column gap-2" data-pay-group>
            <label class="pay-option selected">
              <input type="radio" name="payment_mode" value="cash" class="form-check-input m-0" checked>
              <i class="bi bi-shield-lock text-brand fs-6"></i>
              <span class="flex-grow-1">
                <span class="d-block fw-semibold">Paiement immédiat</span>
                <span class="d-block text-muted" style="font-size:12.5px">Orange Money, MTN MoMo ou carte bancaire, à l'étape suivante.</span>
              </span>
            </label>

            @if ($credit)
              <label class="pay-option">
                <input type="radio" name="payment_mode" value="credit" class="form-check-input m-0" id="ck-credit">
                <i class="bi bi-calendar-check text-brand fs-6"></i>
                <span class="flex-grow-1">
                  <span class="d-block fw-semibold">Payer molo molo — crédit entreprise</span>
                  <span class="d-block text-muted" style="font-size:12.5px">
                    Plafond disponible : <strong>@gnf($credit['available'])</strong>. Soumis à la validation de votre entreprise.
                  </span>
                </span>
              </label>
            @endif
          </div>

          @if ($credit)
            {{-- Réglages du crédit, révélés quand l'option est cochée. --}}
            <div id="ck-credit-settings" class="bg-brand-soft rounded-3 p-3 mt-3 d-none">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Nombre de mensualités</label>
                  <select name="installments" id="ck-installments" class="form-select">
                    @foreach ($credit['plans'] as $n)
                      <option value="{{ $n }}" @selected(old('installments', $credit['plans'][0]) == $n)>{{ $n }} mois</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Acompte <span class="text-muted" style="font-size:12px">(facultatif)</span></label>
                  <input type="number" name="down_payment" id="ck-down" class="form-control @error('down_payment') is-invalid @enderror"
                         value="{{ old('down_payment') }}" min="0" max="{{ (int) $summary['total'] - 1 }}" step="1000" placeholder="0">
                  @error('down_payment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 mt-3" style="font-size:13.5px">
                <i class="bi bi-info-circle text-brand"></i>
                <span>Environ <strong id="ck-monthly">—</strong> par mois pendant <strong id="ck-months">—</strong> mois.</span>
              </div>
              <div class="text-muted mt-2" style="font-size:12px">
                La première échéance tombe un mois après la confirmation de la commande. Vous les réglerez depuis
                <a href="{{ route('shop.account.payments') }}" class="text-brand fw-semibold">Mes échéances</a>.
              </div>
            </div>
          @endif

          <div class="d-flex gap-2 flex-wrap mt-3">
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/om.svg') }}" alt="Orange Money" height="18"></span>
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/momo.svg') }}" alt="MTN MoMo" height="18"></span>
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/card.svg') }}" alt="Carte bancaire" height="16"></span>
          </div>
        </div>
      </div>

      {{-- Récapitulatif --}}
      <div class="col-lg-4">
        <div class="border rounded-3 p-4 d-flex flex-column gap-3 summary-sticky sticky-lg-top">
          <span class="fw-bolder" style="font-size:16px">Votre commande</span>

          @foreach ($summary['items'] as $item)
            @php $cover = $item['product']->coverUrl(); @endphp
            <div class="d-flex align-items-center gap-3" style="font-size:13.5px">
              <span class="ck-thumb">@if ($cover)<img src="{{ $cover }}" alt="{{ $item['product']->name }}" loading="lazy" onerror="this.remove()">@else<span>🛍️</span>@endif</span>
              <span class="flex-grow-1">{{ $item['product']->name }} <span class="text-muted">× {{ $item['quantity'] }}</span></span>
              <span class="fw-semibold text-nowrap">@gnf($item['line_total'])</span>
            </div>
          @endforeach

          <div class="d-flex justify-content-between border-top pt-3" style="font-size:14px;color:#555">
            <span>Sous-total</span><span class="fw-semibold text-dark">@gnf($summary['subtotal'])</span>
          </div>
          <div class="d-flex justify-content-between" style="font-size:14px;color:#555">
            <span>Livraison</span><span class="fw-semibold" id="ck-delivery" style="color:var(--green)">Offerte</span>
          </div>
          @if ($summary['discount'] > 0)
            <div class="d-flex justify-content-between" style="font-size:14px;color:#555">
              <span>Remise ({{ $summary['promo'] }})</span><span class="fw-semibold text-brand">− @gnf($summary['discount'])</span>
            </div>
          @endif
          <div class="d-flex justify-content-between fw-bolder border-top pt-3" style="font-size:16px">
            <span>Total</span><span class="total-amount" id="ck-total">@gnf($summary['total'])</span>
          </div>

          <button type="submit" class="btn-brand" id="ck-submit">Valider et payer</button>
          <span class="text-muted text-center d-flex align-items-center justify-content-center gap-2" style="font-size:12px">
            <i class="bi bi-lock"></i>Paiement 100 % sécurisé
          </span>
        </div>
      </div>
    </div>
  </form>
</main>
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
    if (ckDeliv) {
      ckDeliv.textContent = cost === 0 ? 'Offerte' : fmt(cost);
      ckDeliv.style.color = cost === 0 ? 'var(--green)' : '';
    }
    if (ckTotal) ckTotal.textContent = fmt(base + cost);
  }
  document.querySelectorAll('.js-delivery').forEach(function (r) { r.addEventListener('change', refresh); });
  refresh();

  // Crédit entreprise : réglages, mensualité estimée et libellé du bouton.
  var settings = document.getElementById('ck-credit-settings');
  if (settings) {
    var months = document.getElementById('ck-installments');
    var down = document.getElementById('ck-down');
    var submit = document.getElementById('ck-submit');

    function refreshCredit() {
      var chosen = document.querySelector('input[name="payment_mode"]:checked');
      var onCredit = chosen && chosen.value === 'credit';
      settings.classList.toggle('d-none', !onCredit);

      var delivery = document.querySelector('.js-delivery:checked');
      var total = base + (delivery ? parseInt(delivery.dataset.cost, 10) : 0);
      var n = parseInt(months.value, 10) || 1;
      var acompte = Math.min(parseInt(down.value, 10) || 0, total);

      document.getElementById('ck-monthly').textContent = fmt(Math.round((total - acompte) / n));
      document.getElementById('ck-months').textContent = n;
      if (submit) submit.textContent = onCredit ? 'Soumettre la commande' : 'Valider et payer';
    }

    document.querySelectorAll('input[name="payment_mode"], .js-delivery').forEach(function (el) {
      el.addEventListener('change', refreshCredit);
    });
    months.addEventListener('change', refreshCredit);
    down.addEventListener('input', refreshCredit);
    refreshCredit();
  }

  // Sélection d'une adresse enregistrée : remplit le formulaire de livraison.
  document.querySelectorAll('#savedAddresses .pay-option').forEach(function (card) {
    card.addEventListener('click', function () {
      var name = (card.dataset.name || '').trim().split(/\s+/);
      var set = function (id, value) { var el = document.getElementById(id); if (el) el.value = value; };
      set('ck-first', name[0] || '');
      set('ck-last', name.slice(1).join(' '));
      set('ck-phone', card.dataset.phone || '');
      set('ck-address', card.dataset.address || '');
      set('ck-city', card.dataset.city || '');
    });
  });
})();
</script>
@endpush
