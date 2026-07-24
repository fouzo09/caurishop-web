@extends('shop.layouts.app')

@section('title', 'Contact & aide — CAURISHOP')

@section('content')
<div class="page-banner border-bottom">
  <div class="container-xl py-4">
    <div class="crumbs mb-2"><a href="{{ route('home') }}">Accueil</a> › <span class="crumb-current">Contact</span></div>
    <span class="title-tick"></span>
    <h1 class="page-title mb-0">Contact &amp; aide</h1>
  </div>
</div>

<div class="container-xl py-4">
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="panel p-4 h-100">
        <div class="fs-5 fw-bold text-ink mb-3">Nous joindre</div>
        <div class="d-flex flex-column gap-3 text-muted">
          <div class="d-flex align-items-center gap-3"><span class="fs-4">📍</span> Kaloum, Conakry — Guinée</div>
          <div class="d-flex align-items-center gap-3"><span class="fs-4">📞</span> +224 620 00 00 00</div>
          <div class="d-flex align-items-center gap-3"><span class="fs-4">✉️</span> bonjour@caurishop.gn</div>
          <div class="d-flex align-items-center gap-3"><span class="fs-4">🕒</span> Lun – Sam, 8h – 19h</div>
        </div>
        <hr class="my-4">
        <p class="text-muted small mb-0">Besoin d'aide pour une commande ? Indiquez son numéro (#CAU-…) dans votre message pour un traitement plus rapide.</p>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="panel p-4">
        <div class="fs-5 fw-bold text-ink mb-3">Envoyez-nous un message</div>
        <form method="POST" action="{{ route('shop.contact.send') }}">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nom</label>
              <input name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Votre nom">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">E-mail</label>
              <input name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="vous@exemple.com">
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
              <label class="form-label">Message</label>
              <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" placeholder="Comment pouvons-nous vous aider ?">{{ old('message') }}</textarea>
              @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <button type="submit" class="btn btn-brand mt-4">Envoyer le message</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
