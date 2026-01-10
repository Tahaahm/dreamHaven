<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('agent')->check()) {
            return redirect()->route('agent.login')
                ->with('error', 'Please login as agent to access this page');
        }

        return $next($request);
    }
}
