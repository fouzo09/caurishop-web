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
        <div class="col-sm-6 col-lg-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-bag-check"></i></span>
            <div><div class="acct-stat__label">Commandes</div><div class="acct-stat__value">{{ $ordersCount }}</div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-telephone"></i></span>
            <div><div class="acct-stat__label">Téléphone</div><div class="acct-stat__value">{{ $customer->phone ?? '—' }}</div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-envelope"></i></span>
            <div class="overflow-hidden"><div class="acct-stat__label">E-mail</div><div class="acct-stat__value text-truncate">{{ $customer->email ?? auth()->user()->email }}</div></div>
          </div>
        </div>
      </div>

      <div class="panel">
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
          <div class="fs-6 fw-bold text-ink"><i class="bi bi-clock-history text-brand me-1"></i> Dernières commandes</div>
          <a href="{{ route('shop.account.orders') }}" class="section-link">Tout voir →</a>
        </div>
        @forelse ($recentOrders as $order)
          <a href="{{ route('shop.account.orders.show', $order->id) }}" class="acct-order text-decoration-none">
            <span class="acct-order__ic"><i class="bi bi-box-seam"></i></span>
            <div class="flex-grow-1">
              <div class="fw-bold text-ink">#{{ $order->order_number }}</div>
              <div class="small text-muted">{{ $order->created_at?->translatedFormat('d M Y') }}</div>
            </div>
            @include('shop.account._status', ['status' => $order->status])
            <span class="fw-bold text-brand ms-2">@gnf($order->netTotal())</span>
          </a>
        @empty
          <div class="p-5 text-center text-muted">
            <div class="mb-2"><i class="bi bi-bag-x" style="font-size:38px;"></i></div>
            Aucune commande pour le moment.
            <div class="mt-3"><a href="{{ route('shop.products.index') }}" class="btn btn-brand btn-sm"><i class="bi bi-shop me-1"></i> Découvrir la boutique</a></div>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
