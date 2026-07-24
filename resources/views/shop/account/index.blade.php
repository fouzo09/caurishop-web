@extends('shop.layouts.app')

@section('title', 'Mon compte — CAURISHOP')

@section('content')
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('home') }}">Accueil</a> › <span class="crumb-current">Mon compte</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">Bonjour {{ $customer->first_name ?? auth()->user()->name }} 👋</h1>
  </div>
</div>

<div class="container-xl py-4">
  <div class="row g-4">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'index'])</aside>

    <div class="col-lg-9">
      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="tile"><div class="small text-muted">Commandes</div><div class="fw-bold text-ink mt-1 fs-4">{{ $ordersCount }}</div></div></div>
        <div class="col-md-4"><div class="tile"><div class="small text-muted">Téléphone</div><div class="fw-bold text-ink mt-1">{{ $customer->phone ?? '—' }}</div></div></div>
        <div class="col-md-4"><div class="tile"><div class="small text-muted">E-mail</div><div class="fw-bold text-ink mt-1 text-truncate">{{ $customer->email ?? auth()->user()->email }}</div></div></div>
      </div>

      <div class="panel p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="fs-5 fw-bold text-ink">Dernières commandes</div>
          <a href="{{ route('shop.account.orders') }}" class="section-link">Tout voir →</a>
        </div>
        @forelse ($recentOrders as $order)
          <div class="d-flex align-items-center justify-content-between border-bottom py-2">
            <div>
              <a href="{{ route('shop.account.orders.show', $order->id) }}" class="fw-bold text-ink text-decoration-none">#{{ $order->order_number }}</a>
              <div class="small text-muted">{{ $order->created_at?->translatedFormat('d M Y') }}</div>
            </div>
            <span class="fw-bold text-brand">@gnf($order->netTotal())</span>
          </div>
        @empty
          <p class="text-muted mb-0">Aucune commande pour le moment. <a href="{{ route('shop.products.index') }}">Découvrir la boutique →</a></p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
