<?php

/*
| app/Http/Middleware/VisitorTracker.php
|
| FIXED: app vs web detection.
|
| Your API routes are /v1/api/... so $request->is('api/*') never matched,
| and every app visitor was being stored as source = 'web'.
|
| Now an "app" visit is anything that matches ANY of:
|   • sends an X-Device-Id header
|   • hits a path containing /api/  (api/*, v1/api/*, anything/api/*)
|   • has a Dart / Flutter / OkHttp / CFNetwork user agent
*/

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class VisitorTracker
{
    protected array $ignore = [
        'admin',
        'admin/*',
        'horizon/*',
        'telescope/*',
        'storage/*',
        'build/*',
        'favicon.ico',
        'robots.txt',
        'sitemap.xml',
    ];

    /** Paths that mean "this came from the mobile app". */
    protected array $apiPaths = [
        'api/*',
        'v1/api/*',
        'api/v1/*',
        '*/api/*',
    ];

    /**
     * Record this visitor. Call once per request.
     * Returns the stable visitor id (works for guests and signed-in users).
     */
    public function touch(?Request $request = null): ?string
    {
        $request = $request ?: request();

        try {
            $hash = $this->fingerprint($request);

            if (!$hash) {
                return null;
            }

            $visitorId = substr($hash, 0, 32);

            $request->attributes->set('visitor_id', $visitorId);

            $this->store($request, $hash);

            return $visitorId;
        } catch (\Throwable $e) {
            $this->debug('failed', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return null;
        }
    }

    /**
     * Just the id, with no database write.
     */
    public function id(?Request $request = null): ?string
    {
        $request = $request ?: request();

        if ($existing = $request->attributes->get('visitor_id')) {
            return $existing;
        }

        $hash = $this->fingerprint($request);

        return $hash ? substr($hash, 0, 32) : null;
    }

    // ------------------------------------------------------------------

    protected function store(Request $request, string $hash): void
    {
        if ($request->is($this->ignore)) {
            return;
        }

        if (!Schema::hasTable('visits')) {
            $this->debug('skip: visits table missing');
            return;
        }

        $today = now()->toDateString();
        $key   = 'visit:' . substr($hash, 0, 24) . ':' . $today;

        // One database write per visitor per 15 minutes
        if (!Cache::add($key, 1, 900)) {
            return;
        }

        $agent  = (string) $request->userAgent();
        $userId = $this->resolveUserId($request);
        $isApp  = $this->isApp($request, $agent);

        $inserted = DB::table('visits')->insertOrIgnore([
            'visitor_hash'  => $hash,
            'visit_date'    => $today,
            'user_id'       => $userId,
            'source'        => $isApp ? 'app' : 'web',
            'platform'      => $this->platform($agent, $isApp),
            'landing_path'  => mb_substr($request->path(), 0, 190),
            'referrer'      => mb_substr((string) $request->headers->get('referer'), 0, 190) ?: null,
            'sessions'      => 1,
            'hits'          => 1,
            'first_seen_at' => now(),
            'last_seen_at'  => now(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        if ($inserted) {
            $this->debug('new visitor', [
                'path'   => $request->path(),
                'source' => $isApp ? 'app' : 'web',
                'user'   => $userId ?: 'guest',
            ]);
            return;
        }

        DB::table('visits')
            ->where('visitor_hash', $hash)
            ->where('visit_date', $today)
            ->update([
                'sessions'     => DB::raw('sessions + 1'),
                'hits'         => DB::raw('hits + 1'),
                'last_seen_at' => now(),
                'updated_at'   => now(),
                'user_id'      => $userId ?: DB::raw('user_id'),
                // Promote to 'app' if any request in the session came from the app
                'source'       => $isApp ? 'app' : DB::raw('source'),
            ]);

        $this->debug('return session', [
            'path'   => $request->path(),
            'source' => $isApp ? 'app' : 'web',
            'user'   => $userId ?: 'guest',
        ]);
    }

    /**
     * Did this request come from the mobile app?
     */
    protected function isApp(Request $request, string $agent): bool
    {
        if ($request->header('X-Device-Id')) {
            return true;
        }

        if ($request->is(...$this->apiPaths)) {
            return true;
        }

        return (bool) preg_match('/dart|flutter|okhttp|cfnetwork/i', $agent);
    }

    protected function platform(string $agent, bool $isApp): string
    {
        return match (true) {
            (bool) preg_match('/android/i', $agent)                  => 'android',
            (bool) preg_match('/iphone|ipad|ios|cfnetwork/i', $agent) => 'ios',
            $isApp                                                   => 'app',
            default                                                  => 'browser',
        };
    }

    /**
     * Identity, best source first:
     *   1. X-Device-Id  — exact, survives sign-in and sign-out
     *   2. user id      — exact for signed-in people with no device id
     *   3. IP + agent   — fallback for web browsers
     */
    protected function fingerprint(Request $request): ?string
    {
        $agent = (string) $request->userAgent();

        if ($agent !== '' && preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|headless/i', $agent)) {
            return null;
        }

        $deviceId = $request->header('X-Device-Id');

        if ($deviceId) {
            $raw = 'device:' . $deviceId;
        } elseif ($userId = $this->resolveUserId($request)) {
            $raw = 'user:' . $userId;
        } elseif ($agent !== '') {
            $raw = 'net:' . $request->ip() . '|' . $agent;
        } else {
            return null;
        }

        return hash('sha256', config('app.key') . '|' . $raw);
    }

    protected function resolveUserId(Request $request)
    {
        try {
            if ($user = $request->user()) {
                return $user->id;
            }

            if (Auth::guard('sanctum')->check()) {
                return Auth::guard('sanctum')->id();
            }
        } catch (\Throwable $e) {
            // No guard configured — treat as guest
        }

        return null;
    }

    protected function debug(string $message, array $context = []): void
    {
        if (env('VISITOR_TRACKING_DEBUG', false)) {
            Log::info('VisitorTracker: ' . $message, $context);
        }
    }
}