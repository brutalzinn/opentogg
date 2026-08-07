<?php

use App\Http\Middleware\SetUserPreferences;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Web UI renders in the active user's locale; API stays English + UTC.
        $middleware->appendToGroup('web', SetUserPreferences::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
