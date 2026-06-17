<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── API endpoint call counter ─────────────────────────────────────
        if (app()->runningInConsole()) return;

        $request = request();
        if ($request->is('api/*') || $request->is('v1/api/*')) {
            $endpoint = $request->method() . ' /' . $request->path();
            $hour     = now()->format('Y-m-d H:00');

            // Total count this hour
            Cache::increment("api_total_{$hour}");

            // Per-endpoint count this hour
            $key   = "api_breakdown_{$hour}";
            $calls = Cache::get($key, []);
            $calls[$endpoint] = ($calls[$endpoint] ?? 0) + 1;
            Cache::put($key, $calls, 7200);
        }
    }
}
