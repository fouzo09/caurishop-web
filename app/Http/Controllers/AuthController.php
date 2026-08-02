<?php

namespace App\Http\Controllers;

use App\Services\Admin\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Déconnexion des espaces admin / entreprise / portail.
 * La connexion est commune à tous les profils : Shop\Auth\LoginController (/connexion).
 */
class AuthController extends Controller
{
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
