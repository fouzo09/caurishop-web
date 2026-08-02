@extends('shop.layouts.app')

@section('title', 'Commande confirmée — CAURISHOP')

@section('content')
@php
    $paid = $order->payment_status === \App\Payments\PaymentResult::PAID;

    // Étape atteinte dans le suivi : 0 = confirmée … 3 = livrée.
    $reached = match ($order->status) {
        \App\Models\Order::STATUS_COMPLETED => 3,
        \App\Models\Order::STATUS_CONFIRMED => 1,
        default                             => 0,
    };
    $steps = [
        ['bi-bag-check',   'Confirmée'],
        ['bi-box-seam',    'Préparation'],
        ['bi-truck',       'En route'],
        ['bi-house-check', 'Livrée'],
    ];
@endphp

<main class="mx-auto px-4 py-5 text-center d-flex flex-column align-items-center gap-3" style="max-width:720px">
  <span class="rounded-circle d-grid" style="width:72px;height:72px;background:#e8f5ee;color:var(--green);place-items:center;font-size:34px">
    <i class="bi bi-check-lg"></i>
  </span>

  <h1 class="fw-bolder m-0" style="font-size:28px">Merci, votre commande est enregistrée !</h1>
  <p class="text-muted m-0" style="font-size:15px;line-height:1.6;max-width:480px">
    Commande <span class="fw-bold text-dark">N° {{ $order->order_number }}</span>
    @if ($order->shipping_phone) — une confirmation a été envoyée au {{ $order->shipping_phone }}.@endif
    {{ $paid ? 'Paiement reçu.' : 'Paiement en attente de validation.' }}
  </p>

  {{-- Suivi --}}
  <div class="d-flex align-items-start w-100 my-3" style="font-size:12.5px">
    @foreach ($steps as $i => $step)
      @if ($i > 0)
        <span class="flex-fill {{ $i <= $reached ? 'bg-brand' : '' }}" style="height:2px;margin-top:16px;{{ $i <= $reached ? '' : 'background:#e4e4e4' }}"></span>
      @endif
      <div class="flex-fill d-flex flex-column align-items-center gap-2 {{ $i <= $reached ? 'text-brand fw-bold' : 'text-muted' }}">
        <span class="{{ $i <= $reached ? 'step-done' : 'step-todo' }}" style="width:34px;height:34px;font-size:15px"><i class="bi {{ $step[0] }}"></i></span>
        {{ $step[1] }}
      </div>
    @endforeach
  </div>

  {{-- Récapitulatif --}}
  <div class="border rounded-3 p-4 w-100 text-start d-flex flex-column gap-2">
    <span class="fw-bolder" style="font-size:15px">Récapitulatif</span>

    @foreach ($order->items as $item)
      <div class="d-flex justify-content-between gap-3" style="font-size:13.5px;color:#555">
        <span>{{ $item->product?->name }}@if ($item->variant) — {{ $item->variant->name }}@endif <span class="text-muted">× {{ $item->quantity }}</span></span>
        <span class="fw-semibold text-dark text-nowrap">@gnf($item->line_total)</span>
      </div>
    @endforeach

    @if ($order->discount_amount > 0)
      <div class="d-flex justify-content-between" style="font-size:13.5px;color:#555">
        <span>Remise</span><span class="fw-semibold text-brand">− @gnf($order->discount_amount)</span>
      </div>
    @endif

    <div class="d-flex justify-content-between" style="font-size:13.5px;color:#555">
      <span>Livraison — {{ $order->shipping_city }}</span>
      @if ($order->delivery_fee > 0)
        <span class="fw-semibold text-dark">@gnf($order->delivery_fee)</span>
      @else
        <span class="fw-semibold" style="color:var(--green)">Offerte</span>
      @endif
    </div>

    <div class="d-flex justify-content-between fw-bolder border-top pt-2" style="font-size:15px">
      <span>Total {{ $paid ? 'payé' : 'à payer' }}</span><span>@gnf($order->netTotal())</span>
    </div>

    <span class="text-muted d-flex align-items-center gap-2" style="font-size:12.5px">
      <i class="bi bi-truck text-brand"></i>
      {{ $order->delivery_method === 'express' ? 'Livraison express — le jour même à Conakry' : 'Livraison standard — Conakry 24h, régions 2 à 4 jours' }}
    </span>
  </div>

  <div class="d-flex gap-3 mt-2 flex-wrap justify-content-center">
    <a href="{{ route('shop.account.orders.show', $order->id) }}" class="btn-outline-ink">Suivre ma commande</a>
    <a href="{{ route('shop.products.index') }}" class="btn-brand">Continuer mes achats</a>
  </div>
</main>
@endsection
