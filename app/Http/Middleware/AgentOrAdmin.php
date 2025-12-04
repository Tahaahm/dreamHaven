<?php

namespace App\Http\Middleware;


use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use Closure; // if you need it for middleware

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentOrAdmin
{




public function handle($request, Closure $next)
{
    $webUser = Auth::guard('web')->user();
    $agentUser = Auth::guard('agent')->user();

    Log::info('AgentOrAdmin Middleware - Auth check', [
        'web_user' => $webUser,
        'agent_user' => $agentUser,
        'session_all' => session()->all()
    ]);

    if ($agentUser || ($webUser && $webUser->role === 'admin')) {
        return $next($request);
    }

    Log::info('AgentOrAdmin Middleware - redirecting to login');

    return redirect()->route('login-page');
}







}
