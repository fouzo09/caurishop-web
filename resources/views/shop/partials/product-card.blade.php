@php
    $cover = $product->coverUrl();
    $isVariable = $product->isVariable();
    $displayPrice = $isVariable ? (float) ($product->variants->min('price') ?? 0) : (float) $product->price;
    $prefix = $isVariable ? 'À partir de ' : '';
    $isService = $product->isService();
    $soldOut = ! $isService && $product->stock_status === 'rupture';
    $canQuickAdd = ! $isVariable && ! $isService && ! $soldOut;
    $url = route('shop.products.show', $product->id);
@endphp
<div class="pcard">
  <div class="pcard__media">
    <a href="{{ $url }}" class="pcard__imglink" aria-label="{{ $product->name }}">
      <span class="pcard__emoji">🛍️</span>
      @if ($cover)
        <img class="pcard__img" src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">
      @endif
    </a>

    @if ($soldOut)
      <span class="pcard__badge pcard__badge--out">Rupture</span>
    @elseif ($product->isService())
      <span class="pcard__badge pcard__badge--new">Service</span>
    @endif

    <div class="pcard__actions">
      <button type="button" class="pcard__act" title="Ajouter aux favoris"><i class="bi bi-heart"></i></button>
      <a href="{{ $url }}" class="pcard__act" title="Voir le produit"><i class="bi bi-eye"></i></a>
    </div>

    @if ($canQuickAdd)
      <form method="POST" action="{{ route('shop.cart.add') }}" class="pcard__addbar">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button type="submit"><i class="bi bi-bag-plus"></i> Ajouter au panier</button>
      </form>
    @elseif ($isService)
      <a href="{{ $url }}" class="pcard__addbar pcard__addbar--link"><i class="bi bi-eye"></i> Voir le service</a>
    @else
      <a href="{{ $url }}" class="pcard__addbar pcard__addbar--link"><i class="bi bi-eye"></i> Voir le produit</a>
    @endif
  </div>

  <div class="pcard__body">
    @if ($product->category)
      <span class="pcard__cat">{{ $product->category->name }}</span>
    @endif
    <a href="{{ $url }}" class="pcard__title">{{ $product->name }}</a>
    <div class="pcard__rating" aria-hidden="true">★★★★★</div>
    <div class="pcard__price">{{ $prefix }}@gnf($displayPrice)</div>
  </div>
</div>
