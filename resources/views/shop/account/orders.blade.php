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
      <div class="panel p-0">
        @forelse ($orders as $order)
          <div class="d-flex align-items-center justify-content-between p-3 border-bottom flex-wrap gap-2">
            <div>
              <a href="{{ route('shop.account.orders.show', $order->id) }}" class="fw-bold text-ink text-decoration-none">#{{ $order->order_number }}</a>
              <div class="small text-muted">{{ $order->created_at?->translatedFormat('d M Y') }} · {{ $order->items->count() }} article(s)</div>
            </div>
            <span class="badge rounded-pill text-bg-light">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
            <span class="fw-bold text-brand">@gnf($order->netTotal())</span>
            <a href="{{ route('shop.account.orders.show', $order->id) }}" class="btn btn-sm btn-soft">Détails</a>
          </div>
        @empty
          <div class="p-5 text-center text-muted">
            <div class="fs-1 mb-2">📦</div>
            Aucune commande pour le moment.
            <div class="mt-2"><a href="{{ route('shop.products.index') }}" class="btn btn-brand btn-sm">Découvrir la boutique</a></div>
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
