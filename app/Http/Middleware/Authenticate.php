<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return $next($request);
        }

        // ✅ Check which route is being accessed and redirect accordingly
        if ($request->is('office') || $request->is('office/*')) {
            return redirect()->route('office.login');
        }

        if ($request->is('agent') || $request->is('agent/*')) {
            return redirect()->route('agent.login');
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return redirect()->route('admin.login');
        }

        return redirect()->route('login-page');
    }
}