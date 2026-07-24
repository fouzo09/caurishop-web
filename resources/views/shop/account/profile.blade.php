@extends('shop.layouts.app')

@section('title', 'Mon profil — CAURISHOP')

@section('content')
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('shop.account.index') }}">Mon compte</a> › <span class="crumb-current">Profil</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">Mon profil &amp; adresse</h1>
  </div>
</div>

<div class="container-xl py-4">
  <div class="row g-4">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'profile'])</aside>

    <div class="col-lg-9">
      <div class="panel p-4" style="max-width:640px;">
        <form method="POST" action="{{ route('shop.account.profile.update') }}">
          @csrf
          @method('PUT')
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
            <div class="col-12">
              <label class="form-label">Adresse de livraison</label>
              <input name="address" value="{{ old('address', $customer->address ?? '') }}" class="form-control" placeholder="Quartier, rue, repère…">
            </div>
          </div>
          <button type="submit" class="btn btn-brand mt-4">Enregistrer</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
