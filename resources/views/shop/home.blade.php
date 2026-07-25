@extends('shop.layouts.app')

@section('title', 'Accueil — CAURISHOP')

@section('content')
<!-- HERO : bannière promo pleine largeur (image produit à droite) -->
@php
    // Produits mis en avant : meilleures ventes puis nouveautés, uniquement ceux avec image.
    $heroProducts = $bestSellers->concat($newArrivals)
        ->filter(fn ($p) => $p && $p->coverUrl())
        ->unique('id')
        ->take(3)
        ->values();
    $heroLabels = ['Promo de la semaine', 'Meilleure vente', 'Nouveauté'];
@endphp
<section class="mf-hero">
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      @if ($heroProducts->isEmpty())
        <div class="carousel-item active">
          <div class="container mf-slide__inner mf-slide--right">
            <div class="mf-slide__content">
              <h5 class="mf-subtitle">Bienvenue sur <span class="hl">CAURISHOP</span></h5>
              <h1 class="mf-title">Le marché<br>de la Guinée</h1>
              <p class="mf-offer">Livré partout, payé <span class="hl">mobile money</span></p>
              <a href="{{ route('shop.products.index') }}" class="mf-btn">Découvrir <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      @else
        @foreach ($heroProducts as $i => $product)
          @php
              $isVar = $product->isVariable();
              $price = $isVar ? (float) ($product->variants->min('price') ?? 0) : (float) $product->price;
              $flip  = $i % 2 === 1; // alterne image/texte comme Wolmart
          @endphp
          <div class="carousel-item {{ $i === 0 ? 'active' : '' }}" data-bs-interval="6000">
            <div class="container mf-slide__inner {{ $flip ? 'mf-slide--left' : 'mf-slide--right' }}">
              <figure class="mf-slide__media">
                <a href="{{ route('shop.products.show', $product->id) }}"><img src="{{ $product->coverUrl() }}" alt="{{ $product->name }}" loading="lazy"></a>
              </figure>
              <div class="mf-slide__content">
                <h5 class="mf-subtitle">{{ $heroLabels[$i] ?? 'Sélection CAURISHOP' }}</h5>
                <h{{ $i === 0 ? '1' : '2' }} class="mf-title">{{ $product->name }}</h{{ $i === 0 ? '1' : '2' }}>
                <p class="mf-offer">{{ $isVar ? 'À partir de ' : '' }}<span class="hl">@gnf($price)</span></p>
                <a href="{{ route('shop.products.show', $product->id) }}" class="mf-btn">Acheter <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>
        @endforeach
      @endif
    </div>

    @if ($heroProducts->count() > 1)
      <div class="carousel-indicators mf-dots">
        @foreach ($heroProducts as $i => $product)
          <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
      </div>
      <button class="carousel-control-prev mf-arrow" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><i class="bi bi-chevron-left"></i></button>
      <button class="carousel-control-next mf-arrow" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><i class="bi bi-chevron-right"></i></button>
    @endif
  </div>
</section>

<!-- Services -->
<section class="mf-services border-bottom">
  <div class="container-xl">
    <div class="row">
      <div class="col-6 col-lg-3 mf-service">
        <i class="bi bi-truck"></i>
        <div><div class="mf-service__t">Livraison nationale</div><div class="mf-service__s">Dans les 33 préfectures</div></div>
      </div>
      <div class="col-6 col-lg-3 mf-service">
        <i class="bi bi-arrow-repeat"></i>
        <div><div class="mf-service__t">Retour sous 7 jours</div><div class="mf-service__s">En cas de problème</div></div>
      </div>
      <div class="col-6 col-lg-3 mf-service">
        <i class="bi bi-credit-card-2-back"></i>
        <div><div class="mf-service__t">Paiement sécurisé</div><div class="mf-service__s">Mobile money &amp; carte</div></div>
      </div>
      <div class="col-6 col-lg-3 mf-service">
        <i class="bi bi-headset"></i>
        <div><div class="mf-service__t">Support client</div><div class="mf-service__s">À votre écoute 6j/7</div></div>
      </div>
    </div>
  </div>
</section>

<!-- Catégories -->
@if ($categories->isNotEmpty())
<section class="container-xl section">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="section-title mb-0">Parcourir par catégorie</h2>
    <a href="{{ route('shop.products.index') }}" class="section-link">Tout voir →</a>
  </div>
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
    @foreach ($categories as $cat)
      <div class="col">
        <a href="{{ route('shop.products.index', ['category' => $cat->slug]) }}" class="cat d-flex flex-column align-items-center gap-2">
          <span class="cat__icon"><span class="cat__fallback">🏷️</span></span>
          <span class="cat__name">{{ $cat->name }}</span>
        </a>
      </div>
    @endforeach
  </div>
</section>
@endif

<!-- Meilleures ventes -->
@if ($bestSellers->isNotEmpty())
<section class="container-xl section">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="section-title mb-0">Meilleures ventes</h2>
    <a href="{{ route('shop.products.index') }}" class="section-link">Tout voir →</a>
  </div>
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
    @foreach ($bestSellers as $product)
      <div class="col">@include('shop.partials.product-card', ['product' => $product])</div>
    @endforeach
  </div>
</section>
@endif

<!-- Nouveautés -->
@if ($newArrivals->isNotEmpty())
<section class="container-xl section">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="section-title mb-0">Nouveautés</h2>
    <a href="{{ route('shop.products.index') }}" class="section-link">Tout voir →</a>
  </div>
  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3">
    @foreach ($newArrivals as $product)
      <div class="col">@include('shop.partials.product-card', ['product' => $product])</div>
    @endforeach
  </div>
</section>
@endif
@endsection
