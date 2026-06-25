@extends('tpl.portal')

@section('content')
<style>
/* ═══════════════════════════════════════════════════════════════
   ORDER WIZARD  –  3-step layout
   ═══════════════════════════════════════════════════════════════ */

/* ── Stepper ── */
.wizard-stepper {
    display: flex;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1.25rem 2rem;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    gap: 0;
}
.wizard-step {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex: 1;
    position: relative;
}
.wizard-step:not(:last-child)::after {
    content: '';
    flex: 1;
    height: 2px;
    background: var(--border);
    margin: 0 0.5rem;
    transition: background .3s;
}
.wizard-step.done:not(:last-child)::after  { background: var(--primary); }
.wizard-step.active:not(:last-child)::after { background: var(--border); }

.step-bubble {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 600;
    flex-shrink: 0;
    transition: background .3s, color .3s;
    border: 2px solid var(--border);
    background: #fff;
    color: var(--text-muted);
}
.wizard-step.active .step-bubble {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}
.wizard-step.done .step-bubble {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}
.step-label { font-size: 13px; font-weight: 500; color: var(--text-muted); white-space: nowrap; }
.wizard-step.active .step-label { color: var(--text); font-weight: 600; }
.wizard-step.done .step-label  { color: var(--primary); }

/* ── Panels ── */
.wizard-panel { display: none; }
.wizard-panel.active { display: flex; gap: 1.5rem; }
.wizard-panel-single { display: none; flex-direction: column; }
.wizard-panel-single.active { display: flex; }

/* ── Step 1: Products + Cart ── */
.catalog-pane {
    flex: 1;
    min-width: 0;
}
.cart-pane {
    width: 300px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
}
.cart-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    position: sticky;
    top: 1.5rem;
}
.cart-card-head {
    padding: 0.9rem 1.1rem;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    font-weight: 600; font-size: 14px;
}
.cart-card-head .badge-count {
    background: var(--primary);
    color: #fff;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    padding: 1px 7px;
    min-width: 20px;
    text-align: center;
}
.cart-body { padding: 0.75rem; min-height: 120px; max-height: 340px; overflow-y: auto; }
.cart-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--text-muted);
    font-size: 13px;
}
.cart-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.55rem 0;
    border-bottom: 1px solid var(--bg);
}
.cart-item:last-child { border-bottom: none; }
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cart-item-price { font-size: 12px; color: var(--text-muted); }
.cart-item-qty {
    display: flex; align-items: center; gap: 4px;
}
.qty-btn {
    width: 22px; height: 22px;
    border: 1px solid var(--border);
    border-radius: 4px;
    background: #fff;
    cursor: pointer;
    font-size: 13px;
    display: flex; align-items: center; justify-content: center;
    line-height: 1;
    transition: background .1s;
}
.qty-btn:hover { background: var(--bg); }
.qty-val { font-size: 13px; font-weight: 600; min-width: 20px; text-align: center; }
.cart-remove { color: #d1d5db; cursor: pointer; font-size: 13px; padding: 2px; }
.cart-remove:hover { color: #ef4444; }

.cart-footer {
    padding: 0.9rem 1.1rem;
    border-top: 1px solid var(--border);
    background: var(--bg);
}
.cart-total-row {
    display: flex; justify-content: space-between; align-items: center;
    font-size: 14px; font-weight: 700;
    margin-bottom: 0.85rem;
}
.cart-total-amount { color: var(--primary); font-size: 15px; }

/* ── Product grid (compact for wizard) ── */
.w-product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}
.w-product-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow .15s;
    display: flex;
    flex-direction: column;
}
.w-product-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.07); }
.w-product-thumb {
    width: 100%; height: 120px;
    background: var(--bg);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.w-product-thumb img { width: 100%; height: 120px; object-fit: cover; }
.w-product-body { padding: 0.75rem; display: flex; flex-direction: column; flex: 1; }
.w-product-name { font-size: 13px; font-weight: 600; margin-bottom: 2px; line-height: 1.3; }
.w-product-price { font-size: 13px; color: var(--primary); font-weight: 700; margin-bottom: 0.6rem; }
.w-product-variants {
    font-size: 11.5px; color: var(--text-muted);
    margin-bottom: 0.6rem;
}
.btn-add-cart {
    width: 100%;
    padding: 0.38rem 0;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 12.5px;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s;
}
.btn-add-cart:hover { background: var(--primary-hover); }
.btn-add-cart:disabled { background: #d1d5db; cursor: not-allowed; }

/* Variant inline picker */
.variant-picker {
    margin-bottom: 0.5rem;
    min-height: 34px;
}
.variant-picker select {
    width: 100%;
    padding: 0.28rem 0.5rem;
    border: 1px solid var(--border);
    border-radius: 5px;
    font-size: 12px;
    background: var(--bg);
    color: var(--text);
    outline: none;
}

/* Search bar within step */
.step-search {
    position: relative;
    margin-bottom: 1rem;
}
.step-search i {
    position: absolute; left: 0.75rem; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted); font-size: 12px; pointer-events: none;
}
.step-search input {
    width: 100%; height: 36px;
    padding: 0 0.85rem 0 2.2rem;
    border: 1px solid var(--border); border-radius: 6px;
    font-size: 13.5px; color: var(--text); background: #fff;
    outline: none; box-sizing: border-box;
    transition: border-color .15s;
}
.step-search input:focus { border-color: var(--primary); }

/* ── Step 2: Payment ── */
.payment-section { margin-bottom: 1.75rem; }
.payment-section-title {
    font-size: 11px; font-weight: 600;
    letter-spacing: .06em; text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 0.85rem;
}
.pay-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.pay-card {
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 1.25rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s;
    user-select: none;
    position: relative;
}
.pay-card:hover { border-color: #93c5fd; }
.pay-card.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.pay-card-check {
    position: absolute; top: 8px; right: 8px;
    width: 18px; height: 18px; border-radius: 50%;
    border: 2px solid var(--border);
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; color: #fff;
    transition: background .15s, border-color .15s;
}
.pay-card.selected .pay-card-check { background: var(--primary); border-color: var(--primary); }

/* Brand logos */
.pay-logo { height: 42px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.6rem; }
.pay-logo-om {
    background: #FF6600;
    color: #fff;
    border-radius: 6px;
    padding: 4px 10px;
    font-weight: 800;
    font-size: 16px;
    letter-spacing: .03em;
}
.pay-logo-om span { font-size: 10px; font-weight: 500; display: block; letter-spacing: .05em; }
.pay-logo-momo {
    background: #FFCC00;
    color: #1a1a1a;
    border-radius: 6px;
    padding: 4px 8px;
    font-weight: 800;
    font-size: 14px;
}
.pay-logo-momo span { font-size: 9px; font-weight: 600; color: #0057A8; display: block; letter-spacing: .04em; }
.pay-logo-djomy {
    background: #00897B;
    color: #fff;
    border-radius: 6px;
    padding: 4px 8px;
    font-weight: 800;
    font-size: 14px;
}
.pay-logo-djomy span { font-size: 9px; font-weight: 500; color: rgba(255,255,255,.8); display: block; }

.pay-name { font-size: 13px; font-weight: 600; color: var(--text); }
.pay-sub  { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }

/* Credit block */
.credit-block {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1.25rem;
    display: none;
    margin-top: 0.75rem;
}
.credit-block.visible { display: block; }
.credit-options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

/* Payment type toggle */
.pay-type-toggle {
    display: flex;
    border: 1px solid var(--border);
    border-radius: 7px;
    overflow: hidden;
    margin-bottom: 1.5rem;
    width: fit-content;
}
.pay-type-btn {
    padding: 0.5rem 1.5rem;
    font-size: 13.5px; font-weight: 500;
    cursor: pointer; border: none; background: #fff;
    color: var(--text-muted);
    transition: background .15s, color .15s;
}
.pay-type-btn.active { background: var(--primary); color: #fff; }

/* ── Step 3: Confirmation ── */
.confirm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.confirm-section-title {
    font-size: 11px; font-weight: 600;
    letter-spacing: .06em; text-transform: uppercase;
    color: var(--text-muted); margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
}
.confirm-items-table { width: 100%; border-collapse: collapse; }
.confirm-items-table td { padding: 0.55rem 0; border-bottom: 1px solid var(--bg); font-size: 13.5px; }
.confirm-items-table tr:last-child td { border-bottom: none; }
.confirm-total-row {
    display: flex; justify-content: space-between; align-items: center;
    padding-top: 0.75rem; border-top: 2px solid var(--border); margin-top: 0.5rem;
    font-weight: 700; font-size: 15px;
}
.confirm-total-amt { color: var(--primary); }

.notice-box {
    display: flex; align-items: flex-start; gap: 0.6rem;
    padding: 0.85rem 1rem;
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: 8px; font-size: 13px; color: #1e40af;
    margin-top: 1.25rem;
}

/* ── Wizard nav ── */
.wizard-nav {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
}
.btn-wizard-next {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.55rem 1.5rem;
    background: var(--primary); color: #fff;
    border: none; border-radius: 6px;
    font-size: 14px; font-weight: 500; cursor: pointer;
    transition: background .15s;
}
.btn-wizard-next:hover { background: var(--primary-hover); }
.btn-wizard-back {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.55rem 1.25rem;
    background: #fff; color: var(--text);
    border: 1px solid var(--border); border-radius: 6px;
    font-size: 14px; font-weight: 500; cursor: pointer;
    transition: background .15s;
}
.btn-wizard-back:hover { background: var(--bg); }
.btn-submit-order {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.6rem 2rem;
    background: #16a34a; color: #fff;
    border: none; border-radius: 6px;
    font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background .15s;
}
.btn-submit-order:hover { background: #15803d; }
.btn-submit-order:disabled { background: #d1d5db; cursor: not-allowed; }
</style>

<div class="page-content">

    {{-- Back + title ──────────────────────────────────────── --}}
    <div class="page-header" style="margin-bottom: 1.5rem;">
        <div style="display:flex;align-items:center;gap:0.85rem;">
            <a href="{{ route('portal.orders.index') }}" style="color:var(--text-muted);text-decoration:none;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">Nouvelle commande</h1>
                <p style="color:var(--text-muted);font-size:13px;margin-top:2px;" id="pageSubtitle">
                    Choisissez vos produits et le mode de règlement.
                </p>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div style="margin-bottom:1.25rem;padding:.8rem 1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#991b1b;font-size:13.5px;display:flex;align-items:center;gap:.5rem;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div style="margin-bottom:1.25rem;padding:.8rem 1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#991b1b;font-size:13.5px;">
        <ul style="margin:0;padding-left:1.1rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── Stepper ─────────────────────────────────────────── --}}
    <div class="wizard-stepper" id="stepper">
        <div class="wizard-step active" id="step-ind-1">
            <div class="step-bubble">1</div>
            <span class="step-label">Produits</span>
        </div>
        <div class="wizard-step" id="step-ind-2">
            <div class="step-bubble">2</div>
            <span class="step-label">Paiement</span>
        </div>
        <div class="wizard-step" id="step-ind-3">
            <div class="step-bubble"><i class="fas fa-check" style="font-size:11px;"></i></div>
            <span class="step-label">Confirmation</span>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 1 — Product selection + Cart
         ════════════════════════════════════════ --}}
    <div class="wizard-panel active" id="panel-1">

        {{-- Left: catalog --}}
        <div class="catalog-pane">
            <div class="step-search">
                <i class="fas fa-search"></i>
                <input type="text" id="catalogSearch" placeholder="Rechercher un produit…" oninput="filterProducts(this.value)">
            </div>
            <div class="w-product-grid" id="productGrid">
                @foreach($products as $product)
                <div class="w-product-card"
                     data-name="{{ strtolower($product->name) }}"
                     data-id="{{ $product->id }}">
                    <div class="w-product-thumb">
                        @php $cover = $product->coverUrl(); @endphp
                        @if($cover)
                            <img src="{{ $cover }}" alt="{{ $product->name }}">
                        @else
                            <i class="fas fa-box" style="font-size:1.75rem;color:#d1d5db;"></i>
                        @endif
                    </div>
                    <div class="w-product-body">
                        <div class="w-product-name">{{ $product->name }}</div>
                        <div class="w-product-price">{{ $product->display_price }}</div>
                        {{-- Espace réservé variant : toujours présent pour aligner les boutons --}}
                        <div class="variant-picker">
                            @if($product->variants->isNotEmpty())
                            <select class="variant-select-{{ $product->id }}" id="var_{{ $product->id }}">
                                <option value="">— Variante (optionnel)</option>
                                @foreach($product->variants as $v)
                                <option value="{{ $v->id }}"
                                        data-price="{{ $v->price }}"
                                        data-name="{{ $v->name }}">
                                    {{ $v->name }} — {{ number_format($v->price, 0, ',', ' ') }} GNF
                                </option>
                                @endforeach
                            </select>
                            @else
                            {{-- Placeholder invisible pour aligner le bouton --}}
                            <div style="height: 28px;"></div>
                            @endif
                        </div>
                        <button class="btn-add-cart"
                                onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price ?? 0 }}, '{{ $product->type }}')">
                            <i class="fas fa-cart-plus"></i> Ajouter
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @if($products->isEmpty())
            <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                <i class="fas fa-box-open" style="font-size:2rem;display:block;margin-bottom:.75rem;color:#d1d5db;"></i>
                Aucun produit disponible.
            </div>
            @endif
        </div>

        {{-- Right: cart --}}
        <div class="cart-pane">
            <div class="cart-card">
                <div class="cart-card-head">
                    <span><i class="fas fa-shopping-cart" style="margin-right:.4rem;"></i> Panier</span>
                    <span class="badge-count" id="cartCount">0</span>
                </div>
                <div class="cart-body" id="cartBody">
                    <div class="cart-empty" id="cartEmpty">
                        <i class="fas fa-cart-plus" style="font-size:1.5rem;display:block;margin-bottom:.5rem;color:#d1d5db;"></i>
                        Panier vide
                    </div>
                </div>
                <div class="cart-footer">
                    <div class="cart-total-row">
                        <span>Total</span>
                        <span class="cart-total-amount" id="cartTotal">0 GNF</span>
                    </div>
                    <button class="btn-wizard-next" style="width:100%;" onclick="goStep(2)" id="btnStep2" disabled>
                        Continuer <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 2 — Payment
         ════════════════════════════════════════ --}}
    <div class="wizard-panel-single" id="panel-2">
        <div class="card" style="max-width:640px;">
            <div class="card-body" style="padding:1.5rem;">

                {{-- Type toggle: Comptant / Crédit --}}
                <div class="payment-section">
                    <div class="payment-section-title">Mode de règlement</div>
                    <div class="pay-type-toggle">
                        <button type="button" class="pay-type-btn active" id="typeComptant"
                                onclick="setOrderType('cash')">
                            <i class="fas fa-money-bill-wave"></i> Comptant
                        </button>
                        <button type="button" class="pay-type-btn" id="typeCredit"
                                onclick="setOrderType('credit')">
                            <i class="fas fa-credit-card"></i> Crédit
                        </button>
                    </div>
                </div>

                {{-- Credit section --}}
                <div id="creditSection" class="payment-section" style="display:none;">
                    <div class="payment-section-title">Options de crédit</div>
                    <div class="credit-block visible">
                        <div class="credit-options-grid">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Acompte (GNF)</label>
                                <input type="number" id="downPaymentInput" class="form-control"
                                       placeholder="0" min="0" step="1000" oninput="updateCreditPreview()">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Mensualités</label>
                                <select id="installmentsInput" class="form-control" onchange="updateCreditPreview()">
                                    @foreach([3, 6, 9, 12, 18, 24] as $n)
                                    <option value="{{ $n }}">{{ $n }} mois</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="creditPreview" style="margin-top:1rem;padding:.75rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;font-size:13px;color:#166534;display:none;">
                        </div>
                    </div>
                </div>

                {{-- Cart recap (mini) --}}
                <div style="padding-top:1rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13.5px;color:var(--text-muted);">Total commande</span>
                    <span style="font-weight:700;font-size:15px;color:var(--primary);" id="step2Total">0 GNF</span>
                </div>

            </div>
        </div>

        <div class="wizard-nav" style="max-width:640px;">
            <button type="button" class="btn-wizard-back" onclick="goStep(1)">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
            <button type="button" class="btn-wizard-next" onclick="goStep(3)">
                Continuer <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         STEP 3 — Confirmation + Submit
         ════════════════════════════════════════ --}}
    <div class="wizard-panel-single" id="panel-3">
        <div class="card" style="max-width:720px;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clipboard-check"></i> Récapitulatif de la commande</h3>
            </div>
            <div class="card-body" style="padding:1.5rem;">

                <div class="confirm-grid">
                    {{-- Articles --}}
                    <div>
                        <div class="confirm-section-title">Articles commandés</div>
                        <table class="confirm-items-table" id="confirmItems">
                        </table>
                        <div class="confirm-total-row">
                            <span>Total</span>
                            <span class="confirm-total-amt" id="confirmTotal">0 GNF</span>
                        </div>
                    </div>

                    {{-- Paiement --}}
                    <div>
                        <div class="confirm-section-title">Mode de paiement</div>
                        <div id="confirmPayment" style="font-size:13.5px;"></div>
                    </div>
                </div>

                <div class="notice-box" id="noticeBox">
                    <i class="fas fa-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                    <span id="noticeText">Votre commande sera soumise à votre responsable pour validation avant tout traitement.</span>
                </div>
            </div>
        </div>

        <div class="wizard-nav" style="max-width:720px;">
            <button type="button" class="btn-wizard-back" onclick="goStep(2)">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
            <form action="{{ route('portal.orders.store') }}" method="POST" id="orderForm" style="display:inline;">
                @csrf
                <div id="formHiddenFields"></div>
                <button type="submit" class="btn-submit-order" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> <span id="submitLabel">Soumettre la commande</span>
                </button>
            </form>
        </div>
    </div>

</div>

<script>
/* ════════════════════════════════════════════════════════════
   State
   ════════════════════════════════════════════════════════════ */
let cart        = [];      // [{productId, productName, variantId, variantName, price, qty}]
let orderType   = 'cash';  // 'cash' | 'credit'
let currentStep = 1;

/* Pre-select product from URL param */
const urlProduct = new URLSearchParams(window.location.search).get('product');

/* ════════════════════════════════════════════════════════════
   Cart helpers
   ════════════════════════════════════════════════════════════ */
function addToCart(productId, productName, basePrice, type) {
    const varSel = document.getElementById('var_' + productId);
    let variantId = null, variantName = null, price = parseFloat(basePrice);

    if (varSel && varSel.value) {
        variantId   = parseInt(varSel.value);
        const opt   = varSel.options[varSel.selectedIndex];
        variantName = opt.dataset.name;
        price       = parseFloat(opt.dataset.price);
    }

    // find existing
    const key  = productId + '_' + (variantId || 0);
    const idx  = cart.findIndex(i => i.key === key);

    if (idx >= 0) {
        cart[idx].qty++;
    } else {
        cart.push({ key, productId, productName, variantId, variantName, price, qty: 1 });
    }
    renderCart();
}

function changeQty(key, delta) {
    const idx = cart.findIndex(i => i.key === key);
    if (idx < 0) return;
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    renderCart();
}

function removeFromCart(key) {
    cart = cart.filter(i => i.key !== key);
    renderCart();
}

function cartTotal() {
    return cart.reduce((s, i) => s + i.price * i.qty, 0);
}

function renderCart() {
    const body  = document.getElementById('cartBody');
    const empty = document.getElementById('cartEmpty');
    const count = document.getElementById('cartCount');
    const total = document.getElementById('cartTotal');
    const btn   = document.getElementById('btnStep2');

    const totalItems = cart.reduce((s, i) => s + i.qty, 0);
    count.textContent = totalItems;
    total.textContent = fmt(cartTotal());
    btn.disabled      = cart.length === 0;

    if (cart.length === 0) {
        body.innerHTML = '';
        body.appendChild(createEmpty());
        return;
    }
    body.innerHTML = '';
    cart.forEach(item => {
        const div = document.createElement('div');
        div.className = 'cart-item';
        div.innerHTML = `
            <div class="cart-item-info">
                <div class="cart-item-name">${item.productName}${item.variantName ? ' · ' + item.variantName : ''}</div>
                <div class="cart-item-price">${fmt(item.price)} × ${item.qty} = ${fmt(item.price * item.qty)}</div>
            </div>
            <div class="cart-item-qty">
                <button class="qty-btn" onclick="changeQty('${item.key}', -1)">−</button>
                <span class="qty-val">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty('${item.key}', 1)">+</button>
            </div>
            <span class="cart-remove" onclick="removeFromCart('${item.key}')"><i class="fas fa-times"></i></span>
        `;
        body.appendChild(div);
    });
}

function createEmpty() {
    const d = document.createElement('div');
    d.className = 'cart-empty';
    d.id = 'cartEmpty';
    d.innerHTML = '<i class="fas fa-cart-plus" style="font-size:1.5rem;display:block;margin-bottom:.5rem;color:#d1d5db;"></i>Panier vide';
    return d;
}

/* ════════════════════════════════════════════════════════════
   Catalog search
   ════════════════════════════════════════════════════════════ */
function filterProducts(q) {
    const term = q.toLowerCase();
    document.querySelectorAll('#productGrid .w-product-card').forEach(card => {
        const matches = card.dataset.name.includes(term);
        card.style.display = matches ? '' : 'none';
    });
}

/* ════════════════════════════════════════════════════════════
   Step 2: Payment
   ════════════════════════════════════════════════════════════ */
function setOrderType(type) {
    orderType = type;
    document.getElementById('typeComptant').classList.toggle('active', type === 'cash');
    document.getElementById('typeCredit').classList.toggle('active',   type === 'credit');
    document.getElementById('creditSection').style.display = type === 'credit' ? '' : 'none';

    // Mettre à jour notice + bouton selon type
    const isCash = type === 'cash';
    document.getElementById('noticeText').textContent = isCash
        ? 'Vous serez redirigé vers Djomy pour effectuer le paiement. La commande sera confirmée automatiquement.'
        : 'Votre commande sera soumise à votre responsable pour validation avant tout traitement.';
    document.getElementById('noticeBox').style.background = isCash ? '#f0fdfa' : '';
    document.getElementById('noticeBox').style.borderColor = isCash ? '#99f6e4' : '';
    document.getElementById('noticeBox').style.color = isCash ? '#065f46' : '';
    document.getElementById('submitLabel').textContent = isCash ? 'Continuer vers le paiement' : 'Soumettre la commande';
    document.getElementById('submitBtn').style.background = isCash ? '#00897B' : '';
}

function updateCreditPreview() {
    const total      = cartTotal();
    const down       = parseFloat(document.getElementById('downPaymentInput').value || 0);
    const months     = parseInt(document.getElementById('installmentsInput').value || 3);
    const remaining  = Math.max(0, total - down);
    const monthly    = months > 0 ? remaining / months : 0;
    const preview    = document.getElementById('creditPreview');

    if (total > 0) {
        preview.style.display = '';
        preview.innerHTML = `
            <strong>Estimation :</strong>
            Acompte <strong>${fmt(down)}</strong> +
            <strong>${months} × ${fmt(monthly)}</strong>/mois
            = <strong>${fmt(total)}</strong> total
        `;
    } else {
        preview.style.display = 'none';
    }
}

/* ════════════════════════════════════════════════════════════
   Step 3: Confirmation render
   ════════════════════════════════════════════════════════════ */
function renderConfirmation() {
    // Items
    const tbody = document.getElementById('confirmItems');
    tbody.innerHTML = '';
    cart.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.productName}${item.variantName ? ' <small style="color:var(--text-muted);">· ' + item.variantName + '</small>' : ''}</td>
            <td style="text-align:right;white-space:nowrap;">${item.qty} × ${fmt(item.price)}</td>
            <td style="text-align:right;font-weight:600;white-space:nowrap;">${fmt(item.price * item.qty)}</td>
        `;
        tbody.appendChild(tr);
    });
    document.getElementById('confirmTotal').textContent = fmt(cartTotal());

    // Payment
    const payEl = document.getElementById('confirmPayment');
    if (orderType === 'credit') {
        const down   = parseFloat(document.getElementById('downPaymentInput').value || 0);
        const months = parseInt(document.getElementById('installmentsInput').value || 3);
        const remaining = Math.max(0, cartTotal() - down);
        const monthly   = months > 0 ? remaining / months : 0;
        payEl.innerHTML = `
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;">
                <span style="background:#ede9fe;color:#7c3aed;border-radius:5px;padding:3px 8px;font-weight:700;font-size:13px;">Crédit</span>
            </div>
            <div style="font-size:13px;line-height:1.7;">
                Acompte : <strong>${fmt(down)}</strong><br>
                Mensualités : <strong>${months} × ${fmt(monthly)}</strong>
            </div>
        `;
    } else {
        payEl.innerHTML = `<span style="font-size:13.5px;">Comptant – paiement Djomy</span>`;
    }
}

/* ════════════════════════════════════════════════════════════
   Build hidden form fields before submit
   ════════════════════════════════════════════════════════════ */
function buildFormFields() {
    const container = document.getElementById('formHiddenFields');
    container.innerHTML = '';

    const add = (name, value) => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = name;
        inp.value = value;
        container.appendChild(inp);
    };

    cart.forEach((item, i) => {
        add(`items[${i}][product_id]`, item.productId);
        add(`items[${i}][quantity]`,   item.qty);
        if (item.variantId) add(`items[${i}][variant_id]`, item.variantId);
    });

    add('order_type', orderType);

    if (orderType === 'credit') {
        const down   = document.getElementById('downPaymentInput').value || 0;
        const months = document.getElementById('installmentsInput').value || 3;
        add('down_payment',               down);
        add('credit_installments_count',  months);
    }
}

/* ════════════════════════════════════════════════════════════
   Step navigation
   ════════════════════════════════════════════════════════════ */
function goStep(step) {
    if (step === 2 && cart.length === 0) return;

    if (step === 2) {
        document.getElementById('step2Total').textContent = fmt(cartTotal());
        updateCreditPreview();
    }

    if (step === 3) {
        renderConfirmation();
        buildFormFields();
    }

    // Update panels
    ['panel-1', 'panel-2', 'panel-3'].forEach((id, idx) => {
        const panel = document.getElementById(id);
        if (id === 'panel-1') {
            panel.classList.toggle('active', step === 1);
        } else {
            panel.classList.toggle('active', step === (idx + 1));
        }
    });

    // Update stepper
    [1, 2, 3].forEach(i => {
        const ind = document.getElementById('step-ind-' + i);
        ind.classList.remove('active', 'done');
        if (i < step) ind.classList.add('done');
        if (i === step) ind.classList.add('active');
    });

    currentStep = step;
}

/* ════════════════════════════════════════════════════════════
   Formatting helper
   ════════════════════════════════════════════════════════════ */
function fmt(n) {
    return Math.round(n).toLocaleString('fr-FR') + ' GNF';
}

/* ════════════════════════════════════════════════════════════
   Init
   ════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    renderCart();

    // Form submit: rebuild fields (safety net)
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        buildFormFields();
        if (cart.length === 0) { e.preventDefault(); return; }
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Envoi…';
    });

    // Pre-add product from URL
    if (urlProduct) {
        const card = document.querySelector(`[data-id="${urlProduct}"]`);
        if (card) {
            const btn = card.querySelector('.btn-add-cart');
            if (btn) btn.click();
        }
    }
});
</script>
@endsection
