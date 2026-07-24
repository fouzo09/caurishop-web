<header class="site-header sticky-top bg-white">
  <div class="topstrip border-bottom">
    <div class="container-xl d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
      <div class="d-flex align-items-center gap-3">
        <a href="{{ route('get-started') }}">Vendez sur CAURISHOP</a>
        <a href="{{ route('shop.products.index') }}">Boutiques</a>
        <a href="{{ route('shop.contact') }}">Aide</a>
      </div>
      <div class="d-flex align-items-center gap-3">
        <span>GNF · Français</span>
        <a href="{{ route('shop.contact') }}">Suivi de commande</a>
      </div>
    </div>
  </div>

  <div class="border-bottom">
    <div class="container-xl d-flex align-items-center gap-4 py-3 flex-wrap">
      <a href="{{ route('home') }}" class="logo d-flex align-items-center gap-2 flex-shrink-0">
        <span class="logo__mark">C</span>
        <span class="logo__name">CAURISHOP</span>
      </a>
      <form action="{{ route('shop.products.index') }}" method="GET" class="search d-flex flex-grow-1">
        <select name="category" class="search__select" aria-label="Catégorie de recherche">
          <option value="">Toutes catégories</option>
          @foreach ($shopCategories as $cat)
            <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
          @endforeach
        </select>
        <input name="q" value="{{ request('q') }}" class="search__input" placeholder="Rechercher un produit…" aria-label="Recherche">
        <button class="search__btn" type="submit">Rechercher</button>
      </form>
      <div class="d-flex align-items-center gap-4 flex-shrink-0 header-actions">
        @auth
          <a href="{{ route('shop.account.index') }}" class="header-action d-flex align-items-center gap-2">
            <span class="fs-5">👤</span><span class="header-action__label">Mon compte</span>
          </a>
        @else
          <a href="{{ route('shop.login') }}" class="header-action d-flex align-items-center gap-2">
            <span class="fs-5">👤</span><span class="header-action__label">Mon compte</span>
          </a>
        @endauth
        <a href="{{ route('shop.cart.index') }}" class="header-action d-flex align-items-center gap-2">
          <span class="cart-icon">🛒@if ($shopCartCount > 0)<span class="cart-badge">{{ $shopCartCount }}</span>@endif</span>
          <span class="header-action__label">Panier</span>
        </a>
      </div>
    </div>
  </div>

  <nav class="border-bottom">
    <div class="container-xl catnav d-flex align-items-center flex-wrap">
      <a href="{{ route('shop.products.index') }}" class="catnav__all">☰ Toutes les catégories</a>
      @foreach ($shopCategories as $cat)
        <a href="{{ route('shop.products.index', ['category' => $cat->slug]) }}" class="catnav__link">{{ $cat->name }}</a>
      @endforeach
      <span class="catnav__note ms-auto d-none d-lg-block">Livraison offerte dès @gnf(500000)</span>
    </div>
  </nav>
</header>
