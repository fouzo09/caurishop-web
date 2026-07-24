@extends('shop.layouts.app')

@section('title', 'Accueil — CAURISHOP')

@section('content')
<!-- HERO : carrousel Bootstrap pleine largeur -->
<section class="hero border-bottom">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators hero-dots">
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Livraison offerte"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Paiements sécurisés"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Bienvenue"></button>
    </div>
    <div class="carousel-inner">

      <div class="carousel-item active promo promo--blue" data-bs-interval="6000">
        <div class="container-xl promo__inner">
          <div>
            <span class="promo__eyebrow">Le marché en ligne de la Guinée</span>
            <h1 class="promo__title">Tout ce qu'il vous faut,<br>livré près de chez vous.</h1>
            <p class="promo__sub">Des milliers de produits à petits prix, payables par Orange Money, MTN MoMo ou à la livraison.</p>
            <a href="{{ route('shop.products.index') }}" class="promo__cta">Découvrir la boutique</a>
          </div>
          <span class="promo__figure" aria-hidden="true">C</span>
        </div>
      </div>

      <div class="carousel-item promo promo--dark" data-bs-interval="6000">
        <div class="container-xl promo__inner">
          <div>
            <span class="promo__eyebrow">Couverture nationale</span>
            <h2 class="promo__title">Présents dans les<br>33 préfectures.</h2>
            <p class="promo__sub">Livraison via nos partenaires logistiques à Conakry et dans toutes les régions, suivi de commande inclus.</p>
            <a href="{{ route('shop.products.index') }}" class="promo__cta">Commander maintenant</a>
          </div>
          <span class="promo__figure" aria-hidden="true">33</span>
        </div>
      </div>

      <div class="carousel-item promo promo--light" data-bs-interval="6000">
        <div class="container-xl promo__inner">
          <div>
            <span class="promo__eyebrow">Nouveau client</span>
            <h2 class="promo__title">Créez votre compte<br>en une minute.</h2>
            <p class="promo__sub">Inscrivez-vous gratuitement et retrouvez vos commandes, vos adresses et vos suivis au même endroit.</p>
            <a href="{{ route('shop.register') }}" class="promo__cta">Créer un compte</a>
          </div>
          <span class="promo__figure" aria-hidden="true">1 min</span>
        </div>
      </div>
    </div>

    <button class="carousel-control-prev hero-arrow hero-arrow--prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">‹<span class="visually-hidden">Précédent</span></button>
    <button class="carousel-control-next hero-arrow hero-arrow--next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">›<span class="visually-hidden">Suivant</span></button>
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
