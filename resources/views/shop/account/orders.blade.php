@extends('shop.layouts.app')

@section('title', 'Mes commandes — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'orders'])</aside>

    <div class="col-lg-9">
      <h1 class="fw-bolder mb-3" style="font-size:22px">Mes commandes</h1>

      @if ($orders->isEmpty())
        <div class="empty-state">
          <i class="bi bi-bag-x"></i>
          Aucune commande pour le moment.
          <div class="mt-3"><a href="{{ route('shop.products.index') }}" class="btn-brand btn-sm">Découvrir la boutique</a></div>
        </div>
      @else
        <div class="border rounded-3 overflow-hidden">
          @foreach ($orders as $order)
            <div class="acct-order">
              <span class="acct-order__ic"><i class="bi bi-box-seam"></i></span>
              <div class="flex-grow-1" style="min-width:160px">
                <div class="fw-bold" style="font-size:14.5px">{{ $order->order_number }}</div>
                <div class="text-muted" style="font-size:12.5px">
                  <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at?->translatedFormat('d M Y') }}
                  · {{ $order->items->count() }} article{{ $order->items->count() > 1 ? 's' : '' }}
                </div>
              </div>
              @include('shop.account._status', ['status' => $order->status])
              <span class="fw-bold">@gnf($order->netTotal())</span>
              <a href="{{ route('shop.account.orders.show', $order->id) }}" class="btn-outline-ink btn-sm"><i class="bi bi-eye me-1"></i>Détails</a>
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
