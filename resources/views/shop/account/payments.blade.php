@extends('shop.layouts.app')

@section('title', 'Mes échéances — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'payments'])</aside>

    <div class="col-lg-9">
      <h1 class="fw-bolder mb-3" style="font-size:22px">Mes échéances</h1>

      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-cash-coin"></i></span>
            <div><div class="acct-stat__value">@gnf($stats['total_paid'])</div><div class="acct-stat__label">Total réglé</div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-hourglass-split"></i></span>
            <div><div class="acct-stat__value">{{ $stats['pending_count'] }}</div><div class="acct-stat__label">Échéance{{ $stats['pending_count'] > 1 ? 's' : '' }} à venir</div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-exclamation-triangle"></i></span>
            <div><div class="acct-stat__value">{{ $stats['late_count'] }}</div><div class="acct-stat__label">En retard</div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-credit-card"></i></span>
            <div><div class="acct-stat__value">{{ $stats['credit_limit'] ? \App\Support\Money::gnf($stats['credit_limit']) : '—' }}</div><div class="acct-stat__label">Plafond de crédit</div></div>
          </div>
        </div>
      </div>

      {{-- Échéancier --}}
      <div class="border rounded-3 overflow-hidden mb-4">
        <div class="p-3 border-bottom fw-bolder" style="font-size:15px"><i class="bi bi-calendar3 text-brand me-1"></i> Échéancier</div>

        @if ($installments->isEmpty())
          <div class="empty-state border-0 py-5">
            <i class="bi bi-calendar-x"></i>
            Aucune échéance pour le moment.
          </div>
        @else
          <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size:13.5px">
              <thead class="text-muted" style="font-size:12px">
                <tr><th class="ps-3">Commande</th><th>Échéance</th><th>Date</th><th>Montant</th><th>Réglé</th><th>Statut</th><th></th></tr>
              </thead>
              <tbody>
                @foreach ($installments as $i)
                  @php
                    $remaining = (float) $i->amount_due - (float) $i->amount_paid;
                    $order = $i->creditPlan?->order;
                  @endphp
                  <tr>
                    <td class="ps-3 fw-semibold">{{ $order?->order_number ?? '—' }}</td>
                    <td>n° {{ $i->installment_number }}</td>
                    <td>{{ $i->due_date?->translatedFormat('d M Y') }}</td>
                    <td>@gnf($i->amount_due)</td>
                    <td>@gnf($i->amount_paid)</td>
                    <td>@include('shop.account._status', ['status' => $i->status])</td>
                    <td class="text-end pe-3">
                      @if ($remaining > 0)
                        <a href="{{ route('shop.account.payments.pay', $i->id) }}" class="btn-brand btn-sm">Payer</a>
                      @else
                        <span class="text-muted" style="font-size:12.5px">Soldée</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      @if ($installments instanceof \Illuminate\Contracts\Pagination\Paginator && $installments->hasPages())
        <nav class="mb-4">{{ $installments->links('pagination::bootstrap-5') }}</nav>
      @endif

      {{-- Derniers paiements --}}
      <div class="border rounded-3 overflow-hidden">
        <div class="p-3 border-bottom fw-bolder" style="font-size:15px"><i class="bi bi-receipt text-brand me-1"></i> Derniers paiements</div>

        @if ($payments->isEmpty())
          <div class="empty-state border-0 py-5">
            <i class="bi bi-wallet2"></i>
            Aucun paiement enregistré.
          </div>
        @else
          @foreach ($payments as $p)
            <div class="acct-order">
              <span class="acct-order__ic"><i class="bi bi-check2-circle"></i></span>
              <div class="flex-grow-1" style="min-width:150px">
                <div class="fw-bold" style="font-size:14.5px">{{ $p->order?->order_number ?? 'Paiement' }}</div>
                <div class="text-muted" style="font-size:12.5px">{{ $p->payment_date?->translatedFormat('d M Y') }}</div>
              </div>
              <span class="fw-bold">@gnf($p->amount)</span>
            </div>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</main>
@endsection
