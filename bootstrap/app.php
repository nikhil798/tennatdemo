<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'tenant' => TenantMiddleware::class,
        ]);

        $middleware->prependToPriorityList(
            [RedirectIfAuthenticated::class, Authenticate::class],
            TenantMiddleware::class,
        );

        $middleware->redirectGuestsTo(function (Request $request) {
            return str_starts_with($request->path(), 'landlord')
                ? route('landlord.login')
                : route('login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if ($request->user('landlord')) {
                return route('landlord.dashboard');
            }

            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
