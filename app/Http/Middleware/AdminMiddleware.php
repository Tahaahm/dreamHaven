<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if admin is logged in
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login first.');
        }

        $admin = Auth::guard('admin')->user();

        // Check if admin account is active
        if (!$admin->is_active) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Your account has been suspended.');
        }

        // Check if admin is verified
        if (!$admin->is_verified) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Your account is not verified.');
        }

        return $next($request);
    }
}
