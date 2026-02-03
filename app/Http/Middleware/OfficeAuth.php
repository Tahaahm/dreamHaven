<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('office')->check()) {
            return redirect()->route('office.login')
                ->with('error', 'Please login to access this page');
        }

        return $next($request);
    }
}
