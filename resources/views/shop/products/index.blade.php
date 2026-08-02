@extends('shop.layouts.app')

@section('title', ($activeCategory->name ?? 'Boutique') . ' — CAURISHOP')

@section('content')
@php
    // Paramètres à conserver d'un filtre à l'autre.
    $keep = array_filter([
        'q'    => request('q'),
        'sort' => $sort !== 'popularity' ? $sort : null,
    ]);
    $hasFilter = $activeCategory || request('q') || request('min_price') || request('max_price');
@endphp

<div class="breadcrumb-bar">
  <div class="container-xl d-flex align-items-center gap-2 py-2 px-3">
    <a href="{{ route('home') }}">Accueil</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <span class="fw-semibold text-dark">{{ $activeCategory->name ?? 'Boutique' }}</span>
  </div>
</div>

<div class="container-xl"><div class="row g-0">

  <aside class="col-lg-3 col-xl-2 shop-aside p-4">
    <div class="d-flex align-items-baseline justify-content-between pb-3">
      <span class="fw-bolder d-flex align-items-center gap-2" style="font-size:16px"><i class="bi bi-sliders" style="font-size:14px"></i>Filtres</span>
      @if ($hasFilter)
        <a href="{{ route('shop.products.index') }}" class="text-brand fw-semibold" style="font-size:12.5px">Effacer tout</a>
      @endif
    </div>

    <div class="border-top py-3">
      <div class="d-flex align-items-center justify-content-between filter-title mb-2">Catégories</div>
      <a href="{{ route('shop.products.index', $keep) }}" class="filter-item{{ $activeCategory ? '' : ' active' }}">
        Toutes <span class="count">{{ $totalPublished }}</span>
      </a>
      @foreach ($categories as $cat)
        <a href="{{ route('shop.products.index', $keep + ['category' => $cat->slug]) }}"
           class="filter-item{{ $activeCategory && $activeCategory->id === $cat->id ? ' active' : '' }}">
          {{ $cat->name }} <span class="count">{{ $cat->products_count }}</span>
        </a>
      @endforeach
    </div>

    <form method="GET" action="{{ route('shop.products.index') }}" class="border-top py-3">
      @foreach ($keep as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
      @endforeach
      @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif

      <div class="d-flex align-items-center justify-content-between filter-title mb-3">Prix (GNF)</div>
      <div class="d-flex align-items-center gap-2">
        <input type="number" name="min_price" min="0" class="range-input" value="{{ request('min_price') }}" placeholder="0" aria-label="Prix minimum">
        <span class="text-muted" style="font-size:12px">—</span>
        <input type="number" name="max_price" min="0" class="range-input" value="{{ request('max_price') }}" placeholder="{{ (int) $maxPrice }}" aria-label="Prix maximum">
      </div>
      <button class="btn-brand w-100 mt-3" type="submit" style="font-size:14px;padding:.7rem 0">Appliquer les filtres</button>
    </form>
  </aside>

  <main class="col p-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <span class="fw-bolder" style="font-size:20px">
        {{ $activeCategory->name ?? 'Boutique' }}
        <span class="text-muted fw-normal" style="font-size:13.5px">— {{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</span>
      </span>
      <form method="GET" action="{{ route('shop.products.index') }}">
        @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
        @if ($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory->slug }}">@endif
        @if (request('min_price'))<input type="hidden" name="min_price" value="{{ request('min_price') }}">@endif
        @if (request('max_price'))<input type="hidden" name="max_price" value="{{ request('max_price') }}">@endif
        <select name="sort" class="form-select w-auto" style="font-size:13.5px" aria-label="Tri" onchange="this.form.submit()">
          <option value="popularity" @selected($sort === 'popularity')>Trier : Pertinence</option>
          <option value="price_asc" @selected($sort === 'price_asc')>Prix croissant</option>
          <option value="price_desc" @selected($sort === 'price_desc')>Prix décroissant</option>
          <option value="newest" @selected($sort === 'newest')>Nouveautés</option>
        </select>
      </form>
    </div>

    @if ($products->isEmpty())
      <div class="empty-state">
        <i class="bi bi-search"></i>
        Aucun produit ne correspond à votre recherche.
        <div class="mt-3"><a href="{{ route('shop.products.index') }}" class="btn-brand btn-sm">Voir tous les produits</a></div>
      </div>
    @else
      <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
        @foreach ($products as $product)
          <div class="col">@include('shop.partials.product-card', ['product' => $product])</div>
        @endforeach
      </div>

      @if ($products->hasPages())
        <nav class="mt-4" aria-label="Pagination des produits">{{ $products->links('pagination::bootstrap-5') }}</nav>
      @endif
    @endif
  </main>

</div></div>
@endsection
