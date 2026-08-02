<header class="border-bottom">
  <div class="container-xl d-flex align-items-center gap-4 py-3 px-3">
    <a href="{{ route('home') }}" class="logo flex-shrink-0">caurishop<span class="text-brand">.</span></a>
    <form action="{{ route('shop.products.index') }}" method="GET" class="search-box d-flex align-items-center flex-grow-1 px-3">
      <i class="bi bi-search text-muted"></i>
      <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher un produit, une boutique…" aria-label="Recherche">
    </form>
    <div class="d-flex align-items-center gap-4 flex-shrink-0" style="font-size:14px">
      <a href="{{ auth()->check() ? route('shop.account.index') : route('shop.login') }}" class="d-flex align-items-center gap-2"><i class="bi bi-person fs-5"></i><span class="header-action__label">Compte</span></a>
      <a href="{{ route('shop.cart.index') }}" class="d-flex align-items-center gap-2"><i class="bi bi-cart2 fs-5"></i><span class="header-action__label">Panier</span> @if ($shopCartCount > 0)<span class="cart-badge">{{ $shopCartCount }}</span>@endif</a>
    </div>
  </div>
</header>

@php
  // Catégorie active : filtre courant, sinon celle du produit consulté (fiche produit).
  $currentCategory = request('category');
  if (! $currentCategory && ($p = request()->route('product')) instanceof \App\Models\Product) {
      $currentCategory = $p->category?->slug;
  }
@endphp
<nav class="border-bottom"><div class="container-xl d-flex align-items-center gap-2 py-2 flex-wrap px-3">
  <a href="{{ route('shop.products.index') }}" class="cat-pill-dark d-flex align-items-center gap-2"><i class="bi bi-list"></i>Toutes les catégories</a>
  @foreach ($shopCategories as $cat)
    <a href="{{ route('shop.products.index', ['category' => $cat->slug]) }}" class="cat-pill{{ $currentCategory === $cat->slug ? ' active' : '' }}">{{ $cat->name }}</a>
  @endforeach
  <span class="ms-auto d-none d-lg-flex align-items-center gap-2" style="font-size:13px;color:var(--muted)"><i class="bi bi-truck"></i>Livraison dans les 33 préfectures</span>
</div></nav>
