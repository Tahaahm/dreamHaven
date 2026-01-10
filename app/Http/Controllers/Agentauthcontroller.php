<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Property;
use App\Models\Subscription\Subscription as ModelsSubscription;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AgentAuthController extends Controller
{
    // AUTHENTICATION
    public function showLogin()
    {
        if (Auth::guard('agent')->check()) {
            return redirect()->route('agent.dashboard');
        }
        return view('agent.agent-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $agent = Agent::where('primary_email', $request->email)->first();

        if ($agent && Hash::check($request->password, $agent->password)) {
            Auth::guard('agent')->login($agent);
            return redirect()->route('agent.dashboard')->with('success', 'Welcome back, ' . $agent->agent_name . '!');
        }

        return back()->withInput()->with('error', 'Invalid email or password');
    }

    public function showRegister()
    {
        if (Auth::guard('agent')->check()) {
            return redirect()->route('agent.dashboard');
        }
        return view('agent.agent-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'agent_name' => 'required|string|max:255',
            'primary_email' => 'required|email|unique:agents,primary_email',
            'primary_phone' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'city' => 'required|string',
            'license_number' => 'nullable|string|unique:agents,license_number',
        ]);

        $agent = new Agent();
        $agent->id = (string) Str::uuid();
        $agent->agent_name = $request->agent_name;
        $agent->primary_email = $request->primary_email;
        $agent->primary_phone = $request->primary_phone;
        $agent->password = Hash::make($request->password);
        $agent->city = $request->city;
        $agent->license_number = $request->license_number;
        $agent->is_verified = false;
        $agent->status = 'active';
        $agent->save();

        Auth::guard('agent')->login($agent);

        return redirect()->route('agent.dashboard')->with('success', 'Welcome to Dream Mulk! Your account has been created successfully.');
    }

    public function logout(Request $request)
    {
        Auth::guard('agent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('agent.login')->with('success', 'You have been logged out successfully');
    }

    // DASHBOARD
    public function showDashboard()
    {
        $agent = Auth::guard('agent')->user();

        $totalProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->count();

        $activeProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->where('status', 'available')
            ->count();

        $stats = [
            'total_properties' => $totalProperties,
            'active_properties' => $activeProperties,
            'active_percentage' => $totalProperties > 0 ? round(($activeProperties / $totalProperties) * 100) : 0,
            'new_this_month' => Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\Models\Agent')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'total_views' => 0,
            'views_this_week' => 0,
            'properties_sold' => Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\Models\Agent')
                ->where('status', 'sold')
                ->count(),
            'sold_this_year' => Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\Models\Agent')
                ->where('status', 'sold')
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->latest()
            ->take(6)
            ->get();

        return view('agent.agent-dashboard', compact('stats', 'recentProperties'));
    }

    // PROPERTIES
    public function showProperties()
    {
        $agent = Auth::guard('agent')->user();

        $properties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->latest()
            ->paginate(12);

        return view('agent.agent-properties', compact('properties'));
    }

    public function showAddProperty()
    {
        return view('agent.agent-property-add');
    }

    public function storeProperty(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        $request->validate([
            'title_en' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'property_type' => 'required|string',
            'status' => 'required|string',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'area' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        // Calculate USD price (assuming 1 USD = 1320 IQD)
        $priceIQD = $request->price;
        $priceUSD = round($priceIQD / 1320, 2);

        // Generate unique property ID
        do {
            $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (DB::table('properties')->where('id', $propertyId)->exists());

        // Create property with correct structure matching PropertyController
        DB::table('properties')->insert([
            'id' => $propertyId,
            'owner_id' => $agent->id,
            'owner_type' => 'App\Models\Agent',

            // JSON fields
            'name' => json_encode([
                'en' => $request->title_en,
                'ar' => $request->title_ar ?? '',
                'ku' => $request->title_ku ?? '',
            ]),
            'description' => json_encode([
                'en' => $request->description_en ?? '',
                'ar' => $request->description_ar ?? '',
                'ku' => $request->description_ku ?? '',
            ]),
            'type' => json_encode([
                'category' => $request->property_type,
            ]),
            'price' => json_encode([
                'iqd' => (float) $priceIQD,
                'usd' => (float) $priceUSD,
            ]),
            'rooms' => json_encode([
                'bedroom' => ['count' => (int) ($request->bedrooms ?? 0)],
                'bathroom' => ['count' => (int) ($request->bathrooms ?? 0)],
            ]),
            'locations' => json_encode([
                [
                    'lat' => (float) $request->latitude,
                    'lng' => (float) $request->longitude,
                ]
            ]),
            'address_details' => json_encode([
                'city' => [
                    'en' => $request->city_en,
                    'ar' => $request->city_ar ?? '',
                    'ku' => $request->city_ku ?? '',
                ],
                'district' => [
                    'en' => $request->district_en,
                    'ar' => $request->district_ar ?? '',
                    'ku' => $request->district_ku ?? '',
                ],
            ]),

            // Simple fields
            'listing_type' => 'sell',
            'area' => (float) ($request->area ?? 0),
            'furnished' => 0,
            'electricity' => 1,
            'water' => 1,
            'internet' => 0,
            'images' => json_encode($imagePaths),
            'address' => $request->address ?? null,
            'floor_number' => (int) ($request->floors ?? 0),
            'year_built' => (int) ($request->year_built ?? null),

            // Additional required fields
            'features' => json_encode([]),
            'amenities' => json_encode([]),
            'furnishing_details' => json_encode(['status' => 'unfurnished']),
            'floor_details' => null,
            'rental_period' => null,
            'virtual_tour_url' => null,
            'floor_plan_url' => null,
            'availability' => json_encode([
                'status' => 'available',
                'labels' => [
                    'en' => 'Available',
                    'ar' => 'متوفر',
                    'ku' => 'بەردەست'
                ]
            ]),

            // System fields
            'verified' => 0,
            'is_active' => 1,
            'published' => 1,
            'status' => $request->status,
            'views' => 0,
            'favorites_count' => 0,
            'rating' => 0,
            'is_boosted' => 0,
            'view_analytics' => json_encode(['unique_views' => 0, 'returning_views' => 0]),
            'favorites_analytics' => json_encode(['last_30_days' => 0]),

            // Timestamps
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('agent.properties')->with('success', 'Property added successfully!');
    }

    public function showEditProperty($id)
    {
        $agent = Auth::guard('agent')->user();

        $property = Property::where('id', $id)
            ->where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->firstOrFail();

        return view('agent.agent-property-edit', compact('property'));
    }

    public function updateProperty(Request $request, $id)
    {
        $agent = Auth::guard('agent')->user();

        $property = Property::where('id', $id)
            ->where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->firstOrFail();

        $request->validate([
            'title_en' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'property_type' => 'required|string',
            'status' => 'required|string',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Get current images
        $currentImages = is_array($property->images) ? $property->images : json_decode($property->images, true) ?? [];

        // Remove marked images
        if (!empty($request->remove_images)) {
            $removeIndices = json_decode($request->remove_images, true);
            rsort($removeIndices);
            foreach ($removeIndices as $index) {
                if (isset($currentImages[$index])) {
                    unset($currentImages[$index]);
                }
            }
            $currentImages = array_values($currentImages);
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                $currentImages[] = Storage::url($path);
            }
        }

        // Calculate USD price
        $priceIQD = $request->price;
        $priceUSD = round($priceIQD / 1320, 2);

        // Update property with correct structure
        DB::table('properties')
            ->where('id', $id)
            ->update([
                'name' => json_encode([
                    'en' => $request->title_en,
                    'ar' => $request->title_ar ?? '',
                    'ku' => $request->title_ku ?? '',
                ]),
                'description' => json_encode([
                    'en' => $request->description_en ?? '',
                    'ar' => $request->description_ar ?? '',
                    'ku' => $request->description_ku ?? '',
                ]),
                'type' => json_encode([
                    'category' => $request->property_type,
                ]),
                'price' => json_encode([
                    'iqd' => (float) $priceIQD,
                    'usd' => (float) $priceUSD,
                ]),
                'rooms' => json_encode([
                    'bedroom' => ['count' => (int) ($request->bedrooms ?? 0)],
                    'bathroom' => ['count' => (int) ($request->bathrooms ?? 0)],
                ]),
                'locations' => json_encode([
                    [
                        'lat' => (float) $request->latitude,
                        'lng' => (float) $request->longitude,
                    ]
                ]),
                'address_details' => json_encode([
                    'city' => [
                        'en' => $request->city_en,
                        'ar' => $request->city_ar ?? '',
                        'ku' => $request->city_ku ?? '',
                    ],
                    'district' => [
                        'en' => $request->district_en,
                        'ar' => $request->district_ar ?? '',
                        'ku' => $request->district_ku ?? '',
                    ],
                ]),
                'area' => (float) ($request->area ?? 0),
                'images' => json_encode($currentImages),
                'address' => $request->address ?? null,
                'floor_number' => (int) ($request->floors ?? 0),
                'year_built' => (int) ($request->year_built ?? null),
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        return redirect()->route('agent.properties')->with('success', 'Property updated successfully!');
    }

    public function deleteProperty($id)
    {
        $agent = Auth::guard('agent')->user();

        $property = Property::where('id', $id)
            ->where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->firstOrFail();

        $property->delete();

        return redirect()->route('agent.properties')->with('success', 'Property deleted successfully!');
    }

    // SUBSCRIPTIONS
    public function showSubscriptions()
    {
        $agent = Auth::guard('agent')->user();

        $currentSubscription = ModelsSubscription::where('user_id', $agent->id)
            // Remove this line: ->where('user_type', 'App\Models\Agent')
            ->where('status', 'active')
            ->first();

        return view('agent.agent-subscriptions', compact('currentSubscription'));
    }

    // PROFILE
    public function showProfile($id)
    {
        $agent = Auth::guard('agent')->user();

        // Security check: agents can only view their own profile
        if ($agent->id !== $id) {
            abort(403, 'Unauthorized - You can only view your own profile');
        }

        // Load relationships safely
        try {
            $agent->load([
                'properties' => function ($query) {
                    $query->latest()->limit(10);
                },
                'Subscription.currentPlan'
            ]);
        } catch (Exception $e) {
            Log::warning('Could not load agent relationships in profile', [
                'agent_id' => $id,
                'error' => $e->getMessage()
            ]);
        }

        return view('agent.agent-profile', compact('agent'));
    }
}
