@extends('shop.layouts.app')

@section('title', 'Mon profil — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'profile'])</aside>

    <div class="col-lg-9">
      <h1 class="fw-bolder mb-3" style="font-size:22px">Mon profil</h1>

      <form method="POST" action="{{ route('shop.account.profile.update') }}" style="max-width:680px">
        @csrf
        @method('PUT')

        <div class="border rounded-3 p-4 mb-3">
          <div class="fw-bolder mb-3" style="font-size:16px"><i class="bi bi-person-badge text-brand me-1"></i> Informations personnelles</div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Prénom</label>
              <input name="first_name" value="{{ old('first_name', $customer->first_name ?? '') }}" class="form-control @error('first_name') is-invalid @enderror">
              @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input name="last_name" value="{{ old('last_name', $customer->last_name ?? '') }}" class="form-control @error('last_name') is-invalid @enderror">
              @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Téléphone</label>
              <input name="phone" value="{{ old('phone', $customer->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror">
              @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">E-mail</label>
              <input name="email" type="email" value="{{ old('email', $customer->email ?? auth()->user()->email) }}" class="form-control @error('email') is-invalid @enderror">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <div class="border rounded-3 p-4 mb-3">
          <div class="d-flex align-items-baseline justify-content-between mb-3">
            <span class="fw-bolder" style="font-size:16px"><i class="bi bi-geo-alt text-brand me-1"></i> Adresse principale</span>
            <a href="{{ route('shop.account.addresses') }}" class="section-link">Gérer mon carnet d'adresses</a>
          </div>
          <label class="form-label">Adresse</label>
          <input name="address" value="{{ old('address', $customer->address ?? '') }}" class="form-control" placeholder="Quartier, rue, repère…">
        </div>

        <button type="submit" class="btn-brand"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
      </form>
    </div>
  </div>
</main>
@endsection
