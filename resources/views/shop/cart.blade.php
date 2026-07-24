@extends('shop.layouts.app')

@section('title', 'Mon panier — CAURISHOP')

@section('content')
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('home') }}">Accueil</a> › <span class="crumb-current">Panier</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">Mon panier</h1>
  </div>
</div>

<div class="container-xl py-4">
  @if (empty($summary['items']))
    <div class="panel p-5 text-center">
      <div class="fs-1 mb-2">🛒</div>
      <p class="text-muted mb-3">Votre panier est vide.</p>
      <a href="{{ route('shop.products.index') }}" class="btn btn-brand">Découvrir la boutique</a>
    </div>
  @else
  <div class="row g-4">

    <div class="col-lg-8">
      <div class="panel">
        <div class="cart-head row g-3 d-none d-md-flex">
          <div class="col-md-5">Produit</div>
          <div class="col-md-2">Prix</div>
          <div class="col-md-3 text-center">Quantité</div>
          <div class="col-md-2 text-end">Total</div>
        </div>

        @foreach ($summary['items'] as $item)
          @php $product = $item['product']; $variant = $item['variant']; $cover = $product->coverUrl(); @endphp
          <div class="cart-row row g-3 align-items-center">
            <div class="col-12 col-md-5 d-flex align-items-center gap-3">
              <span class="cart-thumb">
                <span>🛍️</span>
                @if ($cover)<img class="mini__img" src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">@endif
              </span>
              <div>
                <a href="{{ route('shop.products.show', $product->id) }}" class="fw-bold text-ink text-decoration-none">{{ $product->name }}</a>
                @if ($variant)<div class="small text-muted">{{ $variant->name }}</div>@endif
              </div>
            </div>
            <div class="col-4 col-md-2 small cart-price-label">@gnf($item['unit_price'])</div>
            <div class="col-5 col-md-3 d-flex justify-content-md-center">
              <form method="POST" action="{{ route('shop.cart.update') }}" class="d-flex align-items-center gap-1">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" value="{{ $variant->id ?? '' }}">
                <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" max="99" class="form-control form-control-sm text-center" style="width:70px">
                <button type="submit" class="btn btn-sm btn-soft" title="Mettre à jour">↻</button>
              </form>
            </div>
            <div class="col-3 col-md-2 text-end fw-bold text-brand">@gnf($item['line_total'])</div>
            <div class="col-12 col-md-12 text-end">
              <form method="POST" action="{{ route('shop.cart.remove') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" value="{{ $variant->id ?? '' }}">
                <button type="submit" class="btn btn-link btn-sm text-muted p-0" aria-label="Retirer l'article">✕ Retirer</button>
              </form>
            </div>
          </div>
        @endforeach

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3">
          <a href="{{ route('shop.products.index') }}" class="fw-bold text-brand small">← Continuer mes achats</a>
          <form method="POST" action="{{ route('shop.cart.promo') }}" class="d-flex gap-2">
            @csrf
            <input name="code" value="{{ $summary['promo'] }}" class="form-control form-control-sm promo-input" placeholder="Code promo">
            <button class="btn btn-soft btn-sm px-3" type="submit">Appliquer</button>
          </form>
        </div>
      </div>
    </div>

    <aside class="col-lg-4">
      <div class="panel p-4 sticky-lg-top summary-sticky">
        <div class="fs-5 fw-bold text-ink mb-3">Récapitulatif</div>
        <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Sous-total</span><span class="fw-semibold">@gnf($summary['subtotal'])</span></div>
        <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Livraison (Conakry)</span><span class="fw-semibold text-success">Offerte</span></div>
        @if ($summary['discount'] > 0)
          <div class="d-flex justify-content-between small mb-3"><span class="text-muted">Remise ({{ $summary['promo'] }})</span><span class="fw-semibold text-brand">− @gnf($summary['discount'])</span></div>
        @endif
        <div class="summary-total d-flex justify-content-between align-items-baseline mb-3">
          <span class="fw-bold">Total</span>
          <span class="total-amount">@gnf($summary['total'])</span>
        </div>
        <a href="{{ route('shop.checkout.index') }}" class="btn btn-brand w-100 btn-lg">Passer la commande →</a>
        <div class="d-flex gap-2 flex-wrap justify-content-center mt-3">
          <span class="mini-chip">🟠 Orange Money</span>
          <span class="mini-chip">🟡 MTN MoMo</span>
          <span class="mini-chip">💳 Visa</span>
        </div>
      </div>
      <div class="secure-note mt-3"><span class="fs-5">🔒</span> Paiement 100% sécurisé. Vos informations sont protégées.</div>
    </aside>
  </div>
  @endif
</div>
@endsection
