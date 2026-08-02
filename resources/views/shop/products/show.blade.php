@extends('shop.layouts.app')

@section('title', $product->name . ' — CAURISHOP')

@section('content')
@php
    $images = $product->images;
    $cover  = $product->coverUrl();
    $isVariable = $product->isVariable();
    $basePrice = $isVariable ? (float) ($product->variants->min('price') ?? 0) : (float) $product->price;
    $soldOut = ! $product->isService() && $product->stock_status === 'rupture';
    $customer = auth()->check() ? auth()->user()->customer : null;
    $isFavorite = $customer ? $customer->favorites()->where('product_id', $product->id)->exists() : false;
@endphp

<div class="breadcrumb-bar">
  <div class="container-xl d-flex align-items-center gap-2 py-2 px-3 flex-wrap">
    <a href="{{ route('home') }}">Accueil</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    @if ($product->category)
      <a href="{{ route('shop.products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
      <i class="bi bi-chevron-right" style="font-size:11px"></i>
    @endif
    <span class="fw-semibold text-dark">{{ $product->name }}</span>
  </div>
</div>

<main class="container-xl py-4">
  <div class="row g-5">

    {{-- GALERIE --}}
    <div class="col-lg-6">
      <div class="gallery-main">
        @if ($cover)
          <img id="mainImage" src="{{ $cover }}" alt="{{ $product->name }}" onerror="this.remove()">
        @else
          <span class="ph">🛍️</span>
        @endif
      </div>
      @if ($images->count() > 1)
        <div class="d-flex gap-2 mt-3 flex-wrap">
          @foreach ($images as $img)
            <button type="button" class="thumb {{ $loop->first ? 'active' : '' }}" data-full="{{ $img->url }}" aria-label="Vue {{ $loop->iteration }}">
              <img src="{{ $img->url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">
            </button>
          @endforeach
        </div>
      @endif
    </div>

    {{-- INFOS --}}
    <div class="col-lg-6 d-flex flex-column gap-3">
      @if ($product->category)
        <span class="text-brand fw-bold text-uppercase" style="font-size:12.5px;letter-spacing:.07em">{{ $product->category->name }}</span>
      @endif
      <h1 class="fw-bolder m-0" style="font-size:30px;letter-spacing:-.02em">{{ $product->name }}</h1>

      <div class="d-flex align-items-center gap-2 text-muted" style="font-size:13.5px">
        <span style="color:#f5a623" aria-hidden="true">
          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
        </span>
        @if ($soldOut)
          <span class="fw-semibold" style="color:var(--danger)">Rupture de stock</span>
        @else
          <span class="fw-semibold" style="color:var(--green)">En stock</span>
        @endif
      </div>

      <div class="d-flex align-items-baseline gap-3">
        <span class="fw-bolder" style="font-size:28px">{{ $isVariable ? 'À partir de ' : '' }}@gnf($basePrice)</span>
      </div>

      {{-- Emplacement du paiement échelonné « molo molo » (à brancher sur les CreditPlan). --}}

      <form method="POST" action="{{ route('shop.cart.add') }}" class="d-flex flex-column gap-3">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        @if ($isVariable)
          <div>
            <div class="fw-bold mb-2" style="font-size:13.5px">Choix</div>
            <select name="variant_id" class="form-select" required>
              @foreach ($product->variants as $variant)
                <option value="{{ $variant->id }}" @disabled(! $variant->hasStock())>
                  {{ $variant->name ?? ('Variante #' . $variant->id) }} — @gnf($variant->price){{ $variant->hasStock() ? '' : ' (rupture)' }}
                </option>
              @endforeach
            </select>
          </div>
        @endif

        <div class="d-flex gap-3 align-items-center flex-wrap">
          <div class="qty-box">
            <button type="button" data-minus aria-label="Diminuer"><i class="bi bi-dash"></i></button>
            <input class="val" type="number" name="quantity" value="1" min="1" aria-label="Quantité">
            <button type="button" data-plus aria-label="Augmenter"><i class="bi bi-plus"></i></button>
          </div>
          <button type="submit" class="btn-brand flex-grow-1 d-flex align-items-center justify-content-center gap-2" @disabled($soldOut)>
            <i class="bi bi-cart-plus"></i>Ajouter au panier
          </button>
          <button type="button" class="btn-outline-ink px-3 wish-lg{{ $isFavorite ? ' is-on' : '' }}"
                  data-fav-toggle="{{ route('shop.account.favorites.toggle', $product->id) }}"
                  data-fav-login="{{ route('shop.login') }}"
                  aria-pressed="{{ $isFavorite ? 'true' : 'false' }}" aria-label="Ajouter aux favoris">
            <i class="bi {{ $isFavorite ? 'bi-heart-fill' : 'bi-heart' }}"></i>
          </button>
        </div>
      </form>

      {{-- Fiche technique : toujours renseignée, elle tient la colonne quand le produit est peu documenté. --}}
      <div class="spec-list">
        @if ($product->sku)
          <div class="spec"><i class="bi bi-upc-scan"></i><span class="spec__k">Référence</span><span class="spec__v">{{ $product->sku }}</span></div>
        @endif
        @if ($product->category)
          <div class="spec"><i class="bi bi-tag"></i><span class="spec__k">Catégorie</span><span class="spec__v"><a href="{{ route('shop.products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></span></div>
        @endif
        <div class="spec">
          <i class="bi {{ $product->isService() ? 'bi-calendar-check' : 'bi-box-seam' }}"></i>
          <span class="spec__k">Type</span><span class="spec__v">{{ $product->isService() ? 'Service' : 'Produit' }}</span>
        </div>
        <div class="spec">
          @if ($soldOut)
            <i class="bi bi-x-circle spec--out"></i><span class="spec__k">Disponibilité</span><span class="spec__v">Rupture de stock</span>
          @elseif ($product->stock_status === 'faible')
            <i class="bi bi-exclamation-circle spec--low"></i><span class="spec__k">Disponibilité</span><span class="spec__v">Stock limité</span>
          @else
            <i class="bi bi-check-circle spec--ok"></i><span class="spec__k">Disponibilité</span><span class="spec__v">En stock</span>
          @endif
        </div>
        @if ($isVariable)
          <div class="spec"><i class="bi bi-sliders"></i><span class="spec__k">Options</span><span class="spec__v">{{ $product->variants->count() }} disponible{{ $product->variants->count() > 1 ? 's' : '' }}</span></div>
        @endif
      </div>

      {{-- Réassurance : présente sur toutes les fiches. --}}
      <div class="reassure">
        <div><i class="bi bi-truck"></i><span><strong>Livraison partout en Guinée</strong>Conakry 24h · régions 2 à 4 jours</span></div>
        <div><i class="bi bi-arrow-repeat"></i><span><strong>Retours sous 7 jours</strong>En cas de problème sur la commande</span></div>
        <div><i class="bi bi-shield-check"></i><span><strong>Paiement sécurisé</strong>Orange Money, MTN MoMo ou carte</span></div>
        <div><i class="bi bi-headset"></i><span><strong>Support client</strong>À votre écoute du lundi au samedi</span></div>
      </div>
    </div>
  </div>

  {{-- Description : affichée uniquement si le produit en a une. --}}
  @if ($product->description)
    <div class="mt-4">
      <ul class="nav tab-line" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab">Description</button></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">
          <p class="mt-3" style="font-size:14.5px;color:#444;line-height:1.7;max-width:760px">{{ $product->description }}</p>
        </div>
      </div>
    </div>
  @endif

  {{-- SUGGESTIONS --}}
  @if ($suggestions->isNotEmpty())
    <section class="mt-4">
      <div class="d-flex align-items-baseline justify-content-between mb-3">
        <span class="section-title">Vous aimerez aussi</span>
        <a href="{{ route('shop.products.index') }}" class="section-link">Tout voir</a>
      </div>
      <div class="row row-cols-2 row-cols-md-4 g-3">
        @foreach ($suggestions as $suggestion)
          <div class="col">@include('shop.partials.product-card', ['product' => $suggestion])</div>
        @endforeach
      </div>
    </section>
  @endif
</main>
@endsection
