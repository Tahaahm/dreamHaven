<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Agent;
use App\Models\Property;
use App\Models\AdminAnalytics;
use Illuminate\Pagination\LengthAwarePaginator;
class AdminController extends Controller
{
    public function __construct()
    {
        // Only allow admin users
        $this->middleware('auth'); // Make sure the user is logged in
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || auth()->user()->role !== 'admin') {
                abort(403); // Forbidden
            }
            return $next($request);
        });
    }

    /** -----------------------
     * List all users and agents
     * ----------------------- */
public function entitiesList()
{
    // Simple pagination
    $users = User::paginate(5);
    $agents = Agent::paginate(5);

    return view('Admin.users-list', compact('users', 'agents'));
}

    /** -----------------------
     * View User Detail
     * ----------------------- */
    public function userDetail($id)
    {
        $user = User::findOrFail($id);
        return view('Admin.user-detail', compact('user'));
    }

    /** -----------------------
     * View Agent Detail
     * ----------------------- */
  // In AdminController
public function agentDetail($id)
{
    $agent = Agent::findOrFail($id); // fetch agent
    return view('Admin.agent-detail', compact('agent')); // pass to view
}


    /** -----------------------
     * Suspend / Activate User or Agent
     * ----------------------- */
    public function suspendEntity($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->is_suspended = !$user->is_suspended;
            $user->save();
            return redirect()->back()->with('success', 'User status updated.');
        }

        $agent = Agent::find($id);
        if ($agent) {
            $agent->status = ($agent->status ?? 'active') === 'active' ? 'disabled' : 'active';
            $agent->save();
            return redirect()->back()->with('success', 'Agent status updated.');
        }

        return redirect()->back()->with('error', 'Entity not found.');
    }



    /** -----------------------
     * Delete User or Agent
     * ----------------------- */
    public function deleteEntity($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return redirect()->back()->with('success', 'User deleted.');
        }

        $agent = Agent::find($id);
        if ($agent) {
            $agent->delete();
            return redirect()->back()->with('success', 'Agent deleted.');
        }

        return redirect()->back()->with('error', 'Entity not found.');
    }

    public function suspendUser($id)
{
    $user = User::findOrFail($id);
    $user->is_suspended = !$user->is_suspended;
    $user->save();

    return redirect()->back()->with('success', 'User status updated successfully!');
}



public function adminProperties(Request $request)
{
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }

    // SEARCH
    $search = $request->query('search');

    $properties = Property::all()->map(function ($property) {
        return $property;
    });

    // Filter by search
    if ($search) {
        $properties = $properties->filter(function ($p) use ($search) {
            return str_contains(strtolower($p->id), strtolower($search)) ||
                   str_contains(strtolower($p->name['en'] ?? ''), strtolower($search)) ||
                   str_contains(strtolower($p->name['ar'] ?? ''), strtolower($search)) ||
                   str_contains(strtolower($p->name['ku'] ?? ''), strtolower($search));
        });
    }

    // Sort newest first
    $properties = $properties->sortByDesc('created_at')->values();

    // Manual pagination
    $perPage = 25;
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $currentPageItems = $properties->slice(($currentPage - 1) * $perPage, $perPage)->values();

    $paginatedProperties = new LengthAwarePaginator(
        $currentPageItems,
        $properties->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    return view('Admin.property-list', [
        'properties' => $paginatedProperties,
        'search' => $search,
    ]);
}

    // DELETE PROPERTY
    public function deleteProperty($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();

        return response()->json(['success' => true, 'message' => 'Property deleted successfully']);
    }









    // Zana's code

  

}
