@extends('tpl.portal')

@section('content')
<style>
/* ── Image slider ─────────────────────────────────────────── */
.slider-wrap {
    position: relative;
    background: var(--bg);
    border-radius: 10px;
    overflow: hidden;
    aspect-ratio: 4/3;
}
.slider-main {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: opacity .2s;
}
.slider-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: #d1d5db;
    font-size: 3rem;
}
.slider-btn {
    position: absolute; top: 50%; transform: translateY(-50%);
    width: 34px; height: 34px;
    background: rgba(255,255,255,.9);
    border: 1px solid var(--border);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 13px; color: var(--text);
    transition: background .15s;
    box-shadow: 0 1px 4px rgba(0,0,0,.1);
}
.slider-btn:hover { background: #fff; }
.slider-btn.prev { left: 10px; }
.slider-btn.next { right: 10px; }
.slider-thumbs {
    display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap;
}
.slider-thumb {
    width: 62px; height: 62px;
    border-radius: 6px; overflow: hidden;
    border: 2px solid var(--border);
    cursor: pointer; transition: border-color .15s;
    flex-shrink: 0;
}
.slider-thumb.active { border-color: var(--primary); }
.slider-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.slider-counter {
    position: absolute; bottom: 8px; right: 10px;
    background: rgba(0,0,0,.5); color: #fff;
    font-size: 11px; padding: 2px 7px; border-radius: 10px;
}
</style>

<div class="page-content">
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <div style="display:flex;align-items:center;gap:.85rem;">
            <a href="{{ route('portal.products.index') }}" style="color:var(--text-muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title">{{ $product->name }}</h1>
        </div>
        <a href="{{ route('portal.orders.create', ['product' => $product->id]) }}" class="btn btn-primary">
            <i class="{{ $product->is_service ? 'fas fa-calendar-check' : 'fas fa-cart-plus' }}"></i>
            {{ $product->is_service ? 'Réserver ce service' : 'Commander ce produit' }}
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">

        {{-- ── Slider gauche ─────────────────────────── --}}
        <div>
            @php $images = $product->images; $hasImages = $images->isNotEmpty(); @endphp
            <div class="slider-wrap" id="sliderWrap">
                @if($hasImages)
                    <img id="sliderMain" class="slider-main" src="{{ $images->first()->url }}" alt="{{ $product->name }}">
                    @if($images->count() > 1)
                    <button class="slider-btn prev" onclick="slide(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button class="slider-btn next" onclick="slide(1)"><i class="fas fa-chevron-right"></i></button>
                    <span class="slider-counter" id="sliderCounter">1 / {{ $images->count() }}</span>
                    @endif
                @else
                    <div class="slider-placeholder"><i class="fas fa-box"></i></div>
                @endif
            </div>

            @if($hasImages && $images->count() > 1)
            <div class="slider-thumbs" id="thumbsRow">
                @foreach($images as $i => $img)
                <div class="slider-thumb {{ $i === 0 ? 'active' : '' }}" onclick="goSlide({{ $i }})" id="thumb-{{ $i }}">
                    <img src="{{ $img->url }}" alt="">
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── Infos droite ─────────────────────────── --}}
        <div>
            <div class="card" style="margin-bottom: 1.25rem;">
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem;flex-wrap:wrap;">
                        <h2 style="font-size: 1.25rem; font-weight: 700; margin:0;">{{ $product->name }}</h2>
                        @if($product->is_service)
                        <span style="background:#d1fae5;color:#059669;font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;"><i class="fas fa-concierge-bell"></i> Service</span>
                        @endif
                    </div>
                    @if($product->provider)
                    <div style="font-size:13px;color:var(--text-muted);margin-bottom:.75rem;">
                        <i class="fas fa-store" style="margin-right:4px;"></i>{{ $product->provider }}
                    </div>
                    @endif
                    @if($product->description)
                    <p style="color: var(--text-muted); line-height: 1.65; margin-bottom: 1.25rem; font-size: 13.5px;">{{ $product->description }}</p>
                    @endif

                    @if($product->isSimple())
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:.85rem 0;border-top:1px solid var(--border);">
                        <span style="font-size:13px;color:var(--text-muted);">Prix</span>
                        <span style="font-size:1.35rem;font-weight:700;color:var(--primary);">
                            {{ number_format($product->price, 0, ',', ' ') }} GNF
                        </span>
                    </div>
                    @if($product->credit_enabled && $product->credit_installments_count)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:.7rem 0;border-top:1px solid var(--border);">
                        <span style="font-size:13px;color:var(--text-muted);">Option crédit</span>
                        <span style="font-size:13px;font-weight:600;color:#7c3aed;">
                            {{ $product->credit_installments_count }}× {{ $product->display_monthly_payment }}/mois
                        </span>
                    </div>
                    @endif
                    @if(!$product->is_service)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:.7rem 0;border-top:1px solid var(--border);">
                        <span style="font-size:13px;color:var(--text-muted);">Stock</span>
                        @if($product->stock_quantity > 0)
                        <span style="font-size:13px;font-weight:600;color:#15803d;">{{ $product->stock_quantity }} unité(s) disponible(s)</span>
                        @else
                        <span style="font-size:13px;font-weight:600;color:#b91c1c;">Rupture de stock</span>
                        @endif
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            @if($product->variants->isNotEmpty())
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tags"></i> Variantes disponibles</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Variante</th>
                                <th>Prix</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($product->variants as $variant)
                        <tr>
                            <td><strong>{{ $variant->name }}</strong></td>
                            <td style="font-weight:600;color:var(--primary);">{{ number_format($variant->price, 0, ',', ' ') }} GNF</td>
                            <td>
                                @if(($variant->stock_quantity ?? 0) > 0)
                                <span style="color:#15803d;font-weight:500;">{{ $variant->stock_quantity }}</span>
                                @else
                                <span style="color:#b91c1c;">Épuisé</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@if($hasImages && $images->count() > 1)
<script>
const images = @json($images->pluck('url'));
let current = 0;

function goSlide(idx) {
    current = (idx + images.length) % images.length;
    document.getElementById('sliderMain').src = images[current];
    document.getElementById('sliderCounter').textContent = (current + 1) + ' / ' + images.length;
    document.querySelectorAll('.slider-thumb').forEach((t, i) => t.classList.toggle('active', i === current));
}

function slide(dir) { goSlide(current + dir); }

// Keyboard
document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') slide(-1);
    if (e.key === 'ArrowRight') slide(1);
});
</script>
@endif
@endsection
