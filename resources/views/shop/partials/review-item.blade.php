{{-- Un avis client dans la liste de la fiche produit. --}}
<article class="review-item">
  <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
    <span class="review-item__author">{{ $review->authorName() }}</span>
    @if ($review->is_verified)
      <span class="review-badge"><i class="bi bi-patch-check-fill"></i> Achat vérifié</span>
    @endif
    <span class="text-muted ms-auto" style="font-size:12px">{{ $review->created_at?->translatedFormat('j F Y') }}</span>
  </div>

  @include('shop.partials.stars', ['rating' => $review->rating])

  @if ($review->title)
    <div class="review-item__title">{{ $review->title }}</div>
  @endif

  <p class="review-item__body">{{ $review->comment }}</p>
</article>
