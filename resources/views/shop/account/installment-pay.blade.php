@extends('shop.layouts.app')

@section('title', 'Payer une échéance — CAURISHOP')

@section('content')
@php
    $order     = $installment->creditPlan?->order;
    $remaining = (float) $installment->amount_due - (float) $installment->amount_paid;
    $late      = $installment->status === \App\Models\Installment::STATUS_LATE;
@endphp

<div class="breadcrumb-bar">
  <div class="container-xl d-flex align-items-center gap-2 py-2 px-3 flex-wrap">
    <a href="{{ route('shop.account.index') }}">Mon compte</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <a href="{{ route('shop.account.payments') }}">Mes échéances</a>
    <i class="bi bi-chevron-right" style="font-size:11px"></i>
    <span class="fw-semibold text-dark">Échéance n° {{ $installment->installment_number }}</span>
  </div>
</div>

<main class="container-xl py-4">
  <div class="row g-4 align-items-start justify-content-center">
    <div class="col-lg-7 col-xl-6">

      <div class="border rounded-3 p-4">
        <div class="d-flex align-items-center gap-3 mb-4">
          <span class="acct-stat__ic" style="width:52px;height:52px;font-size:22px"><i class="bi bi-calendar-check"></i></span>
          <div>
            <h1 class="fw-bolder m-0" style="font-size:20px">Échéance n° {{ $installment->installment_number }}</h1>
            <div class="text-muted" style="font-size:13.5px">Commande {{ $order?->order_number }}</div>
          </div>
        </div>

        @if ($late)
          <div class="pay-option mb-3" style="border-color:var(--danger);background:var(--danger-soft);color:var(--danger)">
            <i class="bi bi-exclamation-triangle fs-6"></i>
            <span class="flex-grow-1 fw-semibold">Cette échéance est en retard. Réglez-la au plus vite.</span>
          </div>
        @endif

        <div class="spec-list mb-4">
          <div class="spec"><i class="bi bi-calendar3"></i><span class="spec__k">Date d'échéance</span><span class="spec__v">{{ $installment->due_date?->translatedFormat('d F Y') }}</span></div>
          <div class="spec"><i class="bi bi-cash"></i><span class="spec__k">Montant dû</span><span class="spec__v">@gnf($installment->amount_due)</span></div>
          @if ((float) $installment->amount_paid > 0)
            <div class="spec"><i class="bi bi-check2"></i><span class="spec__k">Déjà réglé</span><span class="spec__v">@gnf($installment->amount_paid)</span></div>
          @endif
          <div class="spec"><i class="bi bi-telephone"></i><span class="spec__k">Numéro à débiter</span><span class="spec__v">{{ $customer->phone ?: 'Non renseigné' }}</span></div>
        </div>

        <div class="d-flex align-items-baseline justify-content-between border-top pt-3 mb-4">
          <span class="fw-bolder" style="font-size:16px">Reste à payer</span>
          <span class="total-amount">@gnf($remaining)</span>
        </div>

        @if (empty($customer->phone))
          <div class="empty-state border-0 py-4 mb-3">
            <i class="bi bi-telephone-x"></i>
            Renseignez votre numéro de téléphone avant de payer.
            <div class="mt-3"><a href="{{ route('shop.account.profile') }}" class="btn-brand btn-sm">Compléter mon profil</a></div>
          </div>
        @else
          <form method="POST" action="{{ route('portal.djomy.checkout.initiate', $installment->id) }}">
            @csrf
            <button type="submit" class="btn-brand w-100 d-flex align-items-center justify-content-center gap-2">
              <i class="bi bi-shield-lock"></i>Payer @gnf($remaining)
            </button>
          </form>

          <div class="d-flex gap-2 flex-wrap justify-content-center mt-3">
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/om.svg') }}" alt="Orange Money" height="18"></span>
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/momo.svg') }}" alt="MTN MoMo" height="18"></span>
            <span class="pay-chip"><img src="{{ asset('shop/img/pay/card.svg') }}" alt="Carte bancaire" height="16"></span>
          </div>
          <div class="text-muted text-center mt-3" style="font-size:12px">
            <i class="bi bi-lock me-1"></i>Vous allez être redirigé vers la page sécurisée de notre partenaire de paiement.
          </div>
        @endif

        <div class="text-center mt-3">
          <a href="{{ route('shop.account.payments') }}" class="text-brand fw-semibold" style="font-size:13.5px">← Retour à mes échéances</a>
        </div>
      </div>

    </div>
  </div>
</main>
@endsection
