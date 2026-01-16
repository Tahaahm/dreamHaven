<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // ✅ Redirect based on the authenticated guard
                switch ($guard) {
                    case 'office':
                        return redirect()->route('office.dashboard');

                    case 'agent':
                        return redirect()->route('agent.dashboard');

                    case 'admin':
                        return redirect()->route('admin.dashboard');

                    case 'web':
                    default:
                        return redirect()->route('newindex');
                }
            }
        }

        return $next($request);
    }
}