@extends('shop.layouts.app')

@section('title', 'Accueil — CAURISHOP')

@section('content')
<section class="hero">
  <div class="row g-0 mx-0" style="min-height:540px">
    <div class="col-lg-7 d-flex flex-column justify-content-center gap-3 py-5" style="padding-left:max(32px,calc((100vw - 1296px)/2));padding-right:48px">
      <span class="eyebrow">Paiement échelonné</span>
      <h1>Commandez aujourd'hui,<br>payez molo molo.</h1>
      <p class="text-muted" style="font-size:17px;line-height:1.6;max-width:520px">Artisanat, mode, cosmétiques : les boutiques de tout le pays, livrées jusqu'à votre porte, de Conakry à Nzérékoré.</p>
      <div class="d-flex gap-3 mt-2">
        <a href="{{ route('shop.products.index') }}" class="btn-brand">Commander maintenant</a>
        <a href="{{ route('get-started') }}" class="btn-outline-ink">Ouvrir un compte pro</a>
      </div>
    </div>
    <div class="col-lg-5 position-relative">
      <div id="heroCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel">
        <div class="carousel-indicators">
          <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Casque"></button>
          <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Réfrigérateur"></button>
        </div>
        <div class="carousel-inner">
          <div class="carousel-item active" data-bs-interval="4000"><img class="cover" src="https://images.unsplash.com/photo-1583394838336-acd977736f90?q=80&w=900&h=760&fit=crop&auto=format" alt="Casque audio — Électronique"></div>
          <div class="carousel-item" data-bs-interval="4000"><div class="contain-wrap"><img src="{{ asset('shop/img/frigo.png') }}" alt="Réfrigérateur — Électroménager"></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<main class="container-xl py-4">

  @if ($categories->isNotEmpty())
    <section class="mb-4" data-hscroll>
      <div class="d-flex align-items-baseline justify-content-between mb-3"><span class="section-title">Categories</span>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('shop.products.index') }}" class="section-link me-2">Tout voir</a>
          <button class="arrow-btn" data-dir="prev" type="button" aria-label="Précédent"><i class="bi bi-chevron-left"></i></button>
          <button class="arrow-btn" data-dir="next" type="button" aria-label="Suivant"><i class="bi bi-chevron-right"></i></button>
        </div>
      </div>
      @php
        $catIcons = [
            'electronique'   => 'bi-phone',
            'mode-vetements' => 'bi-bag',
            'maison-cuisine' => 'bi-house-door',
            'beaute-sante'   => 'bi-heart',
            'informatique'   => 'bi-laptop',
            'accessoires'    => 'bi-smartwatch',
        ];
      @endphp
      <div class="hscroll pb-1">
        @foreach ($categories as $cat)
          <a href="{{ route('shop.products.index', ['category' => $cat->slug]) }}" class="cat-tile d-flex flex-column align-items-center gap-2">
            <span class="cat-ic"><i class="bi {{ $catIcons[$cat->slug] ?? 'bi-tag' }}"></i></span>
            <span>{{ $cat->name }}</span>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  @if ($newArrivals->isNotEmpty())
    <section class="mb-4" data-hscroll>
      <div class="d-flex align-items-baseline justify-content-between mb-3"><span class="section-title">Nouveautés</span>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('shop.products.index', ['sort' => 'newest']) }}" class="section-link me-2">Tout voir</a>
          <button class="arrow-btn" data-dir="prev" type="button" aria-label="Précédent"><i class="bi bi-chevron-left"></i></button>
          <button class="arrow-btn" data-dir="next" type="button" aria-label="Suivant"><i class="bi bi-chevron-right"></i></button>
        </div>
      </div>
      <div class="hscroll pb-1">
        @foreach ($newArrivals as $product)
          @include('shop.partials.product-card', ['product' => $product, 'isNew' => true])
        @endforeach
      </div>
    </section>
  @endif

  @if ($popularProducts->isNotEmpty())
    <section>
      <div class="d-flex align-items-baseline justify-content-between mb-3"><span class="section-title">Nos produits</span><a href="{{ route('shop.products.index') }}" class="section-link">Tout voir</a></div>
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
        @foreach ($popularProducts as $product)
          <div class="col">@include('shop.partials.product-card', ['product' => $product])</div>
        @endforeach
      </div>
    </section>
  @endif

</main>
@endsection
