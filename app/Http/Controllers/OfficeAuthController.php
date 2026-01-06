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

    public function dashboard()
    {
        $office = Auth::guard('office')->user();

        // Get statistics
        $stats = [
            'total_agents' => Agent::where('company_id', $office->id)->count(),
            'total_properties' => Property::where('owner_type', 'App\Models\RealEstateOffice')
                ->where('owner_id', $office->id)->count(),
            'active_listings' => Property::where('owner_type', 'App\Models\RealEstateOffice')
                ->where('owner_id', $office->id)
                ->where('status', 'available')->count(),
            'sold_properties' => Property::where('owner_type', 'App\Models\RealEstateOffice')
                ->where('owner_id', $office->id)
                ->where('status', 'sold')->count(),
            'total_appointments' => Appointment::where('office_id', $office->id)->count(),
            'pending_appointments' => Appointment::where('office_id', $office->id)
                ->where('status', 'pending')->count(),
        ];

        // Recent properties (latest 6)
        $recentProperties = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Recent appointments (latest 5)
        $recentAppointments = Appointment::with(['user', 'agent', 'property'])
            ->where('office_id', $office->id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(5)
            ->get();

        // Top agents by property count
        $topAgents = Agent::where('company_id', $office->id)
            ->withCount(['ownedProperties' => function ($query) {
                $query->where('status', 'available');
            }])
            ->orderBy('owned_properties_count', 'desc')
            ->limit(5)
            ->get();

        return view('office.dashboard', compact('office', 'stats', 'recentProperties', 'recentAppointments', 'topAgents'));
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
            // ❌ REMOVED: current_plan validation
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
            // ❌ REMOVED: current_plan
            'office_address',
            'city',
            'district',
            'latitude',
            'longitude',
            'availability_schedule',
        ]);

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
        $office = Auth::guard('office')->user();

        $property = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'listing_type' => 'required|in:rent,sell',
            'status' => 'required|in:available,sold,rented',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $property->update([
            'name' => json_encode(['en' => $request->name]),
            'description' => json_encode(['en' => $request->description]),
            'price' => json_encode(['iqd' => $request->price, 'usd' => round($request->price / 1333, 2)]),
            'listing_type' => $request->listing_type,
            'status' => $request->status,
        ]);

        return redirect()->route('office.properties')
            ->with('success', 'Property updated successfully!');
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
        $office = Auth::guard('office')->user();

        $agents = Agent::where('company_id', $office->id)
            ->withCount(['ownedProperties' => function ($query) {
                $query->where('status', 'available');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('office.agents', compact('agents'));
    }

    public function showAddAgent()
    {
        $office = Auth::guard('office')->user();

        // Get agents not assigned to any office
        $availableAgents = Agent::whereNull('company_id')
            ->orWhere('company_id', '')
            ->get();

        return view('office.agent-add', compact('availableAgents'));
    }

    public function storeAgent(Request $request)
    {
        $office = Auth::guard('office')->user();

        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:agents,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $agent = Agent::findOrFail($request->agent_id);

        // Check if agent is already assigned
        if ($agent->company_id && $agent->company_id !== '') {
            return redirect()->back()->withErrors(['agent_id' => 'This agent is already assigned to another office.']);
        }

        $agent->update(['company_id' => $office->id]);

        return redirect()->route('office.agents')
            ->with('success', 'Agent added successfully!');
    }

    public function removeAgent($id)
    {
        $office = Auth::guard('office')->user();

        $agent = Agent::where('company_id', $office->id)->findOrFail($id);
        $agent->update(['company_id' => null]);

        return redirect()->route('office.agents')
            ->with('success', 'Agent removed successfully!');
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

    /**
     * ✅ Store new project
     */
    public function storeProject(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'description_en' => 'required|string|min:10',
            'project_type' => 'required|in:residential,commercial,mixed_use,industrial,retail,office,hospitality',
            'status' => 'required|in:planning,under_construction,completed,delivered,on_hold',
            'total_units' => 'required|integer|min:1',
            'city_en' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            $office = auth('office')->user();

            // Upload images
            $imageUrls = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('project_images', 'public');
                    $imageUrls[] = asset('storage/' . $path);
                }
            }

            $projectData = [
                'id' => 'proj_' . date('Ymd') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                'developer_id' => $office->id,
                'developer_type' => 'App\\Models\\RealEstateOffice',
                'slug' => Str::slug($request->name_en) . '-' . Str::random(6),

                // Name
                'name' => json_encode([
                    'en' => $request->name_en,
                    'ar' => $request->name_ar ?? $request->name_en,
                    'ku' => $request->name_ku ?? $request->name_en,
                ]),

                // Description
                'description' => json_encode([
                    'en' => $request->description_en,
                    'ar' => $request->description_ar ?? $request->description_en,
                    'ku' => $request->description_ku ?? $request->description_en,
                ]),

                // Images
                'images' => json_encode($imageUrls),
                'cover_image_url' => !empty($imageUrls) ? $imageUrls[0] : null,

                // Project details
                'project_type' => $request->project_type,
                'total_units' => $request->total_units,
                'available_units' => $request->available_units ?? $request->total_units,
                'total_area' => $request->total_area,
                'built_area' => $request->built_area,
                'total_floors' => $request->total_floors,
                'buildings_count' => $request->buildings_count ?? 1,
                'year_built' => $request->year_built,
                'completion_year' => $request->completion_year,

                // Status
                'status' => $request->status,
                'sales_status' => $request->sales_status ?? 'pre_launch',
                'completion_percentage' => $request->completion_percentage ?? 0,

                // Pricing
                'price_range' => json_encode([
                    'min' => $request->min_price ?? 0,
                    'max' => $request->max_price ?? 0,
                ]),
                'pricing_currency' => $request->pricing_currency ?? 'IQD',

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
                        'en' => $request->district_en ?? '',
                        'ar' => $request->district_ar ?? $request->district_en ?? '',
                        'ku' => $request->district_ku ?? $request->district_en ?? '',
                    ],
                ]),
                'full_address' => $request->full_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,

                // Dates
                'launch_date' => $request->launch_date,
                'construction_start_date' => $request->construction_start_date,
                'expected_completion_date' => $request->expected_completion_date,
                'handover_date' => $request->handover_date,

                // Features
                'is_featured' => $request->has('is_featured'),
                'is_premium' => $request->has('is_premium'),
                'is_hot_project' => $request->has('is_hot_project'),
                'eco_friendly' => $request->has('eco_friendly'),

                // System
                'is_active' => true,
                'published' => true,
                'views' => 0,
                'rating' => 0,
                'units_sold' => 0,
                'sales_velocity' => 0,

                // Timestamps
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Project::insert($projectData);

            return redirect()->route('office.projects')->with('success', 'Project created successfully!');
        } catch (\Exception $e) {
            Log::error('Project creation error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create project. Please try again.'])->withInput();
        }
    }

    /**
     * ✅ Show edit project form
     */
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
     * ✅ Update project
     */
    public function updateProject(Request $request, $id)
    {
        $office = auth('office')->user();

        $project = Project::where('id', $id)
            ->where('developer_id', $office->id)
            ->where('developer_type', 'App\\Models\\RealEstateOffice')
            ->firstOrFail();

        $request->validate([
            'name_en' => 'required|string|max:255',
            'description_en' => 'required|string|min:10',
            'project_type' => 'required|in:residential,commercial,mixed_use,industrial,retail,office,hospitality',
            'status' => 'required|in:planning,under_construction,completed,delivered,on_hold',
            'total_units' => 'required|integer|min:1',
            'city_en' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            // Handle new images
            $imageUrls = is_array($project->images) ? $project->images : [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('project_images', 'public');
                    $imageUrls[] = asset('storage/' . $path);
                }
            }

            $updateData = [
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
                'images' => json_encode($imageUrls),
                'project_type' => $request->project_type,
                'total_units' => $request->total_units,
                'available_units' => $request->available_units ?? $request->total_units,
                'total_area' => $request->total_area,
                'built_area' => $request->built_area,
                'total_floors' => $request->total_floors,
                'buildings_count' => $request->buildings_count ?? 1,
                'status' => $request->status,
                'sales_status' => $request->sales_status ?? 'pre_launch',
                'completion_percentage' => $request->completion_percentage ?? 0,
                'price_range' => json_encode([
                    'min' => $request->min_price ?? 0,
                    'max' => $request->max_price ?? 0,
                ]),
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
                ]),
                'full_address' => $request->full_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_featured' => $request->has('is_featured'),
                'is_premium' => $request->has('is_premium'),
                'is_hot_project' => $request->has('is_hot_project'),
                'eco_friendly' => $request->has('eco_friendly'),
                'updated_at' => now(),
            ];

            $project->update($updateData);

            return redirect()->route('office.projects')->with('success', 'Project updated successfully!');
        } catch (\Exception $e) {
            Log::error('Project update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update project.'])->withInput();
        }
    }

    /**
     * ✅ Delete project
     */
    public function deleteProject($id)
    {
        $office = auth('office')->user();

        $project = Project::where('id', $id)
            ->where('developer_id', $office->id)
            ->where('developer_type', 'App\\Models\\RealEstateOffice')
            ->firstOrFail();

        // Delete images from storage
        if (is_array($project->images)) {
            foreach ($project->images as $imageUrl) {
                $path = str_replace(asset('storage/'), '', $imageUrl);
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $project->delete();

        return redirect()->route('office.projects')->with('success', 'Project deleted successfully!');
    }

    public function showSubscriptions(Request $request)
    {
        $type = $request->get('type', 'all');
        $query = SubscriptionPlan::where('active', true)->orderBy('sort_order', 'asc');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $plans = $query->get();

        return view('office.subscription-plans', compact('plans'));
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
        $office = Auth::guard('office')->user();
        $plan = SubscriptionPlan::findOrFail($id);

        // TODO: Implement subscription logic
        // - Create subscription record
        // - Process payment
        // - Update office subscription status

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
}
