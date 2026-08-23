@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier le produit</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.index') }}">Produits</a>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                <i class="fas fa-chevron-right"></i>
                <span>Modifier</span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informations du produit</h3>
            </div>
            <div class="card-body">

                {{-- Catégorie : physique / service --}}
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <div style="display:flex;gap:1rem;">
                        <label style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:var(--light);border-radius:8px;cursor:pointer;border:2px solid var(--border);" onclick="toggleCatalogType(false)">
                            <div>
                                <div style="font-weight:600;"><i class="fas fa-box"></i> Produit physique</div>
                                <div style="font-size:.8rem;color:var(--gray);">Article avec stock</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:var(--light);border-radius:8px;cursor:pointer;border:2px solid var(--border);" onclick="toggleCatalogType(true)">
                            <div>
                                <div style="font-weight:600;"><i class="fas fa-concierge-bell"></i> Service</div>
                                <div style="font-size:.8rem;color:var(--gray);">Prestation / soin / cours</div>
                            </div>
                        </label>
                    </div>
                    <input type="hidden" name="is_service" id="isServiceHidden" value="{{ old('is_service', $product->is_service ? 1 : 0) }}">
                </div>

                <div id="providerField" style="display:{{ old('is_service', $product->is_service) ? '' : 'none' }};">
                    <div class="form-group">
                        <label class="form-label">Fournisseur / Prestataire</label>
                        <input type="text" name="provider" class="form-input" placeholder="Ex : Beauty Palace…" value="{{ old('provider', $product->provider) }}">
                    </div>
                </div>

                {{-- Type --}}
                <div class="form-group">
                    <label class="form-label">Type de produit</label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor: pointer; border: 2px solid var(--border);" onclick="toggleProductType('simple')">
                            <input type="radio" name="type" value="simple" {{ old('type', $product->type) === 'simple' ? 'checked' : '' }} required>
                            <div>
                                <div style="font-weight: 600;"><i class="fas fa-box"></i> Simple</div>
                                <div style="font-size: 0.8rem; color: var(--gray);">Produit sans variantes</div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor: pointer; border: 2px solid var(--border);" onclick="toggleProductType('variable')">
                            <input type="radio" name="type" value="variable" {{ old('type', $product->type) === 'variable' ? 'checked' : '' }} required>
                            <div>
                                <div style="font-weight: 600;"><i class="fas fa-boxes"></i> Variable</div>
                                <div style="font-size: 0.8rem; color: var(--gray);">Avec variantes (couleur, taille...)</div>
                            </div>
                        </label>
                    </div>
                    @error('type')
                    <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Nom & SKU --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Nom du produit</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input" value="{{ old('sku', $product->sku) }}" required>
                        @error('sku')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="4">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Champs produit simple : prix & stock --}}
                <div id="simple-fields" style="display: {{ old('type', $product->type) === 'simple' ? 'block' : 'none' }};">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">
                                Prix fournisseur (GNF)
                                <span style="font-weight:400;color:var(--gray);font-size:.8rem;">(optionnel)</span>
                            </label>
                            <input type="number" name="supplier_price" id="supplierPrice" class="form-input"
                                   value="{{ old('supplier_price', $product->supplier_price) }}"
                                   step="0.01" min="0" oninput="previewMarginEdit()">
                            @error('supplier_price')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Prix client (GNF)</label>
                            <input type="number" name="price" id="salePrice" class="form-input"
                                   value="{{ old('price', $product->price) }}" step="0.01" min="0">
                            <div id="marginHint" style="display:none;font-size:.78rem;color:var(--primary);margin-top:.3rem;">
                                <i class="fas fa-magic"></i> <span id="marginHintText"></span>
                            </div>
                            @error('price')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="stockField" class="form-group" style="display:{{ old('is_service', $product->is_service) ? 'none' : '' }};">
                            <label class="form-label">Quantité en stock</label>
                            <input type="number" name="stock_quantity" class="form-input" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                            @error('stock_quantity')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                @php
                    $applicableMargin = app(\App\Services\Admin\MarginService::class)->findMarginForProduct($product->id);
                @endphp
                <script>
                const MARGIN_TYPE  = '{{ $applicableMargin?->type ?? '' }}';
                const MARGIN_VALUE = {{ $applicableMargin?->value ?? 0 }};

                function previewMarginEdit() {
                    const sp    = parseFloat(document.getElementById('supplierPrice').value);
                    const hint  = document.getElementById('marginHint');
                    const htext = document.getElementById('marginHintText');

                    if (!sp || sp <= 0 || !MARGIN_VALUE) { hint.style.display = 'none'; return; }

                    let sale;
                    if (MARGIN_TYPE === 'percentage') {
                        sale = Math.round(sp * (1 + MARGIN_VALUE / 100));
                    } else {
                        sale = Math.round(sp + MARGIN_VALUE);
                    }

                    document.getElementById('salePrice').value = sale;
                    const gain = sale - sp;
                    htext.textContent = 'Marge appliquée → ' + new Intl.NumberFormat('fr-FR').format(gain) + ' GNF';
                    hint.style.display = 'block';
                }
                </script>

                {{-- Crédit --}}
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Options de Crédit</h4>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="credit_enabled" value="1"
                                {{ old('credit_enabled', $product->credit_enabled) ? 'checked' : '' }}
                                onchange="toggleCreditFields(this)">
                            <span>Activer le paiement à crédit</span>
                        </label>
                    </div>

                    <div id="credit-fields" style="display: {{ old('credit_enabled', $product->credit_enabled) ? 'block' : 'none' }}; margin-top: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Durée maximale (mois)</label>
                                <input type="number" name="credit_duration_months" class="form-input"
                                    value="{{ old('credit_duration_months', $product->credit_duration_months) }}" min="1" max="24">
                                @error('credit_duration_months')
                                <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nombre de mensualités</label>
                                <input type="number" name="credit_installments_count" class="form-input"
                                    value="{{ old('credit_installments_count', $product->credit_installments_count) }}" min="1" max="12">
                                @error('credit_installments_count')
                                <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Images --}}
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Images du produit</h4>

                    @php $images = $product->images; @endphp
                    @if($images->isNotEmpty())
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
                        @foreach($images as $img)
                        <div style="position: relative; width: 110px;">
                            {{-- Le badge est posé sur l'image : il ne recouvre pas les
                                 contrôles placés dessous. --}}
                            <div style="position:relative;width:110px;height:110px;border-radius:6px;overflow:hidden;border:2px solid {{ $img->is_primary ? 'var(--primary)' : 'var(--border)' }};">
                                <img src="{{ $img->url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                @if($img->is_primary)
                                <span style="position:absolute;bottom:0;left:0;right:0;text-align:center;background:var(--primary);color:#fff;font-size:10px;padding:1px 0;">Principale</span>
                                @endif
                            </div>
                            @if($product->isVariable() && $product->variants->isNotEmpty())
                            {{-- Rattachement à une variante : le visuel sert de vignette
                                 dans le sélecteur couleur/taille de la fiche publique. --}}
                            <form action="{{ route('admin.products.images.variant', [$product, $img]) }}" method="POST" style="margin-top:4px;">
                                @csrf
                                <select name="variant_id" onchange="this.form.submit()" title="Variante illustrée"
                                        style="width:110px;padding:2px;font-size:11px;border:1px solid var(--border);border-radius:4px;background:#fff;cursor:pointer;">
                                    <option value="">— Produit entier —</option>
                                    @foreach($product->variants as $variant)
                                    <option value="{{ $variant->id }}" {{ $img->variant_id === $variant->id ? 'selected' : '' }}>
                                        {{ $variant->name ?: ('Variante #' . $variant->id) }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                            @endif

                            <div style="display:flex;gap:3px;margin-top:4px;">
                                @if(!$img->is_primary)
                                <form action="{{ route('admin.products.images.primary', [$product, $img]) }}" method="POST" style="flex:1;">
                                    @csrf
                                    <button type="submit" title="Définir principale"
                                            style="width:100%;padding:2px 0;font-size:11px;border:1px solid var(--border);border-radius:4px;background:#fff;cursor:pointer;">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.products.images.destroy', [$product, $img]) }}" method="POST" style="flex:1;" onsubmit="return confirm('Supprimer cette image ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Supprimer"
                                            style="width:100%;padding:2px 0;font-size:11px;border:1px solid var(--border);border-radius:4px;background:#fff;color:var(--danger);cursor:pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div id="imageDropzone" onclick="document.getElementById('imageInput').click()"
                         style="border:2px dashed var(--border);border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;background:var(--bg);">
                        <i class="fas fa-plus-circle" style="font-size:1.5rem;color:var(--gray);opacity:.5;display:block;margin-bottom:.4rem;"></i>
                        <div style="font-size:.85rem;color:var(--gray);">Ajouter des images</div>
                    </div>
                    <input type="file" id="imageInput" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
                    <div id="imagePreviews" style="display:flex;flex-wrap:wrap;gap:.75rem;margin-top:.75rem;"></div>
                </div>

                {{-- Statut --}}
                <div style="display: flex; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                            <span>Produit actif</span>
                        </label>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $product->is_published) ? 'checked' : '' }}>
                            <span>Publier le produit</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function previewImages(input) {
        const container = document.getElementById('imagePreviews');
        container.innerHTML = '';
        Array.from(input.files).forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = e => {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'width:100px;height:100px;border-radius:6px;overflow:hidden;border:2px solid var(--border);';
                wrap.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
                container.appendChild(wrap);
            };
            reader.readAsDataURL(file);
        });
    }
    const dz = document.getElementById('imageDropzone');
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--primary)'; });
    dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--border)'; });
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.style.borderColor = 'var(--border)';
        const input = document.getElementById('imageInput');
        const dt = new DataTransfer();
        Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
        input.files = dt.files; previewImages(input);
    });

    let _isService = {{ old('is_service', $product->is_service) ? 'true' : 'false' }};

    function toggleCatalogType(isService) {
        _isService = isService;
        document.getElementById('isServiceHidden').value = isService ? 1 : 0;
        document.getElementById('providerField').style.display = isService ? '' : 'none';
        const sf = document.getElementById('stockField');
        if (sf) sf.style.display = isService ? 'none' : '';
        const labels = document.querySelectorAll('[onclick^="toggleCatalogType"]');
        labels[0].style.borderColor = !isService ? 'var(--primary)' : 'var(--border)';
        labels[1].style.borderColor =  isService ? 'var(--primary)' : 'var(--border)';
    }
    toggleCatalogType(_isService);

    function toggleProductType(type) {
        document.getElementById('simple-fields').style.display = type === 'simple' ? 'block' : 'none';
    }

    function toggleCreditFields(checkbox) {
        document.getElementById('credit-fields').style.display = checkbox.checked ? 'block' : 'none';
    }
</script>
@endsection
