@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier la variante</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.index') }}">Produits</a>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ $variant->name }}</span>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert danger">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Veuillez corriger les erreurs dans le formulaire.</span>
    </div>
    @endif

    <form action="{{ route('admin.products.variants.update', [$product, $variant]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Modifier : {{ $variant->name }}</h3>
                <span style="font-size: 0.85rem; color: var(--gray);">Produit : <strong>{{ $product->name }}</strong></span>
            </div>
            <div class="card-body">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Nom de la variante</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $variant->name) }}" required>
                        @error('name')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-input" value="{{ old('sku', $variant->sku) }}" required>
                        @error('sku')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prix (GNF)</label>
                        <input type="number" name="price" class="form-input" value="{{ old('price', $variant->price) }}" step="0.01" min="0" required>
                        @error('price')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Quantité en stock</label>
                        <input type="number" name="stock_quantity" class="form-input" value="{{ old('stock_quantity', $variant->stock_quantity) }}" min="0" required>
                        @error('stock_quantity')
                        <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Attributs --}}
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Attributs <span style="font-weight: 400; font-size: 0.85rem; color: var(--gray);">(optionnel)</span></h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Couleur</label>
                            <input type="text" name="attributes[color]" class="form-input"
                                value="{{ old('attributes.color', $variant->attributes['color'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Taille</label>
                            <input type="text" name="attributes[size]" class="form-input"
                                value="{{ old('attributes.size', $variant->attributes['size'] ?? '') }}">
                        </div>
                    </div>
                </div>

                {{-- Override crédit --}}
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">Crédit</h4>
                    <p style="font-size: 0.85rem; color: var(--gray); margin-bottom: 1rem;">
                        Par défaut, cette variante hérite des paramètres de crédit du produit parent
                        ({{ $product->credit_enabled ? $product->credit_installments_count . ' mensualités' : 'non activé' }}).
                        Remplissez ces champs uniquement pour les surcharger.
                    </p>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="credit_enabled" value="1"
                                {{ old('credit_enabled', $variant->credit_enabled) ? 'checked' : '' }}
                                onchange="toggleCreditFields(this)">
                            <span>Surcharger le crédit pour cette variante</span>
                        </label>
                    </div>

                    <div id="credit-fields" style="display: {{ old('credit_enabled', $variant->credit_enabled) ? 'block' : 'none' }}; margin-top: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Durée (mois)</label>
                                <input type="number" name="credit_duration_months" class="form-input"
                                    value="{{ old('credit_duration_months', $variant->credit_duration_months) }}" min="1" max="24">
                                @error('credit_duration_months')
                                <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nombre de mensualités</label>
                                <input type="number" name="credit_installments_count" class="form-input"
                                    value="{{ old('credit_installments_count', $variant->credit_installments_count) }}" min="1" max="12">
                                @error('credit_installments_count')
                                <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Statut --}}
                <div style="margin-top: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $variant->is_active) ? 'checked' : '' }}>
                            <span>Variante active</span>
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
    function toggleCreditFields(checkbox) {
        document.getElementById('credit-fields').style.display = checkbox.checked ? 'block' : 'none';
    }
</script>
@endsection
