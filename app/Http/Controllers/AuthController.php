<?php

namespace App\Http\Controllers;

use App\Http\Requests\admin\AuthenticationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
       if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('admin.auth.login');
    }

    public function authenticate(AuthenticationRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'Votre compte est désactivé. Contactez l\'administrateur.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            logger('Connexion réussie pour: ' . $credentials['email']);

            return redirect()->intended(route('home'))
                ->with('success', 'Connexion réussie ! Bienvenue ' . Auth::user()->name);
        }
        logger('Tentative de connexion échouée pour: ' . $credentials['email']);

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userName = Auth::user()->name ?? 'Utilisateur';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'À bientôt ' . $userName . ' !');
    }
}
