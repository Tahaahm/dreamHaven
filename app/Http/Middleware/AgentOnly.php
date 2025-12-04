<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentOnly
{
public function handle(Request $request, Closure $next)
{
    // Check if the user is an agent
    if (session('agent_logged_in')) {
        return $next($request);
    }

    // Check if user is logged in as a normal user
    if (Auth::check()) {
        $user = Auth::user();
        return redirect()->route('agent.create.from.user', ['user_id' => $user->id]);
    }

    // Otherwise redirect to login
    return redirect()->route('login')->with('error', 'You must log in as an agent.');
}

}
