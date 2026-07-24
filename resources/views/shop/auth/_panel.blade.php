@php
    $activeTab = $activeTab ?? 'login';
    if ($errors->hasAny(['first_name', 'last_name', 'phone', 'email'])) {
        $activeTab = 'register';
    } elseif ($errors->has('login')) {
        $activeTab = 'login';
    }
@endphp
<div class="container-xl py-5" style="max-width:1080px;">
  <div class="panel overflow-hidden shadow-card">
    <div class="row g-0">

      <div class="col-lg-6 brand-panel d-flex flex-column p-4 p-lg-5">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 mb-auto">
          <span class="logo__mark">C</span><span class="logo__name fs-5">CAURISHOP</span>
        </a>
        <div class="mt-5">
          <div style="font-size:72px;">🐚</div>
          <h2 class="fw-bold text-ink mt-2 mb-3" style="font-size:28px;">Bienvenue sur le marché de la Guinée</h2>
          <p class="text-muted mb-4">Suivez vos commandes, enregistrez vos adresses et profitez d'offres exclusives réservées aux membres.</p>
          <div class="d-flex flex-column gap-2 small">
            <span>✓ Paiement mobile money en 1 clic</span>
            <span>✓ Suivi de livraison en temps réel</span>
            <span>✓ Vos commandes centralisées</span>
          </div>
        </div>
      </div>

      <div class="col-lg-6 p-4 p-lg-5">
        <ul class="nav nav-pills auth-pills mb-4" role="tablist">
          <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link w-100 {{ $activeTab === 'login' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pane-login" type="button" role="tab">Connexion</button>
          </li>
          <li class="nav-item flex-fill" role="presentation">
            <button class="nav-link w-100 {{ $activeTab === 'register' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pane-register" type="button" role="tab">Inscription</button>
          </li>
        </ul>

        <div class="tab-content">
          {{-- CONNEXION --}}
          <div class="tab-pane fade {{ $activeTab === 'login' ? 'show active' : '' }}" id="pane-login" role="tabpanel">
            <h3 class="fw-bold text-ink mb-1" style="font-size:22px;">Content de vous revoir 👋</h3>
            <p class="small text-muted mb-4">Connectez-vous pour continuer vos achats.</p>
            <form method="POST" action="{{ route('shop.login.store') }}">
              @csrf
              <div class="mb-3">
                <label class="form-label">Téléphone ou e-mail</label>
                <input name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" placeholder="+224 6 00 00 00 00">
                @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input class="form-control" type="password" name="password" placeholder="••••••••">
              </div>
              <div class="d-flex justify-content-between align-items-center small mb-3">
                <label class="form-check d-flex align-items-center gap-2 m-0"><input class="form-check-input m-0" type="checkbox" name="remember"> Se souvenir de moi</label>
              </div>
              <button type="submit" class="btn btn-brand w-100 btn-lg">Se connecter</button>
            </form>
          </div>

          {{-- INSCRIPTION --}}
          <div class="tab-pane fade {{ $activeTab === 'register' ? 'show active' : '' }}" id="pane-register" role="tabpanel">
            <h3 class="fw-bold text-ink mb-1" style="font-size:22px;">Créer un compte</h3>
            <p class="small text-muted mb-4">Rejoignez CAURISHOP en moins d'une minute.</p>
            <form method="POST" action="{{ route('shop.register.store') }}">
              @csrf
              <div class="row g-2 mb-3">
                <div class="col">
                  <label class="form-label">Prénom</label>
                  <input name="first_name" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @enderror" placeholder="Aïssatou">
                  @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                  <label class="form-label">Nom</label>
                  <input name="last_name" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @enderror" placeholder="Diallo">
                  @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Téléphone</label>
                <input name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="+224 6 00 00 00 00">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">E-mail <span class="text-muted small">(facultatif)</span></label>
                <input name="email" value="{{ old('email') }}" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="vous@exemple.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input class="form-control @error('password') is-invalid @enderror" type="password" name="password" placeholder="8 caractères minimum">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <button type="submit" class="btn btn-amber w-100 btn-lg">Créer mon compte</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
