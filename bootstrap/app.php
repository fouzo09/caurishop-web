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

        // Connexion unique pour tous les profils : les invités sont envoyés sur /connexion.
        $middleware->redirectGuestsTo(fn () => route('shop.login'));

        // Déjà connecté : on renvoie chacun vers son espace plutôt que sur le formulaire.
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request) => $request->user()->homeRoute());
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
