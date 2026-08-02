@extends('shop.layouts.app')

@section('title', 'Mon entreprise — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'company'])</aside>

    <div class="col-lg-9">
      <h1 class="fw-bolder mb-3" style="font-size:22px">{{ $company->name }}</h1>

      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-people"></i></span>
            <div><div class="acct-stat__value">{{ $stats['staff'] }}</div><div class="acct-stat__label">Salarié{{ $stats['staff'] > 1 ? 's' : '' }}</div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-receipt"></i></span>
            <div><div class="acct-stat__value">{{ $stats['orders'] }}</div><div class="acct-stat__label">Commande{{ $stats['orders'] > 1 ? 's' : '' }}</div></div>
          </div>
        </div>
        <div class="col-sm-6 col-lg-3">
          <a href="{{ route('shop.account.company.orders', ['status' => 'pending_approval']) }}" class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-hourglass-split"></i></span>
            <div><div class="acct-stat__value">{{ $stats['pending'] }}</div><div class="acct-stat__label">À approuver</div></div>
          </a>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="acct-stat">
            <span class="acct-stat__ic"><i class="bi bi-cash-stack"></i></span>
            <div><div class="acct-stat__value">@gnf($stats['spent'])</div><div class="acct-stat__label">Total commandé</div></div>
          </div>
        </div>
      </div>

      <div class="border rounded-3 p-4 mb-3">
        <div class="fw-bolder mb-3" style="font-size:16px"><i class="bi bi-building text-brand me-1"></i> Informations</div>
        <div class="spec-list">
          <div class="spec"><i class="bi bi-buildings"></i><span class="spec__k">Raison sociale</span><span class="spec__v">{{ $company->name }}</span></div>
          @if ($company->email)
            <div class="spec"><i class="bi bi-envelope"></i><span class="spec__k">E-mail</span><span class="spec__v">{{ $company->email }}</span></div>
          @endif
          @if ($company->phone)
            <div class="spec"><i class="bi bi-telephone"></i><span class="spec__k">Téléphone</span><span class="spec__v">{{ $company->phone }}</span></div>
          @endif
          @if ($company->address)
            <div class="spec"><i class="bi bi-geo-alt"></i><span class="spec__k">Adresse</span><span class="spec__v">{{ $company->address }}</span></div>
          @endif
          <div class="spec">
            <i class="bi bi-credit-card"></i><span class="spec__k">Plafond de crédit</span>
            <span class="spec__v">{{ $company->credit_limit ? \App\Support\Money::gnf($company->credit_limit) : 'Non défini' }}</span>
          </div>
        </div>
        <p class="text-muted mt-3 mb-0" style="font-size:12.5px">
          Ces informations sont gérées par CAURISHOP. <a href="{{ route('shop.contact') }}" class="text-brand fw-semibold">Nous contacter</a> pour toute correction.
        </p>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <a href="{{ route('shop.account.company.staff') }}" class="border rounded-3 p-4 d-block h-100">
            <div class="fw-bolder mb-1" style="font-size:15px"><i class="bi bi-people text-brand me-1"></i> Salariés</div>
            <div class="text-muted" style="font-size:13.5px">Voir les {{ $stats['staff'] }} salarié{{ $stats['staff'] > 1 ? 's' : '' }} rattaché{{ $stats['staff'] > 1 ? 's' : '' }} et leur plafond.</div>
          </a>
        </div>
        <div class="col-md-6">
          <a href="{{ route('shop.account.company.orders') }}" class="border rounded-3 p-4 d-block h-100">
            <div class="fw-bolder mb-1" style="font-size:15px"><i class="bi bi-receipt text-brand me-1"></i> Commandes entreprise</div>
            <div class="text-muted" style="font-size:13.5px">Suivre et approuver les commandes de vos salariés.</div>
          </a>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
