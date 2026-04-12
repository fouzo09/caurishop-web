<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AuthenticationRequest;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->homeRoute());
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

            app(ActivityLogService::class)->log(
                'login',
                'Connexion réussie — ' . Auth::user()->name,
                Auth::id(),
            );

            return redirect()->intended(Auth::user()->homeRoute())
                ->with('success', 'Connexion réussie ! Bienvenue ' . Auth::user()->name);
        }

        app(ActivityLogService::class)->log(
            'login_failed',
            'Tentative de connexion échouée — ' . $credentials['email'],
            null,
        );

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userName = Auth::user()->name ?? 'Utilisateur';
        $userId   = Auth::id();

        app(ActivityLogService::class)->log('logout', 'Déconnexion — ' . $userName, $userId);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'À bientôt ' . $userName . ' !');
    }
}
