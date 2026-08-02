@extends('shop.layouts.app')

@section('title', 'Contact & aide — CAURISHOP')

@section('content')
<section class="text-center border-bottom py-5 px-4" style="background:#f7f7f7">
  <h1 class="fw-bolder m-0" style="font-size:30px">Une question ? Parlons-en.</h1>
  <p class="text-muted mx-auto mt-2 mb-0" style="font-size:15px;max-width:520px;line-height:1.6">
    Notre équipe vous répond du lundi au samedi, de 8h à 19h.
  </p>
</section>

<main class="container-xl py-4">
  <div class="row g-4 align-items-start">

    <div class="col-lg-8">
      <div class="border rounded-3 p-4">
        <div class="fw-bolder mb-3" style="font-size:17px">Envoyez-nous un message</div>
        <form method="POST" action="{{ route('shop.contact.send') }}">
          @csrf
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nom complet</label>
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
              <textarea name="message" rows="5" class="form-control @error('message') is-invalid @enderror" placeholder="Décrivez votre demande…">{{ old('message') }}</textarea>
              @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <button class="btn-brand w-100 mt-3" type="submit">Envoyer le message</button>
        </form>
      </div>
    </div>

    <div class="col-lg-4 d-flex flex-column gap-3">
      <div class="border rounded-3 p-4 d-flex flex-column gap-2" style="font-size:14px;color:#444">
        <span class="fw-bolder text-dark" style="font-size:15px">Nous contacter</span>
        <span><i class="bi bi-geo-alt text-brand me-2"></i>Kaloum, Conakry — Guinée</span>
        <span><i class="bi bi-telephone text-brand me-2"></i>+224 620 00 00 00</span>
        <span><i class="bi bi-envelope text-brand me-2"></i>bonjour@caurishop.gn</span>
        <span><i class="bi bi-clock text-brand me-2"></i>Lun – Sam, 8h – 19h</span>
      </div>

      <div class="border rounded-3 p-4">
        <span class="fw-bolder d-block mb-2" style="font-size:15px">Questions fréquentes</span>
        <div class="accordion accordion-flush" id="faq">
          @php
            $faq = [
                ['Où en est ma commande ?', "Rendez-vous dans « Mon compte › Mes commandes » : chaque commande affiche son statut en temps réel."],
                ['Quels sont les délais de livraison ?', "Conakry sous 24h en standard, 2 à 4 jours pour les autres préfectures. L'express livre le jour même à Conakry avant 12h."],
                ['Comment retourner un article ?', "Vous disposez de 7 jours après réception. Contactez-nous avec le numéro de commande, nous organisons le retour."],
                ['Comment vendre sur CAURISHOP ?', "Ouvrez un compte professionnel depuis la page « Démarrer » et notre équipe vous accompagne dans la mise en ligne."],
            ];
          @endphp
          @foreach ($faq as $i => $item)
            <div class="accordion-item">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed px-0" style="font-size:13.5px;box-shadow:none" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $i }}">
                  {{ $item[0] }}
                </button>
              </h2>
              <div id="faq-{{ $i }}" class="accordion-collapse collapse" data-bs-parent="#faq">
                <div class="accordion-body px-0 pt-0 text-muted" style="font-size:13px;line-height:1.6">{{ $item[1] }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="bg-brand-soft rounded-3 p-4 d-flex flex-column gap-2">
        <span class="fw-bolder" style="font-size:15px">Vendez sur CAURISHOP</span>
        <span class="text-muted" style="font-size:13.5px;line-height:1.6">Ouvrez votre boutique en ligne et touchez des clients dans tout le pays.</span>
        <a href="{{ route('get-started') }}" class="text-brand fw-bold" style="font-size:13.5px">Ouvrir un compte pro →</a>
      </div>
    </div>
  </div>
</main>
@endsection
