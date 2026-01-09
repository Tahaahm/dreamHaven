<?php

namespace App\Http\Controllers;

use App\Models\RealEstateOffice;
use App\Models\Agent;
use App\Models\Property;
use App\Models\Appointment;
use App\Models\Project;
use App\Models\Subscription\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OfficeAuthController extends Controller
{
    // ==================== AUTHENTICATION ====================

    public function showLogin()
    {
        return view('office.login');
    }

    public function showRegister()
    {
        return view('office.register');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $credentials = [
            'email_address' => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->filled('remember');

        if (Auth::guard('office')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('office.dashboard'))
                ->with('success', 'Welcome back, ' . Auth::guard('office')->user()->company_name . '!');
        }

        return redirect()->back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->withInput($request->only('email'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|unique:real_estate_offices,email_address',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'district' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $office = RealEstateOffice::create([
            'company_name' => $request->company_name,
            'email_address' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'city' => $request->city,
            'district' => $request->district,
            'account_type' => 'real_estate_official',
            'is_verified' => false,
        ]);

        Auth::guard('office')->login($office);
        $request->session()->regenerate();

        return redirect()->route('office.dashboard')
            ->with('success', 'Registration successful! Welcome to Dream Haven.');
    }

    public function logout(Request $request)
    {
        Auth::guard('office')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('office.login')
            ->with('success', 'Logged out successfully.');
    }

    // ==================== DASHBOARD ====================

    public function dashboard(Request $request)
    {
        $office = Auth::guard('office')->user();

        // Get search query
        $search = $request->get('search', '');

        // ==================== REAL STATISTICS ====================

        // Total properties
        $totalProperties = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->count();

        // Total agents
        $totalAgents = Agent::where('company_id', $office->id)->count();

        // Total appointments
        $totalAppointments = Appointment::where('office_id', $office->id)->count();
        $pendingAppointments = Appointment::where('office_id', $office->id)
            ->where('status', 'pending')
            ->whereDate('appointment_date', today())
            ->count();

        // ==================== GROWTH CALCULATIONS ====================

        // Property growth (last 30 days vs previous 30 days)
        $propertiesThisMonth = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $propertiesLastMonth = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();

        $propertyGrowth = $propertiesLastMonth > 0
            ? round((($propertiesThisMonth - $propertiesLastMonth) / $propertiesLastMonth) * 100, 1)
            : 0;

        // Agent growth
        $agentsThisMonth = Agent::where('company_id', $office->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $agentsLastMonth = Agent::where('company_id', $office->id)
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();

        $agentGrowth = $agentsLastMonth > 0
            ? round((($agentsThisMonth - $agentsLastMonth) / $agentsLastMonth) * 100, 1)
            : 0;

        // ==================== REVENUE CALCULATION ====================

        // Calculate total revenue from sold/rented properties
        $soldProperties = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->where('status', 'sold')
            ->get();

        $totalRevenue = 0;
        foreach ($soldProperties as $property) {
            $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
            $totalRevenue += $price['usd'] ?? 0;
        }

        // Revenue growth
        $revenueThisMonth = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->where('status', 'sold')
            ->where('updated_at', '>=', now()->subDays(30))
            ->get()
            ->sum(function ($property) {
                $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
                return $price['usd'] ?? 0;
            });

        $revenueLastMonth = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->where('status', 'sold')
            ->whereBetween('updated_at', [now()->subDays(60), now()->subDays(30)])
            ->get()
            ->sum(function ($property) {
                $price = is_array($property->price) ? $property->price : json_decode($property->price, true);
                return $price['usd'] ?? 0;
            });

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

        // ==================== RECENT PROPERTIES WITH SEARCH ====================

        $recentPropertiesQuery = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id);

        // Apply search if provided
        if (!empty($search)) {
            $recentPropertiesQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%")
                    ->orWhere('listing_type', 'LIKE', "%{$search}%");
            });
        }

        $recentProperties = $recentPropertiesQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // ==================== TOP AGENTS ====================

        $topAgents = Agent::where('company_id', $office->id)
            ->withCount(['ownedProperties' => function ($query) {
                $query->where('status', 'available');
            }])
            ->orderBy('owned_properties_count', 'desc')
            ->limit(5)
            ->get();

        return view('office.dashboard', compact(
            'office',
            'totalProperties',
            'totalAgents',
            'totalAppointments',
            'pendingAppointments',
            'totalRevenue',
            'propertyGrowth',
            'agentGrowth',
            'revenueGrowth',
            'recentProperties',
            'topAgents',
            'search'
        ));
    }

    // ==================== PROFILE ====================

    public function showProfile()
    {
        $office = Auth::guard('office')->user();
        return view('office.profile', compact('office'));
    }



    public function updateProfile(Request $request)
    {
        $office = auth('office')->user();

        $request->validate([
            'company_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'company_bio' => 'nullable|string',
            'about_company' => 'nullable|string',
            'properties_sold' => 'nullable|integer|min:0',
            'years_experience' => 'nullable|integer|min:0',
            'office_address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'availability_schedule' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'company_bio_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $data = $request->only([
            'company_name',
            'phone_number',
            'company_bio',
            'about_company',
            'properties_sold',
            'years_experience',
            'office_address',
            'city',
            'district',
            'latitude',
            'longitude',
        ]);

        // ✅ Transform schedule format
        if ($request->has('availability_schedule') && $request->availability_schedule) {
            try {
                $scheduleData = json_decode($request->availability_schedule, true);
                $transformedSchedule = [];

                // Define all days
                $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                foreach ($allDays as $day) {
                    if (isset($scheduleData[$day]) && is_array($scheduleData[$day])) {
                        // Day is available with open/close times
                        $open = $scheduleData[$day]['open'] ?? '09:00';
                        $close = $scheduleData[$day]['close'] ?? '18:00';
                        $transformedSchedule[$day] = "{$open}-{$close}";
                    } else {
                        // Day is not available
                        $transformedSchedule[$day] = 'closed';
                    }
                }

                $data['availability_schedule'] = json_encode($transformedSchedule);
            } catch (\Exception $e) {
                Log::error('Schedule transformation error: ' . $e->getMessage());
                // If transformation fails, keep original or set to null
                $data['availability_schedule'] = null;
            }
        }

        // ✅ Handle profile image
        if ($request->hasFile('profile_image')) {
            if ($office->profile_image && Storage::disk('public')->exists($office->profile_image)) {
                Storage::disk('public')->delete($office->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('office_profiles', 'public');
        }

        // ✅ Handle bio image
        if ($request->hasFile('company_bio_image')) {
            if ($office->company_bio_image && Storage::disk('public')->exists($office->company_bio_image)) {
                Storage::disk('public')->delete($office->company_bio_image);
            }
            $data['company_bio_image'] = $request->file('company_bio_image')->store('office_bio_images', 'public');
        }

        $office->update($data);

        return redirect()->route('office.profile')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $office = auth('office')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $office->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $office->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('office.profile')->with('success', 'Password changed successfully!');
    }

    // ==================== PROPERTIES ====================

    public function showProperties()
    {
        $office = Auth::guard('office')->user();

        $properties = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('office.properties', compact('properties'));
    }





    public function editProperty($id)
    {
        $office = Auth::guard('office')->user();

        $property = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        return view('office.property-edit', compact('property'));
    }

    public function updateProperty(Request $request, $id)
    {
        $property = Property::findOrFail($id);

        // Check ownership
        if ($property->owner_type !== 'App\Models\RealEstateOffice' || $property->owner_id !== auth('office')->id()) {
            abort(403, 'Unauthorized');
        }

        // Validate
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'property_type' => 'required|string',
            'listing_type' => 'required|in:sell,rent',
            'status' => 'required|in:available,sold,rented',
            'area' => 'required|numeric|min:1',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'floor_number' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:2030',
            'price_usd' => 'required|numeric|min:0',
            'price_iqd' => 'required|numeric|min:0',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'city_ar' => 'nullable|string',
            'district_ar' => 'nullable|string',
            'city_ku' => 'nullable|string',
            'district_ku' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'images.*' => 'nullable|image|max:5120',
            'remove_images' => 'nullable|string',
        ]);

        // Update name
        $property->name = [
            'en' => $validated['name_en'],
            'ar' => $validated['name_ar'] ?? '',
            'ku' => $validated['name_ku'] ?? '',
        ];

        // Update description
        $property->description = [
            'en' => $validated['description_en'],
            'ar' => $validated['description_ar'] ?? '',
            'ku' => $validated['description_ku'] ?? '',
        ];

        // Update type
        $property->type = [
            'category' => $validated['property_type'],
        ];

        // Update price
        $property->price = [
            'usd' => $validated['price_usd'],
            'iqd' => $validated['price_iqd'],
        ];

        // Update rooms
        $property->rooms = [
            'bedroom' => ['count' => $validated['bedrooms']],
            'bathroom' => ['count' => $validated['bathrooms']],
        ];

        // Update address
        $property->address_details = [
            'city' => [
                'en' => $validated['city_en'],
                'ar' => $validated['city_ar'] ?? '',
                'ku' => $validated['city_ku'] ?? '',
            ],
            'district' => [
                'en' => $validated['district_en'],
                'ar' => $validated['district_ar'] ?? '',
                'ku' => $validated['district_ku'] ?? '',
            ],
        ];

        // Update locations
        $property->locations = [[
            'lat' => $validated['latitude'],
            'lng' => $validated['longitude'],
        ]];

        // Update simple fields
        $property->listing_type = $validated['listing_type'];
        $property->status = $validated['status'];
        $property->area = $validated['area'];
        $property->floor_number = $validated['floor_number'] ?? null;
        $property->year_built = $validated['year_built'] ?? null;
        $property->address = $validated['address'] ?? '';

        // Update utilities
        $property->furnished = $request->has('furnished') ? 1 : 0;
        $property->electricity = $request->has('electricity') ? 1 : 0;
        $property->water = $request->has('water') ? 1 : 0;
        $property->internet = $request->has('internet') ? 1 : 0;

        // ==================== HANDLE IMAGE UPDATES ====================

        // Get current images
        $currentImages = is_array($property->images) ? $property->images : json_decode($property->images, true);
        $currentImages = $currentImages ?? [];

        // Remove images if requested
        if (!empty($request->remove_images)) {
            $removeIndices = json_decode($request->remove_images, true);
            if (is_array($removeIndices)) {
                // Sort in reverse order to remove from end to start
                rsort($removeIndices);
                foreach ($removeIndices as $index) {
                    if (isset($currentImages[$index])) {
                        unset($currentImages[$index]);
                    }
                }
                // Re-index array
                $currentImages = array_values($currentImages);
            }
        }

        // Add new images if uploaded
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                $currentImages[] = Storage::url($path);
            }
        }

        // Update images
        $property->images = $currentImages;

        $property->save();

        return redirect()->route('office.properties')->with('success', 'Property updated successfully!');
    }
    public function deleteProperty($id)
    {
        $office = Auth::guard('office')->user();

        $property = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $property->delete();

        return redirect()->route('office.properties')
            ->with('success', 'Property deleted successfully!');
    }

    // ==================== AGENTS ====================

    public function showAgents()
    {
        $office = auth('office')->user();

        // Get only agents belonging to this office
        $agents = Agent::where('company_id', $office->id)
            ->withCount('ownedProperties')
            ->get();

        return view('office.agents', compact('agents'));
    }

    public function showAddAgent()
    {
        $office = auth('office')->user();

        // Only get agents that don't have an office assigned
        $availableAgents = Agent::whereNull('company_id')
            ->orWhere('company_id', '')
            ->get();

        return view('office.agent-add', compact('availableAgents'));
    }

    public function storeAgent(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id'
        ]);

        $office = auth('office')->user();
        $agent = Agent::findOrFail($request->agent_id);

        // Check if agent is already assigned
        if ($agent->company_id) {
            return back()->with('error', 'This agent is already assigned to an office.');
        }

        // Assign agent to office
        $agent->company_id = $office->id;
        $agent->save();

        return redirect()->route('office.agents')->with('success', 'Agent added successfully!');
    }

    public function removeAgent($id)
    {
        $office = auth('office')->user();
        $agent = Agent::findOrFail($id);

        // Check if agent belongs to this office
        if ($agent->company_id != $office->id) {
            return back()->with('error', 'This agent does not belong to your office.');
        }

        // Remove office assignment
        $agent->company_id = null;
        $agent->save();

        return redirect()->route('office.agents')->with('success', 'Agent removed successfully!');
    }

    // ==================== APPOINTMENTS ====================

    public function showAppointments()
    {
        $office = Auth::guard('office')->user();

        $appointments = Appointment::with(['user', 'agent', 'property'])
            ->where('office_id', $office->id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Appointment::where('office_id', $office->id)->count(),
            'pending' => Appointment::where('office_id', $office->id)->where('status', 'pending')->count(),
            'confirmed' => Appointment::where('office_id', $office->id)->where('status', 'confirmed')->count(),
            'completed' => Appointment::where('office_id', $office->id)->where('status', 'completed')->count(),
            'cancelled' => Appointment::where('office_id', $office->id)->where('status', 'cancelled')->count(),
        ];

        return view('office.appointments', compact('appointments', 'stats'));
    }

    public function updateAppointmentStatus(Request $request, $id)
    {
        $office = Auth::guard('office')->user();

        $appointment = Appointment::where('office_id', $office->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $appointment->update(['status' => $request->status]);

        return redirect()->route('office.appointments')
            ->with('success', 'Appointment status updated successfully!');
    }

    public function showPropertyUpload()
    {
        return view('office.property-add');
    }

    /**
     * ✅ Store new property
     */
    public function storeProperty(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description_en' => 'required|string|min:10',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'listing_type' => 'required|in:sell,rent',
            'property_type' => 'required|string',
            'price_usd' => 'required|numeric|min:0',
            'price_iqd' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required|numeric|min:1',
            'furnished' => 'nullable|boolean',
            'floor_number' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:2030',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'city_ar' => 'nullable|string',
            'district_ar' => 'nullable|string',
            'city_ku' => 'nullable|string',
            'district_ku' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'images' => 'required|array|min:3|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',

            'electricity' => 'nullable|boolean',
            'water' => 'nullable|boolean',
            'internet' => 'nullable|boolean',
        ]);

        try {
            $office = auth('office')->user();

            // ✅ Upload images
            $imageUrls = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('property_images', 'public');
                    $imageUrls[] = asset('storage/' . $path);
                }
            }

            // ✅ Build property data
            $propertyData = [
                'id' => $this->generateUniquePropertyId(),
                'owner_id' => $office->id,
                'owner_type' => 'App\\Models\\RealEstateOffice',

                // Multi-language fields
                'name' => json_encode([
                    'en' => $request->name_en,
                    'ar' => $request->name_ar ?? $request->name_en,
                    'ku' => $request->name_ku ?? $request->name_en,
                ]),
                'description' => json_encode([
                    'en' => $request->description_en,
                    'ar' => $request->description_ar ?? $request->description_en,
                    'ku' => $request->description_ku ?? $request->description_en,
                ]),

                // Property type
                'type' => json_encode([
                    'category' => $request->property_type,
                    'labels' => [
                        'en' => ucfirst($request->property_type),
                        'ar' => $this->translatePropertyType($request->property_type, 'ar'),
                        'ku' => $this->translatePropertyType($request->property_type, 'ku'),
                    ]
                ]),

                // Pricing
                'price' => json_encode([
                    'usd' => (float) $request->price_usd,
                    'iqd' => (float) $request->price_iqd,
                ]),
                'listing_type' => $request->listing_type,

                // Rooms
                'rooms' => json_encode([
                    'bedroom' => [
                        'count' => (int) $request->bedrooms,
                        'labels' => ['en' => 'Bedrooms', 'ar' => 'غرف نوم', 'ku' => 'ژووری نوستن']
                    ],
                    'bathroom' => [
                        'count' => (int) $request->bathrooms,
                        'labels' => ['en' => 'Bathrooms', 'ar' => 'حمامات', 'ku' => 'ژووری ئاو']
                    ]
                ]),

                // Location
                'locations' => json_encode([[
                    'lat' => (float) $request->latitude,
                    'lng' => (float) $request->longitude,
                ]]),
                'address_details' => json_encode([
                    'city' => [
                        'en' => $request->city_en,
                        'ar' => $request->city_ar ?? $request->city_en,
                        'ku' => $request->city_ku ?? $request->city_en,
                    ],
                    'district' => [
                        'en' => $request->district_en,
                        'ar' => $request->district_ar ?? $request->district_en,
                        'ku' => $request->district_ku ?? $request->district_en,
                    ],
                ]),
                'address' => $request->address,

                // Details
                'area' => (float) $request->area,
                'furnished' => $request->furnished ? 1 : 0,
                'floor_number' => $request->floor_number,
                'year_built' => $request->year_built,

                // Utilities
                'electricity' => $request->electricity ? 1 : 0,
                'water' => $request->water ? 1 : 0,
                'internet' => $request->internet ? 1 : 0,

                // Images
                'images' => json_encode($imageUrls),

                // Status
                'verified' => 0,
                'is_active' => 1,
                'published' => 1,
                'status' => 'available',
                'views' => 0,
                'favorites_count' => 0,
                'rating' => 0,

                // Availability
                'availability' => json_encode([
                    'status' => 'available',
                    'labels' => [
                        'en' => 'Available',
                        'ar' => 'متوفر',
                        'ku' => 'بەردەست'
                    ]
                ]),

                // Analytics
                'view_analytics' => json_encode(['unique_views' => 0]),
                'favorites_analytics' => json_encode(['last_30_days' => 0]),

                // Timestamps
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // ✅ Insert property
            Property::insert($propertyData);

            return redirect()->route('office.properties')->with('success', 'Property added successfully!');
        } catch (\Exception $e) {
            Log::error('Property creation error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create property. Please try again.'])->withInput();
        }
    }

    /**
     * ✅ Generate unique property ID
     */
    private function generateUniquePropertyId(): string
    {
        do {
            $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Property::where('id', $propertyId)->exists());

        return $propertyId;
    }

    /**
     * ✅ Translate property type
     */
    private function translatePropertyType($type, $lang)
    {
        $translations = [
            'apartment' => ['ar' => 'شقة', 'ku' => 'شوقە'],
            'house' => ['ar' => 'منزل', 'ku' => 'خانوو'],
            'villa' => ['ar' => 'فيلا', 'ku' => 'ڤیلا'],
            'land' => ['ar' => 'أرض', 'ku' => 'زەوی'],
            'commercial' => ['ar' => 'تجاري', 'ku' => 'بازرگانی'],
            'office' => ['ar' => 'مكتب', 'ku' => 'ئۆفیس'],
        ];

        return $translations[$type][$lang] ?? ucfirst($type);
    }


    public function projects()
    {
        $office = auth('office')->user();

        $projects = Project::where('developer_id', $office->id)
            ->where('developer_type', 'App\\Models\\RealEstateOffice')
            ->latest()
            ->get();

        return view('office.projects', compact('projects'));
    }

    /**
     * ✅ Show add project form
     */
    public function showProjectAdd()
    {
        return view('office.project-add');
    }




    public function editProject($id)
    {
        $office = auth('office')->user();

        $project = Project::where('id', $id)
            ->where('developer_id', $office->id)
            ->where('developer_type', 'App\\Models\\RealEstateOffice')
            ->firstOrFail();

        return view('office.project-edit', compact('project'));
    }



    /**
     * ✅ Delete project
     */

    public function showSubscriptions(Request $request)
    {
        $office = auth('office')->user();

        // Default to real_estate_office if no type specified
        $type = $request->get('type', 'real_estate_office');

        // ✅ ONLY allow banner or real_estate_office
        if (!in_array($type, ['banner', 'real_estate_office'])) {
            $type = 'real_estate_office';
        }

        // ✅ Fetch ONLY the selected type
        $plans = \App\Models\SubscriptionPlan::where('active', true)
            ->where('type', $type)  // This ensures ONLY selected type
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('office.subscriptions', compact('plans'));
    }


    /**
     * Show subscription plan details
     */
    public function subscriptionDetails($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('office.subscription-details', compact('plan'));
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request, $id)
    {
        $office = auth('office')->user();
        $plan = \App\Models\SubscriptionPlan::findOrFail($id);

        // ✅ Validate plan type
        if (!in_array($plan->type, ['banner', 'real_estate_office'])) {
            return redirect()->route('office.subscription-plans')
                ->with('error', 'Invalid subscription plan type.');
        }

        // TODO: Implement subscription logic

        return redirect()->route('office.subscriptions')
            ->with('success', 'Subscription request submitted successfully!');
    }

    /**
     * Show user's active subscriptions
     */
    public function mySubscriptions()
    {
        $office = Auth::guard('office')->user();

        // TODO: Get active subscriptions from database
        $subscriptions = collect(); // Replace with actual query

        return view('office.my-subscriptions', compact('subscriptions'));
    }

    // ==================== LEADS ====================

    /**
     * Show all leads
     */
    public function showLeads(Request $request)
    {
        $office = Auth::guard('office')->user();

        // TODO: Replace with actual Lead model
        // For now, create sample data structure
        $leads = collect([
            // Sample lead data - replace with actual database query
            (object)[
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+964 750 123 4567',
                'property_name' => 'Luxury Villa in Erbil',
                'source' => 'website',
                'priority' => 'high',
                'status' => 'new',
                'assigned_agent' => 'Ahmed Ali',
                'created_at' => now()->subDays(2),
            ],
            (object)[
                'id' => 2,
                'name' => 'Sarah Smith',
                'email' => 'sarah@example.com',
                'phone' => '+964 751 987 6543',
                'property_name' => 'Modern Apartment',
                'source' => 'referral',
                'priority' => 'medium',
                'status' => 'contacted',
                'assigned_agent' => 'Omar Hassan',
                'created_at' => now()->subDays(5),
            ],
        ]);

        $stats = [
            'total' => $leads->count(),
            'new' => $leads->where('status', 'new')->count(),
            'qualified' => $leads->where('status', 'qualified')->count(),
            'converted' => $leads->where('status', 'converted')->count(),
        ];

        return view('office.leads', compact('leads', 'stats'));
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,contacted,qualified,converted,lost',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // TODO: Update lead status in database
        // Lead::findOrFail($id)->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Lead status updated successfully!');
    }

    /**
     * Create new lead
     */
    public function createLead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'property_interest' => 'nullable|string',
            'source' => 'required|in:website,referral,social_media,walk_in,other',
            'priority' => 'required|in:low,medium,high',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // TODO: Create lead in database
        // Lead::create([...]);

        return redirect()->route('office.leads')->with('success', 'Lead created successfully!');
    }

    // ==================== OFFERS ====================

    /**
     * Show all offers
     */
    public function showOffers(Request $request)
    {
        $office = Auth::guard('office')->user();

        // TODO: Replace with actual Offer model query
        $offers = collect([
            // Sample offer data
            (object)[
                'id' => 1,
                'amount' => 250000,
                'property_name' => 'Villa in Dream City',
                'client_name' => 'Mohammed Ahmed',
                'status' => 'pending',
                'created_at' => now()->subDays(1),
                'valid_until' => now()->addDays(14),
            ],
            (object)[
                'id' => 2,
                'amount' => 180000,
                'property_name' => 'Modern Apartment',
                'client_name' => 'Layla Hassan',
                'status' => 'accepted',
                'created_at' => now()->subDays(5),
                'valid_until' => now()->addDays(10),
            ],
        ]);

        $stats = [
            'total' => $offers->count(),
            'pending' => $offers->where('status', 'pending')->count(),
            'accepted' => $offers->where('status', 'accepted')->count(),
            'total_value' => $offers->sum('amount'),
        ];

        return view('office.offers', compact('offers', 'stats'));
    }

    /**
     * Create new offer
     */
    public function createOffer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'required|string|max:20',
            'offer_amount' => 'required|numeric|min:0',
            'valid_until' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // TODO: Create offer in database
        // Offer::create([...]);

        return redirect()->route('office.offers')->with('success', 'Offer created successfully!');
    }

    /**
     * Respond to an offer
     */
    public function respondToOffer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:accept,reject,negotiate',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // TODO: Update offer status
        // Offer::findOrFail($id)->update(['status' => $request->action, 'notes' => $request->notes]);

        return redirect()->back()->with('success', 'Offer response submitted successfully!');
    }

    // ==================== AGREEMENTS ====================

    /**
     * Show all agreements
     */
    public function showAgreements(Request $request)
    {
        $office = Auth::guard('office')->user();

        // TODO: Replace with actual Agreement model query
        $agreements = collect([
            // Sample agreement data
            (object)[
                'id' => 1,
                'title' => 'Sale Agreement - Villa #123',
                'type' => 'sale',
                'property_name' => 'Luxury Villa',
                'client_name' => 'Ahmed Mohammed',
                'amount' => 350000,
                'start_date' => now()->subMonths(2),
                'end_date' => now()->addMonths(10),
                'status' => 'active',
                'created_at' => now()->subMonths(2),
            ],
            (object)[
                'id' => 2,
                'title' => 'Rental Agreement - Apt #456',
                'type' => 'rental',
                'property_name' => 'Modern Apartment',
                'client_name' => 'Sara Ali',
                'amount' => 1500,
                'start_date' => now()->subMonth(),
                'end_date' => now()->addMonths(11),
                'status' => 'signed',
                'created_at' => now()->subMonth(),
            ],
        ]);

        return view('office.agreements', compact('agreements'));
    }

    /**
     * Show single agreement
     */
    public function showAgreement($id)
    {
        $office = Auth::guard('office')->user();

        // TODO: Get agreement from database
        // $agreement = Agreement::findOrFail($id);

        return view('office.agreement-details', compact('agreement'));
    }

    /**
     * Create new agreement
     */
    public function createAgreement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:sale,rental,lease',
            'property_id' => 'required|exists:properties,id',
            'client_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // TODO: Create agreement in database
        // Agreement::create([...]);

        return redirect()->route('office.agreements')->with('success', 'Agreement created successfully!');
    }

    // ==================== ACTIVITIES ====================

    /**
     * Show activity log
     */
    public function showActivities(Request $request)
    {
        $office = Auth::guard('office')->user();

        // TODO: Replace with actual Activity model query
        $activities = collect([
            // Sample activity data
            (object)[
                'id' => 1,
                'title' => 'New Property Added',
                'description' => 'Villa in Dream City was added to your listings',
                'type' => 'create',
                'icon' => 'plus',
                'category' => 'Properties',
                'user_name' => $office->company_name,
                'meta' => true,
                'created_at' => now()->subHours(2),
            ],
            (object)[
                'id' => 2,
                'title' => 'Appointment Confirmed',
                'description' => 'Client meeting scheduled for tomorrow at 10:00 AM',
                'type' => 'update',
                'icon' => 'calendar-check',
                'category' => 'Appointments',
                'user_name' => 'Ahmed Ali',
                'meta' => true,
                'created_at' => now()->subHours(5),
            ],
            (object)[
                'id' => 3,
                'title' => 'New Lead Received',
                'description' => 'John Doe expressed interest in Modern Apartment',
                'type' => 'contact',
                'icon' => 'user-plus',
                'category' => 'Leads',
                'user_name' => 'System',
                'meta' => true,
                'created_at' => now()->subDay(),
            ],
        ]);

        return view('office.activities', compact('activities'));
    }

    // ==================== CONTACTS ====================

    /**
     * Show all contacts
     */
    public function showContacts(Request $request)
    {
        $office = Auth::guard('office')->user();

        // TODO: Replace with actual Contact model query
        $contacts = collect([
            // Sample contact data
            (object)[
                'id' => 1,
                'name' => 'Ahmed Mohammed',
                'email' => 'ahmed@example.com',
                'phone' => '+964 750 123 4567',
                'role' => 'Client',
                'company' => null,
                'created_at' => now()->subMonths(3),
            ],
            (object)[
                'id' => 2,
                'name' => 'Sara Ali',
                'email' => 'sara@company.com',
                'phone' => '+964 751 987 6543',
                'role' => 'Business Partner',
                'company' => 'ABC Real Estate',
                'created_at' => now()->subMonths(6),
            ],
            (object)[
                'id' => 3,
                'name' => 'Omar Hassan',
                'email' => 'omar@example.com',
                'phone' => '+964 752 456 7890',
                'role' => 'Investor',
                'company' => 'Investment Group Ltd',
                'created_at' => now()->subMonth(),
            ],
        ]);

        return view('office.contacts', compact('contacts'));
    }

    /**
     * Create new contact
     */
    public function createContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'role' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // TODO: Create contact in database
        // Contact::create([...]);

        return redirect()->route('office.contacts')->with('success', 'Contact added successfully!');
    }

    // ==================== CAMPAIGNS ====================

    /**
     * Show all campaigns
     */
    public function showCampaigns(Request $request)
    {
        $office = Auth::guard('office')->user();

        // TODO: Replace with actual Campaign model query
        $campaigns = collect([
            // Sample campaign data
            (object)[
                'id' => 1,
                'title' => 'Summer Sale 2024',
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(45),
                'status' => 'active',
                'impressions' => 12500,
                'clicks' => 890,
                'leads' => 45,
                'conversion_rate' => 5.1,
                'progress' => 65,
                'created_at' => now()->subDays(15),
            ],
            (object)[
                'id' => 2,
                'title' => 'New Project Launch',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(30),
                'status' => 'active',
                'impressions' => 8900,
                'clicks' => 567,
                'leads' => 32,
                'conversion_rate' => 5.6,
                'progress' => 50,
                'created_at' => now()->subDays(30),
            ],
            (object)[
                'id' => 3,
                'title' => 'Spring Promotion',
                'start_date' => now()->subMonths(3),
                'end_date' => now()->subMonth(),
                'status' => 'completed',
                'impressions' => 25600,
                'clicks' => 1234,
                'leads' => 78,
                'conversion_rate' => 6.3,
                'progress' => 100,
                'created_at' => now()->subMonths(3),
            ],
        ]);

        return view('office.campaigns', compact('campaigns'));
    }

    /**
     * Create new campaign
     */
    public function createCampaign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'target_audience' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // TODO: Create campaign in database
        // Campaign::create([...]);

        return redirect()->route('office.campaigns')->with('success', 'Campaign created successfully!');
    }

    // ==================== DOCUMENTS ====================

    /**
     * Show all documents
     */
    public function showDocuments(Request $request)
    {
        $office = Auth::guard('office')->user();

        // TODO: Replace with actual Document model query
        $documents = collect([
            // Sample document data
            (object)[
                'id' => 1,
                'name' => 'Property Agreement Template.pdf',
                'type' => 'pdf',
                'size' => '245 KB',
                'created_at' => now()->subDays(10),
            ],
            (object)[
                'id' => 2,
                'name' => 'Client Contract - Villa 123.docx',
                'type' => 'doc',
                'size' => '128 KB',
                'created_at' => now()->subDays(5),
            ],
            (object)[
                'id' => 3,
                'name' => 'Sales Report Q1 2024.xlsx',
                'type' => 'xls',
                'size' => '567 KB',
                'created_at' => now()->subDays(20),
            ],
            (object)[
                'id' => 4,
                'name' => 'Property Photos - Modern Apt.zip',
                'type' => 'other',
                'size' => '12.5 MB',
                'created_at' => now()->subDays(3),
            ],
        ]);

        return view('office.documents', compact('documents'));
    }

    /**
     * Upload new document
     */
    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document' => 'required|file|max:10240', // 10MB max
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $office = Auth::guard('office')->user();

        try {
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('office_documents/' . $office->id, $fileName, 'public');

            // TODO: Save document record to database
            // Document::create([
            //     'office_id' => $office->id,
            //     'name' => $request->title ?? $file->getClientOriginalName(),
            //     'file_path' => $path,
            //     'file_type' => $file->getClientOriginalExtension(),
            //     'file_size' => $file->getSize(),
            //     'category' => $request->category,
            // ]);

            return redirect()->route('office.documents')->with('success', 'Document uploaded successfully!');
        } catch (\Exception $e) {
            Log::error('Document upload error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to upload document.']);
        }
    }

    /**
     * Download document
     */
    public function downloadDocument($id)
    {
        $office = Auth::guard('office')->user();

        // TODO: Get document from database and verify ownership
        // $document = Document::where('office_id', $office->id)->findOrFail($id);
        // return Storage::disk('public')->download($document->file_path, $document->name);

        return redirect()->back()->with('error', 'Document not found.');
    }

    // STEP 1: Replace searchAgents method in OfficeAuthController.php

    public function searchAgents(Request $request)
    {
        // Log the incoming request
        Log::info('Search request received', [
            'search' => $request->input('search'),
            'all_params' => $request->all(),
            'method' => $request->method(),
            'headers' => $request->headers->all()
        ]);

        try {
            $office = auth('office')->user();
            $search = trim($request->input('search', ''));

            Log::info('Office authenticated', ['office_id' => $office->id]);

            // Validate minimum search length
            if (strlen($search) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter at least 3 characters',
                    'agents' => []
                ]);
            }

            // First, let's check if ANY agents exist
            $totalAgents = Agent::count();
            Log::info('Total agents in database', ['count' => $totalAgents]);

            // Check agents without company
            $availableCount = Agent::whereNull('company_id')->count();
            Log::info('Available agents (no company)', ['count' => $availableCount]);

            // Now search with the query
            $availableAgents = Agent::where(function ($query) {
                $query->whereNull('company_id')
                    ->orWhere('company_id', '');
            })
                ->where(function ($query) use ($search) {
                    $query->where('agent_name', 'LIKE', "%{$search}%")
                        ->orWhere('primary_email', 'LIKE', "%{$search}%")
                        ->orWhere('primary_phone', 'LIKE', "%{$search}%")
                        ->orWhere('whatsapp_number', 'LIKE', "%{$search}%")
                        ->orWhere('license_number', 'LIKE', "%{$search}%");
                })
                ->get();

            Log::info('Search completed', [
                'search_term' => $search,
                'results_count' => $availableAgents->count(),
                'results' => $availableAgents->toArray()
            ]);

            return response()->json([
                'success' => true,
                'agents' => $availableAgents,
                'count' => $availableAgents->count(),
                'debug' => [
                    'search_term' => $search,
                    'total_agents' => $totalAgents,
                    'available_agents' => $availableCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Agent search error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
                'agents' => [],
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    public function showProjects()
    {
        $office = auth('office')->user();

        $projects = Project::where('developer_type', 'App\Models\RealEstateOffice')
            ->where('developer_id', $office->id)
            ->withCount('properties')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('office.projects', compact('projects'));
    }

    // Show add project form
    public function showAddProject()
    {
        return view('office.project-add');
    }

    // Store new project
    public function storeProject(Request $request)
    {
        $office = auth('office')->user();

        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'project_type' => 'required|string',
            'status' => 'required|string',
            'total_units' => 'required|integer|min:1',
            'available_units' => 'required|integer|min:0',
            'total_floors' => 'nullable|integer|min:1',
            'total_area' => 'nullable|numeric|min:0',
            'built_area' => 'nullable|numeric|min:0',
            'buildings_count' => 'nullable|integer|min:1',
            'contractor' => 'nullable|string',
            'architect' => 'nullable|string',
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0',
            'pricing_currency' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'construction_start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'handover_date' => 'nullable|date',
            'completion_year' => 'nullable|integer',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'city_ar' => 'nullable|string',
            'district_ar' => 'nullable|string',
            'full_address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'images.*' => 'nullable|image|max:5120',
        ]);

        // Create project
        $project = new Project();
        $project->id = (string) Str::uuid();
        $project->developer_id = $office->id;
        $project->developer_type = 'App\Models\RealEstateOffice';

        // Name
        $project->name = [
            'en' => $validated['name_en'],
            'ar' => $validated['name_ar'] ?? '',
            'ku' => $validated['name_ku'] ?? '',
        ];

        // Slug
        $project->slug = Str::slug($validated['name_en']) . '-' . Str::random(6);

        // Description
        $project->description = [
            'en' => $validated['description_en'],
            'ar' => $validated['description_ar'] ?? '',
            'ku' => $validated['description_ku'] ?? '',
        ];

        // Details
        $project->project_type = $validated['project_type'];
        $project->status = $validated['status'];
        $project->total_units = $validated['total_units'];
        $project->available_units = $validated['available_units'];
        $project->total_floors = $validated['total_floors'] ?? null;
        $project->total_area = $validated['total_area'] ?? null;
        $project->built_area = $validated['built_area'] ?? null;
        $project->buildings_count = $validated['buildings_count'] ?? null;
        $project->contractor = $validated['contractor'] ?? null;
        $project->architect = $validated['architect'] ?? null;

        // Price range
        $project->price_range = [
            'min' => $validated['min_price'],
            'max' => $validated['max_price'],
        ];
        $project->pricing_currency = $validated['pricing_currency'] ?? 'IQD';

        // Dates
        $project->launch_date = $validated['launch_date'] ?? null;
        $project->construction_start_date = $validated['construction_start_date'] ?? null;
        $project->expected_completion_date = $validated['expected_completion_date'] ?? null;
        $project->handover_date = $validated['handover_date'] ?? null;
        $project->completion_year = $validated['completion_year'] ?? null;
        $project->completion_percentage = $validated['completion_percentage'];

        // Location
        $project->address_details = [
            'city' => [
                'en' => $validated['city_en'],
                'ar' => $validated['city_ar'] ?? '',
                'ku' => $validated['city_ku'] ?? '',
            ],
            'district' => [
                'en' => $validated['district_en'],
                'ar' => $validated['district_ar'] ?? '',
                'ku' => $validated['district_ku'] ?? '',
            ],
        ];

        $project->full_address = $validated['full_address'];
        $project->latitude = $validated['latitude'];
        $project->longitude = $validated['longitude'];

        $project->locations = [[
            'lat' => $validated['latitude'],
            'lng' => $validated['longitude'],
        ]];

        // Features
        $project->is_featured = $request->has('is_featured') ? 1 : 0;
        $project->is_premium = $request->has('is_premium') ? 1 : 0;

        // Status flags
        $project->is_active = true;
        $project->published = true;
        $project->sales_status = 'launched';

        // Handle images
        if ($request->hasFile('images')) {
            $imageUrls = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('projects', 'public');
                $imageUrls[] = Storage::url($path);
            }
            $project->images = $imageUrls;
        }

        $project->save();

        return redirect()->route('office.projects')->with('success', 'Project created successfully!');
    }

    // Show edit project form
    public function showEditProject($id)
    {
        $project = Project::where('developer_type', 'App\Models\RealEstateOffice')
            ->where('developer_id', auth('office')->id())
            ->withCount('properties')
            ->findOrFail($id);

        return view('office.project-edit', compact('project'));
    }

    // Update project
    public function updateProject(Request $request, $id)
    {
        $project = Project::where('developer_type', 'App\Models\RealEstateOffice')
            ->where('developer_id', auth('office')->id())
            ->findOrFail($id);

        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'project_type' => 'required|string',
            'status' => 'required|string',
            'total_units' => 'required|integer|min:1',
            'available_units' => 'required|integer|min:0',
            'total_floors' => 'nullable|integer|min:1',
            'total_area' => 'nullable|numeric|min:0',
            'built_area' => 'nullable|numeric|min:0',
            'buildings_count' => 'nullable|integer|min:1',
            'contractor' => 'nullable|string',
            'architect' => 'nullable|string',
            'min_price' => 'required|numeric|min:0',
            'max_price' => 'required|numeric|min:0',
            'pricing_currency' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'construction_start_date' => 'nullable|date',
            'expected_completion_date' => 'nullable|date',
            'handover_date' => 'nullable|date',
            'completion_year' => 'nullable|integer',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'city_en' => 'required|string',
            'district_en' => 'required|string',
            'city_ar' => 'nullable|string',
            'district_ar' => 'nullable|string',
            'full_address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'images.*' => 'nullable|image|max:5120',
            'remove_images' => 'nullable|string',
        ]);

        // Update name
        $project->name = [
            'en' => $validated['name_en'],
            'ar' => $validated['name_ar'] ?? '',
            'ku' => $validated['name_ku'] ?? '',
        ];

        // Update slug
        $project->slug = Str::slug($validated['name_en']) . '-' . Str::random(6);

        // Update description
        $project->description = [
            'en' => $validated['description_en'],
            'ar' => $validated['description_ar'] ?? '',
            'ku' => $validated['description_ku'] ?? '',
        ];

        // Update details
        $project->project_type = $validated['project_type'];
        $project->status = $validated['status'];
        $project->total_units = $validated['total_units'];
        $project->available_units = $validated['available_units'];
        $project->total_floors = $validated['total_floors'] ?? null;
        $project->total_area = $validated['total_area'] ?? null;
        $project->built_area = $validated['built_area'] ?? null;
        $project->buildings_count = $validated['buildings_count'] ?? null;
        $project->contractor = $validated['contractor'] ?? null;
        $project->architect = $validated['architect'] ?? null;

        // Update price range
        $project->price_range = [
            'min' => $validated['min_price'],
            'max' => $validated['max_price'],
        ];
        $project->pricing_currency = $validated['pricing_currency'] ?? 'IQD';

        // Update dates
        $project->launch_date = $validated['launch_date'] ?? null;
        $project->construction_start_date = $validated['construction_start_date'] ?? null;
        $project->expected_completion_date = $validated['expected_completion_date'] ?? null;
        $project->handover_date = $validated['handover_date'] ?? null;
        $project->completion_year = $validated['completion_year'] ?? null;
        $project->completion_percentage = $validated['completion_percentage'];

        // Update location
        $project->address_details = [
            'city' => [
                'en' => $validated['city_en'],
                'ar' => $validated['city_ar'] ?? '',
                'ku' => $validated['city_ku'] ?? '',
            ],
            'district' => [
                'en' => $validated['district_en'],
                'ar' => $validated['district_ar'] ?? '',
                'ku' => $validated['district_ku'] ?? '',
            ],
        ];

        $project->full_address = $validated['full_address'];
        $project->latitude = $validated['latitude'];
        $project->longitude = $validated['longitude'];

        $project->locations = [[
            'lat' => $validated['latitude'],
            'lng' => $validated['longitude'],
        ]];

        // Update features
        $project->is_featured = $request->has('is_featured') ? 1 : 0;
        $project->is_premium = $request->has('is_premium') ? 1 : 0;


        // Handle images
        $currentImages = is_array($project->images) ? $project->images : json_decode($project->images, true);
        $currentImages = $currentImages ?? [];

        // Remove images if requested
        if (!empty($request->remove_images)) {
            $removeIndices = json_decode($request->remove_images, true);
            if (is_array($removeIndices)) {
                rsort($removeIndices);
                foreach ($removeIndices as $index) {
                    if (isset($currentImages[$index])) {
                        unset($currentImages[$index]);
                    }
                }
                $currentImages = array_values($currentImages);
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('projects', 'public');
                $currentImages[] = Storage::url($path);
            }
        }

        $project->images = $currentImages;

        $project->save();

        return redirect()->route('office.projects')->with('success', 'Project updated successfully!');
    }

    // Delete project
    public function deleteProject($id)
    {
        $project = Project::where('developer_type', 'App\Models\RealEstateOffice')
            ->where('developer_id', auth('office')->id())
            ->findOrFail($id);

        $project->delete();

        return redirect()->route('office.projects')->with('success', 'Project deleted successfully!');
    }
}
