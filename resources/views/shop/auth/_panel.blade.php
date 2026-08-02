@php
    $activeTab = $activeTab ?? 'login';
    if ($errors->hasAny(['first_name', 'last_name', 'phone', 'email', 'password'])) {
        $activeTab = 'register';
    } elseif ($errors->has('login')) {
        $activeTab = 'login';
    }
@endphp
<main class="mx-auto px-4 py-5" style="max-width:480px">
  <ul class="nav auth-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link {{ $activeTab === 'login' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pane-login" type="button" role="tab">Connexion</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link {{ $activeTab === 'register' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#pane-register" type="button" role="tab">Inscription</button>
    </li>
  </ul>

  <div class="tab-content">

    {{-- CONNEXION --}}
    <div class="tab-pane fade {{ $activeTab === 'login' ? 'show active' : '' }}" id="pane-login" role="tabpanel">
      <h1 class="fw-bolder mb-1" style="font-size:24px">Bon retour !</h1>
      <p class="text-muted mb-4" style="font-size:13.5px">Accédez à votre compte CAURISHOP.</p>

      <form method="POST" action="{{ route('shop.login.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Téléphone ou e-mail</label>
          <input name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" placeholder="+224 6XX XX XX XX">
          @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">Mot de passe</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••">
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3" style="font-size:13px">
          <span class="form-check m-0">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
          </span>
          <a href="{{ route('shop.contact') }}" class="text-brand fw-semibold">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn-brand w-100">Se connecter</button>
      </form>
    </div>

    {{-- INSCRIPTION --}}
    <div class="tab-pane fade {{ $activeTab === 'register' ? 'show active' : '' }}" id="pane-register" role="tabpanel">
      <h1 class="fw-bolder mb-1" style="font-size:24px">Créer un compte</h1>
      <p class="text-muted mb-4" style="font-size:13.5px">Rejoignez CAURISHOP en moins d'une minute.</p>

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
          <input name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="+224 6XX XX XX XX">
          @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">E-mail <span class="text-muted" style="font-size:12px">(facultatif)</span></label>
          <input name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="vous@exemple.com">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">Mot de passe</label>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="8 caractères minimum">
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn-brand w-100 mb-2">Créer mon compte</button>
        <p class="text-muted text-center m-0" style="font-size:12.5px">
          En vous inscrivant, vous acceptez nos <a href="{{ route('shop.contact') }}" class="text-brand">conditions d'utilisation</a>.
        </p>
      </form>
    </div>
  </div>
</main>
