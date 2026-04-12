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

    @if($errors->any())
    <div class="alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Veuillez corriger les erreurs dans le formulaire.</span>
    </div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informations du produit</h3>
            </div>
            <div class="card-body">

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
                            <label class="form-label">Prix (GNF)</label>
                            <input type="number" name="price" class="form-input" value="{{ old('price', $product->price) }}" step="0.01" min="0">
                            @error('price')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quantité en stock</label>
                            <input type="number" name="stock_quantity" class="form-input" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0">
                            @error('stock_quantity')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

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
    function toggleProductType(type) {
        document.getElementById('simple-fields').style.display = type === 'simple' ? 'block' : 'none';
    }

    function toggleCreditFields(checkbox) {
        document.getElementById('credit-fields').style.display = checkbox.checked ? 'block' : 'none';
    }
</script>
@endsection
