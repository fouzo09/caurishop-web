@php
    $cover = $product->coverUrl();
    // Seconde photo : révélée au survol pour donner un aperçu du produit.
    $altCover = $product->images->pluck('url')->first(fn ($url) => $url !== $cover);

    $isVariable = $product->isVariable();
    $displayPrice = $isVariable ? (float) ($product->variants->min('price') ?? 0) : (float) $product->price;
    $isService = $product->isService();
    $stock = $product->stock_status;                 // disponible | faible | rupture
    $soldOut = ! $isService && $stock === 'rupture';
    $canQuickAdd = ! $isVariable && ! $isService && ! $soldOut;
    $url = route('shop.products.show', $product->id);
    $isFavorite = in_array($product->id, $shopFavoriteIds ?? [], true);
    $isNew = $isNew ?? false;
@endphp
<div class="pcard">
  <div class="media">
    <a href="{{ $url }}" aria-label="{{ $product->name }}">
      @if ($cover)
        <img src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">
        @if ($altCover)
          <img class="alt" src="{{ $altCover }}" alt="" aria-hidden="true" loading="lazy" onerror="this.remove()">
        @endif
      @else
        <span class="ph">🛍️</span>
      @endif
    </a>

    <div class="badges">
      @if ($soldOut)
        <span class="badge-promo badge-promo--out">Rupture</span>
      @else
        @if ($isNew)<span class="badge-promo">Nouveau</span>@endif
        @if ($isService)<span class="badge-promo badge-promo--new">Service</span>@endif
        @if ($stock === 'faible')<span class="badge-promo badge-promo--low">Stock limité</span>@endif
      @endif
    </div>

    <div class="quick">
      <button type="button" class="wish{{ $isFavorite ? ' is-on' : '' }}"
              data-fav-toggle="{{ route('shop.account.favorites.toggle', $product->id) }}"
              data-fav-login="{{ route('shop.login') }}"
              aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"
              title="Ajouter aux favoris" aria-label="Ajouter aux favoris">
        <i class="bi {{ $isFavorite ? 'bi-heart-fill' : 'bi-heart' }}"></i>
      </button>
      <a href="{{ $url }}" title="Voir le produit" aria-label="Voir le produit"><i class="bi bi-eye"></i></a>
    </div>

    @if ($canQuickAdd)
      <form method="POST" action="{{ route('shop.cart.add') }}" class="addbar">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button type="submit"><i class="bi bi-cart-plus"></i> Ajouter au panier</button>
      </form>
    @elseif ($isVariable)
      <div class="addbar"><a href="{{ $url }}"><i class="bi bi-sliders"></i> Choisir une option</a></div>
    @elseif ($isService)
      <div class="addbar"><a href="{{ $url }}"><i class="bi bi-calendar-check"></i> Voir le service</a></div>
    @else
      <div class="addbar addbar--muted"><a href="{{ $url }}"><i class="bi bi-eye"></i> Voir le produit</a></div>
    @endif
  </div>

  {{-- Chaque ligne a une hauteur fixe : toutes les cartes font la même taille. --}}
  <div class="body">
    <span class="cat">{{ $product->category->name ?? '' }}</span>
    <a href="{{ $url }}" class="title">{{ $product->name }}</a>

    @include('shop.partials.stars', ['rating' => $product->ratingAverage(), 'count' => $product->ratingCount()])

    <div class="foot d-flex align-items-end justify-content-between gap-2">
      <span class="price">
        <small>{{ $isVariable ? 'À partir de' : '' }}</small>
        @gnf($displayPrice)
      </span>
      @if ($soldOut)
        <span class="stock stock--out"><i class="bi bi-x-circle"></i> Épuisé</span>
      @elseif ($stock === 'faible')
        <span class="stock stock--low"><i class="bi bi-exclamation-circle"></i> Bientôt fini</span>
      @else
        <span class="stock stock--ok"><i class="bi bi-check-circle"></i> En stock</span>
      @endif
    </div>
  </div>
</div>
