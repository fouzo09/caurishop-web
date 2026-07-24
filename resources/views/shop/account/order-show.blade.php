@extends('shop.layouts.app')

@section('title', 'Commande #' . $order->order_number . ' — CAURISHOP')

@section('content')
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('shop.account.orders') }}">Mes commandes</a> › <span class="crumb-current">#{{ $order->order_number }}</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">Commande #{{ $order->order_number }}</h1>
  </div>
</div>

<div class="container-xl py-4">
  <div class="row g-4">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'orders'])</aside>

    <div class="col-lg-9">
      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="tile"><div class="small text-muted">Statut</div><div class="fw-bold text-ink mt-1">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</div></div></div>
        <div class="col-md-4"><div class="tile"><div class="small text-muted">Paiement</div><div class="fw-bold text-ink mt-1">{{ ucfirst(str_replace('_', ' ', (string) $order->payment_method)) }} · {{ $order->payment_status === 'paid' ? 'Payé' : 'En attente' }}</div></div></div>
        <div class="col-md-4"><div class="tile"><div class="small text-muted">Date</div><div class="fw-bold text-ink mt-1">{{ $order->created_at?->translatedFormat('d M Y') }}</div></div></div>
      </div>

      <div class="panel mb-4">
        @foreach ($order->items as $item)
          <div class="d-flex align-items-center gap-3 p-3 border-bottom m-0">
            <span class="flex-grow-1 small text-ink">{{ $item->product?->name }} @if ($item->variant)<span class="text-muted">— {{ $item->variant->name }}</span>@endif <span class="text-muted">×{{ $item->quantity }}</span></span>
            <span class="fw-bold small text-brand text-nowrap">@gnf($item->line_total)</span>
          </div>
        @endforeach
        <div class="d-flex justify-content-between p-3 small"><span class="text-muted">Sous-total</span><span>@gnf($order->total_amount)</span></div>
        @if ($order->discount_amount > 0)<div class="d-flex justify-content-between px-3 small"><span class="text-muted">Remise</span><span class="text-brand">− @gnf($order->discount_amount)</span></div>@endif
        @if ($order->delivery_fee > 0)<div class="d-flex justify-content-between px-3 small"><span class="text-muted">Livraison</span><span>@gnf($order->delivery_fee)</span></div>@endif
        <div class="d-flex justify-content-between p-3 bg-surface"><span class="fw-bold">Total</span><span class="total-amount">@gnf($order->netTotal())</span></div>
      </div>

      <div class="panel p-4">
        <div class="fs-5 fw-bold text-ink mb-2">Livraison</div>
        <div class="small text-muted">
          {{ $order->shipping_name }}<br>
          {{ $order->shipping_phone }}<br>
          {{ $order->shipping_address }}, {{ $order->shipping_city }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
