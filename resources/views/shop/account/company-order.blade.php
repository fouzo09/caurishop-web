@extends('shop.layouts.app')

@section('title', 'Commande ' . $order->order_number . ' — CAURISHOP')

@section('content')
<div class="breadcrumb-bar">
  <div class="container-xl d-flex align-items-center gap-2 py-2 px-3 flex-wrap">
    <a href="{{ route('shop.account.index') }}">Mon compte</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <a href="{{ route('shop.account.company.orders') }}">Commandes entreprise</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <span class="fw-semibold text-dark">{{ $order->order_number }}</span>
  </div>
</div>

<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'company.orders'])</aside>

    <div class="col-lg-9">
      <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h1 class="fw-bolder m-0" style="font-size:22px">Commande {{ $order->order_number }}</h1>

        @if ($order->status === \App\Models\Order::STATUS_PENDING_APPROVAL)
          <div class="d-flex gap-2">
            <form method="POST" action="{{ route('shop.account.company.orders.approve', $order->id) }}">
              @csrf
              <button type="submit" class="btn-brand btn-sm"><i class="bi bi-check2 me-1"></i>Approuver</button>
            </form>
            <form method="POST" action="{{ route('shop.account.company.orders.reject', $order->id) }}">
              @csrf
              <button type="submit" class="btn-danger-soft btn-sm"><i class="bi bi-x me-1"></i>Rejeter</button>
            </form>
          </div>
        @endif
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-person"></i></span>
            <div class="overflow-hidden">
              <div class="acct-stat__label">Salarié</div>
              <div class="acct-stat__value text-truncate" style="font-size:15px">{{ trim(($order->customer->first_name ?? '') . ' ' . ($order->customer->last_name ?? '')) ?: '—' }}</div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-truck"></i></span>
            <div><div class="acct-stat__label">Statut</div><div class="mt-1">@include('shop.account._status', ['status' => $order->status])</div></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-calendar3"></i></span>
            <div><div class="acct-stat__label">Date</div><div class="acct-stat__value" style="font-size:15px">{{ $order->created_at?->translatedFormat('d M Y') }}</div></div>
          </div>
        </div>
      </div>

      <div class="border rounded-3 overflow-hidden mb-4">
        <div class="p-3 border-bottom fw-bolder" style="font-size:15px"><i class="bi bi-box-seam text-brand me-1"></i> Articles</div>
        @foreach ($order->items as $item)
          <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <span class="flex-grow-1" style="font-size:13.5px">
              {{ $item->product?->name }}@if ($item->variant)<span class="text-muted"> — {{ $item->variant->name }}</span>@endif
              <span class="text-muted">× {{ $item->quantity }}</span>
            </span>
            <span class="fw-semibold text-nowrap" style="font-size:13.5px">@gnf($item->line_total)</span>
          </div>
        @endforeach
        <div class="d-flex justify-content-between p-3 bg-brand-soft fw-bolder" style="font-size:15px">
          <span>Total</span><span class="total-amount">@gnf($order->netTotal())</span>
        </div>
      </div>

      @if ($order->creditPlan)
        <div class="border rounded-3 overflow-hidden">
          <div class="p-3 border-bottom fw-bolder" style="font-size:15px"><i class="bi bi-calendar3 text-brand me-1"></i> Échéancier</div>
          <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:13.5px">
              <thead class="text-muted" style="font-size:12px">
                <tr><th class="ps-3">Échéance</th><th>Date</th><th>Montant</th><th>Réglé</th><th class="pe-3">Statut</th></tr>
              </thead>
              <tbody>
                @foreach ($order->creditPlan->installments as $i)
                  <tr>
                    <td class="ps-3 fw-semibold">n° {{ $i->installment_number }}</td>
                    <td>{{ $i->due_date?->translatedFormat('d M Y') }}</td>
                    <td>@gnf($i->amount_due)</td>
                    <td>@gnf($i->amount_paid)</td>
                    <td class="pe-3">@include('shop.account._status', ['status' => $i->status])</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif
    </div>
  </div>
</main>
@endsection
