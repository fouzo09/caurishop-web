@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Créer un Produit</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.index') }}">Produits</a>
                <i class="fas fa-chevron-right"></i>
                <span>Nouveau</span>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Veuillez corriger les erreurs dans le formulaire</span>
    </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informations du Produit</h3>
            </div>
            <div class="card-body">

                {{-- Catalogue : Produit physique ou Service --}}
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:var(--light);border-radius:8px;cursor:pointer;border:2px solid var(--border);" onclick="toggleCatalogType(false)">
                            <input type="checkbox" name="is_service" value="0" id="isServiceNo" {{ !old('is_service') ? 'checked' : '' }} style="display:none;">
                            <div>
                                <div style="font-weight:600;"><i class="fas fa-box"></i> Produit physique</div>
                                <div style="font-size:.8rem;color:var(--gray);">Article avec stock</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;background:var(--light);border-radius:8px;cursor:pointer;border:2px solid var(--border);" onclick="toggleCatalogType(true)">
                            <input type="checkbox" name="is_service" value="1" id="isServiceYes" {{ old('is_service') ? 'checked' : '' }} style="display:none;">
                            <div>
                                <div style="font-weight:600;"><i class="fas fa-concierge-bell"></i> Service</div>
                                <div style="font-size:.8rem;color:var(--gray);">Prestation / soin / cours</div>
                            </div>
                        </label>
                    </div>
                    <input type="hidden" name="is_service" id="isServiceHidden" value="{{ old('is_service', 0) }}">
                </div>

                {{-- Fournisseur (services) --}}
                <div id="providerField" style="display:{{ old('is_service') ? '' : 'none' }};">
                    <div class="form-group">
                        <label class="form-label">Fournisseur / Prestataire</label>
                        <input type="text" name="provider" class="form-input" placeholder="Ex : Beauty Palace, FitZone Conakry…" value="{{ old('provider') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Type de produit</label>
                    <div style="display: flex; gap: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor: pointer; border: 2px solid var(--border);" onclick="toggleProductType('simple')">
                            <input type="radio" name="type" value="simple" {{ old('type', 'simple') === 'simple' ? 'checked' : '' }} required>
                            <div>
                                <div style="font-weight: 600;"><i class="fas fa-box"></i> Simple</div>
                                <div style="font-size: 0.8rem; color: var(--gray);">Produit sans variantes</div>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor: pointer; border: 2px solid var(--border);" onclick="toggleProductType('variable')">
                            <input type="radio" name="type" value="variable" {{ old('type') === 'variable' ? 'checked' : '' }} required>
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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Nom du produit</label>
                        <input type="text" name="name" class="form-input" placeholder="iPhone 15 Pro Max" value="{{ old('name') }}" required>
                        @error('name')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            SKU
                            <span id="sku-optional-label" style="display: none; font-weight: 400; color: var(--gray); font-size: 0.8rem;">(optionnel pour un produit variable)</span>
                        </label>
                        <input type="text" name="sku" class="form-input" placeholder="IP15PM-001" value="{{ old('sku') }}">
                        @error('sku')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-input" rows="4" placeholder="Description détaillée du produit">{{ old('description') }}</textarea>
                        @error('description')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div id="simple-fields" style="display: {{ old('type', 'simple') === 'simple' ? 'block' : 'none' }};">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">
                                Prix fournisseur (GNF)
                                <span style="font-weight:400;color:var(--gray);font-size:.8rem;">(optionnel)</span>
                            </label>
                            <input type="number" name="supplier_price" id="supplierPrice" class="form-input"
                                   placeholder="Ex: 3500000" value="{{ old('supplier_price') }}"
                                   step="0.01" min="0" oninput="previewMarginCreate()">
                            @error('supplier_price')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Prix client (GNF)</label>
                            <input type="number" name="price" id="salePrice" class="form-input"
                                   placeholder="4200000" value="{{ old('price') }}" step="0.01" min="0">
                            <div id="marginHint" style="display:none;font-size:.78rem;color:var(--primary);margin-top:.3rem;">
                                <i class="fas fa-magic"></i> <span id="marginHintText"></span>
                            </div>
                            @error('price')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="stockField" class="form-group">
                            <label class="form-label">Quantité en stock</label>
                            <input type="number" name="stock_quantity" class="form-input" placeholder="50" value="{{ old('stock_quantity', 0) }}" min="0">
                            @error('stock_quantity')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                @php
                    $globalMargin = \App\Models\PlatformMargin::where('scope','global')->where('is_active',true)->first();
                @endphp
                <script>
                const MARGIN_TYPE  = '{{ $globalMargin?->type ?? '' }}';
                const MARGIN_VALUE = {{ $globalMargin?->value ?? 0 }};

                function previewMarginCreate() {
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

                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Options de Crédit</h4>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="credit_enabled" value="1" {{ old('credit_enabled') ? 'checked' : '' }} onchange="toggleCreditFields(this)">
                            <span>Activer le paiement à crédit</span>
                        </label>
                    </div>

                    <div id="credit-fields" style="display: {{ old('credit_enabled') ? 'block' : 'none' }}; margin-top: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Durée maximale (mois)</label>
                                <input type="number" name="credit_duration_months" class="form-input" placeholder="12" value="{{ old('credit_duration_months') }}" min="1" max="24">
                                @error('credit_duration_months')
                                <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nombre de mensualités</label>
                                <input type="number" name="credit_installments_count" class="form-input" placeholder="12" value="{{ old('credit_installments_count') }}" min="1" max="12">
                                @error('credit_installments_count')
                                <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 1.5rem; margin-top: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <span>Produit actif</span>
                        </label>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                            <span>Publier le produit</span>
                        </label>
                    </div>
                </div>

                {{-- Images --}}
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">Images du produit</h4>
                    <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 1rem;">Vous pouvez ajouter plusieurs images. La première sera l'image principale.</p>
                    <div id="imageDropzone" onclick="document.getElementById('imageInput').click()"
                         style="border: 2px dashed var(--border); border-radius: 8px; padding: 2rem; text-align: center; cursor: pointer; transition: border-color .15s; background: var(--bg);">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--gray); opacity: 0.5; display: block; margin-bottom: 0.5rem;"></i>
                        <div style="font-size: 0.9rem; color: var(--gray);">Cliquez pour sélectionner des images</div>
                        <div style="font-size: 0.8rem; color: var(--gray); margin-top: 0.25rem;">JPG, PNG, WEBP — max 4 Mo chacune</div>
                    </div>
                    <input type="file" id="imageInput" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
                    <div id="imagePreviews" style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem;"></div>
                </div>
            </div>
            <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Créer
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
                wrap.style.cssText = 'position:relative;width:100px;height:100px;border-radius:6px;overflow:hidden;border:2px solid ' + (i === 0 ? 'var(--primary)' : 'var(--border)');
                wrap.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">` +
                    (i === 0 ? '<span style="position:absolute;bottom:3px;left:3px;background:var(--primary);color:#fff;font-size:10px;padding:1px 5px;border-radius:3px;">Principale</span>' : '');
                container.appendChild(wrap);
            };
            reader.readAsDataURL(file);
        });
    }

    // Drag & drop
    const dz = document.getElementById('imageDropzone');
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--primary)'; });
    dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--border)'; });
    dz.addEventListener('drop', e => {
        e.preventDefault();
        dz.style.borderColor = 'var(--border)';
        const input = document.getElementById('imageInput');
        const dt = new DataTransfer();
        Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
        input.files = dt.files;
        previewImages(input);
    });

    let _isService = {{ old('is_service') ? 'true' : 'false' }};

    function toggleCatalogType(isService) {
        _isService = isService;
        document.getElementById('isServiceHidden').value = isService ? 1 : 0;
        document.getElementById('providerField').style.display = isService ? '' : 'none';
        document.getElementById('stockField').style.display = isService ? 'none' : '';
        // Highlight selected
        const labels = document.querySelectorAll('[onclick^="toggleCatalogType"]');
        labels[0].style.borderColor = !isService ? 'var(--primary)' : 'var(--border)';
        labels[1].style.borderColor =  isService ? 'var(--primary)' : 'var(--border)';
    }
    // Init highlight
    toggleCatalogType(_isService);

    function toggleProductType(type) {
        document.getElementById('simple-fields').style.display = type === 'simple' ? 'block' : 'none';
        document.getElementById('sku-optional-label').style.display = type === 'variable' ? 'inline' : 'none';
    }

    function toggleCreditFields(checkbox) {
        const creditFields = document.getElementById('credit-fields');
        creditFields.style.display = checkbox.checked ? 'block' : 'none';
    }
</script>
@endsection
