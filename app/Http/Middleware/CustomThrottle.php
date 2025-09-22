<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Helper\ApiResponse;

class CustomThrottle
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 30)
    {
        // Use email or IP address as the key
        $key = $this->resolveRequestSignature($request);

        // Get current attempts
        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            $retryAfter = Cache::get($key . ':timer', 0);

            return ApiResponse::error(
                'Too many attempts',
                [
                    'message' => 'Too many login attempts. Please try again later.',
                    'retry_after' => $retryAfter,
                    'max_attempts' => $maxAttempts
                ],
                429
            );
        }

        $response = $next($request);

        // If the response is successful (2xx), clear the attempts
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            Cache::forget($key);
            Cache::forget($key . ':timer');
        } else {
            // If the response indicates failure, increment attempts
            if ($response->getStatusCode() === 401 || $response->getStatusCode() === 400) {
                $this->incrementAttempts($key, $decayMinutes);
            }
        }

        return $response;
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request)
    {
        // Prioritize email for login attempts, fallback to IP
        if ($request->has('email')) {
            return 'login_attempts:email:' . $request->input('email');
        }

        return 'login_attempts:ip:' . $request->ip();
    }

    /**
     * Increment the counter for a given key.
     *
     * @param  string  $key
     * @param  int  $decayMinutes
     * @return void
     */
    protected function incrementAttempts($key, $decayMinutes)
    {
        $attempts = Cache::get($key, 0) + 1;
        $decaySeconds = $decayMinutes * 60;

        Cache::put($key, $attempts, $decaySeconds);

        // Set timer for retry after
        if ($attempts >= 5) {
            Cache::put($key . ':timer', $decaySeconds, $decaySeconds);
        }
    }
}