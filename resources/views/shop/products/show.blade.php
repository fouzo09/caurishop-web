@extends('shop.layouts.app')

@section('title', $product->name . ' — CAURISHOP')

@section('content')
@php
    $images = $product->images;
    $cover  = $product->coverUrl();
    $isVariable = $product->isVariable();
    $basePrice = $isVariable ? (float) ($product->variants->min('price') ?? 0) : (float) $product->price;
    $soldOut = ! $product->isService() && $product->stock_status === 'rupture';
    $customer = auth()->check() ? auth()->user()->customer : null;
    $isFavorite = $customer ? $customer->favorites()->where('product_id', $product->id)->exists() : false;
    $ratingAvg   = $product->ratingAverage();
    $ratingCount = $product->ratingCount();
@endphp

<div class="breadcrumb-bar">
  <div class="container-xl d-flex align-items-center gap-2 py-2 px-3 flex-wrap">
    <a href="{{ route('home') }}">Accueil</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    @if ($product->category)
      <a href="{{ route('shop.products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
      <i class="bi bi-chevron-right" style="font-size:11px"></i>
    @endif
    <span class="fw-semibold text-dark">{{ $product->name }}</span>
  </div>
</div>

<main class="container-xl py-4">
  <div class="row g-5">

    {{-- GALERIE --}}
    <div class="col-lg-6">
      <div class="gallery-main">
        @if ($cover)
          <img id="mainImage" src="{{ $cover }}" alt="{{ $product->name }}" onerror="this.remove()">
        @else
          <span class="ph">🛍️</span>
        @endif
      </div>
      @if ($images->count() > 1)
        <div class="d-flex gap-2 mt-3 flex-wrap">
          @foreach ($images as $img)
            <button type="button" class="thumb {{ $loop->first ? 'active' : '' }}" data-full="{{ $img->url }}" aria-label="Vue {{ $loop->iteration }}">
              <img src="{{ $img->url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">
            </button>
          @endforeach
        </div>
      @endif
    </div>

    {{-- INFOS --}}
    <div class="col-lg-6 d-flex flex-column gap-3">
      @if ($product->category)
        <span class="text-brand fw-bold text-uppercase" style="font-size:12.5px;letter-spacing:.07em">{{ $product->category->name }}</span>
      @endif
      <h1 class="fw-bolder m-0" style="font-size:30px;letter-spacing:-.02em">{{ $product->name }}</h1>

      <div class="d-flex align-items-center gap-2 text-muted" style="font-size:13.5px">
        @include('shop.partials.stars', ['rating' => $ratingAvg])
        <a href="#avis" class="text-muted">
          {{ $ratingCount > 0 ? $ratingAvg . '/5 · ' . $ratingCount . ' avis' : 'Aucun avis' }}
        </a>
        <span class="text-muted">·</span>
        <span class="fw-semibold" id="productStock" style="color:{{ $soldOut ? 'var(--danger)' : 'var(--green)' }}">
          {{ $soldOut ? 'Rupture de stock' : 'En stock' }}
        </span>
      </div>

      <div class="d-flex align-items-baseline gap-3">
        <span class="fw-bolder" style="font-size:28px" id="productPrice">{{ $isVariable ? 'À partir de ' : '' }}@gnf($basePrice)</span>
      </div>

      {{-- Emplacement du paiement échelonné « molo molo » (à brancher sur les CreditPlan). --}}

      <form method="POST" action="{{ route('shop.cart.add') }}" class="d-flex flex-column gap-3">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        @if ($isVariable)
          @php
              $axes     = $product->variantAxes();
              $variants = $product->variantPayload();
              // Variante retenue au chargement : la première disponible.
              $selected = collect($variants)->firstWhere('inStock', true) ?? ($variants[0] ?? null);
          @endphp

          @if ($axes)
            <input type="hidden" name="variant_id" id="variantId" value="{{ $selected['id'] ?? '' }}">

            <div class="variant-picker" id="variantPicker" data-variants="{{ json_encode($variants) }}">
              @foreach ($axes as $axe)
                <div class="variant-axis" data-axis="{{ $axe['key'] }}">
                  <div class="variant-axis__head">
                    <span class="variant-axis__label">{{ $axe['label'] }}&nbsp;:</span>
                    <span class="variant-axis__value" data-axis-value></span>
                  </div>
                  <div class="variant-axis__options">
                    @foreach ($axe['values'] as $option)
                      <button type="button"
                              class="variant-option{{ $axe['imaged'] ? ' variant-option--img' : '' }}"
                              data-value="{{ $option['value'] }}"
                              aria-label="{{ $axe['label'] }} {{ $option['value'] }}"
                              title="{{ $option['value'] }}">
                        @if ($axe['imaged'] && $option['image'])
                          <img src="{{ $option['image'] }}" alt="{{ $option['value'] }}" loading="lazy" onerror="this.remove()">
                        @else
                          <span class="variant-option__text">{{ $option['value'] }}</span>
                        @endif
                      </button>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          @else
            {{-- Variantes sans attributs : on garde la liste déroulante. --}}
            <div>
              <div class="fw-bold mb-2" style="font-size:13.5px">Choix</div>
              <select name="variant_id" class="form-select" required>
                @foreach ($product->variants as $variant)
                  <option value="{{ $variant->id }}" @disabled(! $variant->hasStock())>
                    {{ $variant->name ?? ('Variante #' . $variant->id) }} — @gnf($variant->price){{ $variant->hasStock() ? '' : ' (rupture)' }}
                  </option>
                @endforeach
              </select>
            </div>
          @endif
        @endif

        <div class="d-flex gap-3 align-items-center flex-wrap">
          <div class="qty-box">
            <button type="button" data-minus aria-label="Diminuer"><i class="bi bi-dash"></i></button>
            <input class="val" type="number" name="quantity" value="1" min="1" aria-label="Quantité">
            <button type="button" data-plus aria-label="Augmenter"><i class="bi bi-plus"></i></button>
          </div>
          <button type="submit" id="addToCart" class="btn-brand flex-grow-1 d-flex align-items-center justify-content-center gap-2" @disabled($soldOut)>
            <i class="bi bi-cart-plus"></i>Ajouter au panier
          </button>
          <button type="button" class="btn-outline-ink px-3 wish-lg{{ $isFavorite ? ' is-on' : '' }}"
                  data-fav-toggle="{{ route('shop.account.favorites.toggle', $product->id) }}"
                  data-fav-login="{{ route('shop.login') }}"
                  aria-pressed="{{ $isFavorite ? 'true' : 'false' }}" aria-label="Ajouter aux favoris">
            <i class="bi {{ $isFavorite ? 'bi-heart-fill' : 'bi-heart' }}"></i>
          </button>
        </div>
      </form>

      {{-- Fiche technique : toujours renseignée, elle tient la colonne quand le produit est peu documenté. --}}
      <div class="spec-list">
        @if ($product->sku)
          <div class="spec"><i class="bi bi-upc-scan"></i><span class="spec__k">Référence</span><span class="spec__v">{{ $product->sku }}</span></div>
        @endif
        @if ($product->category)
          <div class="spec"><i class="bi bi-tag"></i><span class="spec__k">Catégorie</span><span class="spec__v"><a href="{{ route('shop.products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a></span></div>
        @endif
        <div class="spec">
          <i class="bi {{ $product->isService() ? 'bi-calendar-check' : 'bi-box-seam' }}"></i>
          <span class="spec__k">Type</span><span class="spec__v">{{ $product->isService() ? 'Service' : 'Produit' }}</span>
        </div>
        <div class="spec">
          @if ($soldOut)
            <i class="bi bi-x-circle spec--out"></i><span class="spec__k">Disponibilité</span><span class="spec__v">Rupture de stock</span>
          @elseif ($product->stock_status === 'faible')
            <i class="bi bi-exclamation-circle spec--low"></i><span class="spec__k">Disponibilité</span><span class="spec__v">Stock limité</span>
          @else
            <i class="bi bi-check-circle spec--ok"></i><span class="spec__k">Disponibilité</span><span class="spec__v">En stock</span>
          @endif
        </div>
        @if ($isVariable)
          <div class="spec"><i class="bi bi-sliders"></i><span class="spec__k">Options</span><span class="spec__v">{{ $product->variants->count() }} disponible{{ $product->variants->count() > 1 ? 's' : '' }}</span></div>
        @endif
      </div>

      {{-- Réassurance : présente sur toutes les fiches. --}}
      <div class="reassure">
        <div><i class="bi bi-truck"></i><span><strong>Livraison partout en Guinée</strong>Conakry 24h · régions 2 à 4 jours</span></div>
        <div><i class="bi bi-arrow-repeat"></i><span><strong>Retours sous 7 jours</strong>En cas de problème sur la commande</span></div>
        <div><i class="bi bi-shield-check"></i><span><strong>Paiement sécurisé</strong>Orange Money, MTN MoMo ou carte</span></div>
        <div><i class="bi bi-headset"></i><span><strong>Support client</strong>À votre écoute du lundi au samedi</span></div>
      </div>
    </div>
  </div>

  {{-- Description : affichée uniquement si le produit en a une. --}}
  @if ($product->description)
    <div class="mt-4">
      <ul class="nav tab-line" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab">Description</button></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">
          <p class="mt-3" style="font-size:14.5px;color:#444;line-height:1.7;max-width:760px">{{ $product->description }}</p>
        </div>
      </div>
    </div>
  @endif

  {{-- AVIS CLIENTS --}}
  <section class="mt-5" id="avis">
    <div class="d-flex align-items-baseline justify-content-between mb-3 flex-wrap gap-2">
      <span class="section-title">Avis et commentaires</span>
      <span class="text-muted" style="font-size:13px">{{ $ratingCount }} avis publié{{ $ratingCount > 1 ? 's' : '' }}</span>
    </div>

    <div class="row g-4 align-items-start">

      {{-- Note moyenne + répartition --}}
      <div class="col-lg-4">
        <div class="review-summary">
          <div class="review-summary__score">{{ $ratingCount ? number_format($ratingAvg, 1, ',', ' ') : '—' }}<small>/5</small></div>
          @include('shop.partials.stars', ['rating' => $ratingAvg])
          <div class="text-muted mt-1" style="font-size:12.5px">
            {{ $ratingCount ? 'Sur ' . $ratingCount . ' avis client' . ($ratingCount > 1 ? 's' : '') : 'Aucun avis pour le moment' }}
          </div>

          <div class="review-bars mt-3">
            @for ($note = 5; $note >= 1; $note--)
              @php
                  $nb  = (int) ($breakdown[$note] ?? 0);
                  $pct = $ratingCount ? round($nb * 100 / $ratingCount) : 0;
              @endphp
              <div class="review-bar">
                <span class="review-bar__k">{{ $note }} <i class="bi bi-star-fill"></i></span>
                <span class="review-bar__track"><span class="review-bar__fill" style="width:{{ $pct }}%"></span></span>
                <span class="review-bar__n">{{ $nb }}</span>
              </div>
            @endfor
          </div>
        </div>
      </div>

      {{-- Formulaire + liste --}}
      <div class="col-lg-8 d-flex flex-column gap-3">

        @auth
          <form method="POST" action="{{ route('shop.products.reviews.store', $product->id) }}" class="review-form">
            @csrf
            <div class="fw-bolder" style="font-size:15px">
              {{ $myReview ? 'Modifier mon avis' : 'Donner mon avis' }}
            </div>
            <div class="text-muted mb-3" style="font-size:12.5px">
              @if ($myReview && ! $myReview->is_approved)
                <i class="bi bi-eye-slash"></i> Votre avis a été retiré du site par la modération.
              @elseif ($myReview)
                Publié le {{ $myReview->created_at?->translatedFormat('j F Y') }}
                @if ($myReview->is_verified)<span class="review-badge ms-1"><i class="bi bi-patch-check-fill"></i> Achat vérifié</span>@endif
              @else
                Votre avis aide les autres clients à se décider.
              @endif
            </div>

            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
              <span class="text-muted" style="font-size:13.5px">Votre note</span>
              {{-- row-reverse : les étoiles se remplissent de gauche à droite au survol. --}}
              <span class="rate-input">
                @for ($note = 5; $note >= 1; $note--)
                  <input type="radio" id="rate-{{ $note }}" name="rating" value="{{ $note }}"
                         @checked((int) old('rating', $myReview->rating ?? 0) === $note) required>
                  <label for="rate-{{ $note }}" title="{{ $note }} sur 5"><i class="bi bi-star-fill"></i></label>
                @endfor
              </span>
            </div>
            @error('rating')<div class="text-danger mb-2" style="font-size:12.5px">{{ $message }}</div>@enderror

            <input name="title" value="{{ old('title', $myReview->title ?? '') }}" maxlength="120"
                   class="form-control mb-2 @error('title') is-invalid @enderror" placeholder="Titre de votre avis (facultatif)">
            @error('title')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror

            <textarea name="comment" rows="4" maxlength="2000"
                      class="form-control @error('comment') is-invalid @enderror"
                      placeholder="Qu'avez-vous pensé de ce produit ? Qualité, livraison, rapport qualité-prix…">{{ old('comment', $myReview->comment ?? '') }}</textarea>
            @error('comment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

            <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
              <button type="submit" class="btn-brand btn-sm">{{ $myReview ? 'Mettre à jour mon avis' : 'Publier mon avis' }}</button>
              @if ($myReview)
                <button type="submit" form="reviewDelete" class="btn p-0 fw-semibold" style="font-size:13px;color:var(--danger)">
                  <i class="bi bi-trash me-1"></i>Supprimer mon avis
                </button>
              @endif
            </div>
          </form>

          @if ($myReview)
            <form method="POST" action="{{ route('shop.products.reviews.destroy', $myReview->id) }}" id="reviewDelete" class="d-none">
              @csrf @method('DELETE')
            </form>
          @endif
        @else
          <div class="review-cta">
            <i class="bi bi-chat-quote"></i>
            <span>Connectez-vous pour laisser un avis et un commentaire sur ce produit.</span>
            <a href="{{ route('shop.login') }}" class="btn-brand btn-sm">Se connecter</a>
          </div>
        @endauth

        @forelse ($reviews as $review)
          @include('shop.partials.review-item', ['review' => $review])
        @empty
          @if (! $myReview)
            <div class="empty-state">
              <i class="bi bi-chat-square-text"></i>
              Aucun avis sur ce produit pour l'instant. Soyez le premier à en laisser un.
            </div>
          @endif
        @endforelse

        @if ($reviews->hasPages())
          <nav aria-label="Pagination des avis">{{ $reviews->links('pagination::bootstrap-5') }}</nav>
        @endif
      </div>
    </div>
  </section>

  {{-- SUGGESTIONS --}}
  @if ($suggestions->isNotEmpty())
    <section class="mt-4">
      <div class="d-flex align-items-baseline justify-content-between mb-3">
        <span class="section-title">Vous aimerez aussi</span>
        <a href="{{ route('shop.products.index') }}" class="section-link">Tout voir</a>
      </div>
      <div class="row row-cols-2 row-cols-md-4 g-3">
        @foreach ($suggestions as $suggestion)
          <div class="col">@include('shop.partials.product-card', ['product' => $suggestion])</div>
        @endforeach
      </div>
    </section>
  @endif
</main>
@endsection

@push('scripts')
<script>
/* Sélecteur de variantes : une ligne par axe (couleur, taille…), en vignettes
   quand les valeurs ont un visuel, en pastilles sinon. Le champ caché
   variant_id suit la combinaison choisie. */
(function () {
  var picker = document.getElementById('variantPicker');
  if (!picker) return;

  var variants = JSON.parse(picker.dataset.variants || '[]');
  if (!variants.length) return;

  var axisEls  = Array.prototype.slice.call(picker.querySelectorAll('.variant-axis'));
  var axes     = axisEls.map(function (el) { return el.dataset.axis; });
  var input    = document.getElementById('variantId');
  var priceEl  = document.getElementById('productPrice');
  var stockEl  = document.getElementById('productStock');
  var addBtn   = document.getElementById('addToCart');
  var mainImg  = document.getElementById('mainImage');

  /* Première variante disponible, sinon la première tout court. */
  var start = variants.filter(function (v) { return v.inStock; })[0] || variants[0];
  var selection = {};
  axes.forEach(function (axis) { selection[axis] = start.attrs[axis]; });

  function find(combo) {
    return variants.filter(function (v) {
      return axes.every(function (axis) { return v.attrs[axis] === combo[axis]; });
    })[0] || null;
  }

  /* Combinaison si l'on changeait `axis` pour `value`, les autres axes inchangés. */
  function probe(axis, value) {
    var combo = {};
    axes.forEach(function (a) { combo[a] = a === axis ? value : selection[a]; });
    return combo;
  }

  function choose(axis, value) {
    var combo = probe(axis, value);

    /* Combinaison inexistante : on bascule sur une variante portant la valeur
       cliquée, en privilégiant celles qui sont en stock. */
    if (!find(combo)) {
      var candidates = variants.filter(function (v) { return v.attrs[axis] === value; });
      var fallback = candidates.filter(function (v) { return v.inStock; })[0] || candidates[0];
      if (!fallback) return;
      axes.forEach(function (a) { combo[a] = fallback.attrs[a]; });
    }

    selection = combo;
    render();
  }

  function render() {
    var current = find(selection);

    axisEls.forEach(function (axisEl) {
      var axis  = axisEl.dataset.axis;
      var label = axisEl.querySelector('[data-axis-value]');
      if (label) label.textContent = selection[axis] || '';

      axisEl.querySelectorAll('.variant-option').forEach(function (btn) {
        var value = btn.dataset.value;
        var match = find(probe(axis, value));

        btn.classList.toggle('is-selected', value === selection[axis]);
        /* Atténué quand la valeur ne se combine pas avec la sélection courante :
           le clic reste possible, il ajuste alors les autres axes. */
        btn.classList.toggle('is-dimmed', !match);
        btn.classList.toggle('is-soldout', !!match && !match.inStock);
      });
    });

    if (!current) return;

    if (input)   input.value = current.id;
    if (priceEl) priceEl.textContent = current.price;
    if (addBtn)  addBtn.disabled = !current.inStock;

    if (stockEl) {
      stockEl.textContent = current.inStock ? 'En stock' : 'Rupture de stock';
      stockEl.style.color = current.inStock ? 'var(--green)' : 'var(--danger)';
    }

    if (mainImg && current.image) {
      mainImg.src = current.image;
      document.querySelectorAll('.thumb').forEach(function (t) {
        t.classList.toggle('active', t.dataset.full === current.image);
      });
    }
  }

  picker.addEventListener('click', function (event) {
    var btn = event.target.closest('.variant-option');
    if (!btn) return;
    choose(btn.closest('.variant-axis').dataset.axis, btn.dataset.value);
  });

  render();
})();
</script>
@endpush
