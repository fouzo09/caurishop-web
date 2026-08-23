@extends('shop.layouts.app')

@section('title', 'Mon compte — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'index'])</aside>

    <div class="col-lg-9">
      <h1 class="fw-bolder mb-3" style="font-size:22px">Bonjour {{ $customer->first_name ?? auth()->user()->name }} 👋</h1>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-box-seam"></i></span>
            <div><div class="acct-stat__value">{{ $ordersCount }}</div><div class="acct-stat__label">Commande{{ $ordersCount > 1 ? 's' : '' }}</div></div>
          </div>
        </div>
        <div class="col-md-4">
          <a href="{{ route('shop.account.favorites') }}" class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-heart"></i></span>
            <div><div class="acct-stat__value">{{ $favoritesCount }}</div><div class="acct-stat__label">Favori{{ $favoritesCount > 1 ? 's' : '' }}</div></div>
          </a>
        </div>
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-telephone"></i></span>
            <div class="overflow-hidden"><div class="acct-stat__value text-truncate">{{ $customer->phone ?? '—' }}</div><div class="acct-stat__label">Téléphone</div></div>
          </div>
        </div>
      </div>

      <div class="border rounded-3 p-4 mb-4">
        <div class="d-flex align-items-baseline justify-content-between mb-3">
          <span class="fw-bolder" style="font-size:16px">Dernières commandes</span>
          <a href="{{ route('shop.account.orders') }}" class="text-brand fw-semibold" style="font-size:13px">Tout voir</a>
        </div>

        @if ($recentOrders->isEmpty())
          <div class="empty-state border-0 py-4">
            <i class="bi bi-bag-x"></i>
            Aucune commande pour le moment.
            <div class="mt-3"><a href="{{ route('shop.products.index') }}" class="btn-brand btn-sm">Découvrir la boutique</a></div>
          </div>
        @else
          <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:13.5px">
              <thead class="text-muted" style="font-size:12px">
                <tr><th>N° commande</th><th>Date</th><th>Articles</th><th>Total</th><th>Statut</th><th></th></tr>
              </thead>
              <tbody>
                @foreach ($recentOrders as $order)
                  <tr>
                    <td class="fw-semibold">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at?->translatedFormat('d M Y') }}</td>
                    <td>{{ $order->items->count() }} article{{ $order->items->count() > 1 ? 's' : '' }}</td>
                    <td>@gnf($order->netTotal())</td>
                    <td>@include('shop.account._status', ['status' => $order->status])</td>
                    <td class="text-end"><a href="{{ route('shop.account.orders.show', $order->id) }}" class="text-brand fw-semibold">Détails</a></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <div class="border rounded-3 p-4 h-100">
            <div class="d-flex align-items-baseline justify-content-between mb-2">
              <span class="fw-bolder" style="font-size:15px">Adresse par défaut</span>
              <a href="{{ route('shop.account.addresses') }}" class="text-brand fw-semibold" style="font-size:13px">{{ $defaultAddress ? 'Modifier' : 'Ajouter' }}</a>
            </div>
            @if ($defaultAddress)
              <div style="font-size:13.5px;color:#555;line-height:1.7">
                {{ $defaultAddress->full_name }}<br>
                {{ $defaultAddress->street() }}<br>
                {{ $defaultAddress->cityName() }} — Guinée<br>
                {{ $defaultAddress->phone }}
              </div>
            @else
              <div class="text-muted" style="font-size:13.5px">Aucune adresse enregistrée pour l'instant.</div>
            @endif
          </div>
        </div>

        <div class="col-md-6">
          <div class="bg-brand-soft rounded-3 p-4 h-100">
            <div class="fw-bolder mb-1" style="font-size:15px">Besoin d'aide ?</div>
            <div style="font-size:13.5px;color:#555;line-height:1.6">
              Une question sur une commande, une livraison ou un retour ? Notre équipe vous répond du lundi au samedi, 8h – 19h.
            </div>
            <a href="{{ route('shop.contact') }}" class="text-brand fw-bold d-inline-block mt-2" style="font-size:13.5px">Nous contacter →</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
