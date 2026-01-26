<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAURISHOP - Connexion</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/login.css') }}">
</head>
<body>
<div class="login-container">
    <h1 class="logo">CAURISHOP</h1>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('login.authenticate') }}" id="loginForm">
        @csrf

        <div class="form-group">
            <label class="form-label">Email</label>
            <input
                type="email"
                class="form-input @error('email') error @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="admin@caurishop.com"
                required
                autocomplete="email"
            >
            @error('email')
            <div class="alert alert-danger" style="margin-top: 0.5rem; margin-bottom: 0;">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $message }}</span>
            </div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input
                type="password"
                class="form-input @error('password') error @enderror"
                id="password"
                name="password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            >
            @error('password')
            <div class="alert alert-danger" style="margin-top: 0.5rem; margin-bottom: 0;">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $message }}</span>
            </div>
            @enderror
        </div>

        <div class="form-options">
            <a href="#" class="forgot-password">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn btn-primary" id="loginButton">
            <span id="normal-text">Se connecter</span>
            <span id="loading-text" class="loading-text" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Connexion...
                </span>
        </button>
    </form>

    <div class="login-footer" style="text-align: center; color: var(--gray-600); font-size: 0.9rem; margin-top: 1rem;">
        <p>&copy; 2025 CAURISHOP. Tous droits réservés.</p>
    </div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('submit', function() {
        const button = document.getElementById('loginButton');
        const normalText = document.getElementById('normal-text');
        const loadingText = document.getElementById('loading-text');

        button.disabled = true;
        normalText.style.display = 'none';
        loadingText.style.display = 'inline-flex';
    });

    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
    });
</script>
</body>
</html>
