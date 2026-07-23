<?php

namespace App\Http\Controllers\Concerns;

use App\Helper\ApiResponse;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Owner-facing "my properties" JSON endpoints, plus a handful of legacy
 * Blade-view routes (list/edit/portfolio pages) and the guest home-screen
 * stats widget. Extracted from PropertyController as-is — no behavior
 * changed, only relocated. See ManagesPropertyEngagement.php for why this
 * is safe (traits compile directly into the class that uses them).
 */
trait ManagesPropertyOwnerViews
{
    public function getMyProperties(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                return ApiResponse::error('Authentication required', null, 401);
            }

            $perPage  = $request->get('per_page', 20);
            $language = $request->get('language', 'en');

            $properties = Property::where('owner_id', $user->id)
                ->where('owner_type', get_class($user))
                ->where('published', true)
                ->whereNotIn('status', ['cancelled', 'pending'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $transformedData = collect($properties->items())->map(function ($property) use ($language) {
                return $this->transformPropertyForSearch($property, $language);
            });

            return ApiResponse::success(
                'Your properties retrieved',
                [
                    'data'         => $transformedData,
                    'total'        => $properties->total(),
                    'current_page' => $properties->currentPage(),
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error('Get my properties error', ['message' => $e->getMessage()]);
            return ApiResponse::error('Failed to get your properties', $e->getMessage(), 500);
        }
    }

    /**
     * Get user's draft (unpublished) properties
     */
    public function getMyDrafts(Request $request)
    {
        try {
            $user = Auth::user();

            $language = $request->get('language', 'en');

            $drafts = Property::where('owner_id', $user->id)
                ->where('owner_type', get_class($user))
                ->where('published', false)
                ->orderBy('updated_at', 'desc')
                ->get();

            $transformedData = $drafts->map(function ($property) use ($language) {
                return $this->transformPropertyForSearch($property, $language);
            });

            return ApiResponse::success(
                'Your draft properties retrieved',
                ['data' => $transformedData],
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get draft properties', $e->getMessage(), 500);
        }
    }

    public function showList(Request $request)
    {
        $perPage = $request->get('per_page', 20);

        $properties = \App\Models\Property::where('published', true)  // ← ADD THIS
            ->where(function ($query) {
                $query->whereNotIn('status', ['cancelled', 'pending'])
                    ->orWhere('owner_type', 'Agent');
            })
            ->paginate($perPage);

        return view('list', [
            'properties' => $properties
        ]);
    }


    // Edit user method
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('agent.edit-agent-admin', compact('user'));
    }


    public function showUserProperties()
    {
        // 1️⃣ Check for logged-in user
        if (auth()->check()) {
            $owner = auth()->user();
        }
        // 2️⃣ Check for logged-in agent (session-based)
        elseif (session('agent_logged_in')) {
            $owner = \App\Models\Agent::find(session('agent_id'));
        } else {
            // Not logged in
            return redirect()->route('login-page');
        }

        // Fetch properties posted by this owner (user or agent)
        $properties = \App\Models\Property::where('owner_id', $owner->id)
            ->where('owner_type', get_class($owner))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('agent.agent-property-list', compact('properties'));
    }

    public function showPortfolio($property_id)
    {
        $property = Property::find($property_id);

        if (!$property) {
            return redirect()->back()->with('error', 'Property not found.');
        }

        // Decode JSON fields
        $property->images = is_string($property->images) ? json_decode($property->images, true) : $property->images;
        $property->location = is_string($property->location) ? json_decode($property->location, true) : $property->location;

        // Return the Blade view
        return view('PropertyDetail', compact('property'));
    }

    public function searchView(Request $request)
    {
        $query = $request->input('q');

        // Get properties based on search query
        $properties = Property::query();

        if ($query) {
            $properties->where(function ($q) use ($query) {
                $q->where('name->en', 'like', "%{$query}%")
                    ->orWhere('name->ar', 'like', "%{$query}%")
                    ->orWhere('name->ku', 'like', "%{$query}%")
                    ->orWhere('address', 'like', "%{$query}%");
            });
        }

        $properties = $properties->paginate(12);

        // Return the LIST VIEW, not JSON
        return view('list', compact('properties'));
    }

    public function guestStats(Request $request)
    {
        try {
            $city   = trim($request->get('city',   ''));
            $type   = trim($request->get('type',   ''));
            $budget = (int) $request->get('budget', 0);

            // Cache key per context so Erbil guests get Erbil counts
            $cacheKey = 'guest_stats_v2_'
                . md5($city . '|' . $type . '|' . ($budget > 0 ? 'b' : ''));

            $data = Cache::remember($cacheKey, now()->addMinutes(10), function ()
            use ($city, $type, $budget) {

                // ── Base query: active + published ────────────────────────
                $base = Property::where('is_active', true)
                    ->where('published', true);

                // ── Apply city filter if provided ─────────────────────────
                if ($city !== '') {
                    $cityLower = strtolower($city);
                    $base->where(function ($q) use ($cityLower) {
                        $q->whereRaw(
                            "LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en')))) LIKE ?",
                            ['%' . $cityLower . '%']
                        )->orWhereRaw(
                            "LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ku')))) LIKE ?",
                            ['%' . $cityLower . '%']
                        )->orWhereRaw(
                            "LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.ar')))) LIKE ?",
                            ['%' . $cityLower . '%']
                        );
                    });
                }

                // ── Apply type filter if provided ─────────────────────────
                if ($type !== '') {
                    $base->where(function ($q) use ($type) {
                        $q->whereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(type, '$.category'))) LIKE ?",
                            ['%' . strtolower($type) . '%']
                        );
                    });
                }

                // ── Total count (city+type filtered) ──────────────────────
                $total = $base->count();

                // ── Added today (same filters) ─────────────────────────────
                $addedToday = (clone $base)
                    ->whereDate('created_at', today())
                    ->count();

                // ── Top 3 cities — always global (helps guest discover) ───
                // We always show global top cities in the pills regardless of
                // filter, so the guest can see where most listings are.
                $rawCities = Property::where('is_active', true)
                    ->where('published', true)
                    ->whereNotNull('address_details')
                    ->selectRaw(
                        "TRIM(JSON_UNQUOTE(JSON_EXTRACT(address_details, '$.city.en')))
                         as city, COUNT(*) as cnt"
                    )
                    ->groupBy('city')
                    ->orderByDesc('cnt')
                    ->limit(3)
                    ->get();

                $topCities = [];
                foreach ($rawCities as $row) {
                    $name = $row->city ?? '';
                    if ($name === '' || $name === 'null') continue;
                    $topCities[$name] = (int) $row->cnt;
                }

                return [
                    'total'        => (int) $total,
                    'added_today'  => (int) $addedToday,
                    'top_cities'   => $topCities,
                    // Echo back the context so Flutter knows what this count is for
                    'city_context' => $city  ?: null,
                    'type_context' => $type  ?: null,
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('guestStats error: ' . $e->getMessage());

            // Always return valid JSON — never crash the guest home screen
            return response()->json([
                'status' => true,
                'data'   => [
                    'total'        => 0,
                    'added_today'  => 0,
                    'top_cities'   => (object) [],
                    'city_context' => null,
                    'type_context' => null,
                ],
            ], 200);
        }
    }
}
