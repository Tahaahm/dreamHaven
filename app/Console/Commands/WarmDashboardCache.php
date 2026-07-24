<?php

/*
| REPLACE: app/Http/Middleware/TrackVisitor.php
|
| Changes from the first version:
|
|  1. Removed the $request->ajax() check — some HTTP clients set
|     X-Requested-With, which was silently skipping API traffic.
|  2. Errors are no longer swallowed in silence. Set
|         VISITOR_TRACKING_DEBUG=true
|     in .env and every skip/failure is written to laravel.log with a reason.
|  3. Sets a stable visitor_id on the request so PropertyController can stop
|     using session()->getId() for guests.
|  4. Admin paths are still excluded — see the note at the bottom.
*/

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /** Paths that never count as a visit. */
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
            $this->debug('failed', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return $next($request);
    }

    protected function record(Request $request): void
    {
        if (!$request->isMethod('GET')) {
            return $this->debug('skip: not GET', ['method' => $request->method()]);
        }

        if ($request->is($this->ignore)) {
            return $this->debug('skip: ignored path', ['path' => $request->path()]);
        }

        if (!Schema::hasTable('visits')) {
            return $this->debug('skip: visits table missing');
        }

        $agent = (string) $request->userAgent();

        if ($agent === '') {
            return $this->debug('skip: no user agent');
        }

        if (preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|headless/i', $agent)) {
            return $this->debug('skip: bot', ['agent' => $agent]);
        }

        // Identity: the app's device id when present, otherwise IP + agent
        $deviceId = $request->header('X-Device-Id');
        $raw      = $deviceId ?: ($request->ip() . '|' . $agent);
        $hash     = hash('sha256', config('app.key') . '|' . $raw);

        // Make the stable id available to controllers (replaces session()->getId())
        $request->attributes->set('visitor_id', substr($hash, 0, 32));

        $today = now()->toDateString();
        $key   = 'visit:' . substr($hash, 0, 24) . ':' . $today;

        // Only touch the database once every 15 minutes per visitor
        if (!Cache::add($key, 1, 900)) {
            return;
        }

        $source = ($deviceId || $request->is('api/*')) ? 'app' : 'web';
        $userId = optional($request->user())->id;

        $platform = match (true) {
            (bool) preg_match('/android/i', $agent)          => 'android',
            (bool) preg_match('/iphone|ipad|ios/i', $agent)   => 'ios',
            (bool) preg_match('/dart|okhttp|cfnetwork/i', $agent) => 'app',
            default                                           => 'browser',
        };

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
            $this->debug('new visitor recorded', ['path' => $request->path(), 'source' => $source]);
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

        $this->debug('returning session recorded', ['path' => $request->path()]);
    }

    /**
     * Only writes when VISITOR_TRACKING_DEBUG=true — turn it off once it works.
     */
    protected function debug(string $message, array $context = []): void
    {
        if (env('VISITOR_TRACKING_DEBUG', false)) {
            Log::info('TrackVisitor: ' . $message, $context);
        }
    }
}

/*
|------------------------------------------------------------------------------
| WHY YOUR OWN VISITS AREN'T COUNTED
|------------------------------------------------------------------------------
| 'admin' and 'admin/*' are in the ignore list on purpose — otherwise your own
| dashboard refreshes would inflate every number you're trying to read.
|
| To test that tracking works, open the PUBLIC site (dreammulk.com) or use the
| Flutter app, not /admin.
*/