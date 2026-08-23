@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Nouvelle commande</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.orders.index') }}">Commandes</a>
                <i class="fas fa-chevron-right"></i>
                <span>Nouvelle</span>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.orders.store') }}" method="POST" id="order-form">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

            {{-- Colonne principale --}}
            <div>

                {{-- 1. Client --}}
                <div class="card">
                    <div class="card-header"><h3 class="card-title">1. Client</h3></div>
                    <div class="card-body">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Sélectionner un client</label>
                            <select name="customer_id" id="customer-select" class="form-input" required onchange="onCustomerChange(this)">
                                <option value="">— Choisir un client —</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    data-credit-limit="{{ $customer->effectiveCreditLimit() ?? '' }}"
                                    data-used-credit="{{ $customer->usedCredit() }}"
                                    {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->displayName() }}
                                    @if($customer->company) · {{ $customer->company->name }} @endif
                                </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                            <span style="color: var(--danger); font-size: 0.85rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div id="customer-credit-info" style="display: none; margin-top: 1rem; padding: 0.75rem 1rem; background: rgba(59,130,246,0.05); border-left: 3px solid #3B82F6; border-radius: 4px;">
                            <div style="font-size: 0.85rem; color: var(--gray);">Plafond crédit disponible</div>
                            <div id="customer-credit-value" style="font-weight: 700; font-size: 1.1rem; color: #3B82F6;"></div>
                        </div>
                    </div>
                </div>

                {{-- 2. Produits --}}
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header"><h3 class="card-title">2. Produits</h3></div>
                    <div class="card-body">

                        {{-- Sélecteur produit --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr auto auto; gap: 0.75rem; align-items: flex-end; margin-bottom: 1.5rem;">
                            <div>
                                <label class="form-label">Produit</label>
                                <select id="product-picker" class="form-input" onchange="onProductChange(this)">
                                    <option value="">— Choisir —</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        data-type="{{ $product->type }}"
                                        data-price="{{ $product->price }}"
                                        data-name="{{ $product->name }}"
                                        data-sku="{{ $product->sku }}">
                                        {{ $product->name }}
                                        @if($product->isSimple()) · {{ number_format($product->price, 0, ',', ' ') }} GNF @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="variant-picker-wrap" style="display: none;">
                                <label class="form-label">Variante</label>
                                <select id="variant-picker" class="form-input">
                                    <option value="">— Variante —</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Qté</label>
                                <input type="number" id="qty-picker" class="form-input" value="1" min="1" style="width: 80px;">
                            </div>
                            <div style="padding-bottom: 2px;">
                                <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
                                    <i class="fas fa-plus"></i> Ajouter
                                </button>
                            </div>
                        </div>

                        {{-- Tableau des articles --}}
                        <div id="items-empty" style="text-align: center; padding: 2rem; color: var(--gray); border: 2px dashed var(--border); border-radius: 8px;">
                            <i class="fas fa-box" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem;"></i>
                            Aucun article ajouté
                        </div>

                        <div id="items-table-wrap" style="display: none;">
                            <div class="table-container">
                                <table id="items-table">
                                    <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix unitaire</th>
                                        <th>Qté</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="items-tbody"></tbody>
                                    <tfoot>
                                    <tr style="background: var(--light);">
                                        <td colspan="3" style="text-align:right; font-weight:600; padding: 0.75rem 1rem;">Total</td>
                                        <td id="total-display" style="font-weight:700; color: var(--primary); font-size: 1.1rem;">0 GNF</td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div id="items-error" style="display: none; color: var(--danger); font-size: 0.85rem; margin-top: 0.5rem;">
                            Veuillez ajouter au moins un article.
                        </div>
                    </div>
                </div>

                {{-- 3. Paiement --}}
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header"><h3 class="card-title">3. Mode de paiement</h3></div>
                    <div class="card-body">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                            <label style="display:flex; align-items:center; gap:0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor:pointer; border: 2px solid var(--border);" onclick="togglePaymentType('cash')">
                                <input type="radio" name="order_type" value="cash" {{ old('order_type', 'cash') === 'cash' ? 'checked' : '' }} required>
                                <div>
                                    <div style="font-weight:600;"><i class="fas fa-money-bill"></i> Comptant</div>
                                    <div style="font-size:0.8rem; color:var(--gray);">Paiement immédiat</div>
                                </div>
                            </label>
                            <label style="display:flex; align-items:center; gap:0.5rem; padding: 0.75rem 1.5rem; background: var(--light); border-radius: 8px; cursor:pointer; border: 2px solid var(--border);" onclick="togglePaymentType('credit')">
                                <input type="radio" name="order_type" value="credit" {{ old('order_type') === 'credit' ? 'checked' : '' }} required>
                                <div>
                                    <div style="font-weight:600;"><i class="fas fa-credit-card"></i> Crédit</div>
                                    <div style="font-size:0.8rem; color:var(--gray);">Paiement en mensualités</div>
                                </div>
                            </label>
                        </div>

                        {{-- Champs crédit --}}
                        <div id="credit-fields" style="display: {{ old('order_type') === 'credit' ? 'block' : 'none' }};">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <div class="form-group">
                                    <label class="form-label">Acompte (GNF) <span style="font-weight:400; color:var(--gray);">(optionnel)</span></label>
                                    <input type="number" name="down_payment" id="down-payment" class="form-input"
                                        placeholder="0" value="{{ old('down_payment', 0) }}" min="0"
                                        oninput="updateCreditSummary()">
                                    @error('down_payment')
                                    <span style="color:var(--danger); font-size:0.85rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nombre de mensualités</label>
                                    <input type="number" name="credit_installments_count" id="installments-count" class="form-input"
                                        placeholder="12" value="{{ old('credit_installments_count') }}" min="1" max="24"
                                        oninput="updateCreditSummary()">
                                    @error('credit_installments_count')
                                    <span style="color:var(--danger); font-size:0.85rem;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Résumé crédit --}}
                            <div id="credit-summary" style="display:none; margin-top: 1rem; padding: 1rem; background: rgba(139,92,246,0.05); border-radius: 8px; border-left: 3px solid #8B5CF6;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <div style="font-size:0.8rem; color:var(--gray);">Montant financé</div>
                                        <div id="credit-amount" style="font-weight:700; color:#8B5CF6;"></div>
                                    </div>
                                    <div>
                                        <div style="font-size:0.8rem; color:var(--gray);">Mensualité estimée</div>
                                        <div id="monthly-payment" style="font-weight:700; color:var(--primary);"></div>
                                    </div>
                                    <div id="limit-check-wrap">
                                        <div style="font-size:0.8rem; color:var(--gray);">Plafond disponible</div>
                                        <div id="limit-check" style="font-weight:700;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panneau récap --}}
            <div>
                <div class="card" style="position: sticky; top: 1rem;">
                    <div class="card-header"><h3 class="card-title">Récapitulatif</h3></div>
                    <div class="card-body">
                        <div style="display:flex; flex-direction:column; gap:0.75rem;">
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--gray);">Articles</span>
                                <span id="recap-items">0</span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="color:var(--gray);">Total</span>
                                <span id="recap-total" style="font-weight:700; color:var(--primary);">0 GNF</span>
                            </div>
                            <div id="recap-credit-wrap" style="display:none; padding-top:0.75rem; border-top:1px solid var(--border);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                                    <span style="color:var(--gray);">Acompte</span>
                                    <span id="recap-down" style="color:var(--success);">0 GNF</span>
                                </div>
                                <div style="display:flex; justify-content:space-between;">
                                    <span style="color:var(--gray);">À financer</span>
                                    <span id="recap-finance" style="font-weight:700; color:#8B5CF6;">0 GNF</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" style="display:flex; flex-direction:column; gap:0.75rem;">
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <i class="fas fa-save"></i> Créer la commande
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline" style="text-align:center;">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Données produits pour JS --}}
<script>
const PRODUCTS_DATA = {!! $productsJson !!};

let cartItems  = [];
let orderTotal = 0;
let customerCreditLimit  = null;
let customerUsedCredit   = 0;

// ---- Customer ----
function onCustomerChange(select) {
    const opt = select.options[select.selectedIndex];
    const limit = opt.dataset.creditLimit;
    const used  = parseFloat(opt.dataset.usedCredit || 0);

    if (limit !== '') {
        customerCreditLimit = parseFloat(limit);
        customerUsedCredit  = used;
        const available = customerCreditLimit - customerUsedCredit;
        document.getElementById('customer-credit-info').style.display = 'block';
        document.getElementById('customer-credit-value').textContent  = formatGNF(available) + ' GNF';
    } else {
        customerCreditLimit = null;
        customerUsedCredit  = 0;
        document.getElementById('customer-credit-info').style.display = 'none';
    }
    updateCreditSummary();
}

// ---- Produits ----
function onProductChange(select) {
    const productId = select.value;
    const product   = PRODUCTS_DATA.find(p => p.id == productId);
    const varWrap   = document.getElementById('variant-picker-wrap');
    const varPicker = document.getElementById('variant-picker');

    varPicker.innerHTML = '<option value="">— Variante —</option>';

    if (!product) { varWrap.style.display = 'none'; return; }

    if (product.type === 'variable') {
        product.variants.forEach(v => {
            const opt  = document.createElement('option');
            opt.value  = v.id;
            opt.textContent = v.name + ' · ' + formatGNF(v.price) + ' GNF';
            opt.dataset.price = v.price;
            opt.dataset.sku   = v.sku;
            varPicker.appendChild(opt);
        });
        varWrap.style.display = 'block';
    } else {
        varWrap.style.display = 'none';
    }
}

function addItem() {
    const productPicker = document.getElementById('product-picker');
    const variantPicker = document.getElementById('variant-picker');
    const qtyInput      = document.getElementById('qty-picker');

    const productId = productPicker.value;
    if (!productId) { alert('Veuillez sélectionner un produit.'); return; }

    const product   = PRODUCTS_DATA.find(p => p.id == productId);
    const qty       = parseInt(qtyInput.value) || 1;

    let variantId = null, unitPrice, itemName, itemSku;

    if (product.type === 'variable') {
        variantId = variantPicker.value;
        if (!variantId) { alert('Veuillez sélectionner une variante.'); return; }
        const variant = product.variants.find(v => v.id == variantId);
        unitPrice = variant.price;
        itemName  = product.name + ' — ' + variant.name;
        itemSku   = variant.sku;
    } else {
        unitPrice = product.price;
        itemName  = product.name;
        itemSku   = product.sku;
    }

    // Vérifier doublon
    const existing = cartItems.find(i => i.productId == productId && i.variantId == variantId);
    if (existing) {
        existing.qty += qty;
        existing.lineTotal = existing.unitPrice * existing.qty;
    } else {
        cartItems.push({
            productId,
            variantId,
            name: itemName,
            sku:  itemSku,
            unitPrice,
            qty,
            lineTotal: unitPrice * qty,
        });
    }

    renderCart();
    productPicker.value = '';
    variantPicker.innerHTML = '<option value="">— Variante —</option>';
    document.getElementById('variant-picker-wrap').style.display = 'none';
    qtyInput.value = 1;
}

function removeItem(index) {
    cartItems.splice(index, 1);
    renderCart();
}

function renderCart() {
    const tbody   = document.getElementById('items-tbody');
    const empty   = document.getElementById('items-empty');
    const tableW  = document.getElementById('items-table-wrap');

    tbody.innerHTML = '';
    orderTotal = 0;

    cartItems.forEach((item, i) => {
        orderTotal += item.lineTotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <div style="font-weight:600;">${item.name}</div>
                <div style="font-size:0.8rem; color:var(--gray);">${item.sku || ''}</div>
                <input type="hidden" name="items[${i}][product_id]" value="${item.productId}">
                ${item.variantId ? `<input type="hidden" name="items[${i}][variant_id]" value="${item.variantId}">` : ''}
                <input type="hidden" name="items[${i}][quantity]" value="${item.qty}">
            </td>
            <td>${formatGNF(item.unitPrice)} GNF</td>
            <td>${item.qty}</td>
            <td style="font-weight:600;">${formatGNF(item.lineTotal)} GNF</td>
            <td><button type="button" class="btn btn-outline btn-sm" style="color:var(--danger);" onclick="removeItem(${i})"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('total-display').textContent = formatGNF(orderTotal) + ' GNF';

    if (cartItems.length === 0) {
        empty.style.display = 'block';
        tableW.style.display = 'none';
    } else {
        empty.style.display = 'none';
        tableW.style.display = 'block';
    }

    updateRecap();
    updateCreditSummary();
}

// ---- Paiement ----
function togglePaymentType(type) {
    const creditFields = document.getElementById('credit-fields');
    const recapCredit  = document.getElementById('recap-credit-wrap');
    creditFields.style.display = type === 'credit' ? 'block' : 'none';
    recapCredit.style.display  = type === 'credit' ? 'block' : 'none';
    updateCreditSummary();
}

function updateCreditSummary() {
    const isCredit = document.querySelector('input[name="order_type"]:checked')?.value === 'credit';
    if (!isCredit) {
        document.getElementById('credit-summary').style.display = 'none';
        return;
    }

    const down         = parseFloat(document.getElementById('down-payment')?.value || 0);
    const installments = parseInt(document.getElementById('installments-count')?.value || 0);
    const creditAmount = Math.max(0, orderTotal - down);

    if (orderTotal > 0) {
        document.getElementById('credit-summary').style.display = 'block';
        document.getElementById('credit-amount').textContent    = formatGNF(creditAmount) + ' GNF';
        document.getElementById('monthly-payment').textContent  = installments > 0
            ? formatGNF(creditAmount / installments) + ' GNF'
            : '—';

        // Vérification plafond
        if (customerCreditLimit !== null) {
            const available = customerCreditLimit - customerUsedCredit;
            const limitEl   = document.getElementById('limit-check');
            if (creditAmount > available) {
                limitEl.textContent = formatGNF(available) + ' GNF ⚠';
                limitEl.style.color = 'var(--danger)';
            } else {
                limitEl.textContent = formatGNF(available) + ' GNF ✓';
                limitEl.style.color = 'var(--success)';
            }
            document.getElementById('limit-check-wrap').style.display = 'block';
        } else {
            document.getElementById('limit-check-wrap').style.display = 'none';
        }
    } else {
        document.getElementById('credit-summary').style.display = 'none';
    }

    updateRecap();
}

function updateRecap() {
    const isCredit = document.querySelector('input[name="order_type"]:checked')?.value === 'credit';
    const down     = isCredit ? parseFloat(document.getElementById('down-payment')?.value || 0) : 0;

    document.getElementById('recap-items').textContent  = cartItems.length + ' article(s)';
    document.getElementById('recap-total').textContent  = formatGNF(orderTotal) + ' GNF';
    document.getElementById('recap-down').textContent   = formatGNF(down) + ' GNF';
    document.getElementById('recap-finance').textContent= formatGNF(Math.max(0, orderTotal - down)) + ' GNF';
    document.getElementById('recap-credit-wrap').style.display = isCredit ? 'block' : 'none';
}

// ---- Validation avant submit ----
document.getElementById('order-form').addEventListener('submit', function(e) {
    if (cartItems.length === 0) {
        e.preventDefault();
        document.getElementById('items-error').style.display = 'block';
        document.getElementById('items-empty').scrollIntoView({ behavior: 'smooth' });
    }
});

// ---- Helpers ----
function formatGNF(n) {
    return Math.round(n).toLocaleString('fr-FR');
}
</script>
@endsection
