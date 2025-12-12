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
        $middleware->validateCsrfTokens(except: [
            // Users routes
            'users',
            'users/*',

            // Real Estate Offices routes
            'real-estate-offices',
            'real-estate-offices/*',
            'real-estate-office/login',

            // Agents routes
            'agents',
            'agents/*',
            'agents/users',

            // Properties routes
            'properties',
            'properties/*',

            // Projects routes
            'projects',
            'projects/*',

            // Appointments routes
            'appointments',
            'appointments/*',

            // API routes (v1)
            'api/*',
            'v1/api/*', // Add this line

            // Specific property routes
            'v1/api/properties',
            'v1/api/properties/*',
            'v1/api/properties/map',

            // Specific agent routes - ADD THESE LINES
            'v1/api/agents/*',
            'v1/api/agents/users/*',
            'v1/api/agents/users/*/convert-to-agent',,
            "/upload-images"
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();