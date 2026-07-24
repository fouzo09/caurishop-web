@php
    $cover = $product->coverUrl();
    // Prix affiché : produit simple => prix ; variable => plus petit prix de variante.
    $displayPrice = $product->isVariable()
        ? (float) ($product->variants->min('price') ?? 0)
        : (float) $product->price;
    $prefix = $product->isVariable() ? 'À partir de ' : '';
@endphp
<a href="{{ route('shop.products.show', $product->slug ?? $product->id) }}" class="pcard d-flex flex-column h-100">
  <div class="pcard__media">
    @if ($product->isOutOfStock())
      <span class="pcard__badge">Rupture</span>
    @endif
    <span class="pcard__emoji">🛍️</span>
    @if ($cover)
      <img class="pcard__img" src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">
    @endif
  </div>
  <div class="pcard__body d-flex flex-column gap-2">
    <span class="pcard__title">{{ $product->name }}</span>
    <div class="d-flex align-items-baseline gap-2">
      <span class="pcard__price">{{ $prefix }}@gnf($displayPrice)</span>
    </div>
  </div>
</a>
