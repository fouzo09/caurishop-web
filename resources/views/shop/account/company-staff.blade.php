@extends('shop.layouts.app')

@section('title', 'Salariés — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'company.staff'])</aside>

    <div class="col-lg-9">
      <div class="d-flex align-items-baseline justify-content-between mb-3 flex-wrap gap-2">
        <h1 class="fw-bolder m-0" style="font-size:22px">Salariés</h1>
        <span class="text-muted" style="font-size:13.5px">{{ $employees->count() }} rattaché{{ $employees->count() > 1 ? 's' : '' }} à {{ $company->name }}</span>
      </div>

      @if ($employees->isEmpty())
        <div class="empty-state">
          <i class="bi bi-people"></i>
          Aucun salarié rattaché à votre entreprise pour le moment.
          <div class="mt-3"><a href="{{ route('shop.contact') }}" class="btn-brand btn-sm">Demander un rattachement</a></div>
        </div>
      @else
        <div class="border rounded-3 overflow-hidden">
          @foreach ($employees as $employee)
            @php
              $c = $employee->customer;
              $initials = strtoupper(mb_substr($c->first_name ?? $employee->name, 0, 1) . mb_substr($c->last_name ?? '', 0, 1));
            @endphp
            <div class="acct-order">
              <span class="acct-avatar" style="width:42px;height:42px;font-size:14px">{{ $initials ?: '?' }}</span>

              <div class="flex-grow-1" style="min-width:160px">
                <div class="fw-bold" style="font-size:14.5px">{{ $employee->name }}</div>
                <div class="text-muted" style="font-size:12.5px">
                  <i class="bi bi-envelope me-1"></i>{{ $employee->email }}
                  @if ($c?->phone) · <i class="bi bi-telephone me-1"></i>{{ $c->phone }}@endif
                </div>
              </div>

              <span class="st-pill st-pill--{{ $employee->is_active ? 'completed' : 'cancelled' }}">
                <i class="bi {{ $employee->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                {{ $employee->is_active ? 'Actif' : 'Désactivé' }}
              </span>

              <span class="text-end" style="min-width:130px">
                <span class="d-block text-muted" style="font-size:11.5px">Plafond</span>
                <span class="fw-bold" style="font-size:13.5px">{{ $c?->effectiveCreditLimit() ? \App\Support\Money::gnf($c->effectiveCreditLimit()) : '—' }}</span>
              </span>
            </div>
          @endforeach
        </div>

        <p class="text-muted mt-3 mb-0" style="font-size:12.5px">
          L'ajout et le retrait de salariés sont gérés par CAURISHOP.
          <a href="{{ route('shop.contact') }}" class="text-brand fw-semibold">Nous contacter</a>.
        </p>
      @endif
    </div>
  </div>
</main>
@endsection
