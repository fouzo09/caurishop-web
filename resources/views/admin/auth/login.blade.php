<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAURISHOP — Connexion</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/login.css') }}">
</head>
<body>

{{-- ── Panneau gauche ──────────────────────────────────────────── --}}
<div class="panel-left">
    <div class="panel-brand">
        <a href="{{ route('home') }}" style="text-decoration:none;color:inherit;">
            <span class="logo-text">Caurishop</span>
        </a>
    </div>

    <div class="panel-center">
        <h2>Gérez vos ventes à crédit en toute simplicité.</h2>
        <p>Plateforme B2B dédiée aux entreprises guinéennes pour gérer commandes, clients et échéanciers.</p>

        <div class="feature-list">
            <div class="feature-item">
                <span class="feature-dot"></span>
                Suivi des commandes et livraisons
            </div>
            <div class="feature-item">
                <span class="feature-dot"></span>
                Gestion des crédits et échéanciers
            </div>
            <div class="feature-item">
                <span class="feature-dot"></span>
                Portail employé avec validation hiérarchique
            </div>
            <div class="feature-item">
                <span class="feature-dot"></span>
                Tableau de bord en temps réel
            </div>
        </div>
    </div>

    <div class="panel-footer">
        &copy; {{ date('Y') }} Caurishop. Tous droits réservés.
    </div>
</div>

{{-- ── Panneau droit (formulaire) ─────────────────────────────── --}}
<div class="panel-right">
    <div class="login-box">

        <div class="login-heading">
            <h1>Connexion</h1>
            <p>Entrez vos identifiants pour accéder à votre espace.</p>
        </div>

        @if(session('error'))
        <div class="alert alert-danger" id="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success" id="alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->has('email') && !$errors->has('password'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ $errors->first('email') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('login.authenticate') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Adresse email</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="vous@exemple.com"
                        autocomplete="email"
                        autofocus
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.35rem;">
                    <label class="form-label" for="password" style="margin:0;">Mot de passe</label>
                </div>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pwd" onclick="togglePwd()" tabindex="-1" id="toggleBtn">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                @if($errors->has('password'))
                <div class="field-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('password') }}
                </div>
                @endif
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span id="btn-text">Se connecter</span>
                <span id="btn-loading" style="display:none;align-items:center;gap:0.4rem;">
                    <i class="fas fa-circle-notch fa-spin"></i> Connexion…
                </span>
            </button>
        </form>
    </div>
</div>

<script>
    function togglePwd() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        document.getElementById('btn-text').style.display    = 'none';
        document.getElementById('btn-loading').style.display = 'flex';
        btn.disabled = true;
    });

    // Auto-dismiss alerts
    ['alert-error','alert-success'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) setTimeout(function() {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity    = '0';
            setTimeout(function() { el.remove(); }, 400);
        }, 5000);
    });
</script>
</body>
</html>
