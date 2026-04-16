<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // existing
        "/register",
        "/login",
        "/post-property",
        'api/v1/auth/office/login',

        // upload images
        'upload-images',

        // properties
        'v1/api/properties/*',
        'v1/api/properties/store',
        'v1/api/properties/upload-images',

        // agents
        'v1/api/agents/*',

        // location
        'v1/api/location/*',

        // projects
        'v1/api/projects/*',

        // offices
        'real-estate-offices/*',

        // auth
        'api/v1/*',
        'api/v1/auth/*',

        // office dashboard
        'api/v1/office/*',

        // notifications
        'notifications/*',

        // appointments
        'appointments/*',

        // service providers
        'v1/api/service-providers/*',

        // banner ads
        'v1/api/banner-ads/*',

        // app version
        'app/*',

        // subscription plans
        'subscription-plans/*',

        // video
        'api/video/*',

        // AI routes
        'api/v1/map/*',
        'api/v1/areas/*',
        'api/v1/market/*',
    ];
}
