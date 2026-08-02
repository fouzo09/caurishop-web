@extends('shop.layouts.app')

@section('title', 'Mon panier — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <span class="fw-bolder" style="font-size:24px">
    Mon panier
    @if (! empty($summary['items']))
      <span class="text-muted fw-normal" style="font-size:14px">— {{ count($summary['items']) }} article{{ count($summary['items']) > 1 ? 's' : '' }}</span>
    @endif
  </span>

  @if (empty($summary['items']))
    <div class="empty-state mt-3">
      <i class="bi bi-cart-x"></i>
      Votre panier est vide.
      <div class="mt-3"><a href="{{ route('shop.products.index') }}" class="btn-brand btn-sm">Découvrir la boutique</a></div>
    </div>
  @else
  <div class="row g-4 mt-1 align-items-start">

    <div class="col-lg-8">
      <div class="border rounded-3 overflow-hidden">
        @foreach ($summary['items'] as $item)
          @php $product = $item['product']; $variant = $item['variant']; $cover = $product->coverUrl(); @endphp
          <div class="d-flex align-items-center gap-3 p-3 {{ $loop->last ? '' : 'border-bottom' }} flex-wrap">
            <a href="{{ route('shop.products.show', $product->id) }}" class="cart-thumb" style="width:76px;height:76px">
              @if ($cover)<img src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">@else<span>🛍️</span>@endif
            </a>

            <div class="flex-grow-1" style="min-width:150px">
              <a href="{{ route('shop.products.show', $product->id) }}" class="fw-semibold d-block" style="font-size:14.5px">{{ $product->name }}</a>
              <span class="text-muted" style="font-size:12.5px">
                @if ($variant){{ $variant->name }} · @endif
                @gnf($item['unit_price']) × {{ $item['quantity'] }}
              </span>
            </div>

            <form method="POST" action="{{ route('shop.cart.update') }}">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <input type="hidden" name="variant_id" value="{{ $variant->id ?? '' }}">
              <div class="qty-box">
                <button type="button" data-minus aria-label="Diminuer"><i class="bi bi-dash"></i></button>
                <input class="val" type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" max="99" onchange="this.form.submit()" aria-label="Quantité">
                <button type="button" data-plus aria-label="Augmenter"><i class="bi bi-plus"></i></button>
              </div>
            </form>

            <span class="fw-bold text-end" style="font-size:15px;min-width:120px">@gnf($item['line_total'])</span>

            <form method="POST" action="{{ route('shop.cart.remove') }}">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <input type="hidden" name="variant_id" value="{{ $variant->id ?? '' }}">
              <button class="btn p-0" style="color:var(--danger)" type="submit" aria-label="Supprimer l'article"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        @endforeach
      </div>

      <form method="POST" action="{{ route('shop.cart.promo') }}" class="d-flex gap-2 mt-3">
        @csrf
        <input name="code" value="{{ $summary['promo'] }}" class="form-control promo-input" placeholder="Code promo">
        <button class="btn-outline-ink btn-sm px-3" type="submit">Appliquer</button>
      </form>
    </div>

    <div class="col-lg-4">
      <div class="border rounded-3 p-4 d-flex flex-column gap-3 summary-sticky sticky-lg-top">
        <span class="fw-bolder" style="font-size:16px">Résumé de la commande</span>

        <div class="d-flex justify-content-between" style="font-size:14px;color:#555">
          <span>Sous-total ({{ count($summary['items']) }} article{{ count($summary['items']) > 1 ? 's' : '' }})</span>
          <span class="fw-semibold text-dark">@gnf($summary['subtotal'])</span>
        </div>
        <div class="d-flex justify-content-between" style="font-size:14px;color:#555">
          <span>Livraison</span><span class="fw-semibold" style="color:var(--green)">Offerte</span>
        </div>
        @if ($summary['discount'] > 0)
          <div class="d-flex justify-content-between" style="font-size:14px;color:#555">
            <span>Remise ({{ $summary['promo'] }})</span><span class="fw-semibold text-brand">− @gnf($summary['discount'])</span>
          </div>
        @endif
        <div class="d-flex justify-content-between fw-bolder border-top pt-3" style="font-size:16px">
          <span>Total</span><span class="total-amount">@gnf($summary['total'])</span>
        </div>

        <a href="{{ route('shop.checkout.index') }}" class="btn-brand text-center">Passer la commande</a>
        <a href="{{ route('shop.products.index') }}" class="text-brand fw-semibold text-center" style="font-size:13.5px">Continuer mes achats</a>

        <div class="d-flex gap-2 flex-wrap justify-content-center pt-2 border-top">
          <span class="pay-chip"><img src="{{ asset('shop/img/pay/om.svg') }}" alt="Orange Money" height="18"></span>
          <span class="pay-chip"><img src="{{ asset('shop/img/pay/momo.svg') }}" alt="MTN MoMo" height="18"></span>
          <span class="pay-chip"><img src="{{ asset('shop/img/pay/card.svg') }}" alt="Carte bancaire" height="16"></span>
        </div>
      </div>
    </div>
  </div>
  @endif
</main>
@endsection
