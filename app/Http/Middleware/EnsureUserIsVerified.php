<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsVerified
{
   public function handle($request, Closure $next)
{
    // Check both user + agent guards
    $user = Auth::user() ?? Auth::guard('agent')->user();

    // Not logged in → let auth middleware redirect to login
    if (!$user) {
        return $next($request);
    }

    // Already verified → allow access
    if ($user->is_verified) {
        return $next($request);
    }

    // Allow the verification routes only
    if ($request->routeIs('verification.notice') ||
        $request->routeIs('verify.code') ||
        $request->routeIs('verification.send') ||
        $request->routeIs('verification.verify')) {

        return $next($request);
    }

    // Not verified → force verification page
    return redirect()->route('verification.notice');
}

}

