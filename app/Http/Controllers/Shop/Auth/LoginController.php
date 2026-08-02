<?php

namespace App\Http\Controllers\Shop\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use App\Services\Shop\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('shop.auth.login');
    }

    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Renseignez votre e-mail ou téléphone.',
        ]);

        $remember = $request->boolean('remember');
        $login    = trim($credentials['login']);

        // Résolution de l'utilisateur : e-mail direct, sinon via le téléphone du client.
        // Le téléphone ne concerne que les clients ; les autres profils se connectent
        // par e-mail, ce qui suffit puisque la page est commune à tout le monde.
        if (str_contains($login, '@')) {
            $user = User::where('email', $login)->first();
        } else {
            $user = Customer::where('phone', $login)->first()?->user;
        }

        if (! $user) {
            return $this->failed($login);
        }

        if (! $user->is_active) {
            return back()->withErrors(['login' => 'Votre compte est désactivé.'])->onlyInput('login');
        }

        if (! Auth::attempt(['email' => $user->email, 'password' => $credentials['password']], $remember)) {
            return $this->failed($login);
        }

        $request->session()->regenerate();

        // Le panier de session n'a de sens que pour un client du storefront.
        if ($user->isCustomer()) {
            $cart->mergeSessionIntoUser();
        }

        app(ActivityLogService::class)->log('login', 'Connexion — ' . $user->name, $user->id);

        // Chaque profil repart vers son espace (admin, entreprise, portail, boutique).
        return redirect()->intended($user->homeRoute())
            ->with('success', 'Connexion réussie ! Bienvenue ' . $user->name);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $name = Auth::user()?->name ?? 'Utilisateur';
        $id   = Auth::id();

        app(ActivityLogService::class)->log('logout', 'Déconnexion — ' . $name, $id);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'À bientôt ' . $name . ' !');
    }

    protected function failed(string $login): RedirectResponse
    {
        app(ActivityLogService::class)->log('login_failed', 'Connexion échouée — ' . $login, null);

        return back()->withErrors([
            'login' => 'Identifiants incorrects. Vérifiez votre e-mail/téléphone et mot de passe.',
        ])->onlyInput('login');
    }
}
