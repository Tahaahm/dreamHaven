<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Office guard - redirect to office dashboard
                if ($guard === 'office') {
                    return redirect()->route('office.dashboard');
                }

                // Agent guard - redirect to agent profile
                if ($guard === 'agent') {
                    return redirect()->route('agent.profile.page');
                }

                // Web guard or null - redirect to home
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
