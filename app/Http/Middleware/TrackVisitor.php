<?php

/*
| Save as: app/Http/Middleware/TrackVisitor.php
|
| Writes at most one database row per visitor per day, and only touches the
| database once every 15 minutes per visitor (a "session"). Everything is
| wrapped in try/catch — tracking must never break a page.
*/

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /** Paths that should never count as a visit. */
    protected array $ignore = [
        'admin',
        'admin/*',
        'horizon',
        'horizon/*',
        'telescope',
        'telescope/*',
        'storage/*',
        'build/*',
        'favicon.ico',
        'robots.txt',
        'sitemap.xml',
        'up',
        '_debugbar/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->record($request);
        } catch (\Throwable $e) {
            // Never let analytics take the site down.
        }

        return $next($request);
    }

    protected function record(Request $request): void
    {
        if (!$request->isMethod('GET') || $request->is($this->ignore) || $request->ajax()) {
            return;
        }

        if (!Schema::hasTable('visits')) {
            return;
        }

        // Ignore obvious crawlers
        $agent = (string) $request->userAgent();
        if ($agent === '' || preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|headless/i', $agent)) {
            return;
        }

        // Identity: the app's device id if it sends one, otherwise IP + agent
        $deviceId = $request->header('X-Device-Id');
        $raw      = $deviceId ?: ($request->ip() . '|' . $agent);
        $hash     = hash('sha256', config('app.key') . '|' . $raw);

        $today = now()->toDateString();
        $key   = 'visit:' . substr($hash, 0, 24) . ':' . $today;

        // Cache::add returns false if the key already exists — that means we
        // saw this visitor within the last 15 minutes, so skip the write.
        if (!Cache::add($key, 1, 900)) {
            return;
        }

        $source = $deviceId || $request->is('api/*') ? 'app' : 'web';
        $userId = optional($request->user())->id;

        $platform = match (true) {
            (bool) preg_match('/android/i', $agent) => 'android',
            (bool) preg_match('/iphone|ipad|ios/i', $agent) => 'ios',
            default => 'browser',
        };

        // First visit today → create the row. Otherwise → bump the counters.
        $inserted = DB::table('visits')->insertOrIgnore([
            'visitor_hash'  => $hash,
            'visit_date'    => $today,
            'user_id'       => $userId,
            'source'        => $source,
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
                // Attach the account as soon as they sign in
                'user_id'      => $userId ?: DB::raw('user_id'),
            ]);
    }
}
