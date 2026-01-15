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

    <div id="alert" class="alert">
        <span id="alert-message"></span>
    </div>

    <form id="loginForm">
        <div class="form-group">
            <label class="form-label">Email</label>
            <input
                type="email"
                class="form-input"
                id="email"
                name="email"
                placeholder="votre.email@entreprise.com"
                required
            >
        </div>
        <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input
                type="password"
                class="form-input"
                id="password"
                name="password"
                placeholder="••••••••"
                required
            >
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <span>Se souvenir de moi</span>
            </label>
            <a href="#" class="forgot-password">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn btn-primary" id="loginButton">
            <span id="normal-text">Se connecter</span>
            <span id="loading-text" class="loading-text">Connexion...</span>
        </button>
    </form>
</div>
</body>
</html>
