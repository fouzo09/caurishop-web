@extends('tpl.portal')

@section('content')
<style>
/* ── Product Catalog ───────────────────────────────────────────── */
.catalog-hero {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.5rem 1.75rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.catalog-hero-search {
    flex: 1;
    position: relative;
}
.catalog-hero-search i {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 13px;
    pointer-events: none;
}
.catalog-hero-search input {
    width: 100%;
    height: 38px;
    padding: 0 1rem 0 2.4rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 14px;
    color: var(--text);
    background: var(--bg);
    outline: none;
    transition: border-color .15s;
    box-sizing: border-box;
}
.catalog-hero-search input:focus { border-color: var(--primary); background: #fff; }

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.25rem;
}

.product-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow .15s, transform .15s;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
}
.product-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    transform: translateY(-2px);
}
.product-card-img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.product-card-img img { width: 100%; height: 160px; object-fit: cover; display: block; }
.product-card-img-placeholder {
    width: 100%;
    height: 160px;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d1d5db;
}
.product-card-body {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.product-card-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.3rem;
    line-height: 1.35;
}
.product-card-desc {
    font-size: 12.5px;
    color: var(--text-muted);
    line-height: 1.45;
    flex: 1;
    margin-bottom: 0.75rem;
}
.product-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
    gap: 0.5rem;
}
.product-card-price {
    font-size: 15px;
    font-weight: 700;
    color: var(--primary);
    white-space: nowrap;
}
.product-card-price small {
    display: block;
    font-size: 11px;
    font-weight: 400;
    color: var(--text-muted);
}
.stock-dot {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 500;
}
.stock-dot::before {
    content: '';
    width: 6px; height: 6px;
    border-radius: 50%;
    display: inline-block;
}
.stock-dot.ok { color: #15803d; }
.stock-dot.ok::before { background: #22c55e; }
.stock-dot.low { color: #b45309; }
.stock-dot.low::before { background: #f59e0b; }
.stock-dot.out { color: #b91c1c; }
.stock-dot.out::before { background: #ef4444; }

.btn-order-now {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    background: var(--primary);
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s;
}
.btn-order-now:hover { background: var(--primary-hover); color: #fff; }

.catalog-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
}
.catalog-empty i { font-size: 2.5rem; color: #d1d5db; display: block; margin-bottom: 1rem; }
.catalog-empty p { font-size: 14px; margin-bottom: 1.5rem; }
</style>

<div class="page-content">

    {{-- Hero + search ──────────────────────────────────────── --}}
    <div class="catalog-hero">
        <form method="GET" action="{{ route('portal.products.index') }}" class="catalog-hero-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher un produit…" autocomplete="off">
        </form>
        @if(request('search'))
        <a href="{{ route('portal.products.index') }}" class="btn btn-outline" style="flex-shrink:0;">
            <i class="fas fa-times"></i> Effacer
        </a>
        @endif
        <a href="{{ route('portal.orders.create') }}" class="btn btn-primary" style="flex-shrink:0;">
            <i class="fas fa-shopping-cart"></i> Commander
        </a>
    </div>

    {{-- Grid ───────────────────────────────────────────────── --}}
    @if($products->isEmpty())
    <div class="catalog-empty">
        <i class="fas fa-box-open"></i>
        <p>Aucun produit disponible pour le moment.</p>
        @if(request('search'))
        <a href="{{ route('portal.products.index') }}" class="btn btn-outline">Voir tous les produits</a>
        @endif
    </div>
    @else
    <div class="product-grid">
        @foreach($products as $product)
        @php
            $status = $product->stock_status; // disponible / faible / rupture
            $stockClass = match($status) { 'faible' => 'low', 'rupture' => 'out', default => 'ok' };
            $stockLabel = match($status) { 'faible' => 'Stock faible', 'rupture' => 'Rupture', default => 'Disponible' };
        @endphp
        <div class="product-card" onclick="window.location='{{ route('portal.products.show', $product->id) }}'">
            @php $cover = $product->coverUrl(); @endphp
            @if($cover)
                <div class="product-card-img"><img src="{{ $cover }}" alt="{{ $product->name }}"></div>
            @else
                <div class="product-card-img-placeholder">
                    <i class="fas fa-box" style="font-size: 2rem;"></i>
                </div>
            @endif
            <div class="product-card-body">
                <div class="product-card-name">{{ $product->name }}</div>
                @if($product->description)
                <div class="product-card-desc">{{ Str::limit($product->description, 70) }}</div>
                @endif
                <div class="product-card-footer">
                    <div class="product-card-price">
                        {{ $product->display_price }}
                        @if($product->credit_enabled && $product->credit_installments_count)
                        <small>ou {{ $product->display_monthly_payment ?? '' }}/mois</small>
                        @endif
                    </div>
                    <span class="stock-dot {{ $stockClass }}">{{ $stockLabel }}</span>
                </div>
                <div style="margin-top: 0.85rem;">
                    <a href="{{ route('portal.orders.create', ['product' => $product->id]) }}"
                       class="btn-order-now" onclick="event.stopPropagation()">
                        <i class="fas fa-cart-plus"></i> Commander
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($products->hasPages())
    <div style="margin-top: 1.5rem;">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
    @endif

</div>
@endsection
