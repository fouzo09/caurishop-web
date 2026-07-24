@extends('shop.layouts.app')

@section('title', 'Accueil — CAURISHOP')

@section('content')
<!-- HERO marketplace : rail catégories + carrousel + services -->
@php
    $slides = [
        [
            'mod' => 'blue', 'eyebrow' => 'Le marché en ligne de la Guinée',
            'title' => "Tout ce qu'il vous faut,<br>livré près de chez vous.",
            'sub' => 'Des milliers de produits à petits prix, payables par mobile money ou à la livraison.',
            'cta' => 'Découvrir la boutique', 'url' => route('shop.products.index'),
            'product' => $bestSellers->get(0),
        ],
        [
            'mod' => 'dark', 'eyebrow' => 'Meilleures ventes',
            'title' => 'Les produits<br>préférés des Guinéens.',
            'sub' => 'Sélection des articles les plus commandés du moment, prêts à être livrés.',
            'cta' => 'Voir les meilleures ventes', 'url' => route('shop.products.index', ['sort' => 'popularity']),
            'product' => $bestSellers->get(1) ?? $bestSellers->get(0),
        ],
        [
            'mod' => 'amber', 'eyebrow' => 'Nouveautés',
            'title' => 'Les dernières<br>arrivées en boutique.',
            'sub' => 'Découvrez les nouveaux produits ajoutés cette semaine par nos vendeurs.',
            'cta' => 'Voir les nouveautés', 'url' => route('shop.products.index', ['sort' => 'newest']),
            'product' => $newArrivals->get(0),
        ],
    ];
@endphp
<section class="mkt-hero">
  <div class="container-xl">
    <div class="row g-3">

      <!-- Rail catégories -->
      <aside class="col-lg-3 d-none d-lg-block">
        <nav class="mkt-rail">
          <div class="mkt-rail__title">Catégories</div>
          @foreach ($categories as $cat)
            <a href="{{ route('shop.products.index', ['category' => $cat->slug]) }}">
              <i class="bi {{ $cat->icon ? 'bi-tag' : 'bi-tag' }}"></i>
              <span>{{ $cat->name }}</span>
              <i class="bi bi-chevron-right chev"></i>
            </a>
          @endforeach
        </nav>
      </aside>

      <!-- Carrousel central -->
      <div class="col-lg-6">
        <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel">
          <div class="carousel-inner h-100 rounded-4 overflow-hidden">
            @foreach ($slides as $i => $s)
              <div class="carousel-item h-100 {{ $i === 0 ? 'active' : '' }}" data-bs-interval="6000">
                <div class="mkt-slide mkt-slide--{{ $s['mod'] }}">
                  <div class="mkt-slide__text">
                    <span class="mkt-slide__eyebrow">{{ $s['eyebrow'] }}</span>
                    <h{{ $i === 0 ? '1' : '2' }} class="mkt-slide__title">{!! $s['title'] !!}</h{{ $i === 0 ? '1' : '2' }}>
                    <p class="mkt-slide__sub">{{ $s['sub'] }}</p>
                    <a href="{{ $s['url'] }}" class="mkt-slide__cta">{{ $s['cta'] }}</a>
                  </div>
                  @if ($s['product'] && $s['product']->coverUrl())
                    <a href="{{ route('shop.products.show', $s['product']->id) }}" class="mkt-slide__media">
                      <img src="{{ $s['product']->coverUrl() }}" alt="{{ $s['product']->name }}" loading="lazy" onerror="this.closest('.mkt-slide__media').remove()">
                    </a>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
          <div class="carousel-indicators mkt-dots">
            @foreach ($slides as $i => $s)
              <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
            @endforeach
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span><span class="visually-hidden">Précédent</span></button>
          <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span><span class="visually-hidden">Suivant</span></button>
        </div>
      </div>

      <!-- Cartes services -->
      <div class="col-lg-3 d-none d-lg-flex flex-column gap-3">
        <div class="mkt-promo__card">
          <span class="ic">🚚</span>
          <span class="t">Livraison nationale</span>
          <span class="s">Dans les 33 préfectures, suivi inclus.</span>
        </div>
        <div class="mkt-promo__card">
          <span class="ic">📱</span>
          <span class="t">Paiement mobile money</span>
          <span class="s">Orange Money, MTN MoMo ou à la livraison.</span>
        </div>
        <div class="mkt-promo__card">
          <span class="ic">🔒</span>
          <span class="t">Achat protégé</span>
          <span class="s">Vos paiements et données sécurisés.</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Bande de confiance -->
<section class="border-bottom">
  <div class="container-xl py-3">
    <div class="row g-3">
      <div class="col-12 col-md-4 d-flex align-items-center gap-3">
        <span class="trust__icon">🚚</span>
        <div><div class="trust__title">Livraison dans les 33 préfectures</div><div class="trust__sub">via nos partenaires logistiques</div></div>
      </div>
      <div class="col-12 col-md-4 d-flex align-items-center gap-3">
        <span class="trust__icon">🔒</span>
        <div><div class="trust__title">Paiements sécurisés</div><div class="trust__sub">Mobile money &amp; carte protégés</div></div>
      </div>
      <div class="col-12 col-md-4 d-flex align-items-center gap-3">
        <span class="trust__icon">⭐</span>
        <div><div class="trust__title">Service client à l'écoute</div><div class="trust__sub">Du lundi au samedi</div></div>
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
