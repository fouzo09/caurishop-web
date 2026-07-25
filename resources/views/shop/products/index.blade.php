@extends('shop.layouts.app')

@section('title', ($activeCategory->name ?? 'Boutique') . ' — CAURISHOP')

@section('content')
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('home') }}">Accueil</a> › <span class="crumb-current">{{ $activeCategory->name ?? 'Boutique' }}</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">{{ $activeCategory ? $activeCategory->name : 'Boutique — Tous les produits' }}</h1>
  </div>
</div>

<div class="container-xl py-4">
  <div class="row g-4">

    <!-- SIDEBAR -->
    <aside class="col-lg-3">
      <form method="GET" action="{{ route('shop.products.index') }}">
        {{-- conserve la recherche courante --}}
        @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
        <input type="hidden" name="sort" value="{{ $sort }}">

        <div class="side-card mb-3">
          <div class="side-card__title">Catégories</div>
          <label class="form-check d-flex align-items-center justify-content-between filter-row">
            <span class="d-flex align-items-center gap-2">
              <input class="form-check-input m-0" type="radio" name="category" value="" @checked(! $activeCategory) onchange="this.form.submit()"> Toutes
            </span>
          </label>
          @foreach ($categories as $cat)
            <label class="form-check d-flex align-items-center justify-content-between filter-row">
              <span class="d-flex align-items-center gap-2">
                <input class="form-check-input m-0" type="radio" name="category" value="{{ $cat->slug }}" @checked($activeCategory && $activeCategory->id === $cat->id) onchange="this.form.submit()"> {{ $cat->name }}
              </span>
              <span class="filter-count">{{ $cat->products()->where('is_published', true)->where('is_active', true)->count() }}</span>
            </label>
          @endforeach
        </div>

        <div class="side-card mb-3">
          <div class="side-card__title">Prix (GNF)</div>
          <input type="range" class="form-range" id="priceRange" name="max_price" min="0" max="{{ $maxPrice }}" step="50000" value="{{ request('max_price', $maxPrice) }}">
          <div class="d-flex justify-content-between small mt-1">
            <span class="text-muted">0 GNF</span>
            <span class="fw-bold text-brand">Jusqu'à <span id="priceLabel">@gnf(request('max_price', $maxPrice))</span></span>
          </div>
          <button type="submit" class="btn btn-sm btn-brand w-100 mt-2">Filtrer</button>
        </div>
      </form>
    </aside>

    <!-- PRODUITS -->
    <div class="col-lg-9">
      <div class="toolbar d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <span class="small text-muted"><strong class="text-ink">{{ $products->total() }}</strong> produits trouvés</span>
        <form method="GET" action="{{ route('shop.products.index') }}" class="d-flex align-items-center gap-2">
          @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
          @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
          @if (request('max_price'))<input type="hidden" name="max_price" value="{{ request('max_price') }}">@endif
          <span class="small text-muted">Trier par</span>
          <select name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="popularity" @selected($sort === 'popularity')>Popularité</option>
            <option value="price_asc" @selected($sort === 'price_asc')>Prix croissant</option>
            <option value="price_desc" @selected($sort === 'price_desc')>Prix décroissant</option>
            <option value="newest" @selected($sort === 'newest')>Nouveautés</option>
          </select>
        </form>
      </div>

      @if ($products->isEmpty())
        <div class="side-card text-center py-5">
          <div class="fs-1 mb-2">🛍️</div>
          <p class="text-muted mb-0">Aucun produit ne correspond à votre recherche.</p>
        </div>
      @else
        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
          @foreach ($products as $product)
            <div class="col">@include('shop.partials.product-card', ['product' => $product])</div>
          @endforeach
        </div>

        <nav class="mt-4" aria-label="Pagination des produits">
          {{ $products->links('pagination::bootstrap-5') }}
        </nav>
      @endif
    </div>
  </div>
</div>
@endsection
