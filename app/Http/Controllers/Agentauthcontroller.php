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
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

        // ✅ FIX: Refresh agent to ensure latest data
        $agent->refresh();

        $totalProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->count();

        $activeProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->where('status', 'available')
            ->count();

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
                ->whereYear('created_at', now()->year) // ✅ Added year check
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
                $property->name = is_string($property->name) ? json_decode($property->name, true) : $property->name;
                $property->price = is_string($property->price) ? json_decode($property->price, true) : $property->price;
                $property->images = is_string($property->images) ? json_decode($property->images, true) : $property->images;
                $property->type = is_string($property->type) ? json_decode($property->type, true) : $property->type;
                $property->rooms = is_string($property->rooms) ? json_decode($property->rooms, true) : $property->rooms;
                $property->address_details = is_string($property->address_details) ? json_decode($property->address_details, true) : $property->address_details;
                return $property;
            });

        return view('agent.agent-dashboard', compact('stats', 'recentProperties', 'agent'));
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
        // 1. LOG INCOMING REQUEST
        Log::info('----------------------------------------------------');
        Log::info('🚀 STORE PROPERTY STARTED');
        Log::info('Agent ID: ' . Auth::guard('agent')->id());

        // Log all inputs except the actual image binary data to keep logs clean
        Log::info('📥 Raw Request Data:', $request->except(['images', '_token']));

        try {
            // 1. Validate Subscription
            $validationRedirect = $this->validateSubscription();
            if ($validationRedirect) {
                Log::warning('⚠️ Subscription Validation Failed');
                return $validationRedirect;
            }

            $agent = Auth::guard('agent')->user();

            // 2. Validate the request
            Log::info('⏳ Starting Validation...');

            // We capture the validated data to ensure we are using clean data
            $validatedData = $request->validate([
                'title_en'       => 'required|string|max:255',
                'title_ar'       => 'nullable|string|max:255',
                'title_ku'       => 'nullable|string|max:255',
                'description_en' => 'nullable|string',
                'description_ar' => 'nullable|string',
                'description_ku' => 'nullable|string',
                'price'          => 'required|numeric|min:0',
                'price_usd'      => 'required|numeric|min:0',
                'property_type'  => 'required|string',
                'status'         => 'required|string',
                'city_en'        => 'required|string',
                'district_en'    => 'required|string',
                'has_map'        => 'nullable|boolean',
                'latitude'       => 'required_if:has_map,1|nullable|numeric',
                'longitude'      => 'required_if:has_map,1|nullable|numeric',
                'area'           => 'nullable|numeric',
                'bedrooms'       => 'nullable|integer',
                'bathrooms'      => 'nullable|integer',
                'floors'         => 'nullable|integer',
                'year_built'     => 'nullable|integer',
                'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:30720',
            ]);

            Log::info('✅ Validation Passed');

            // 3. Handle image uploads
            $imagePaths = [];
            if ($request->hasFile('images')) {
                Log::info('📸 Processing Images. Count: ' . count($request->file('images')));

                $manager = new ImageManager(new Driver());

                foreach ($request->file('images') as $index => $image) {
                    try {
                        Log::info("   Processing Image #{$index}: " . $image->getClientOriginalName());

                        $filename = 'prop_' . uniqid() . '_' . time() . '.webp';
                        $storagePath = 'properties/' . $filename;

                        $img = $manager->read($image);
                        $img->scaleDown(width: 1920);
                        $encoded = $img->toWebp(quality: 90);

                        Storage::disk('public')->put($storagePath, (string) $encoded);
                        $imagePaths[] = asset('storage/' . $storagePath);

                        Log::info("   ✅ Image #{$index} saved to: $storagePath");
                    } catch (\Exception $e) {
                        Log::error("   ❌ Image Compression Failed for #{$index}: " . $e->getMessage());
                        // Fallback
                        $path = $image->store('properties', 'public');
                        $imagePaths[] = asset('storage/' . $path);
                    }
                }
            } else {
                Log::warning('⚠️ No images found in request');
            }

            // 4. Generate ID
            do {
                $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            } while (DB::table('properties')->where('id', $propertyId)->exists());

            Log::info('🆔 Generated Property ID: ' . $propertyId);

            // 5. Prepare Location Data
            $locationsJson = json_encode([]);
            if ($request->boolean('has_map') && $request->filled('latitude') && $request->filled('longitude')) {
                $locationsJson = json_encode([
                    [
                        'lat' => (float) $request->latitude,
                        'lng' => (float) $request->longitude,
                    ]
                ]);
            }

            // 6. PREPARE DB DATA (Log this specifically to see invalid types)
            $dbData = [
                'id' => $propertyId,
                'owner_id' => $agent->id,
                'owner_type' => 'App\Models\Agent',
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
                    'iqd' => (float) $request->price,
                    'usd' => (float) $request->price_usd,
                ]),
                'rooms' => json_encode([
                    'bedroom' => ['count' => (int) ($request->bedrooms ?? 0)],
                    'bathroom' => ['count' => (int) ($request->bathrooms ?? 0)],
                ]),
                'locations' => $locationsJson,
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
                'listing_type' => 'sell', // Hardcoded as per your code? Check if this should be dynamic.
                'area' => (float) ($request->area ?? 0),
                'furnished' => $request->boolean('furnished') ? 1 : 0,
                'electricity' => $request->boolean('electricity') ? 1 : 0,
                'water' => $request->boolean('water') ? 1 : 0,
                'internet' => $request->boolean('internet') ? 1 : 0,
                'images' => json_encode($imagePaths),
                'address' => $request->address ?? null,
                'floor_number' => (int) ($request->floors ?? 0),
                'year_built' => (int) ($request->year_built ?? null),
                'features' => json_encode([]),
                'amenities' => json_encode([]),
                'furnishing_details' => json_encode(['status' => 'unfurnished']),
                'floor_details' => null,
                'rental_period' => null,
                'virtual_tour_url' => null,
                'floor_plan_url' => null,
                'availability' => json_encode([
                    'status' => 'available',
                    'labels' => ['en' => 'Available', 'ar' => 'متوفر', 'ku' => 'بەردەست']
                ]),
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
            ];

            Log::info('💾 Attempting DB Insert with Data:', $dbData);

            DB::table('properties')->insert($dbData);

            Log::info('✅ DB Insert Successful');
            return redirect()->route('agent.properties')->with('success', 'Property added successfully!');
        } catch (ValidationException $e) {
            // CATCH VALIDATION ERRORS SPECIFICALLY
            Log::error('❌ VALIDATION FAILED:', $e->errors());
            throw $e; // Throw it back so Laravel redirects with errors
        } catch (\Exception $e) {
            // CATCH EVERYTHING ELSE
            Log::error('🔥 CRITICAL EXCEPTION in storeProperty');
            Log::error('Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' on line ' . $e->getLine());

            // Return a clear error to the UI
            return back()->withInput()->with('error', 'System Error: ' . $e->getMessage());
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

    // ... inside AgentAuthController class

    public function updateProperty(Request $request, $id)
    {
        $agent = Auth::guard('agent')->user();

        // 1. Find Property
        $property = DB::table('properties')
            ->where('id', $id)
            ->where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->first();

        if (!$property) {
            return redirect()->route('agent.properties')->with('error', 'Property not found or unauthorized.');
        }

        // 2. Validate (Updated to match Add Property logic)
        $request->validate([
            'title_en'       => 'required|string|max:255',
            'title_ar'       => 'nullable|string|max:255',
            'title_ku'       => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',

            // Prices: Both Required & Manual
            'price'          => 'required|numeric|min:0', // IQD
            'price_usd'      => 'required|numeric|min:0', // USD

            'property_type'  => 'required|string',
            'status'         => 'required|string',
            'city_en'        => 'required|string',
            'district_en'    => 'required|string',

            // Map Logic
            'has_map'        => 'nullable|boolean',
            'latitude'       => 'required_if:has_map,1|nullable|numeric',
            'longitude'      => 'required_if:has_map,1|nullable|numeric',

            'area'           => 'nullable|numeric',
            'bedrooms'       => 'nullable|integer',
            'bathrooms'      => 'nullable|integer',
            'floors'         => 'nullable|integer',
            'year_built'     => 'nullable|integer',
            'images.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // 3. Handle Images (Remove deleted, Add new)
        $currentImages = json_decode($property->images, true) ?? [];

        // Remove images marked for deletion
        if ($request->filled('remove_images')) {
            $removeIndices = json_decode($request->remove_images, true);
            if (is_array($removeIndices)) {
                rsort($removeIndices);
                foreach ($removeIndices as $index) {
                    if (isset($currentImages[$index])) {
                        // Optional: Delete physical file
                        $filePath = str_replace(asset('storage/'), '', $currentImages[$index]);
                        Storage::disk('public')->delete($filePath);
                        unset($currentImages[$index]);
                    }
                }
                $currentImages = array_values($currentImages);
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                $currentImages[] = asset('storage/' . $path);
            }
        }

        // 4. Map Logic (Empty JSON if disabled)
        $locationsJson = json_encode([]);
        if ($request->boolean('has_map') && $request->filled('latitude') && $request->filled('longitude')) {
            $locationsJson = json_encode([[
                'lat' => (float) $request->latitude,
                'lng' => (float) $request->longitude,
            ]]);
        }

        // 5. Update Database
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
                    // Update Prices (Manual USD)
                    'price' => json_encode([
                        'iqd' => (float) $request->price,
                        'usd' => (float) $request->price_usd,
                    ]),
                    'type' => json_encode(['category' => $request->property_type]),
                    'rooms' => json_encode([
                        'bedroom' => ['count' => (int) ($request->bedrooms ?? 0)],
                        'bathroom' => ['count' => (int) ($request->bathrooms ?? 0)],
                    ]),
                    // Update Location (Empty or Coordinates)
                    'locations' => $locationsJson,
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
                    'furnished' => $request->boolean('furnished') ? 1 : 0,
                    'electricity' => $request->boolean('electricity') ? 1 : 0,
                    'water' => $request->boolean('water') ? 1 : 0,
                    'internet' => $request->boolean('internet') ? 1 : 0,
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
        try {
            $agent = Auth::guard('agent')->user();

            // 1. Log the Agent trying to view the page
            Log::info('------------------------------------------');
            Log::info('ShowSubscriptions: User Request', [
                'agent_id' => $agent->id ?? 'unknown',
                'agent_name' => $agent->agent_name ?? 'unknown'
            ]);

            // 2. Log Current Subscription Search
            $currentSubscription = ModelsSubscription::with('currentPlan')
                ->where('user_id', $agent->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            Log::info('ShowSubscriptions: Current Subscription Found?', [
                'found' => $currentSubscription ? 'Yes' : 'No',
                'plan_name' => $currentSubscription?->currentPlan?->name ?? 'N/A'
            ]);

            // 3. Build the Plans Query to inspect SQL
            $plansQuery = SubscriptionPlan::where('type', 'agent')
                ->active() // checking active scope
                ->orderBy('sort_order', 'asc');

            // 4. Log the exact SQL being executed
            Log::info('ShowSubscriptions: Plans Query SQL', [
                'sql' => $plansQuery->toSql(),
                'bindings' => $plansQuery->getBindings()
            ]);

            // 5. Execute Query
            $plans = $plansQuery->get();

            // 6. Log the results
            Log::info('ShowSubscriptions: Plans Results', [
                'count' => $plans->count(),
                'names_found' => $plans->pluck('name')->toArray(),
                'ids_found' => $plans->pluck('id')->toArray()
            ]);

            // 7. Debug: Check for NON-active plans (To see if they exist but are hidden)
            $hiddenPlans = SubscriptionPlan::where('type', 'agent')->where('active', 0)->count();
            Log::info('ShowSubscriptions: DEBUG - Inactive/Hidden Plans Count', ['count' => $hiddenPlans]);

            return view('agent.agent-subscriptions', compact('currentSubscription', 'plans'));
        } catch (\Exception $e) {
            Log::error('ShowSubscriptions: Error', ['message' => $e->getMessage()]);
            return back()->with('error', 'Error loading subscriptions');
        }
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

        // ✅ FIX: Refresh agent from database to get latest data
        $agent->refresh();

        // ✅ FIX: Calculate statistics using Query Builder instead of relationship
        $totalProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->count();

        $activeProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->where('status', 'available')
            ->count();

        $soldProperties = Property::where('owner_id', $agent->id)
            ->where('owner_type', 'App\Models\Agent')
            ->where('status', 'sold')
            ->count();

        // Add calculated stats to agent object
        $agent->total_properties = $totalProperties;
        $agent->active_properties = $activeProperties;
        $agent->properties_sold = $soldProperties;

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

        // 1. LOGGING: Check exactly what is coming from the form
        Log::info('Agent Profile Update - Request Data:', [
            'agent_id' => $agent->id,
            'working_hours_raw' => $request->input('working_hours'),
            'has_profile_image' => $request->hasFile('profile_image'),
            'has_bio_image' => $request->hasFile('bio_image'),
        ]);

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
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'bio_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            // Allow JSON string or null
            'working_hours' => 'nullable|string',
        ]);

        try {
            // --- 1. PROFILE IMAGE FIX ---
            if ($request->hasFile('profile_image')) {
                if ($agent->profile_image) {
                    $oldPath = str_replace('storage/', '', $agent->profile_image);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $agent->profile_image = $request->file('profile_image')->store('agents/profiles', 'public');
            }

            // --- 2. BIO IMAGE FIX ---
            if ($request->hasFile('bio_image')) {
                if ($agent->bio_image) {
                    $oldPath = str_replace('storage/', '', $agent->bio_image);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $agent->bio_image = $request->file('bio_image')->store('agents/bio', 'public');
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

            // --- 3. WORKING HOURS LOGIC & LOGGING ---
            if ($request->filled('working_hours')) {
                $rawHours = $request->input('working_hours');

                // Try to decode the JSON string from frontend
                $decodedHours = json_decode($rawHours, true);

                // Check for JSON errors
                if (json_last_error() === JSON_ERROR_NONE) {
                    $agent->working_hours = $decodedHours;
                    Log::info('Working hours processed successfully', ['data' => $decodedHours]);
                } else {
                    Log::error('Working hours JSON decode error', [
                        'error' => json_last_error_msg(),
                        'raw_input' => $rawHours
                    ]);
                    // Optional: Don't save if invalid, or save empty array
                    // $agent->working_hours = [];
                }
            } else {
                Log::info('No working hours provided in request.');
            }

            $agent->save();

            // Refresh Auth
            Auth::guard('agent')->setUser($agent->fresh());

            return redirect()->route('agent.profile', $agent->id)
                ->with('success', 'Profile updated successfully!');
        } catch (Exception $e) {
            Log::error('Agent profile update CRITICAL FAILURE', [
                'agent_id' => $agent->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->with('error', 'Failed to update profile. ' . $e->getMessage());
        }
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
