@extends('tpl.company')

@section('content')
<div class="page-content">
    <div class="page-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <a href="{{ route('company.orders.index') }}" style="color: var(--gray); text-decoration: none;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">Nouvelle commande</h1>
                <p style="color: var(--gray); margin-top: 0.25rem; font-size: 0.9rem;">{{ $company->name }}</p>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; color: #991b1b; display: flex; align-items: center; gap: 0.6rem;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div style="margin-bottom: 1.5rem; padding: 0.85rem 1.1rem; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; color: #991b1b;">
        <ul style="margin: 0; padding-left: 1.2rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('company.orders.store') }}" method="POST" id="orderForm">
        @csrf
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

            {{-- Articles --}}
            <div class="card">
                <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
                    <h3 class="card-title"><i class="fas fa-list"></i> Articles</h3>
                    <button type="button" class="btn btn-sm btn-outline" onclick="addItem()">
                        <i class="fas fa-plus"></i> Ajouter
                    </button>
                </div>
                <div class="card-body">
                    <div id="itemsContainer">
                        <div class="item-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 0.75rem; align-items: end; margin-bottom: 1rem;">
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label">Produit</label>
                                <select name="items[0][product_id]" class="form-control product-select" onchange="updateVariants(this, 0)" required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        data-price="{{ $product->price }}"
                                        data-variants="{{ $product->variants->toJson() }}">
                                        {{ $product->name }} — {{ number_format($product->price, 0, ',', ' ') }} GNF
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label">Variante</label>
                                <select name="items[0][variant_id]" class="form-control variant-select" id="variants_0">
                                    <option value="">Aucune</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="items[0][quantity]" class="form-control item-qty" value="1" min="1" required>
                            </div>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)" style="margin-bottom: 0.15rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paramètres --}}
            <div>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog"></i> Paramètres</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">Type de commande</label>
                            <div style="display: flex; gap: 0.75rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; padding: 0.5rem 0.85rem; border: 1px solid var(--border); border-radius: 6px; flex: 1; justify-content: center;"
                                       id="cashLabel">
                                    <input type="radio" name="order_type" value="cash" id="typeCash"
                                           {{ old('order_type', 'cash') === 'cash' ? 'checked' : '' }}
                                           onchange="toggleCreditOptions()">
                                    <i class="fas fa-money-bill-wave" style="color: #10b981;"></i> Comptant
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer; padding: 0.5rem 0.85rem; border: 1px solid var(--border); border-radius: 6px; flex: 1; justify-content: center;"
                                       id="creditLabel">
                                    <input type="radio" name="order_type" value="credit" id="typeCredit"
                                           {{ old('order_type') === 'credit' ? 'checked' : '' }}
                                           onchange="toggleCreditOptions()">
                                    <i class="fas fa-credit-card" style="color: #6366f1;"></i> Crédit
                                </label>
                            </div>
                        </div>

                        <div id="creditOptions" style="{{ old('order_type') === 'credit' ? '' : 'display: none;' }}">
                            <div class="form-group">
                                <label class="form-label">Acompte (GNF)</label>
                                <input type="number" name="down_payment" class="form-control"
                                       value="{{ old('down_payment', 0) }}" min="0" step="1000">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nombre de mensualités</label>
                                <select name="credit_installments_count" class="form-control">
                                    @foreach([3, 6, 9, 12, 18, 24] as $n)
                                    <option value="{{ $n }}" {{ old('credit_installments_count') == $n ? 'selected' : '' }}>{{ $n }} mois</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-receipt"></i> Récapitulatif</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--gray);">Sous-total</span>
                            <span id="subtotalDisplay" style="font-weight: 600;">0 GNF</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 0.75rem; margin-top: 0.5rem;">
                            <span style="font-weight: 700;">Total</span>
                            <span id="totalDisplay" style="font-weight: 700; font-size: 1.1rem; color: var(--primary);">0 GNF</span>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1.25rem;">
                            <i class="fas fa-paper-plane"></i> Passer la commande
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let itemIndex = 1;
const products = @json($products);

function addItem() {
    const container = document.getElementById('itemsContainer');
    const idx = itemIndex++;
    const options = products.map(p =>
        `<option value="${p.id}" data-price="${p.price}" data-variants='${JSON.stringify(p.variants)}'>${p.name} — ${p.price.toLocaleString('fr-FR')} GNF</option>`
    ).join('');

    const html = `
    <div class="item-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 0.75rem; align-items: end; margin-bottom: 1rem;">
        <div class="form-group" style="margin: 0;">
            <select name="items[${idx}][product_id]" class="form-control product-select" onchange="updateVariants(this, ${idx})" required>
                <option value="">Sélectionner...</option>
                ${options}
            </select>
        </div>
        <div class="form-group" style="margin: 0;">
            <select name="items[${idx}][variant_id]" class="form-control variant-select" id="variants_${idx}">
                <option value="">Aucune</option>
            </select>
        </div>
        <div class="form-group" style="margin: 0;">
            <input type="number" name="items[${idx}][quantity]" class="form-control item-qty" value="1" min="1" required>
        </div>
        <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(this)">
            <i class="fas fa-trash"></i>
        </button>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    container.querySelectorAll('.item-qty').forEach(el => el.addEventListener('change', calcTotal));
    container.querySelectorAll('.product-select').forEach(el => el.addEventListener('change', calcTotal));
    container.querySelectorAll('.variant-select').forEach(el => el.addEventListener('change', calcTotal));
}

function removeItem(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length === 1) return;
    btn.closest('.item-row').remove();
    calcTotal();
}

function updateVariants(select, idx) {
    const opt = select.options[select.selectedIndex];
    const variants = JSON.parse(opt.dataset.variants || '[]');
    const variantSelect = document.getElementById(`variants_${idx}`);
    variantSelect.innerHTML = '<option value="">Aucune</option>';
    variants.forEach(v => {
        variantSelect.insertAdjacentHTML('beforeend',
            `<option value="${v.id}" data-price="${v.price}">${v.name} — ${parseFloat(v.price).toLocaleString('fr-FR')} GNF</option>`
        );
    });
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const productSel = row.querySelector('.product-select');
        const variantSel = row.querySelector('.variant-select');
        const qty = parseInt(row.querySelector('.item-qty')?.value || 1);
        let price = 0;
        if (variantSel && variantSel.value) {
            price = parseFloat(variantSel.options[variantSel.selectedIndex]?.dataset.price || 0);
        } else if (productSel && productSel.value) {
            price = parseFloat(productSel.options[productSel.selectedIndex]?.dataset.price || 0);
        }
        total += price * qty;
    });
    const fmt = v => v.toLocaleString('fr-FR') + ' GNF';
    document.getElementById('subtotalDisplay').textContent = fmt(total);
    document.getElementById('totalDisplay').textContent = fmt(total);
}

function toggleCreditOptions() {
    const isCash = document.getElementById('typeCash').checked;
    document.getElementById('creditOptions').style.display = isCash ? 'none' : '';
}

// Initialisation
document.querySelectorAll('.item-qty, .product-select, .variant-select').forEach(el => {
    el.addEventListener('change', calcTotal);
});
</script>
@endsection
