@if ($paginator->hasPages())
<div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">

    <div style="font-size: 0.85rem; color: var(--gray);">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }} résultats
    </div>

    <div style="display: flex; align-items: center; gap: 0.5rem;">

        {{-- Précédent --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm" style="opacity: 0.4; cursor: not-allowed;">
                Précédent
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline btn-sm">
                Précédent
            </a>
        @endif

        {{-- Numéros --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="color: var(--gray); padding: 0 0.25rem;">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-primary btn-sm" style="min-width: 2rem; cursor: default;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-outline btn-sm" style="min-width: 2rem;">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Suivant --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline btn-sm">
                Suivant
            </a>
        @else
            <span class="btn btn-outline btn-sm" style="opacity: 0.4; cursor: not-allowed;">
                Suivant
            </span>
        @endif

    </div>
</div>
@endif
