<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return $next($request);
        }

        // Redirect based on route prefix
        if ($request->is('office') || $request->is('office/*')) {
            return redirect()->route('office.login')->with('error', 'Please login first.');
        }

        if ($request->is('agent') || $request->is('agent/*')) {
            return redirect()->route('agent.login')->with('error', 'Please login first.');
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return redirect()->route('admin.login')->with('error', 'Please login first.');
        }

        return redirect()->route('login-page')->with('error', 'Please login first.');
    }
}
