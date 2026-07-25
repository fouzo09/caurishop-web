<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'             => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'       => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'djomy/webhook',
        ]);
        $middleware->trustProxies(at: '*');

        // Invités : redirection vers la connexion client (/connexion) pour le parcours
        // public, vers la connexion admin (/login) partout ailleurs.
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('checkout', 'commande/*', 'mon-compte', 'mon-compte/*', 'deconnexion')) {
                return route('shop.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
