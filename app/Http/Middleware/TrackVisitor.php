<?php

/*
| Save as: app/Services/VisitorTracker.php
|
| Same logic as the middleware, but callable from anywhere. Use this if you
| would rather wire tracking into your controllers than register middleware.
|
| It is safe to call on every request:
|   • writes to the database at most once per visitor per 15 minutes
|   • never throws — a tracking failure can't break an endpoint
|   • skips bots, admin paths and error responses
*/

namespace App\Services;

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

    /**
     * Record this visitor. Call it once per request.
     *
     * Returns the stable visitor id so you can use it for guest tracking:
     *
     *     $visitorId = app(VisitorTracker::class)->touch();
     *     $userId = $user ? $user->id : 'guest_' . $visitorId;
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

            // Make it available to anything else in this request
            $request->attributes->set('visitor_id', $visitorId);

            $this->store($request, $hash);

            return $visitorId;
        } catch (\Throwable $e) {
            $this->debug('failed', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return null;
        }
    }

    /**
     * Just the id, with no database write. Useful in hot paths.
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
            return $this->debug('skip: visits table missing');
        }

        $today = now()->toDateString();
        $key   = 'visit:' . substr($hash, 0, 24) . ':' . $today;

        // One write per visitor per 15 minutes
        if (!Cache::add($key, 1, 900)) {
            return;
        }

        $isApi    = $request->is('api/*');
        $deviceId = $request->header('X-Device-Id');
        $agent    = (string) $request->userAgent();
        $userId   = $this->resolveUserId($request);

        $platform = match (true) {
            (bool) preg_match('/android/i', $agent)                   => 'android',
            (bool) preg_match('/iphone|ipad|ios|cfnetwork/i', $agent)  => 'ios',
            (bool) preg_match('/dart|okhttp/i', $agent)                => 'app',
            default                                                    => 'browser',
        };

        $inserted = DB::table('visits')->insertOrIgnore([
            'visitor_hash'  => $hash,
            'visit_date'    => $today,
            'user_id'       => $userId,
            'source'        => ($isApi || $deviceId) ? 'app' : 'web',
            'platform'      => $platform,
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
            $this->debug('new visitor', ['path' => $request->path(), 'user' => $userId ?: 'guest']);
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
            ]);

        $this->debug('return session', ['path' => $request->path(), 'user' => $userId ?: 'guest']);
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
