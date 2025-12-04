<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAgent
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $isAgent = \App\Models\Agent::where('subscriber_id', $user->id)->exists();

        if (!$isAgent) {
            return redirect()->route('become.agent.prompt')
                ->with('warning', 'You need to become an agent to access this page.');
        }

        return $next($request);
    }
}
