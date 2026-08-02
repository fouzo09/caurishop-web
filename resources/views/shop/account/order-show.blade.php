@extends('shop.layouts.app')

@section('title', 'Commande ' . $order->order_number . ' — CAURISHOP')

@section('content')
<div class="breadcrumb-bar">
  <div class="container-xl d-flex align-items-center gap-2 py-2 px-3">
    <a href="{{ route('shop.account.index') }}">Mon compte</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <a href="{{ route('shop.account.orders') }}">Mes commandes</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <span class="fw-semibold text-dark">{{ $order->order_number }}</span>
  </div>
</div>

<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'orders'])</aside>

    <div class="col-lg-9">
      <h1 class="fw-bolder mb-3" style="font-size:22px">Commande {{ $order->order_number }}</h1>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-truck"></i></span>
            <div><div class="acct-stat__label">Statut</div><div class="mt-1">@include('shop.account._status', ['status' => $order->status])</div></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-wallet2"></i></span>
            <div>
              <div class="acct-stat__label">Paiement</div>
              <div class="acct-stat__value" style="font-size:15px">
                @if ($order->payment_status === 'paid')
                  <span style="color:var(--green)"><i class="bi bi-check-circle-fill"></i> Payé</span>
                @else
                  <span style="color:var(--amber)"><i class="bi bi-clock-history"></i> En attente</span>
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-calendar3"></i></span>
            <div><div class="acct-stat__label">Date</div><div class="acct-stat__value" style="font-size:15px">{{ $order->created_at?->translatedFormat('d M Y') }}</div></div>
          </div>
        </div>
      </div>

      <div class="border rounded-3 overflow-hidden mb-4">
        <div class="p-3 border-bottom fw-bolder" style="font-size:15px"><i class="bi bi-box-seam text-brand me-1"></i> Articles</div>
        @foreach ($order->items as $item)
          <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <span class="flex-grow-1" style="font-size:13.5px">
              {{ $item->product?->name }}@if ($item->variant)<span class="text-muted"> — {{ $item->variant->name }}</span>@endif
              <span class="text-muted">× {{ $item->quantity }}</span>
            </span>
            <span class="fw-semibold text-nowrap" style="font-size:13.5px">@gnf($item->line_total)</span>
          </div>
        @endforeach

        <div class="d-flex justify-content-between p-3 border-bottom" style="font-size:13.5px;color:#555">
          <span>Sous-total</span><span class="fw-semibold text-dark">@gnf($order->total_amount)</span>
        </div>
        @if ($order->discount_amount > 0)
          <div class="d-flex justify-content-between px-3 pb-3" style="font-size:13.5px;color:#555">
            <span>Remise</span><span class="fw-semibold text-brand">− @gnf($order->discount_amount)</span>
          </div>
        @endif
        @if ($order->delivery_fee > 0)
          <div class="d-flex justify-content-between px-3 pb-3" style="font-size:13.5px;color:#555">
            <span>Livraison</span><span class="fw-semibold text-dark">@gnf($order->delivery_fee)</span>
          </div>
        @endif
        <div class="d-flex justify-content-between p-3 bg-brand-soft fw-bolder" style="font-size:15px">
          <span>Total</span><span class="total-amount">@gnf($order->netTotal())</span>
        </div>
      </div>

      <div class="border rounded-3 p-4">
        <div class="fw-bolder mb-3" style="font-size:15px"><i class="bi bi-geo-alt text-brand me-1"></i> Adresse de livraison</div>
        <div style="font-size:13.5px;color:#555;line-height:1.7">
          {{ $order->shipping_name }}<br>
          {{ $order->shipping_address }}<br>
          {{ $order->shipping_city }} — Guinée<br>
          {{ $order->shipping_phone }}
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
