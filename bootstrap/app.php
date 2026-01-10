<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ ADD THIS - Register middleware alias
        $middleware->alias([
            'auth.office' => \App\Http\Middleware\OfficeAuthenticate::class,

            'auth.agent' => \App\Http\Middleware\AgentAuth::class,

            'guest.agent' => \App\Http\Middleware\RedirectIfAgent::class,

        ]);

        // Existing CSRF exceptions
        $middleware->validateCsrfTokens(except: [
            'users',
            'users/*',
            'real-estate-offices',
            'real-estate-offices/*',
            'real-estate-office/login',
            'agents',
            'agents/*',
            'agents/users',
            'properties',
            'properties/*',
            'projects',
            'projects/*',
            'appointments',
            'appointments/*',
            'api/*',
            'v1/api/*',
            'v1/api/properties',
            'v1/api/properties/*',
            'v1/api/properties/map',
            'v1/api/agents/*',
            'v1/api/agents/users/*',
            'v1/api/agents/users/*/convert-to-agent',
            "/upload-images"
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
