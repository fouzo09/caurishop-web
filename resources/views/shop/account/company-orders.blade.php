@extends('shop.layouts.app')

@section('title', 'Commandes entreprise — CAURISHOP')

@section('content')
@php
    $filters = [
        null                => 'Toutes',
        'pending_approval'  => 'À approuver',
        'confirmed'         => 'Confirmées',
        'completed'         => 'Livrées',
        'cancelled'         => 'Annulées',
    ];
@endphp
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'company.orders'])</aside>

    <div class="col-lg-9">
      <div class="d-flex align-items-baseline justify-content-between mb-3 flex-wrap gap-2">
        <h1 class="fw-bolder m-0" style="font-size:22px">Commandes entreprise</h1>
        @if ($pending > 0)
          <span class="st-pill st-pill--pending"><i class="bi bi-hourglass-split"></i> {{ $pending }} en attente d'approbation</span>
        @endif
      </div>

      <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        @foreach ($filters as $value => $label)
          <a href="{{ route('shop.account.company.orders', $value ? ['status' => $value] : []) }}"
             class="cat-pill{{ $status === $value ? ' active' : '' }}">{{ $label }}</a>
        @endforeach
      </div>

      @if ($orders->isEmpty())
        <div class="empty-state">
          <i class="bi bi-receipt"></i>
          Aucune commande{{ $status ? ' avec ce statut' : '' }} pour le moment.
        </div>
      @else
        <div class="border rounded-3 overflow-hidden">
          @foreach ($orders as $order)
            <div class="acct-order">
              <span class="acct-order__ic"><i class="bi bi-box-seam"></i></span>

              <div class="flex-grow-1" style="min-width:170px">
                <div class="fw-bold" style="font-size:14.5px">{{ $order->order_number }}</div>
                <div class="text-muted" style="font-size:12.5px">
                  <i class="bi bi-person me-1"></i>{{ trim(($order->customer->first_name ?? '') . ' ' . ($order->customer->last_name ?? '')) ?: '—' }}
                  · {{ $order->created_at?->translatedFormat('d M Y') }}
                  · {{ $order->items->count() }} article{{ $order->items->count() > 1 ? 's' : '' }}
                </div>
              </div>

              @include('shop.account._status', ['status' => $order->status])
              <span class="fw-bold">@gnf($order->netTotal())</span>

              <div class="d-flex align-items-center gap-2">
                @if ($order->status === \App\Models\Order::STATUS_PENDING_APPROVAL)
                  <form method="POST" action="{{ route('shop.account.company.orders.approve', $order->id) }}">
                    @csrf
                    <button type="submit" class="btn-brand btn-sm"><i class="bi bi-check2 me-1"></i>Approuver</button>
                  </form>
                  <form method="POST" action="{{ route('shop.account.company.orders.reject', $order->id) }}">
                    @csrf
                    <button type="submit" class="btn-danger-soft btn-sm"><i class="bi bi-x me-1"></i>Rejeter</button>
                  </form>
                @endif
                <a href="{{ route('shop.account.company.orders.show', $order->id) }}" class="btn-outline-ink btn-sm"><i class="bi bi-eye me-1"></i>Détails</a>
              </div>
            </div>
          @endforeach
        </div>

        @if ($orders->hasPages())
          <nav class="mt-4">{{ $orders->links('pagination::bootstrap-5') }}</nav>
        @endif
      @endif
    </div>
  </div>
</main>
@endsection
