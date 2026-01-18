<?php

namespace App\Http\Controllers;

use App\Models\RealEstateOffice;
use App\Models\Agent;
use App\Models\Property;
use App\Models\Appointment;
use App\Models\Project;
use App\Models\Subscription\Subscription;
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

            // ✅ CHANGE THIS LINE - Remove ->intended()
            return redirect()->route('office.dashboard')
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
        $office = Auth::guard('office')->user()->load('subscription.currentPlan');

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

        // ==================== SUBSCRIPTION INFO ====================
        $subscription = $office->subscription;
        $propertyLimit = $office->getPropertyLimitInfo();
        $subscriptionBadge = $office->getSubscriptionStatusBadge();

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
            'search',
            'subscription',          // Added
            'propertyLimit',         // Added
            'subscriptionBadge'      // Added
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
            'company_bio' => 'nullable|string|max:1000',
            'about_company' => 'nullable|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'properties_sold' => 'nullable|integer|min:0',
            'years_experience' => 'nullable|integer|min:0',
            'office_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'availability_schedule' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'company_bio_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($office->logo) {
                $oldPath = str_replace(asset('storage/'), '', $office->logo);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('logo')->store('offices/logos', 'public');
            $office->logo = asset('storage/' . $path);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($office->profile_image) {
                $oldPath = str_replace(asset('storage/'), '', $office->profile_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('profile_image')->store('offices/profiles', 'public');
            $office->profile_image = asset('storage/' . $path);
        }

        // Handle company bio image upload
        if ($request->hasFile('company_bio_image')) {
            if ($office->company_bio_image) {
                $oldPath = str_replace(asset('storage/'), '', $office->company_bio_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $path = $request->file('company_bio_image')->store('offices/bio', 'public');
            $office->company_bio_image = asset('storage/' . $path);
        }

        // Transform availability schedule
        if ($request->has('availability_schedule') && $request->availability_schedule) {
            try {
                $scheduleData = json_decode($request->availability_schedule, true);
                $transformedSchedule = [];
                $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                foreach ($allDays as $day) {
                    if (isset($scheduleData[$day]) && is_array($scheduleData[$day])) {
                        $open = $scheduleData[$day]['open'] ?? '09:00';
                        $close = $scheduleData[$day]['close'] ?? '18:00';
                        $transformedSchedule[$day] = "{$open}-{$close}";
                    } else {
                        $transformedSchedule[$day] = 'closed';
                    }
                }

                $office->availability_schedule = $transformedSchedule;
            } catch (\Exception $e) {
                Log::error('Availability schedule transformation error: ' . $e->getMessage());
            }
        }

        // Update other fields
        $office->company_name = $request->company_name;
        $office->phone_number = $request->phone_number;
        $office->company_bio = $request->company_bio;
        $office->about_company = $request->about_company;
        $office->city = $request->city;
        $office->district = $request->district;
        $office->properties_sold = $request->properties_sold;
        $office->years_experience = $request->years_experience;
        $office->office_address = $request->office_address;
        $office->latitude = $request->latitude;
        $office->longitude = $request->longitude;

        $office->save();

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

        // ✅ DECREMENT PROPERTY COUNT
        $office->decrementPropertyCount();

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
        // ✅ CHECK SUBSCRIPTION FIRST
        $validationResult = $this->validateSubscription();
        if ($validationResult) {
            return $validationResult;
        }

        return view('office.property-add');
    }

    /**
     * ✅ Store new property
     */
    public function storeProperty(Request $request)
    {
        // ✅ LOG INCOMING REQUEST
        Log::info('=== PROPERTY CREATION STARTED ===', [
            'office_id' => auth('office')->id(),
            'office_name' => auth('office')->user()->company_name ?? 'Unknown',
            'request_data' => $request->except(['images', '_token']),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
        ]);

        // ✅ CHECK SUBSCRIPTION FIRST
        $validationResult = $this->validateSubscription();
        if ($validationResult) {
            Log::warning('Subscription validation failed', [
                'office_id' => auth('office')->id(),
            ]);
            return $validationResult;
        }

        Log::info('Subscription validation passed');

        // ✅ VALIDATE REQUEST - FIXED: Reduced min description to 5 characters
        try {
            $validated = $request->validate([
                'name_en' => 'required|string|min:3|max:255',
                'name_ar' => 'nullable|string|max:255',
                'name_ku' => 'nullable|string|max:255',
                'description_en' => 'required|string|min:5',  // ✅ CHANGED from 10 to 5
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
                'images' => 'required|array|min:1|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
                'electricity' => 'nullable|boolean',
                'water' => 'nullable|boolean',
                'internet' => 'nullable|boolean',
                'status' => 'nullable|in:available,sold,rented',
            ], [
                // ✅ CUSTOM ERROR MESSAGES
                'name_en.required' => 'Property name is required',
                'name_en.min' => 'Property name must be at least 3 characters',
                'description_en.required' => 'Property description is required',
                'description_en.min' => 'Description must be at least 5 characters',
                'property_type.required' => 'Please select a property type',
                'listing_type.required' => 'Please select listing type (Sale or Rent)',
                'area.required' => 'Area is required',
                'area.min' => 'Area must be at least 1 m²',
                'price_usd.required' => 'Price in USD is required',
                'price_iqd.required' => 'Price in IQD is required',
                'bedrooms.required' => 'Number of bedrooms is required',
                'bathrooms.required' => 'Number of bathrooms is required',
                'city_en.required' => 'Please select a city',
                'district_en.required' => 'Please select a district/area',
                'latitude.required' => 'Please select location on the map',
                'longitude.required' => 'Please select location on the map',
                'images.required' => 'Please upload at least one property image',
                'images.min' => 'Please upload at least one image',
                'images.max' => 'Maximum 10 images allowed',
                'images.*.image' => 'All uploaded files must be images',
                'images.*.mimes' => 'Images must be jpeg, png, jpg, or gif format',
                'images.*.max' => 'Each image must not exceed 5MB',
            ]);

            Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'messages' => $e->getMessage(),
            ]);

            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form for errors.');
        }

        try {
            $office = auth('office')->user();
            Log::info('Office loaded', ['office_id' => $office->id]);

            // ✅ Upload images
            $imageUrls = [];
            if ($request->hasFile('images')) {
                Log::info('Processing images', ['count' => count($request->file('images'))]);

                foreach ($request->file('images') as $index => $image) {
                    try {
                        $path = $image->store('property_images', 'public');
                        $imageUrls[] = asset('storage/' . $path);
                        Log::info("Image {$index} uploaded successfully", ['path' => $path]);
                    } catch (\Exception $e) {
                        Log::error("Image {$index} upload failed", [
                            'error' => $e->getMessage(),
                            'file_name' => $image->getClientOriginalName(),
                            'file_size' => $image->getSize(),
                        ]);
                        throw $e;
                    }
                }

                Log::info('All images uploaded', ['total' => count($imageUrls)]);
            }

            // ✅ Use status from form or default to 'available'
            $propertyStatus = $request->input('status', 'available');
            Log::info('Property status set', ['status' => $propertyStatus]);

            // ✅ Generate unique property ID
            $propertyId = $this->generateUniquePropertyId();
            Log::info('Property ID generated', ['id' => $propertyId]);

            // ✅ Build property data
            $propertyData = [
                'id' => $propertyId,
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
                'status' => $propertyStatus,
                'views' => 0,
                'favorites_count' => 0,
                'rating' => 0,

                // Availability
                'availability' => json_encode([
                    'status' => $propertyStatus,
                    'labels' => [
                        'en' => ucfirst($propertyStatus),
                        'ar' => $this->translateStatus($propertyStatus, 'ar'),
                        'ku' => $this->translateStatus($propertyStatus, 'ku'),
                    ]
                ]),

                // Analytics
                'view_analytics' => json_encode(['unique_views' => 0]),
                'favorites_analytics' => json_encode(['last_30_days' => 0]),

                // Timestamps
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Log::info('Property data prepared', [
                'id' => $propertyId,
                'name_en' => $request->name_en,
                'images_count' => count($imageUrls),
            ]);

            // ✅ Insert property
            Property::insert($propertyData);
            Log::info('Property inserted successfully', ['id' => $propertyId]);

            // ✅ INCREMENT PROPERTY COUNT
            $office->incrementPropertyCount();
            Log::info('Property count incremented');

            Log::info('=== PROPERTY CREATION COMPLETED SUCCESSFULLY ===', [
                'property_id' => $propertyId,
                'office_id' => $office->id,
            ]);

            // ✅ REDIRECT WITH SUCCESS MESSAGE
            return redirect()->route('office.properties')
                ->with('success', '🎉 Property "' . $request->name_en . '" created successfully!');
        } catch (\Exception $e) {
            Log::error('=== PROPERTY CREATION FAILED ===', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'office_id' => auth('office')->id() ?? 'not authenticated',
            ]);

            return back()
                ->withErrors(['error' => 'Failed to create property: ' . $e->getMessage()])
                ->withInput()
                ->with('error', 'Failed to create property. Please try again.');
        }
    }

    // ✅ ADD THIS HELPER METHOD
    private function translateStatus($status, $lang)
    {
        $translations = [
            'available' => ['ar' => 'متوفر', 'ku' => 'بەردەست'],
            'sold' => ['ar' => 'مباع', 'ku' => 'فرۆشراوە'],
            'rented' => ['ar' => 'مؤجر', 'ku' => 'بەکرێدراوە'],
        ];

        return $translations[$status][$lang] ?? ucfirst($status);
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
        $office = auth('office')->user()->load('subscription.currentPlan');

        // Default to real_estate_office if no type specified
        $type = $request->get('type', 'real_estate_office');

        // ✅ ONLY allow banner or real_estate_office
        if (!in_array($type, ['banner', 'real_estate_office'])) {
            $type = 'real_estate_office';
        }

        // ✅ Fetch ONLY the selected type
        $plans = SubscriptionPlan::where('active', true)
            ->where('type', $type)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();

        // Get current subscription info
        $currentSubscription = $office->subscription;
        $propertyLimit = $office->getPropertyLimitInfo();

        return view('office.subscriptions', compact('plans', 'currentSubscription', 'propertyLimit', 'type'));
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
        $office = auth('office')->user()->load('subscription');
        $plan = SubscriptionPlan::findOrFail($id);

        // ✅ Validate plan type
        if (!in_array($plan->type, ['banner', 'real_estate_office'])) {
            return redirect()->route('office.subscriptions')
                ->with('error', 'Invalid subscription plan type.');
        }

        try {
            // Check if office already has a subscription
            $existingSubscription = $office->subscription;

            if ($existingSubscription && $existingSubscription->isActive()) {
                // Office has active subscription - show upgrade/extend options
                return redirect()->route('office.subscription.confirm', [
                    'plan_id' => $plan->id,
                    'action' => 'upgrade'
                ]);
            }

            // No active subscription - create new one
            $subscription = $this->createSubscription($office, $plan);

            return redirect()->route('office.dashboard')
                ->with('success', "Successfully subscribed to {$plan->name}! Your subscription is now active.");
        } catch (\Exception $e) {
            Log::error('Subscription error: ' . $e->getMessage());
            return redirect()->route('office.subscriptions')
                ->with('error', 'Failed to process subscription. Please try again.');
        }
    }
    private function createSubscription($office, $plan)
    {
        $startDate = now();
        $endDate = now()->addMonths($plan->duration_months);

        $subscription = Subscription::create([
            'user_id' => $office->id,
            'current_plan_id' => $plan->id,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'billing_cycle' => $plan->duration_months >= 12 ? 'annual' : 'monthly',
            'auto_renewal' => true,
            'property_activation_limit' => $plan->max_properties ?? 0, // 0 = unlimited
            'properties_activated_this_month' => 0,
            'remaining_activations' => $plan->max_properties ?? 0,
            'next_billing_date' => $endDate,
            'last_payment_date' => now(),
            'trial_period' => false,
            'monthly_amount' => $plan->price_per_month_iqd,
        ]);

        // Link subscription to office
        $office->update(['subscription_id' => $subscription->id]);

        return $subscription;
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
    public function confirmSubscription(Request $request)
    {
        $office = auth('office')->user()->load('subscription.currentPlan');
        $newPlan = SubscriptionPlan::findOrFail($request->plan_id);
        $currentSubscription = $office->subscription;
        $action = $request->action; // 'upgrade' or 'extend'

        return view('office.subscription-confirm', compact('office', 'newPlan', 'currentSubscription', 'action'));
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
    public function processSubscription(Request $request)
    {
        $office = auth('office')->user()->load('subscription');
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $action = $request->action; // 'upgrade' or 'extend'

        try {
            if ($action === 'upgrade') {
                // Upgrade to new plan
                $this->upgradeSubscription($office, $plan);
                $message = "Successfully upgraded to {$plan->name}!";
            } else {
                // Extend current plan
                $this->extendSubscription($office, $plan);
                $message = "Successfully extended your subscription by {$plan->duration_months} months!";
            }

            return redirect()->route('office.dashboard')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Subscription processing error: ' . $e->getMessage());
            return redirect()->route('office.subscriptions')
                ->with('error', 'Failed to process subscription. Please try again.');
        }
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
    private function upgradeSubscription($office, $newPlan)
    {
        $subscription = $office->subscription;

        // Calculate new end date (add months to current end date)
        $currentEndDate = $subscription->end_date ?? now();
        $newEndDate = max($currentEndDate, now())->addMonths($newPlan->duration_months);

        // Update subscription
        $subscription->update([
            'current_plan_id' => $newPlan->id,
            'status' => 'active',
            'end_date' => $newEndDate,
            'billing_cycle' => $newPlan->duration_months >= 12 ? 'annual' : 'monthly',
            'property_activation_limit' => $newPlan->max_properties ?? 0,
            'remaining_activations' => $newPlan->max_properties ?? 0,
            'next_billing_date' => $newEndDate,
            'last_payment_date' => now(),
            'monthly_amount' => $newPlan->price_per_month_iqd,
        ]);

        return $subscription;
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

    private function extendSubscription($office, $plan)
    {
        $subscription = $office->subscription;

        // Add months to current end date
        $currentEndDate = $subscription->end_date ?? now();
        $newEndDate = max($currentEndDate, now())->addMonths($plan->duration_months);

        // Update subscription
        $subscription->update([
            'end_date' => $newEndDate,
            'next_billing_date' => $newEndDate,
            'last_payment_date' => now(),
            'status' => 'active',
        ]);

        return $subscription;
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
    public function cancelSubscription()
    {
        $office = auth('office')->user()->load('subscription');

        if (!$office->subscription) {
            return redirect()->route('office.subscriptions')
                ->with('error', 'You do not have an active subscription.');
        }

        $office->subscription->cancel();

        return redirect()->route('office.subscriptions')
            ->with('success', 'Your subscription has been cancelled.');
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
            'office_id' => auth('office')->id()
        ]);

        try {
            $office = auth('office')->user();
            $search = trim($request->input('search', ''));

            // Validate minimum search length
            if (strlen($search) < 1) { // Changed to 1 to allow searching for ID "1" or "5"
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a search term',
                    'agents' => []
                ]);
            }

            // Search Query
            $availableAgents = Agent::where(function ($query) {
                // 1. Ensure Agent is not already assigned to another office
                $query->whereNull('company_id')
                    ->orWhere('company_id', '');
            })
                ->where(function ($query) use ($search) {
                    // 2. Search Logic: ID OR Name OR Email
                    $query->where('id', $search)                            // Exact ID match
                        ->orWhere('agent_name', 'LIKE', "%{$search}%")      // Partial Name match
                        ->orWhere('primary_email', 'LIKE', "%{$search}%")   // Partial Email match
                        ->orWhere('primary_phone', 'LIKE', "%{$search}%");  // Keep Phone for convenience
                })
                ->take(20) // Limit results for performance
                ->get();

            Log::info('Search completed', [
                'term' => $search,
                'count' => $availableAgents->count()
            ]);

            return response()->json([
                'success' => true,
                'agents' => $availableAgents,
                'count' => $availableAgents->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Agent search error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
                'agents' => []
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


    // ==================== BANNER MANAGEMENT ====================

    /**
     * Show all banners
     */
    public function showBanners(Request $request)
    {
        $office = auth('office')->user();

        $bannersQuery = \App\Models\BannerAd::where('owner_type', 'real_estate')
            ->where('owner_id', $office->id);

        // Apply filters
        if ($request->filled('status')) {
            $bannersQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $bannersQuery->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $banners = $bannersQuery->orderBy('created_at', 'desc')->paginate(12);

        // Stats
        $stats = [
            'total' => \App\Models\BannerAd::where('owner_type', 'real_estate')
                ->where('owner_id', $office->id)->count(),
            'active' => \App\Models\BannerAd::where('owner_type', 'real_estate')
                ->where('owner_id', $office->id)
                ->where('status', 'active')->count(),
            'draft' => \App\Models\BannerAd::where('owner_type', 'real_estate')
                ->where('owner_id', $office->id)
                ->where('status', 'draft')->count(),
            'paused' => \App\Models\BannerAd::where('owner_type', 'real_estate')
                ->where('owner_id', $office->id)
                ->where('status', 'paused')->count(),
        ];

        return view('office.banners', compact('banners', 'stats'));
    }

    /**
     * Show add banner form
     */
    public function showAddBanner()
    {
        $office = auth('office')->user();

        // Get office properties for linking
        $properties = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->where('status', 'available')
            ->get();

        return view('office.banner-add', compact('properties'));
    }

    /**
     * Store new banner
     */
    public function storeBanner(Request $request)
    {
        $office = auth('office')->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_type' => 'required|in:property_listing,agency_branding,service_promotion,event_announcement,general_marketing',
            'link_url' => 'nullable|url|max:500',
            'link_opens_new_tab' => 'nullable|boolean',
            'property_id' => 'nullable|exists:properties,id',
            'banner_size' => 'required|in:banner,leaderboard,rectangle,sidebar,mobile',
            'position' => 'required|in:header,sidebar_top,sidebar_bottom,content_top,content_middle,content_bottom,footer',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'call_to_action' => 'nullable|string|max:50',
            'show_contact_info' => 'nullable|boolean',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'target_locations' => 'nullable|array',
            'target_property_types' => 'nullable|array',
        ]);

        try {
            // Upload image
            $imagePath = $request->file('image')->store('banner_ads', 'public');
            $imageUrl = Storage::url($imagePath);

            // Create banner
            $banner = \App\Models\BannerAd::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'image_url' => asset($imageUrl),
                'image_alt' => $validated['title'],
                'link_url' => $validated['link_url'] ?? null,
                'link_opens_new_tab' => $request->has('link_opens_new_tab'),

                // Owner info
                'owner_type' => 'real_estate',
                'owner_id' => $office->id,
                'owner_name' => $office->company_name,
                'owner_email' => $office->email_address,
                'owner_phone' => $office->phone_number,
                'owner_logo' => $office->profile_image ? asset('storage/' . $office->profile_image) : null,

                // Banner details
                'banner_type' => $validated['banner_type'],
                'property_id' => $validated['property_id'] ?? null,
                'banner_size' => $validated['banner_size'],
                'position' => $validated['position'],

                // Targeting
                'target_locations' => $request->target_locations ? json_encode($request->target_locations) : null,
                'target_property_types' => $request->target_property_types ? json_encode($request->target_property_types) : null,

                // Dates
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,

                // Additional
                'call_to_action' => $validated['call_to_action'] ?? null,
                'show_contact_info' => $request->has('show_contact_info'),

                // Status
                'is_active' => true,
                'status' => 'draft',
                'billing_type' => 'free',
                'display_priority' => 50,

                // Metadata
                'created_by_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('office.banners')
                ->with('success', 'Banner created successfully and pending approval!');
        } catch (\Exception $e) {
            Log::error('Banner creation error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create banner. Please try again.'])->withInput();
        }
    }

    /**
     * Show edit banner form
     */
    public function editBanner($id)
    {
        $office = auth('office')->user();

        $banner = \App\Models\BannerAd::where('owner_type', 'real_estate')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $properties = Property::where('owner_type', 'App\Models\RealEstateOffice')
            ->where('owner_id', $office->id)
            ->where('status', 'available')
            ->get();

        return view('office.banner-edit', compact('banner', 'properties'));
    }

    /**
     * Update banner
     */
    public function updateBanner(Request $request, $id)
    {
        $office = auth('office')->user();

        $banner = \App\Models\BannerAd::where('owner_type', 'real_estate')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_type' => 'required|in:property_listing,agency_branding,service_promotion,event_announcement,general_marketing',
            'link_url' => 'nullable|url|max:500',
            'property_id' => 'nullable|exists:properties,id',
            'banner_size' => 'required|in:banner,leaderboard,rectangle,sidebar,mobile',
            'position' => 'required|in:header,sidebar_top,sidebar_bottom,content_top,content_middle,content_bottom,footer',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'call_to_action' => 'nullable|string|max:50',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            $updateData = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'link_url' => $validated['link_url'] ?? null,
                'link_opens_new_tab' => $request->has('link_opens_new_tab'),
                'banner_type' => $validated['banner_type'],
                'property_id' => $validated['property_id'] ?? null,
                'banner_size' => $validated['banner_size'],
                'position' => $validated['position'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'call_to_action' => $validated['call_to_action'] ?? null,
                'show_contact_info' => $request->has('show_contact_info'),
            ];

            // Update image if new one uploaded
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('banner_ads', 'public');
                $updateData['image_url'] = asset(Storage::url($imagePath));
            }

            $banner->update($updateData);

            return redirect()->route('office.banners')
                ->with('success', 'Banner updated successfully!');
        } catch (\Exception $e) {
            Log::error('Banner update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update banner.'])->withInput();
        }
    }

    /**
     * Delete banner
     */
    public function deleteBanner($id)
    {
        $office = auth('office')->user();

        $banner = \App\Models\BannerAd::where('owner_type', 'real_estate')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $banner->delete();

        return redirect()->route('office.banners')
            ->with('success', 'Banner deleted successfully!');
    }

    /**
     * Pause banner
     */
    public function pauseBanner($id)
    {
        $office = auth('office')->user();

        $banner = \App\Models\BannerAd::where('owner_type', 'real_estate')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $banner->pause();

        return redirect()->route('office.banners')
            ->with('success', 'Banner paused successfully!');
    }

    /**
     * Resume banner
     */
    public function resumeBanner($id)
    {
        $office = auth('office')->user();

        $banner = \App\Models\BannerAd::where('owner_type', 'real_estate')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $banner->resume();

        return redirect()->route('office.banners')
            ->with('success', 'Banner resumed successfully!');
    }

    /**
     * Show banner analytics
     */
    public function bannerAnalytics($id)
    {
        $office = auth('office')->user();

        $banner = \App\Models\BannerAd::where('owner_type', 'real_estate')
            ->where('owner_id', $office->id)
            ->findOrFail($id);

        $metrics = $banner->getPerformanceMetrics();

        return view('office.banner-analytics', compact('banner', 'metrics'));
    }



    private function validateSubscription()
    {
        $office = auth('office')->user()->load('subscription.currentPlan');

        // Check if has subscription
        if (!$office->subscription_id || !$office->subscription) {
            return redirect()->route('office.subscriptions')
                ->with('error', 'You need an active subscription to add properties. Please subscribe to continue.');
        }

        // Check if subscription is active
        if (!$office->hasActiveSubscription()) {
            $subscription = $office->subscription;

            if ($subscription->isExpired()) {
                return redirect()->route('office.subscriptions')
                    ->with('error', 'Your subscription has expired on ' . $subscription->end_date->format('M d, Y') . '. Please renew to continue adding properties.');
            }

            if ($subscription->status === 'suspended') {
                return redirect()->route('office.subscriptions')
                    ->with('error', 'Your subscription is suspended. Please contact support for assistance.');
            }

            return redirect()->route('office.subscriptions')
                ->with('error', 'Your subscription is not active. Please activate your subscription to continue.');
        }

        // Check property limit
        if (!$office->canAddProperty()) {
            $info = $office->getPropertyLimitInfo();

            if ($info['is_unlimited']) {
                return null; // Unlimited, allow
            }

            $message = "You've reached your property limit ({$info['limit']} properties). ";

            if ($info['remaining'] == 0) {
                $message .= "Please upgrade your subscription or remove some properties to add new ones.";
            } else {
                $message .= "You have {$info['remaining']} properties remaining.";
            }

            return redirect()->route('office.properties')
                ->with('error', $message);
        }

        return null; // Validation passed
    }
    public function showSubscriptionStatus()
    {
        $office = auth('office')->user()->load('subscription.currentPlan');
        $subscription = $office->subscription;
        $propertyInfo = $office->getPropertyLimitInfo();
        $subscriptionBadge = $office->getSubscriptionStatusBadge();

        return view('office.subscription-status', compact('subscription', 'propertyInfo', 'subscriptionBadge'));
    }
}
