@extends('shop.layouts.app')

@section('title', 'Mes commandes — CAURISHOP')

@section('content')
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('shop.account.index') }}">Mon compte</a> › <span class="crumb-current">Mes commandes</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">Mes commandes</h1>
  </div>
</div>

<div class="container-xl py-4">
  <div class="row g-4">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'orders'])</aside>

    <div class="col-lg-9">
      <div class="panel">
        @forelse ($orders as $order)
          <div class="acct-order">
            <span class="acct-order__ic"><i class="bi bi-box-seam"></i></span>
            <div class="flex-grow-1">
              <div class="fw-bold text-ink">#{{ $order->order_number }}</div>
              <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i>{{ $order->created_at?->translatedFormat('d M Y') }} · {{ $order->items->count() }} article(s)</div>
            </div>
            @include('shop.account._status', ['status' => $order->status])
            <span class="fw-bold text-brand ms-2">@gnf($order->netTotal())</span>
            <a href="{{ route('shop.account.orders.show', $order->id) }}" class="btn btn-sm btn-soft"><i class="bi bi-eye me-1"></i>Détails</a>
          </div>
        @empty
          <div class="p-5 text-center text-muted">
            <div class="mb-2"><i class="bi bi-bag-x" style="font-size:38px;"></i></div>
            Aucune commande pour le moment.
            <div class="mt-3"><a href="{{ route('shop.products.index') }}" class="btn btn-brand btn-sm"><i class="bi bi-shop me-1"></i> Découvrir la boutique</a></div>
          </div>
        @endforelse
      </div>

      @if ($orders->hasPages())
        <nav class="mt-4">{{ $orders->links('pagination::bootstrap-5') }}</nav>
      @endif
    </div>
  </div>
</div>
@endsection
