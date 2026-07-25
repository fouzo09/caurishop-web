@extends('shop.layouts.app')

@section('title', $product->name . ' — CAURISHOP')

@section('content')
@php
    $images = $product->images;
    $cover  = $product->coverUrl();
    $isVariable = $product->isVariable();
    $basePrice = $isVariable ? (float) ($product->variants->min('price') ?? 0) : (float) $product->price;
    $soldOut = ! $product->isService() && $product->stock_status === 'rupture';
@endphp

<div class="container-xl pt-4">
  <div class="crumbs mb-3">
    <a href="{{ route('home') }}">Accueil</a> ›
    @if ($product->category)
      <a href="{{ route('shop.products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a> ›
    @endif
    <span class="crumb-current">{{ $product->name }}</span>
  </div>
</div>

<div class="container-xl pb-4">
  <div class="row g-5">

    <!-- GALERIE -->
    <div class="col-lg-6">
      <div class="gallery-main">
        <span class="gallery-main__emoji" id="mainEmoji">🛍️</span>
        @if ($cover)
          <img class="gallery-main__img" src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()" id="mainImg">
        @endif
      </div>
      @if ($images->count() > 1)
      <div class="row row-cols-4 g-2 mt-1">
        @foreach ($images as $img)
          <div class="col">
            <button type="button" class="thumb {{ $loop->first ? 'thumb--active' : '' }}" data-emoji="🛍️" data-img="{{ $img->url }}">
              <img class="thumb__img" src="{{ $img->url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">
            </button>
          </div>
        @endforeach
      </div>
      @endif
    </div>

    <!-- INFOS -->
    <div class="col-lg-6">
      @if ($product->category)
        <span class="kicker">{{ $product->category->name }}</span>
      @endif
      <h1 class="product-title">{{ $product->name }}</h1>
      <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
        @if ($soldOut)
          <span class="small fw-semibold text-danger">● Rupture de stock</span>
        @else
          <span class="small fw-semibold stock-ok">● Disponible</span>
        @endif
      </div>
      <div class="d-flex align-items-baseline gap-3 mb-3 flex-wrap">
        <span class="product-price">{{ $isVariable ? 'À partir de ' : '' }}@gnf($basePrice)</span>
      </div>

      @if ($product->description)
        <p class="text-body-col mb-4">{{ $product->description }}</p>
      @endif

      <form method="POST" action="{{ route('shop.cart.add') }}" class="mb-3">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        @if ($isVariable)
          <div class="mb-3">
            <div class="option-label">Choix</div>
            <select name="variant_id" class="form-select" required>
              @foreach ($product->variants as $variant)
                <option value="{{ $variant->id }}" @disabled(! $variant->hasStock())>
                  {{ $variant->name ?? ('Variante #' . $variant->id) }} — @gnf($variant->price){{ $variant->hasStock() ? '' : ' (rupture)' }}
                </option>
              @endforeach
            </select>
          </div>
        @endif

        <div class="d-flex gap-2 mb-3 flex-wrap align-items-stretch">
          <div class="qty-input d-flex align-items-center border rounded">
            <button type="button" class="btn" onclick="const i=this.parentNode.querySelector('input'); i.value=Math.max(1,parseInt(i.value||1)-1)">−</button>
            <input type="number" name="quantity" value="1" min="1" class="form-control border-0 text-center" style="width:64px" aria-label="Quantité">
            <button type="button" class="btn" onclick="const i=this.parentNode.querySelector('input'); i.value=parseInt(i.value||1)+1">+</button>
          </div>
          <button type="submit" class="btn btn-brand btn-lg flex-grow-1" @disabled($soldOut)><i class="bi bi-bag-plus"></i> Ajouter au panier</button>
        </div>
      </form>

      <div class="info-card">
        <div class="d-flex align-items-center gap-2"><i class="bi bi-wallet2 fs-5 text-brand"></i> Payez avec <strong>Orange Money</strong>, <strong>MTN MoMo</strong> ou <strong>carte</strong></div>
        <div class="d-flex align-items-center gap-2"><i class="bi bi-truck fs-5 text-brand"></i> Livraison dans les 33 préfectures de Guinée</div>
        <div class="d-flex align-items-center gap-2"><i class="bi bi-arrow-repeat fs-5 text-brand"></i> Retour sous 7 jours en cas de problème</div>
        <div class="d-flex align-items-center gap-2"><i class="bi bi-headset fs-5 text-brand"></i> Support client à votre écoute 6j/7</div>
      </div>
    </div>
  </div>
</div>

<!-- ONGLETS -->
<div class="container-xl mt-2">
  <div class="tabs-card">
    <ul class="nav nav-tabs product-tabs" id="productTabs" role="tablist">
      <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab">Description</button></li>
      <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-carac" type="button" role="tab">Caractéristiques</button></li>
    </ul>
    <div class="tab-content p-4 text-body-col">
      <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">{{ $product->description ?: 'Aucune description pour ce produit.' }}</div>
      <div class="tab-pane fade" id="tab-carac" role="tabpanel">
        @if ($product->sku)Référence : {{ $product->sku }}<br>@endif
        Catégorie : {{ $product->category->name ?? '—' }}
      </div>
    </div>
  </div>
</div>

<!-- SUGGESTIONS -->
@if ($suggestions->isNotEmpty())
<section class="container-xl section">
  <h2 class="section-title mb-3">Vous aimerez aussi</h2>
  <div class="row row-cols-2 row-cols-md-4 g-3">
    @foreach ($suggestions as $suggestion)
      <div class="col">@include('shop.partials.product-card', ['product' => $suggestion])</div>
    @endforeach
  </div>
</section>
@endif
@endsection
