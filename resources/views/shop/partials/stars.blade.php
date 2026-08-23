{{-- Étoiles de notation. $rating : note sur 5. $count (optionnel) : nombre d'avis. --}}
@php
    // Arrondi au demi-point : c'est la granularité des icônes disponibles.
    $value = round(((float) ($rating ?? 0)) * 2) / 2;
@endphp
<span class="stars" title="{{ $value > 0 ? $value . ' sur 5' : 'Aucun avis' }}">
  @for ($i = 1; $i <= 5; $i++)
    @if ($value >= $i)
      <i class="bi bi-star-fill"></i>
    @elseif ($value >= $i - 0.5)
      <i class="bi bi-star-half"></i>
    @else
      <i class="bi bi-star stars__off"></i>
    @endif
  @endfor
  @isset($count)<span class="count">({{ $count }})</span>@endisset
</span>
