<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Property;
use App\Models\Subscription\Subscription as ModelsSubscription;
use App\Models\BannerAd;
use App\Models\Appointment;
use App\Models\Subscription\SubscriptionPlan;
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

        // Calculate total views
        $totalViews = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->sum('views');

        $stats = [
            'total_properties' => $totalProperties,
            'active_properties' => $activeProperties,
            'active_percentage' => $totalProperties > 0 ? round(($activeProperties / $totalProperties) * 100) : 0,
            'new_this_month' => Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\Models\Agent')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'total_views' => $totalViews,
            'views_this_week' => Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\Models\Agent')
                ->where('updated_at', '>=', now()->subWeek())
                ->sum('views'),
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
            ->get()
            ->map(function ($property) {
                // Decode JSON fields
                $property->name = is_string($property->name) ? json_decode($property->name, true) : $property->name;
                $property->price = is_string($property->price) ? json_decode($property->price, true) : $property->price;
                $property->images = is_string($property->images) ? json_decode($property->images, true) : $property->images;
                $property->type = is_string($property->type) ? json_decode($property->type, true) : $property->type;
                $property->rooms = is_string($property->rooms) ? json_decode($property->rooms, true) : $property->rooms;
                $property->address_details = is_string($property->address_details) ? json_decode($property->address_details, true) : $property->address_details;
                return $property;
            });

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

        // Decode JSON fields for each property
        $properties->getCollection()->transform(function ($property) {
            $property->name = is_string($property->name) ? json_decode($property->name, true) : $property->name;
            $property->price = is_string($property->price) ? json_decode($property->price, true) : $property->price;
            $property->images = is_string($property->images) ? json_decode($property->images, true) : $property->images;
            $property->type = is_string($property->type) ? json_decode($property->type, true) : $property->type;
            $property->rooms = is_string($property->rooms) ? json_decode($property->rooms, true) : $property->rooms;
            $property->address_details = is_string($property->address_details) ? json_decode($property->address_details, true) : $property->address_details;
            return $property;
        });

        return view('agent.agent-properties', compact('properties'));
    }

    public function showAddProperty()
    {
        return view('agent.agent-property-add');
    }

    public function storeProperty(Request $request)
    {

        $validationRedirect = $this->validateSubscription();
        if ($validationRedirect) {
            return $validationRedirect;
        }
        // 1. Get the authenticated Agent
        $agent = Auth::guard('agent')->user();

        // 2. Validate the request
        // We added 'price_usd', 'title_ar', 'title_ku', and more to ensure everything is caught
        $request->validate([
            'title_en' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'title_ku' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'price_usd' => 'nullable|numeric|min:0', // Manual USD input
            'property_type' => 'required|string',
            'status' => 'required|string',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'area' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'floors' => 'nullable|integer',
            'year_built' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // 3. Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Stores in storage/app/public/properties
                $path = $image->store('properties', 'public');
                // Generates a full URL for the Flutter app to consume
                $imagePaths[] = asset('storage/' . $path);
            }
        }

        // 4. Currency Logic
        $priceIQD = $request->price;
        // If the user didn't provide a USD price, calculate it using the 1320 rate
        $priceUSD = $request->filled('price_usd')
            ? $request->price_usd
            : round($priceIQD / 1320, 2);

        // 5. Generate unique property ID (prop_YYYY_MM_DD_xxxxx)
        do {
            $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (DB::table('properties')->where('id', $propertyId)->exists());

        // 6. Database Insertion
        try {
            DB::table('properties')->insert([
                'id' => $propertyId,
                'owner_id' => $agent->id,
                'owner_type' => 'App\Models\Agent',

                // Multi-language Name
                'name' => json_encode([
                    'en' => $request->title_en,
                    'ar' => $request->title_ar ?? '',
                    'ku' => $request->title_ku ?? '',
                ]),

                // Multi-language Description
                'description' => json_encode([
                    'en' => $request->description_en ?? '',
                    'ar' => $request->description_ar ?? '',
                    'ku' => $request->description_ku ?? '',
                ]),

                // Property Category
                'type' => json_encode([
                    'category' => $request->property_type,
                ]),

                // Structured Price (Crucial for Flutter)
                'price' => json_encode([
                    'iqd' => (float) $priceIQD,
                    'usd' => (float) $priceUSD,
                ]),

                // Room details
                'rooms' => json_encode([
                    'bedroom' => ['count' => (int) ($request->bedrooms ?? 0)],
                    'bathroom' => ['count' => (int) ($request->bathrooms ?? 0)],
                ]),

                // Map Coordinates
                'locations' => json_encode([
                    [
                        'lat' => (float) $request->latitude,
                        'lng' => (float) $request->longitude,
                    ]
                ]),

                // Detailed Address Structure
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

                // Static/Standard Fields
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

                // Default JSON fields to prevent null errors in the app
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

                // System & Metadata
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

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('agent.properties')->with('success', 'Property added successfully!');
        } catch (\Exception $e) {
            // Log the error if something goes wrong with the JSON or DB
            Log::error('Property Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to add property. Please check your data.');
        }
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

        // 1. Find the property and ensure this agent owns it
        $property = DB::table('properties')
            ->where('id', $id)
            ->where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->first();

        if (!$property) {
            return redirect()->route('agent.properties')->with('error', 'Property not found or unauthorized.');
        }

        // 2. Validate the request (Matching your Add form)
        $request->validate([
            'title_en' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'price_usd' => 'nullable|numeric|min:0',
            'property_type' => 'required|string',
            'status' => 'required|string',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'area' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
        ]);

        // 3. Handle Image Logic
        $currentImages = json_decode($property->images, true) ?? [];

        // Remove marked images from the array
        if ($request->filled('remove_images')) {
            $removeIndices = json_decode($request->remove_images, true);
            if (is_array($removeIndices)) {
                rsort($removeIndices); // Sort descending to prevent index shifting
                foreach ($removeIndices as $index) {
                    if (isset($currentImages[$index])) {
                        // Optional: Delete the actual file from storage
                        $filePath = str_replace(asset('storage/'), '', $currentImages[$index]);
                        Storage::disk('public')->delete($filePath);

                        unset($currentImages[$index]);
                    }
                }
                $currentImages = array_values($currentImages); // Re-index array
            }
        }

        // Add new uploaded images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                $currentImages[] = asset('storage/' . $path);
            }
        }

        // 4. Currency Logic
        $priceIQD = $request->price;
        $priceUSD = $request->filled('price_usd')
            ? $request->price_usd
            : round($priceIQD / 1320, 2);

        // 5. Execute the Update
        try {
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
                    'price' => json_encode([
                        'iqd' => (float) $priceIQD,
                        'usd' => (float) $priceUSD,
                    ]),
                    'type' => json_encode(['category' => $request->property_type]),
                    'rooms' => json_encode([
                        'bedroom' => ['count' => (int) ($request->bedrooms ?? 0)],
                        'bathroom' => ['count' => (int) ($request->bathrooms ?? 0)],
                    ]),
                    'locations' => json_encode([[
                        'lat' => (float) $request->latitude,
                        'lng' => (float) $request->longitude,
                    ]]),
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
        } catch (\Exception $e) {
            Log::error('Property Update Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
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
    // App/Http/Controllers/AgentAuthController.php

    public function showSubscriptions()
    {
        $agent = Auth::guard('agent')->user();

        $currentSubscription = ModelsSubscription::with('currentPlan')
            ->where('user_id', $agent->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        // Use the scopeActive() from your model if you prefer
        $plans = SubscriptionPlan::where('type', 'agent')
            ->active() // This calls scopeActive from your model
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('agent.agent-subscriptions', compact('currentSubscription', 'plans'));
    }
    // APPOINTMENTS
    public function showAppointments()
    {
        $agent = Auth::guard('agent')->user();

        $appointments = Appointment::where(function ($query) use ($agent) {
            $query->where('agent_id', $agent->id)
                ->orWhereHas('property', function ($q) use ($agent) {
                    $q->where('owner_id', $agent->id)
                        ->where('owner_type', 'Agent');
                });
        })
            ->with(['user', 'property'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(15);

        $stats = [
            'total' => Appointment::where('agent_id', $agent->id)->count(),
            'pending' => Appointment::where('agent_id', $agent->id)->where('status', 'pending')->count(),
            'confirmed' => Appointment::where('agent_id', $agent->id)->where('status', 'confirmed')->count(),
            'completed' => Appointment::where('agent_id', $agent->id)->where('status', 'completed')->count(),
        ];

        return view('agent.agent-appointments', compact('appointments', 'stats'));
    }

    public function updateAppointmentStatus(Request $request, $id)
    {
        $agent = Auth::guard('agent')->user();

        $appointment = Appointment::where('id', $id)
            ->where('agent_id', $agent->id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        $appointment->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Appointment status updated successfully!');
    }

    // BANNERS
    public function showBanners(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        $query = BannerAd::where('owner_type', 'agent')
            ->where('owner_id', $agent->id);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $banners = $query->orderBy('created_at', 'desc')->paginate(12);

        $stats = [
            'total' => BannerAd::where('owner_type', 'agent')->where('owner_id', $agent->id)->count(),
            'active' => BannerAd::where('owner_type', 'agent')->where('owner_id', $agent->id)->where('status', 'active')->count(),
            'draft' => BannerAd::where('owner_type', 'agent')->where('owner_id', $agent->id)->where('status', 'draft')->count(),
            'paused' => BannerAd::where('owner_type', 'agent')->where('owner_id', $agent->id)->where('status', 'paused')->count(),
        ];

        return view('agent.agent-banners', compact('banners', 'stats'));
    }

    public function showAddBanner()
    {
        $agent = Auth::guard('agent')->user();

        $properties = Property::where('owner_type', 'Agent')
            ->where('owner_id', $agent->id)
            ->get();

        return view('agent.agent-banner-add', compact('properties'));
    }

    public function storeBanner(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        $request->validate([
            'title' => 'required|string|min:5|max:255',
            'banner_type' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'banner_size' => 'required|string',
            'position' => 'required|string',
            'start_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'link_url' => 'nullable|url|max:500',
            'call_to_action' => 'nullable|string|max:50',
            'property_id' => 'nullable|exists:properties,id',
            'end_date' => 'nullable|date|after:start_date',
        ], [
            'title.min' => 'Banner title must be at least 5 characters.',
            'title.required' => 'Banner title is required.',
            'end_date.after' => 'End date must be after start date.',
        ]);

        // Upload image
        $imagePath = $request->file('image')->store('banners', 'public');
        $imageUrl = asset('storage/' . $imagePath); // ✅ FIXED

        BannerAd::create([
            'title' => json_encode(['en' => $request->title, 'ar' => $request->title, 'ku' => $request->title]),  // JSON format for multi-language
            'description' => $request->description ? json_encode(['en' => $request->description, 'ar' => $request->description, 'ku' => $request->description]) : null,
            'call_to_action' => $request->call_to_action ? json_encode(['en' => $request->call_to_action, 'ar' => $request->call_to_action, 'ku' => $request->call_to_action]) : null,
            'image_url' => $imageUrl,

            'link_url' => $request->link_url,
            'link_opens_new_tab' => $request->has('link_opens_new_tab'),
            'owner_type' => 'agent',
            'owner_id' => $agent->id,
            'owner_name' => $agent->agent_name,
            'owner_email' => $agent->primary_email,
            'owner_phone' => $agent->primary_phone,
            'banner_type' => $request->banner_type,
            'property_id' => $request->property_id,
            'banner_size' => $request->banner_size,
            'position' => $request->position,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'call_to_action' => $request->call_to_action,
            'show_contact_info' => $request->has('show_contact_info'),
            'status' => 'draft',
            'is_active' => false,
            'views' => 0,
            'clicks' => 0,
            'ctr' => 0,
        ]);

        return redirect()->route('agent.banners')->with('success', 'Banner created successfully! Pending admin approval.');
    }

    public function editBanner($id)
    {
        $agent = Auth::guard('agent')->user();

        $banner = BannerAd::where('owner_type', 'agent')
            ->where('owner_id', $agent->id)
            ->findOrFail($id);

        $properties = Property::where('owner_type', 'Agent')
            ->where('owner_id', $agent->id)
            ->get();

        return view('agent.agent-banner-edit', compact('banner', 'properties'));
    }

    public function updateBanner(Request $request, $id)
    {
        $agent = Auth::guard('agent')->user();

        $banner = BannerAd::where('owner_type', 'agent')
            ->where('owner_id', $agent->id)
            ->findOrFail($id);

        $request->validate([
            'title' => 'required|string|min:5|max:255',
            'banner_type' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'banner_size' => 'required|string',
            'position' => 'required|string',
            'start_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'link_url' => 'nullable|url|max:500',
            'call_to_action' => 'nullable|string|max:50',
            'property_id' => 'nullable|exists:properties,id',
            'end_date' => 'nullable|date|after:start_date',
        ], [
            'title.min' => 'Banner title must be at least 5 characters.',
            'title.required' => 'Banner title is required.',
            'end_date.after' => 'End date must be after start date.',
        ]);

        $data = [
            'title' => json_encode(['en' => $request->title, 'ar' => $request->title, 'ku' => $request->title]),  // JSON format for multi-language
            'description' => $request->description ? json_encode(['en' => $request->description, 'ar' => $request->description, 'ku' => $request->description]) : null,
            'call_to_action' => $request->call_to_action ? json_encode(['en' => $request->call_to_action, 'ar' => $request->call_to_action, 'ku' => $request->call_to_action]) : null,
            'link_url' => $request->link_url,
            'link_opens_new_tab' => $request->has('link_opens_new_tab'),
            'banner_type' => $request->banner_type,
            'property_id' => $request->property_id,
            'banner_size' => $request->banner_size,
            'position' => $request->position,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'call_to_action' => $request->call_to_action,
            'show_contact_info' => $request->has('show_contact_info'),
        ];

        // Upload new image if provided
        // ✅ Upload new image if provided - SAME AS OFFICE CONTROLLER
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
            $data['image_url'] = asset('storage/' . $imagePath); // ✅ FIXED
        }

        $banner->update($data);

        return redirect()->route('agent.banners')->with('success', 'Banner updated successfully!');
    }

    public function deleteBanner($id)
    {
        $agent = Auth::guard('agent')->user();

        $banner = BannerAd::where('owner_type', 'agent')
            ->where('owner_id', $agent->id)
            ->findOrFail($id);

        $banner->delete();

        return redirect()->back()->with('success', 'Banner deleted successfully!');
    }

    public function pauseBanner($id)
    {
        $agent = Auth::guard('agent')->user();

        $banner = BannerAd::where('owner_type', 'agent')
            ->where('owner_id', $agent->id)
            ->findOrFail($id);

        $banner->pause();

        return redirect()->back()->with('success', 'Banner paused successfully!');
    }

    public function resumeBanner($id)
    {
        $agent = Auth::guard('agent')->user();

        $banner = BannerAd::where('owner_type', 'agent')
            ->where('owner_id', $agent->id)
            ->findOrFail($id);

        $banner->resume();

        return redirect()->back()->with('success', 'Banner resumed successfully!');
    }

    public function bannerAnalytics($id)
    {
        $agent = Auth::guard('agent')->user();

        $banner = BannerAd::where('owner_type', 'agent')
            ->where('owner_id', $agent->id)
            ->findOrFail($id);

        $metrics = $banner->getPerformanceMetrics();

        return view('agent.agent-banner-analytics', compact('banner', 'metrics'));
    }

    // PROFILE
    public function showProfile($id)
    {
        $agent = Auth::guard('agent')->user();

        if ($agent->id !== $id) {
            abort(403, 'Unauthorized');
        }

        return view('agent.agent-profile', compact('agent'));
    }

    public function showEditProfile()
    {
        $agent = Auth::guard('agent')->user();
        return view('agent.agent-edit-profile', compact('agent'));
    }

    public function updateProfile(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        $request->validate([
            'agent_name' => 'required|string|max:255',
            'primary_phone' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'city' => 'required|string',
            'district' => 'nullable|string',
            'license_number' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'agent_bio' => 'nullable|string|max:1000',
            'office_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'bio_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'working_hours' => 'nullable|json', // ✅ Added validation for the JSON string
        ]);

        // ✅ Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($agent->profile_image) {
                $oldPath = str_replace(asset('storage/'), '', $agent->profile_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('profile_image')->store('agents/profiles', 'public');
            $agent->profile_image = asset('storage/' . $path);
        }

        // ✅ Handle bio image upload
        if ($request->hasFile('bio_image')) {
            if ($agent->bio_image) {
                $oldPath = str_replace(asset('storage/'), '', $agent->bio_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('bio_image')->store('agents/bio', 'public');
            $agent->bio_image = asset('storage/' . $path);
        }

        // Update basic fields
        $agent->agent_name = $request->agent_name;
        $agent->primary_phone = $request->primary_phone;
        $agent->whatsapp_number = $request->whatsapp_number;
        $agent->city = $request->city;
        $agent->district = $request->district;
        $agent->license_number = $request->license_number;
        $agent->years_experience = $request->years_experience;
        $agent->agent_bio = $request->agent_bio;
        $agent->office_address = $request->office_address;
        $agent->latitude = $request->latitude;
        $agent->longitude = $request->longitude;

        // ✅ THE MISSING PIECE: Save Working Hours
        if ($request->has('working_hours')) {
            // We store it as a JSON string directly into the database column
            $agent->working_hours = $request->working_hours;
        }

        $agent->save();

        return redirect()->route('agent.profile', $agent->id)->with('success', 'Profile and Working Hours updated successfully!');
    }

    public function showChangePassword()
    {
        return view('agent.agent-change-password');
    }

    public function updatePassword(Request $request)
    {
        $agent = Auth::guard('agent')->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $agent->password)) {
            return back()->with('error', 'Current password is incorrect');
        }

        // Update password
        $agent->password = Hash::make($request->new_password);
        $agent->save();

        return redirect()->route('agent.profile', $agent->id)->with('success', 'Password changed successfully!');
    }

    private function validateSubscription()
    {
        $agent = Auth::guard('agent')->user();

        // 1. Check if subscription exists
        $subscription = ModelsSubscription::where('user_id', $agent->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$subscription) {
            return redirect()->route('agent.subscriptions')
                ->with('error', 'You need an active subscription to add properties. Please subscribe.');
        }

        // 2. Check if expired
        if ($subscription->end_date < now()) {
            return redirect()->route('agent.subscriptions')
                ->with('error', 'Your subscription has expired. Please renew to continue.');
        }

        // 3. Check Property Limit (If limit is 0, we assume it means Unlimited or handle differently based on your logic)
        // Assuming > 0 is a limit. If your unlimited plan stores -1 or 0, adjust accordingly.
        $limit = $subscription->property_activation_limit;

        if ($limit > 0) {
            $currentCount = Property::where('owner_id', $agent->id)
                ->where('owner_type', 'App\Models\Agent')
                ->count();

            if ($currentCount >= $limit) {
                return redirect()->route('agent.properties')
                    ->with('error', "You have reached your limit of {$limit} properties. Please upgrade your plan.");
            }
        }

        return null; // Passed all checks
    }
}
