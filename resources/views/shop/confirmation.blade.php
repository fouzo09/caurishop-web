@extends('shop.layouts.app')

@section('title', 'Commande confirmée — CAURISHOP')

@section('content')
@php
    $paid = $order->payment_status === \App\Payments\PaymentResult::PAID;
    $statusLabels = [
        \App\Models\Order::STATUS_CONFIRMED       => 'Confirmée',
        \App\Models\Order::STATUS_PENDING_PAYMENT  => 'En attente de paiement',
        \App\Models\Order::STATUS_COMPLETED        => 'Livrée',
    ];
@endphp
<div class="container-xl py-5" style="max-width:860px;">
  <div class="panel overflow-hidden shadow-card">
    <div class="text-center p-5 border-bottom">
      <div class="ok-badge">✓</div>
      <h1 class="fw-bold text-ink mb-2" style="font-size:30px;">Merci pour votre commande ! 🎉</h1>
      <p class="text-muted mb-0">Votre commande <strong class="text-brand">#{{ $order->order_number }}</strong> a bien été enregistrée.</p>
    </div>
    <div class="p-4 p-md-5">
      <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="tile"><div class="small text-muted">Livraison</div><div class="fw-bold text-ink mt-1">{{ $order->delivery_method === 'express' ? 'Express (jour même)' : 'Standard (24h – 4 jours)' }}</div></div></div>
        <div class="col-md-6"><div class="tile"><div class="small text-muted">Paiement</div><div class="fw-bold text-ink mt-1">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }} · {{ $paid ? 'Payé' : 'En attente' }}</div></div></div>
      </div>

      <div class="d-flex mb-4">
        <div class="ostep flex-fill"><span class="ostep__dot ostep__dot--done">✓</span><span class="ostep__label">{{ $statusLabels[$order->status] ?? 'Enregistrée' }}</span></div>
        <div class="ostep flex-fill"><span class="ostep__dot ostep__dot--current">●</span><span class="ostep__label">En préparation</span></div>
        <div class="ostep flex-fill"><span class="ostep__dot">○</span><span class="ostep__label">Expédiée</span></div>
        <div class="ostep flex-fill"><span class="ostep__dot">○</span><span class="ostep__label">Livrée</span></div>
      </div>

      <div class="panel mb-4">
        @foreach ($order->items as $item)
          @php $cover = $item->product?->coverUrl(); @endphp
          <div class="d-flex align-items-center gap-3 p-3 border-bottom m-0">
            <span class="ck-thumb"><span>🛍️</span>@if ($cover)<img class="mini__img" src="{{ $cover }}" alt="{{ $item->product?->name }}" loading="lazy" onerror="this.remove()">@endif</span>
            <span class="flex-grow-1 small text-ink">{{ $item->product?->name }} @if ($item->variant)<span class="text-muted">— {{ $item->variant->name }}</span>@endif <span class="text-muted">×{{ $item->quantity }}</span></span>
            <span class="fw-bold small text-brand text-nowrap">@gnf($item->line_total)</span>
          </div>
        @endforeach
        @if ($order->discount_amount > 0)
          <div class="d-flex justify-content-between p-3 small border-bottom"><span class="text-muted">Remise</span><span class="text-brand">− @gnf($order->discount_amount)</span></div>
        @endif
        @if ($order->delivery_fee > 0)
          <div class="d-flex justify-content-between p-3 small border-bottom"><span class="text-muted">Livraison</span><span>@gnf($order->delivery_fee)</span></div>
        @endif
        <div class="d-flex justify-content-between p-3 bg-surface">
          <span class="fw-bold">Total {{ $paid ? 'payé' : 'à payer' }}</span>
          <span class="total-amount">@gnf($order->netTotal())</span>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('shop.account.orders') }}" class="btn btn-brand btn-lg flex-fill">Suivre mes commandes</a>
        <a href="{{ route('shop.products.index') }}" class="btn btn-soft btn-lg flex-fill">Continuer mes achats</a>
      </div>
    </div>
  </div>
</div>
@endsection
