@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $data['label'] ?: 'Résultats du scraping' }}</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.scraper.index') }}">Scraper</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ $filename }}</span>
            </div>
        </div>
        <a href="{{ route('admin.scraper.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    {{-- Méta --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-body" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;padding:1rem 1.5rem;">
            <div>
                <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;">Source</div>
                <a href="{{ $data['url'] }}" target="_blank" rel="noopener"
                   style="font-size:.85rem;color:var(--primary);word-break:break-all;">
                    {{ $data['url'] }}
                </a>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;">Produits trouvés</div>
                <div style="font-size:1.5rem;font-weight:700;color:var(--dark);">{{ $data['count'] }}</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;">Scrapé le</div>
                <div style="font-size:.9rem;font-weight:600;">
                    {{ \Carbon\Carbon::parse($data['scraped_at'])->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>
    </div>

    @if(empty($data['products']))
    <div class="card">
        <div class="card-body" style="text-align:center;padding:3rem;color:var(--gray);">
            <i class="fas fa-search" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
            <p>Aucun produit n'a pu être extrait de cette URL.</p>
            <p style="font-size:.85rem;">Vérifiez que le site intègre des balises schema.org ou Open Graph.</p>
        </div>
    </div>
    @else

    <form action="{{ route('admin.scraper.review', $filename) }}" method="POST">
        @csrf

        {{-- Fournisseur + actions --}}
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body" style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
                <div style="flex:1;min-width:220px;">
                    <label class="form-label">Fournisseur <span style="color:#dc2626;">*</span></label>
                    @if($suppliers->isEmpty())
                    <div style="font-size:.85rem;color:var(--gray);padding:.5rem 0;">
                        Aucun fournisseur.
                        <a href="{{ route('admin.suppliers.create') }}" target="_blank">En créer un</a>
                        puis recharger la page.
                    </div>
                    @else
                    <select name="supplier_id" class="form-input" required>
                        <option value="">— Choisir un fournisseur —</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;margin-top:1.4rem;">
                    <label style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;cursor:pointer;user-select:none;">
                        <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                        Tout sélectionner
                    </label>
                    <span id="selectedCount" style="font-size:.85rem;color:var(--gray);">0 sélectionné(s)</span>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:1.4rem;">
                    <i class="fas fa-arrow-right"></i> Réviser &amp; importer
                </button>
            </div>
        </div>

        {{-- Grille des produits --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;">
            @foreach($data['products'] as $product)
            <label style="cursor:pointer;" class="product-card-label">
                <input type="checkbox" name="product_ids[]" value="{{ $product['id'] }}"
                       class="product-checkbox" style="display:none;" onchange="updateCount()">

                <div class="product-card" style="background:#fff;border-radius:10px;border:2px solid var(--border);overflow:hidden;transition:border-color .15s,box-shadow .15s;">

                    {{-- Image --}}
                    <div style="height:180px;background:var(--light);overflow:hidden;position:relative;">
                        @if(!empty($product['image_url']))
                        <img src="{{ route('admin.scraper.image-proxy', ['url' => $product['image_url']]) }}"
                             alt="{{ $product['name'] }}"
                             style="width:100%;height:100%;object-fit:contain;"
                             onerror="this.parentElement.innerHTML='<div style=\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--gray);opacity:.4\'><i class=\'fas fa-image fa-2x\'></i></div>'"
                             loading="lazy">
                        @else
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--gray);opacity:.4;">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                        @endif

                        {{-- Checkbox visuel --}}
                        <div class="check-overlay" style="position:absolute;top:8px;right:8px;width:22px;height:22px;border-radius:50%;border:2px solid #fff;background:rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-check" style="font-size:10px;color:#fff;display:none;"></i>
                        </div>
                    </div>

                    {{-- Infos --}}
                    <div style="padding:.9rem;">
                        <div style="font-size:.9rem;font-weight:600;line-height:1.3;margin-bottom:.5rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $product['name'] }}
                        </div>

                        @if(!empty($product['brand']))
                        <div style="font-size:.75rem;color:var(--gray);margin-bottom:.25rem;text-transform:uppercase;letter-spacing:.04em;">{{ $product['brand'] }}</div>
                        @endif

                        @if(!empty($product['price']) && $product['price'] > 0)
                        <div style="display:flex;align-items:baseline;gap:.4rem;flex-wrap:wrap;">
                            <span style="font-size:1rem;font-weight:700;color:var(--primary);">
                                {{ number_format($product['price'], 0, ',', ' ') }}
                                @if(!empty($product['currency'])) {{ $product['currency'] }} @endif
                            </span>
                            @if(!empty($product['original_price']) && $product['original_price'] > $product['price'])
                            <span style="font-size:.8rem;color:var(--gray);text-decoration:line-through;">
                                {{ number_format($product['original_price'], 0, ',', ' ') }}
                            </span>
                            @endif
                        </div>
                        @else
                        <div style="font-size:.8rem;color:var(--gray);">Prix non disponible</div>
                        @endif

                        @if(!empty($product['rating']))
                        <div style="font-size:.75rem;color:#f59e0b;margin-top:.2rem;">
                            ★ {{ $product['rating'] }}
                            @if(!empty($product['reviews_count']))
                            <span style="color:var(--gray);">({{ $product['reviews_count'] }})</span>
                            @endif
                        </div>
                        @endif

                        @if(!empty($product['description']))
                        <div style="font-size:.78rem;color:var(--gray);margin-top:.4rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $product['description'] }}
                        </div>
                        @endif

                        <a href="{{ $product['source_url'] ?? '#' }}" target="_blank" rel="noopener"
                           style="font-size:.75rem;color:var(--gray);text-decoration:none;display:flex;align-items:center;gap:.3rem;margin-top:.5rem;"
                           onclick="event.stopPropagation()">
                            <i class="fas fa-external-link-alt"></i> Voir la source
                        </a>
                    </div>
                </div>
            </label>
            @endforeach
        </div>

        {{-- Bouton bas de page --}}
        <div style="margin-top:1.5rem;display:flex;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i> Réviser &amp; importer
            </button>
        </div>
    </form>
    @endif
</div>

<style>
.product-card-label input:checked ~ .product-card {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(0,102,255,.12);
}
.product-card-label input:checked ~ .product-card .check-overlay {
    background: var(--primary) !important;
}
.product-card-label input:checked ~ .product-card .check-overlay i {
    display: block !important;
}
.product-card:hover {
    border-color: var(--primary);
}
</style>

<script>
function updateCount() {
    const checked = document.querySelectorAll('.product-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' sélectionné(s)';

    const all = document.querySelectorAll('.product-checkbox').length;
    document.getElementById('selectAll').indeterminate = checked > 0 && checked < all;
    document.getElementById('selectAll').checked = checked === all;
}

function toggleAll(cb) {
    document.querySelectorAll('.product-checkbox').forEach(c => {
        c.checked = cb.checked;
    });
    updateCount();
}
</script>
@endsection
