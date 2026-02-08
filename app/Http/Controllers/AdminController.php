<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;
use App\Models\User;
use App\Models\Agent;
use App\Models\RealEstateOffice;
use App\Models\Property;
use App\Models\Project;
use App\Models\BannerAd;
use App\Models\Transaction;
use App\Models\Appointment;
use App\Models\ServiceProvider;
use App\Models\AdminAnalytics;
use App\Models\Report;
use App\Models\Review;
use App\Models\Subscription\Subscription;
use App\Models\Subscription\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ServiceProviderPlan;
use App\Models\Category;

class AdminController extends Controller
{
    // ==========================================
    // AUTHENTICATION METHODS
    // ==========================================

    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $admin = Auth::guard('admin')->user();

            // Check if admin is verified
            if (!$admin->is_verified) {
                Auth::guard('admin')->logout();
                return back()->with('error', 'Your account is not verified.');
            }

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . $admin->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Invalid credentials. Please try again.');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show admin registration form
     */
    public function showRegisterForm()
    {
        return view('admin.auth.register');
    }

    /**
     * Handle admin registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:admins',
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,super_admin',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.login')
            ->with('success', 'Admin account created successfully! You can now login.');
    }

    // ==========================================
    // DASHBOARD
    // ==========================================

    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        $today = Carbon::today();

        // ✅ 1. SUBSCRIPTION REVENUE CALCULATIONS (IQD)

        // Total Subscription Revenue from ALL active subscriptions
        $totalSubscriptionRevenue = Subscription::where('status', 'active')
            ->sum('monthly_amount'); // This is in IQD as per your subscription table

        // Agent Subscriptions Revenue
        $agentSubscriptions = Subscription::where('status', 'active')
            ->whereHas('agents') // Has relationship to agents
            ->get();

        $agentSubRevenue = $agentSubscriptions->sum('monthly_amount');
        $agentSubCount = $agentSubscriptions->count();

        // Office Subscriptions Revenue
        $officeSubscriptions = Subscription::where('status', 'active')
            ->whereHas('offices') // Has relationship to offices
            ->get();

        $officeSubRevenue = $officeSubscriptions->sum('monthly_amount');
        $officeSubCount = $officeSubscriptions->count();

        // This Month's New Subscriptions
        $thisMonthRevenue = Subscription::where('status', 'active')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->sum('monthly_amount');

        $newSubsThisMonth = Subscription::where('status', 'active')
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        // 2. Core Stats
        $stats = [
            // ✅ SUBSCRIPTION STATS (Primary Revenue Source)
            'subscription_revenue_iqd' => $totalSubscriptionRevenue,
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'agent_subscription_revenue' => $agentSubRevenue,
            'agent_subscriptions_count' => $agentSubCount,
            'office_subscription_revenue' => $officeSubRevenue,
            'office_subscriptions_count' => $officeSubCount,
            'this_month_revenue' => $thisMonthRevenue,
            'new_subscriptions_this_month' => $newSubsThisMonth,

            // Other Stats
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', $today)->count(),
            'total_properties' => Property::count(),
            'active_properties' => Property::where('status', 'available')->where('is_active', true)->count(),
            'properties_for_sale' => Property::whereIn('listing_type', ['sale', 'sell'])->count(),
            'properties_for_rent' => Property::where('listing_type', 'rent')->count(),
            'total_agents' => Agent::count(),
            'total_offices' => RealEstateOffice::count(),
        ];

        // 3. Pending Actions (Action Center)
        $pendingApprovals = [
            'properties' => Property::where('status', 'pending')->count(),
            'agents' => Agent::where('is_verified', false)->count(),
            'offices' => RealEstateOffice::where('is_verified', false)->count(),
        ];

        // 4. Charts Data (User Growth)
        $user_registrations = User::select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $user_registrations[$i] ?? 0;
        }

        // 5. Recent Data Fetching
        // Fetch Properties with Owner to avoid N+1 queries
        $recent_properties = Property::with('owner')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $top_agents = Agent::withCount('properties')
            ->orderBy('properties_count', 'desc')
            ->take(5)
            ->get();

        $recent_users = User::orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'pendingApprovals',
            'monthlyData',
            'recent_properties',
            'top_agents',
            'recent_users'
        ));
    }
    /**
     * Get dashboard stats as JSON
     */
    public function getStats()
    {
        $today = Carbon::today();
        $stats = [
            'users' => User::count(),
            'agents' => Agent::count(),
            'properties' => Property::count(),
            'revenue' => Transaction::where('status', 'completed')->sum('amount_usd'),
            'new_today' => [
                'users' => User::whereDate('created_at', $today)->count(),
                'properties' => Property::whereDate('created_at', $today)->count(),
                'appointments' => Appointment::whereDate('created_at', $today)->count(),
            ]
        ];
        return response()->json($stats);
    }

    /**
     * Get chart data
     */
    public function getChartData(Request $request)
    {
        $type = $request->input('type', 'users');
        $period = $request->input('period', '12');

        switch ($type) {
            case 'users':
                $data = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', Carbon::now()->subMonths($period))->groupBy('month')->orderBy('month')->get();
                break;
            case 'revenue':
                $data = Transaction::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('SUM(amount_usd) as total'))
                    ->where('status', 'completed')->where('created_at', '>=', Carbon::now()->subMonths($period))->groupBy('month')->orderBy('month')->get();
                break;
            case 'properties':
                $data = Property::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', Carbon::now()->subMonths($period))->groupBy('month')->orderBy('month')->get();
                break;
            default:
                $data = [];
        }
        return response()->json($data);
    }

    // ==========================================
    // USERS MANAGEMENT
    // ==========================================

    public function usersIndex(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            if ($request->status == 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status == 'unverified') {
                $query->where('is_verified', false);
            } elseif ($request->status == 'email_verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status == 'email_unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function usersCreate()
    {
        return view('admin.users.create');
    }

    public function usersStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|in:user,agent,admin',
            'photo_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'place' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'about_me' => 'nullable|string',
            'language' => 'nullable|in:en,ar,ku',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except('password', 'photo_image');
        $data['password'] = Hash::make($request->password);
        $data['is_verified'] = true;
        $data['email_verified_at'] = now();

        // Handle image upload following the pattern
        if ($request->hasFile('photo_image')) {
            $image = $request->file('photo_image');
            // Stores in storage/app/public/users
            $path = $image->store('users', 'public');
            // Generates a full URL for the Flutter app to consume
            $data['photo_image'] = asset('storage/' . $path);
        }

        User::create($data);
        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function usersShow($id)
    {
        $user = User::with([
            'ownedProperties',
            'appointments',
            'favoriteProperties',
            'buyerTransactions',
            'sellerTransactions'
        ])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function usersEdit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|in:user,agent,admin',
            'photo_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'place' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'about_me' => 'nullable|string',
            'language' => 'nullable|in:en,ar,ku',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except('password', 'photo_image');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle image upload following the pattern
        if ($request->hasFile('photo_image')) {
            // Delete old image if exists
            if ($user->photo_image) {
                $oldPath = str_replace(asset('storage/'), '', $user->photo_image);
                Storage::disk('public')->delete($oldPath);
            }

            $image = $request->file('photo_image');
            // Stores in storage/app/public/users
            $path = $image->store('users', 'public');
            // Generates a full URL for the Flutter app to consume
            $data['photo_image'] = asset('storage/' . $path);
        }

        $user->update($data);
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function usersDelete($id)
    {
        $user = User::findOrFail($id);

        // Delete user image if exists
        if ($user->photo_image) {
            $path = str_replace(asset('storage/'), '', $user->photo_image);
            Storage::disk('public')->delete($path);
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }

    /**
     * Verify a user
     */
    public function usersVerify($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => true]);
        return back()->with('success', 'User verified successfully!');
    }

    /**
     * Unverify a user
     */
    public function usersUnverify($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => false]);
        return back()->with('success', 'User marked as unverified!');
    }

    // ==========================================
    // AGENTS MANAGEMENT
    // ==========================================

    public function agentsIndex(Request $request)
    {
        $query = Agent::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('agent_name', 'like', "%{$search}%")
                    ->orWhere('primary_email', 'like', "%{$search}%")
                    ->orWhere('primary_phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->status == 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status == 'pending') {
                $query->where('is_verified', false);
            }
        }

        if ($request->has('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $agents = $query->withCount('properties')->paginate(15)->withQueryString();

        $stats = [
            'total' => Agent::count(),
            'verified' => Agent::where('is_verified', true)->count(),
            'pending' => Agent::where('is_verified', false)->count(),
            'total_properties' => Property::where('owner_type', 'App\Models\Agent')->count(),
        ];

        $pendingCount = Agent::where('is_verified', false)->count();

        return view('admin.agents.index', compact('agents', 'stats', 'pendingCount'));
    }

    public function agentsPending()
    {
        $agents = Agent::where('is_verified', false)->paginate(15);
        return view('admin.agents.pending', compact('agents'));
    }

    public function agentsShow($id)
    {
        $agent = Agent::with(['properties', 'subscription', 'appointments'])->findOrFail($id);
        return view('admin.agents.show', compact('agent'));
    }



    public function agentsUpdate(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'agent_name' => 'required|string|max:255',
            'primary_email' => 'required|email|max:255|unique:agents,primary_email,' . $id,
            'primary_phone' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'office_address' => 'nullable|string|max:255',
            'type' => 'nullable|in:independent,company',

            // Allow selecting a system plan
            'plan_id' => 'nullable|exists:subscription_plans,id',

            'company_name' => 'nullable|string|max:255',
            'employment_status' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:100',
            'years_experience' => 'nullable|integer|min:0',
            'properties_sold' => 'nullable|integer|min:0',
            'agent_overview' => 'nullable|string|max:255',
            'agent_bio' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'consultation_fee' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bio_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // 1. Handle Images
        if ($request->hasFile('profile_image')) {
            if ($agent->profile_image) {
                $oldPath = str_replace(asset('storage/'), '', $agent->profile_image);
                if (Storage::disk('public')->exists($oldPath)) Storage::disk('public')->delete($oldPath);
            }
            $data['profile_image'] = asset('storage/' . $request->file('profile_image')->store('agents/profiles', 'public'));
        }

        if ($request->hasFile('bio_image')) {
            if ($agent->bio_image) {
                $oldPath = str_replace(asset('storage/'), '', $agent->bio_image);
                if (Storage::disk('public')->exists($oldPath)) Storage::disk('public')->delete($oldPath);
            }
            $data['bio_image'] = asset('storage/' . $request->file('bio_image')->store('agents/bios', 'public'));
        }

        $data['is_verified'] = $request->has('is_verified') ? 1 : 0;

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        // --- FIX: HANDLE PLAN SELECTION & MAPPING TO ENUM ---
        if ($request->filled('plan_id')) {
            $plan = SubscriptionPlan::find($request->plan_id);

            // 1. Cancel any existing active subscription
            $oldSub = Subscription::where('user_id', $agent->id)->where('status', 'active')->first();
            if ($oldSub) {
                $oldSub->update(['status' => 'cancelled']);
            }

            // 2. Create the real Subscription record
            $subscription = Subscription::create([
                'id' => (string) Str::uuid(),
                'user_id' => $agent->id,
                'current_plan_id' => $plan->id,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonths($plan->duration_months ?? 1),
                'billing_cycle' => ($plan->duration_months >= 12) ? 'annual' : 'monthly',
                'auto_renewal' => true,
                'property_activation_limit' => $plan->max_properties ?? 0,
                'properties_activated_this_month' => 0,
                'remaining_activations' => $plan->max_properties ?? 0,
                'monthly_amount' => $plan->final_price_usd ?? 0,
            ]);

            // 3. Update Agent Relationship
            $data['subscription_id'] = $subscription->id;

            // 4. FIX: Map the Plan Name to your Database ENUM values (starter, professional, enterprise)
            // This prevents the "Data Truncated" error.
            if ($plan->max_properties > 100 || $plan->duration_months >= 12) {
                $data['current_plan'] = 'enterprise';
            } elseif ($plan->max_properties > 30 || $plan->duration_months >= 3) {
                $data['current_plan'] = 'professional';
            } else {
                $data['current_plan'] = 'starter';
            }

            $data['remaining_property_uploads'] = $plan->max_properties ?? 0;
        }

        unset($data['plan_id']); // Remove form field not in DB

        $agent->update($data);

        return redirect()->route('admin.agents.index')->with('success', 'Agent updated successfully!');
    }

    public function agentsDelete($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->delete();
        return redirect()->route('admin.agents.index')->with('success', 'Agent deleted successfully!');
    }

    public function agentsVerify($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->update(['is_verified' => true]);
        return back()->with('success', 'Agent verified successfully!');
    }

    public function agentsSuspend($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->update(['is_verified' => false]);
        return back()->with('success', 'Agent suspended successfully!');
    }

    // ==========================================
    // OFFICES MANAGEMENT
    // ==========================================

    public function officesIndex(Request $request)
    {
        $query = RealEstateOffice::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('email_address', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            if ($request->status == 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status == 'pending') {
                $query->where('is_verified', false);
            }
        }

        if ($request->has('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        $offices = $query->withCount('ownedProperties')->paginate(15)->withQueryString();

        $stats = [
            'total' => RealEstateOffice::count(),
            'verified' => RealEstateOffice::where('is_verified', true)->count(),
            'pending' => RealEstateOffice::where('is_verified', false)->count(),
            'total_properties' => Property::where('owner_type', 'App\Models\RealEstateOffice')->count(),
        ];

        $pendingCount = RealEstateOffice::where('is_verified', false)->count();

        return view('admin.offices.index', compact('offices', 'stats', 'pendingCount'));
    }

    public function officesPending()
    {
        $offices = RealEstateOffice::where('is_verified', false)->paginate(15);
        return view('admin.offices.pending', compact('offices'));
    }

    public function officesShow($id)
    {
        $office = RealEstateOffice::with(['ownedProperties', 'subscription', 'agents'])->findOrFail($id);
        return view('admin.offices.show', compact('office'));
    }

    public function officesEdit($id)
    {
        // Eager load subscription to show current plan in view
        $office = RealEstateOffice::with('subscription.currentPlan')->findOrFail($id);

        // Fetch active plans specifically for OFFICES from the database
        $plans = SubscriptionPlan::where('type', 'real_estate_office')
            ->where('active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.offices.edit', compact('office', 'plans'));
    }

    public function officesUpdate(Request $request, $id)
    {
        $office = RealEstateOffice::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'email_address' => 'required|email|max:255|unique:real_estate_offices,email_address,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'office_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            'years_experience' => 'nullable|integer|min:0',
            'properties_sold' => 'nullable|integer|min:0',
            'company_bio' => 'nullable|string',
            'about_company' => 'nullable|string',

            // Availability Schedule (Assuming JSON string)
            'availability_schedule' => 'nullable|string',

            // Subscription Plan Selection
            'plan_id' => 'nullable|exists:subscription_plans,id',

            // Images
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'company_bio_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Exclude fields not directly in the 'real_estate_offices' table
        $data = $request->except(['password', 'logo', 'company_bio_image', 'plan_id']);

        // Handle Checkbox
        $data['is_verified'] = $request->has('is_verified') ? 1 : 0;

        // Handle Password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        // ---------------------------------------------------------
        // HANDLE IMAGE UPLOADS
        // ---------------------------------------------------------

        // 1. Profile Image (Logo)
        if ($request->hasFile('logo')) {
            if ($office->profile_image) {
                $oldPath = str_replace(asset('storage/'), '', $office->profile_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('logo')->store('office_profiles', 'public');
            $data['profile_image'] = asset('storage/' . $path);
        }

        // 2. Company Bio Image
        if ($request->hasFile('company_bio_image')) {
            if ($office->company_bio_image) {
                $oldPath = str_replace(asset('storage/'), '', $office->company_bio_image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('company_bio_image')->store('office_bio_images', 'public');
            $data['company_bio_image'] = asset('storage/' . $path);
        }

        // ---------------------------------------------------------
        // HANDLE SUBSCRIPTION ASSIGNMENT
        // ---------------------------------------------------------
        if ($request->filled('plan_id')) {
            $plan = SubscriptionPlan::find($request->plan_id);

            // Cancel any previous active subscription for this office
            $oldSub = Subscription::where('user_id', $office->id)->where('status', 'active')->first();
            if ($oldSub) {
                $oldSub->update(['status' => 'cancelled']);
            }

            // Create new subscription
            $subscription = Subscription::create([
                'id' => (string) Str::uuid(),
                'user_id' => $office->id, // Linking subscription to office ID
                'current_plan_id' => $plan->id,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addMonths($plan->duration_months ?? 12),
                'billing_cycle' => 'annual',
                'auto_renewal' => true,
                'property_activation_limit' => 0, // 0 usually means unlimited for offices
                'properties_activated_this_month' => 0,
                'remaining_activations' => 0,
                'monthly_amount' => $plan->final_price_iqd ?? 0,
            ]);

            $data['subscription_id'] = $subscription->id;

            // Map plan name for the text column. Assuming 'enterprise' for office plans to fit ENUM if strict.
            // If column is VARCHAR, you can use $plan->name.
            $data['current_plan'] = 'enterprise';
        }

        $office->update($data);

        return redirect()->route('admin.offices.index')->with('success', 'Office updated successfully!');
    }

    public function officesDelete($id)
    {
        $office = RealEstateOffice::findOrFail($id);
        $office->delete();
        return redirect()->route('admin.offices.index')->with('success', 'Office deleted successfully!');
    }

    public function officesVerify($id)
    {
        $office = RealEstateOffice::findOrFail($id);
        $office->update(['is_verified' => true]);
        return back()->with('success', 'Office verified successfully!');
    }

    public function officesSuspend($id)
    {
        $office = RealEstateOffice::findOrFail($id);
        $office->update(['is_verified' => false]);
        return back()->with('success', 'Office suspended successfully!');
    }

    // ==========================================
    // PROPERTIES MANAGEMENT
    // ==========================================

    // In AdminController.php - Update propertiesIndex method

    public function propertiesIndex(Request $request)
    {
        $query = Property::with('owner');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar')) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.ku')) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('listing_type') && $request->listing_type != '') {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->has('owner_type') && $request->owner_type != '') {
            $ownerType = 'App\\Models\\' . $request->owner_type;
            $query->where('owner_type', $ownerType);
        }

        $properties = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Property::count(),
            'active' => Property::where('is_active', true)->count(),
            'pending' => Property::where('status', 'pending')->count(),
            'for_sale' => Property::where('listing_type', 'sale')->count(),
            'for_rent' => Property::where('listing_type', 'rent')->count(),
        ];

        $pendingCount = Property::where('status', 'pending')->count();

        return view('admin.properties.index', compact('properties', 'stats', 'pendingCount'));
    }

    public function propertiesPending()
    {
        $properties = Property::where('status', 'pending')->with('owner')->paginate(15);
        return view('admin.properties.pending', compact('properties'));
    }

    public function propertiesShow($id)
    {
        $property = Property::with(['owner', 'interactions' => function ($query) {
            $query->where('interaction_type', 'impression')
                ->whereNotNull('user_id')
                ->with('user')
                ->latest()
                ->take(50);
        }])->findOrFail($id);

        return view('admin.properties.show', compact('property'));
    }

    public function propertiesEdit($id)
    {
        $property = Property::findOrFail($id);
        return view('admin.properties.edit', compact('property'));
    }

    public function propertiesUpdate(Request $request, $id)
    {
        // 1. Find the property using Eloquent (Required for JSON casting to work)
        $property = Property::findOrFail($id);

        // 2. Validation
        $validator = Validator::make($request->all(), [
            'name.en' => 'required|string|max:255',
            'area' => 'required|numeric',
            'listing_type' => 'required', // We handle the mapping below
            'status' => 'required|in:available,pending,sold,rented,suspended',
            'price_usd' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0', // This is the IQD field
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // --- FIX: ENUM MAPPING ---
            // Your database ENUM is ['sell', 'rent'], but form sends 'sale'.
            $formListingType = $request->input('listing_type');
            $dbListingType = ($formListingType === 'sale') ? 'sell' : $formListingType;

            // 3. Prepare Data Array
            // We pass raw arrays; Eloquent will automatically JSON-encode them
            // based on the $casts property in your Property model.
            $updateData = [
                'name' => [
                    'en' => $request->input('name.en'),
                    'ar' => $request->input('name.ar'),
                    'ku' => $request->input('name.ku'),
                ],
                'description' => [
                    'en' => $request->input('description.en'),
                    'ar' => $request->input('description.ar'),
                    'ku' => $request->input('description.ku'),
                ],
                // --- FIX: DUAL PRICING ---
                'price' => [
                    'usd' => (float) $request->input('price_usd'),
                    'iqd' => (float) $request->input('price'),
                    'amount' => (float) $request->input('price_usd'), // Fallback for standard display
                    'currency' => 'USD'
                ],
                'listing_type' => $dbListingType,
                'status' => $request->input('status'),
                'area' => (float) $request->input('area'),
                'floor_number' => $request->input('floor_number'),
                'year_built' => $request->input('year_built'),
                'energy_rating' => $request->input('energy_rating'),
                'address' => $request->input('address'),
                'virtual_tour_url' => $request->input('virtual_tour_url'),
                'floor_plan_url' => $request->input('floor_plan_url'),
                'rental_period' => $request->input('rental_period'),

                // Booleans (Checkboxes)
                'furnished' => $request->has('furnished'),
                'electricity' => $request->has('electricity'),
                'water' => $request->has('water'),
                'internet' => $request->has('internet'),
                'is_active' => $request->has('is_active'),
                'published' => $request->has('published'),
                'verified' => $request->has('verified'),
                'is_boosted' => $request->has('is_boosted'),
                'boost_start_date' => $request->input('boost_start_date'),
                'boost_end_date' => $request->input('boost_end_date'),

                // Nested JSON Objects
                'type' => [
                    'category' => $request->input('type.category')
                ],
                'rooms' => [
                    'bedroom' => (int)$request->input('rooms.bedroom.count'),
                    'bathroom' => (int)$request->input('rooms.bathroom.count'),
                    'living_room' => (int)$request->input('rooms.living_room.count'),
                ],
                'address_details' => [
                    'city' => ['en' => $request->input('address_details.city.en')],
                    'district' => ['en' => $request->input('address_details.district.en')],
                ],
                'locations' => [
                    [
                        'lat' => (float) $request->input('locations.0.lat'),
                        'lng' => (float) $request->input('locations.0.lng')
                    ]
                ],
                'availability' => [
                    'from' => $request->input('availability.from'),
                    'to' => $request->input('availability.to'),
                ],
                'construction_details' => [
                    'type' => $request->input('construction_details.type'),
                    'quality' => $request->input('construction_details.quality'),
                ],
                'energy_details' => [
                    'certificate' => $request->input('energy_details.certificate'),
                    'consumption' => $request->input('energy_details.consumption'),
                ],
                'furnishing_details' => [
                    'level' => $request->input('furnishing_details.level'),
                    'items' => array_values(array_filter(explode(',', $request->input('furnishing_details.items', '')))),
                ],
                'seo_metadata' => [
                    'title' => $request->input('seo_metadata.title'),
                    'description' => $request->input('seo_metadata.description'),
                    'keywords' => array_values(array_filter(explode(',', $request->input('seo_metadata.keywords', '')))),
                ]
            ];

            // 4. Handle Image Uploads
            if ($request->hasFile('images')) {
                $currentImages = $property->images ?? [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('properties', 'public');
                    $currentImages[] = 'storage/' . $path;
                }
                $updateData['images'] = $currentImages;
            }

            // 5. Save Changes
            // Using $property->update() ensures that all dirty attributes are
            // synchronized and cast properly.
            $property->update($updateData);

            return redirect()->route('admin.properties.index')->with('success', 'Property updated successfully!');
        } catch (\Exception $e) {
            Log::error('Property Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }
    public function propertiesDelete($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();
        return redirect()->route('admin.properties.index')->with('success', 'Property deleted successfully!');
    }

    public function propertiesApprove($id)
    {
        $property = Property::findOrFail($id);
        $property->update(['status' => 'available', 'verified' => true]);
        return back()->with('success', 'Property approved successfully!');
    }

    public function propertiesReject($id)
    {
        $property = Property::findOrFail($id);
        $property->update(['status' => 'rejected']);
        return back()->with('success', 'Property rejected!');
    }

    public function propertiesToggleActive($id)
    {
        $property = Property::findOrFail($id);
        $property->update(['is_active' => !$property->is_active]);
        return back()->with('success', 'Property status updated!');
    }

    // ==========================================
    // PROJECTS MANAGEMENT
    // ==========================================

    public function projectsIndex(Request $request)
    {
        $query = Project::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereJsonContains('name->en', $search)
                    ->orWhereJsonContains('name->ar', $search)
                    ->orWhereJsonContains('name->ku', $search);
            });
        }

        $projects = $query->paginate(15)->withQueryString();
        return view('admin.projects.index', compact('projects'));
    }

    public function projectsShow($id)
    {
        $project = Project::findOrFail($id);
        return view('admin.projects.show', compact('project'));
    }

    public function projectsEdit($id)
    {
        $project = Project::findOrFail($id);
        return view('admin.projects.edit', compact('project'));
    }

    public function projectsUpdate(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->update($request->only(['is_active', 'published', 'status']));
        return redirect()->route('admin.projects.index')->with('success', 'Project updated successfully!');
    }

    public function projectsDelete($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        return redirect()->route('admin.projects.index')->with('success', 'Project deleted successfully!');
    }

    public function projectsToggleActive($id)
    {
        $project = Project::findOrFail($id);
        $project->update(['is_active' => !$project->is_active]);
        return back()->with('success', 'Project status updated!');
    }

    // ==========================================
    // BANNERS MANAGEMENT
    // ==========================================

    public function bannersIndex(Request $request)
    {
        $query = BannerAd::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereJsonContains('title->en', $search)
                    ->orWhere('owner_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $banners = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => BannerAd::count(),
            'active' => BannerAd::where('is_active', true)->count(),
            'pending' => BannerAd::where('status', 'pending')->count(),
        ];

        $pendingCount = BannerAd::where('status', 'pending')->count();

        return view('admin.banners.index', compact('banners', 'stats', 'pendingCount'));
    }

    public function bannersCreate()
    {
        // Fetch all properties so the dropdown in the view has data
        $properties = Property::select('id', 'name')->get();

        return view('admin.banners.create', compact('properties'));
    }

    public function bannersStore(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'title.en' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'start_date' => 'required|date',
            'status' => 'required|in:pending,active,paused',
            'link_url' => 'nullable|url',
        ]);

        try {
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('banners', 'public');
                $imageUrl = asset('storage/' . $path);
            }

            $data = [
                'title' => [
                    'en' => $request->input('title.en'),
                    'ar' => $request->input('title.ar') ?? $request->input('title.en'),
                    'ku' => $request->input('title.ku') ?? $request->input('title.en'),
                ],
                'description' => [
                    'en' => $request->description ?? '',
                    'ar' => $request->description ?? '',
                    'ku' => $request->description ?? '',
                ],
                'call_to_action' => [
                    'en' => 'Learn More',
                ],
                'image_url' => $imageUrl,
                'link_url' => $request->link_url,

                // FIXED: We must use one of your ENUM values ('real_estate' or 'agent')
                'owner_type' => 'real_estate',
                'owner_id' => $admin->id, // Store admin ID here
                'owner_name' => $request->owner_name ?? $admin->username,

                'banner_type' => $request->banner_type ?? 'general_marketing',
                'banner_size' => $request->banner_size ?? 'leaderboard',
                'position' => $request->position ?? 'header',
                'property_id' => $request->property_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'is_active' => ($request->status === 'active'),
                'created_by_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            BannerAd::create($data);

            return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully!');
        } catch (\Exception $e) {
            Log::error('Banner Store Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function bannersPending()
    {
        $banners = BannerAd::where('status', 'pending')->paginate(15);
        return view('admin.banners.pending', compact('banners'));
    }

    public function bannersShow($id)
    {
        $banner = BannerAd::findOrFail($id);
        return view('admin.banners.show', compact('banner'));
    }

    public function bannersEdit($id)
    {
        $banner = BannerAd::findOrFail($id);
        return view('admin.banners.edit', compact('banner'));
    }

    public function bannersUpdate(Request $request, $id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->update($request->all());
        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully!');
    }

    public function bannersDelete($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully!');
    }

    public function bannersApprove($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->update(['status' => 'active', 'is_active' => true]);
        return back()->with('success', 'Banner approved successfully!');
    }

    public function bannersReject($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->update(['status' => 'rejected', 'is_active' => false]);
        return back()->with('success', 'Banner rejected!');
    }

    public function bannersPause($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->update(['status' => 'paused']);
        return back()->with('success', 'Banner paused!');
    }

    public function bannersResume($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->update(['status' => 'active']);
        return back()->with('success', 'Banner resumed!');
    }

    // ==========================================
    // SUBSCRIPTIONS MANAGEMENT
    // ==========================================

    public function subscriptionsIndex(Request $request)
    {
        $query = Subscription::with(['currentPlan', 'agents', 'offices']);

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->paginate(15)->withQueryString();
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function subscriptionsShow($id)
    {
        $subscription = Subscription::with(['currentPlan', 'agents', 'offices'])->findOrFail($id);
        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function subscriptionsEdit($id)
    {
        $subscription = Subscription::findOrFail($id);
        $plans = SubscriptionPlan::active()->get();
        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    public function subscriptionsUpdate(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->update($request->all());
        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription updated successfully!');
    }

    public function subscriptionsDelete($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();
        return redirect()->route('admin.subscriptions.index')->with('success', 'Subscription deleted successfully!');
    }

    public function subscriptionsCancel($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->cancel();
        return back()->with('success', 'Subscription cancelled successfully!');
    }

    public function subscriptionsRenew($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->renew();
        return back()->with('success', 'Subscription renewed successfully!');
    }

    // ==========================================
    // SUBSCRIPTION PLANS
    // ==========================================



    public function subscriptionPlansShow($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription-plans.show', compact('plan'));
    }





    // ==========================================
    // TRANSACTIONS
    // ==========================================

    public function transactionsIndex()
    {
        $transactions = Transaction::with(['buyer', 'seller', 'property', 'agent'])->paginate(15);
        return view('admin.transactions.index', compact('transactions'));
    }

    public function transactionsShow($id)
    {
        $transaction = Transaction::with(['buyer', 'seller', 'property', 'agent', 'office'])->findOrFail($id);
        return view('admin.transactions.show', compact('transaction'));
    }

    public function transactionsApprove($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => 'approved', 'payment_status' => 'completed']);
        return back()->with('success', 'Transaction approved!');
    }

    public function transactionsReject($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => 'rejected']);
        return back()->with('success', 'Transaction rejected!');
    }

    // ==========================================
    // APPOINTMENTS
    // ==========================================

    public function appointmentsIndex(Request $request)
    {
        // Eager load everything to prevent N+1 queries
        $query = Appointment::with(['user', 'agent', 'office', 'property']);

        // Filter by Status
        if ($request->has('status') && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort by Date (Nearest first)
        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Calculate Stats
        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'today' => Appointment::whereDate('appointment_date', Carbon::today())->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
        ];

        return view('admin.appointments.index', compact('appointments', 'stats'));
    }

    public function appointmentsShow($id)
    {
        $appointment = Appointment::with(['user', 'agent', 'office', 'property'])->findOrFail($id);
        return view('admin.appointments.show', compact('appointment'));
    }

    public function appointmentsCancel($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    public function appointmentsDelete($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return redirect()->route('admin.appointments.index')->with('success', 'Appointment record deleted.');
    }

    // ==========================================
    // SERVICE PROVIDERS
    // ==========================================

    public function serviceProvidersIndex(Request $request)
    {
        $query = ServiceProvider::with(['category', 'plan']);

        if ($request->has('search')) {
            $query->where('company_name', 'like', "%{$request->search}%")
                ->orWhere('email_address', 'like', "%{$request->search}%");
        }

        if ($request->has('verified')) {
            $query->where('is_verified', $request->verified == '1');
        }

        $providers = $query->latest()->paginate(15)->withQueryString();
        return view('admin.service-providers.index', compact('providers'));
    }

    public function serviceProvidersCreate()
    {
        $categories = \App\Models\Category::all(); // Assuming Category model exists
        $plans = \App\Models\ServiceProviderPlan::where('active', true)->get();
        return view('admin.service-providers.create', compact('categories', 'plans'));
    }
    public function serviceProvidersShow($id)
    {
        $provider = ServiceProvider::with(['category', 'plan', 'galleries', 'offerings', 'reviews'])->findOrFail($id);
        return view('admin.service-providers.show', compact('provider'));
    }

    public function serviceProvidersEdit($id)
    {
        $provider = ServiceProvider::findOrFail($id);
        $categories = \App\Models\Category::all();
        $plans = \App\Models\ServiceProviderPlan::where('active', true)->get();
        return view('admin.service-providers.edit', compact('provider', 'categories', 'plans'));
    }

    public function serviceProvidersUpdate(Request $request, $id)
    {
        $provider = ServiceProvider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'email_address' => 'required|email|unique:service_providers,email_address,' . $id,
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // 1. Update Base Info
            $data = $request->except([
                'profile_image',
                'plan_id',
                'gallery_images',
                'gallery_titles',
                'gallery_descriptions',
                'gallery_existing_images', // Added this
                'offering_titles',
                'offering_descriptions',
                'offering_prices',
                'offering_active',
                'reviewer_names',
                'reviewer_ratings',
                'reviewer_contents',
                'reviewer_service_types',
                'reviewer_dates',
                'reviewer_verified',
                'reviewer_featured'
            ]);

            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($provider->profile_image) {
                    $oldPath = str_replace(asset('storage/'), '', $provider->profile_image);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $path = $request->file('profile_image')->store('service_providers/profiles', 'public');
                $data['profile_image'] = asset('storage/' . $path);
            }

            $data['is_verified'] = $request->has('is_verified');

            if ($request->filled('plan_id') && $request->plan_id != $provider->plan_id) {
                $data['plan_id'] = $request->plan_id;
                $data['plan_active'] = true;
                $data['plan_expires_at'] = now()->addMonth();
            }

            $provider->update($data);

            // 2. Handle Galleries (Fixed Logic)
            if ($request->has('gallery_titles')) {
                $provider->galleries()->delete(); // Wipe old items

                foreach ($request->gallery_titles as $index => $title) {
                    $imageUrl = null;

                    // Case A: New File Uploaded
                    if (isset($request->file('gallery_images')[$index])) {
                        $imagePath = $request->file('gallery_images')[$index]->store('service_providers/gallery', 'public');
                        $imageUrl = asset('storage/' . $imagePath);
                    }
                    // Case B: No new file, use existing URL (passed from hidden input)
                    elseif (isset($request->gallery_existing_images[$index])) {
                        $imageUrl = $request->gallery_existing_images[$index];
                    }

                    // Only create if we have a valid image URL
                    if ($imageUrl) {
                        $provider->galleries()->create([
                            'project_title' => $title,
                            'description' => $request->gallery_descriptions[$index] ?? null,
                            'image_url' => $imageUrl,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // 3. Handle Offerings
            if ($request->has('offering_titles')) {
                $provider->offerings()->delete();
                foreach ($request->offering_titles as $index => $title) {
                    if ($title) {
                        $provider->offerings()->create([
                            'service_title' => $title,
                            'service_description' => $request->offering_descriptions[$index] ?? null,
                            'price_range' => $request->offering_prices[$index] ?? null,
                            'active' => isset($request->offering_active[$index]),
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            // 4. Handle Reviews
            if ($request->has('reviewer_names')) {
                $provider->reviews()->delete();
                foreach ($request->reviewer_names as $index => $name) {
                    if ($name) {
                        $provider->reviews()->create([
                            'reviewer_name' => $name,
                            'star_rating' => $request->reviewer_ratings[$index] ?? 5,
                            'review_content' => $request->reviewer_contents[$index] ?? null,
                            'review_date' => $request->reviewer_dates[$index] ?? now(),
                            'is_verified' => isset($request->reviewer_verified[$index]),
                            'is_featured' => isset($request->reviewer_featured[$index]),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.service-providers.index')->with('success', 'Service Provider updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function serviceProvidersDelete($id)
    {
        $provider = ServiceProvider::findOrFail($id);
        $provider->delete();
        return back()->with('success', 'Service Provider deleted successfully');
    }

    public function serviceProvidersVerify($id)
    {
        $provider = ServiceProvider::findOrFail($id);
        $provider->update(['is_verified' => true]);
        return back()->with('success', 'Service provider verified!');
    }
    public function serviceProviderPlansIndex()
    {
        $plans = \App\Models\ServiceProviderPlan::orderBy('sort_order')->paginate(15);
        return view('admin.service-provider-plans.index', compact('plans'));
    }
    // ==========================================
    // REVIEWS & REPORTS
    // ==========================================

    public function reviewsIndex()
    {
        $reviews = Review::with(['user', 'property'])->paginate(15);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function reviewsShow($id)
    {
        $review = Review::with(['user', 'property'])->findOrFail($id);
        return view('admin.reviews.show', compact('review'));
    }

    public function reviewsDelete($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted!');
    }

    public function reviewsApprove($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);
        return back()->with('success', 'Review approved!');
    }

    public function serviceProvidersStore(Request $request)
    {
        Log::info('--- STARTED: Creating Service Provider ---');

        // 1. Log Raw Input (excluding files to keep log clean)
        Log::info('Incoming Request Data:', $request->except(['profile_image', 'gallery_images']));

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'email_address' => 'required|email|unique:service_providers,email_address',
            'phone_number' => 'required|string',
            'profile_image' => 'nullable|image|max:2048',

            // Arrays
            'gallery_images.*' => 'nullable|image|max:2048',
            'gallery_titles.*' => 'nullable|string',
            'offering_titles.*' => 'nullable|string',
            'reviewer_names.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validation Failed:', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // 2. Prepare Base Data
            $data = $request->except([
                'profile_image',
                'plan_id',
                'gallery_images',
                'gallery_titles',
                'gallery_descriptions',
                'offering_titles',
                'offering_descriptions',
                'offering_prices',
                'offering_active',
                'reviewer_names',
                'reviewer_ratings',
                'reviewer_contents',
                'reviewer_service_types',
                'reviewer_dates',
                'reviewer_verified',
                'reviewer_featured'
            ]);

            if ($request->hasFile('profile_image')) {
                Log::info('Uploading profile image...');
                $path = $request->file('profile_image')->store('service_providers/profiles', 'public');
                $data['profile_image'] = asset('storage/' . $path);
            }

            $data['is_verified'] = $request->has('is_verified');

            if ($request->filled('plan_id')) {
                Log::info('Assigning Plan ID: ' . $request->plan_id);
                $data['plan_id'] = $request->plan_id;
                $data['plan_active'] = true;
                $data['plan_expires_at'] = now()->addMonth();
            }

            // 3. Create Main Provider
            $provider = ServiceProvider::create($data);
            Log::info('Service Provider Created. ID: ' . $provider->id);

            // 4. Handle Galleries
            if ($request->has('gallery_titles')) {
                Log::info('Processing Galleries...');
                foreach ($request->gallery_titles as $index => $title) {
                    $imageUrl = null;
                    if (isset($request->file('gallery_images')[$index])) {
                        $imagePath = $request->file('gallery_images')[$index]->store('service_providers/gallery', 'public');
                        $imageUrl = asset('storage/' . $imagePath);
                    }

                    if ($title || $imageUrl) {
                        $provider->galleries()->create([
                            'project_title' => $title,
                            'description' => $request->gallery_descriptions[$index] ?? null,
                            'image_url' => $imageUrl,
                            'sort_order' => $index,
                        ]);
                    }
                }
            } else {
                Log::info('No Gallery titles found in request.');
            }

            // 5. Handle Offerings
            if ($request->has('offering_titles')) {
                Log::info('Processing Offerings...');
                foreach ($request->offering_titles as $index => $title) {
                    if (!empty($title)) {
                        $provider->offerings()->create([
                            'service_title' => $title,
                            'service_description' => $request->offering_descriptions[$index] ?? null,
                            'price_range' => $request->offering_prices[$index] ?? null,
                            'active' => isset($request->offering_active[$index]),
                            'sort_order' => $index,
                        ]);
                    }
                }
            } else {
                Log::info('No Offering titles found.');
            }

            // 6. Handle Reviews
            if ($request->has('reviewer_names')) {
                Log::info('Processing Reviews...');
                foreach ($request->reviewer_names as $index => $name) {
                    if (!empty($name)) {
                        $provider->reviews()->create([
                            'reviewer_name' => $name,
                            'star_rating' => $request->reviewer_ratings[$index] ?? 5,
                            'review_content' => $request->reviewer_contents[$index] ?? null,
                            'service_type' => $request->reviewer_service_types[$index] ?? null,
                            'review_date' => $request->reviewer_dates[$index] ?? now(),
                            'is_verified' => isset($request->reviewer_verified[$index]),
                            'is_featured' => isset($request->reviewer_featured[$index]),
                        ]);
                    }
                }
            } else {
                Log::info('No Reviewer names found.');
            }

            DB::commit();
            Log::info('--- SUCCESS: Transaction Committed ---');

            return redirect()->route('admin.service-providers.index')->with('success', 'Service Provider created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // This is the critical part: Log the actual error message
            Log::error('!!! CRITICAL ERROR Creating Provider !!!');
            Log::error('Message: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());

            return back()->with('error', 'Error creating provider: ' . $e->getMessage())->withInput();
        }
    }

    public function reportsIndex()
    {
        $reports = Report::with(['user', 'property'])->paginate(15);
        return view('admin.reports.index', compact('reports'));
    }

    public function reportsShow($id)
    {
        $report = Report::with(['user', 'property'])->findOrFail($id);
        return view('admin.reports.show', compact('report'));
    }

    public function reportsResolve($id)
    {
        $report = Report::findOrFail($id);
        $report->update(['status' => 'resolved']);
        return back()->with('success', 'Report resolved!');
    }
    public function serviceProviderPlansCreate()
    {
        return view('admin.service-provider-plans.create');
    }
    public function reportsDelete($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();
        return redirect()->route('admin.reports.index')->with('success', 'Report deleted!');
    }

    // ==========================================
    // SETTINGS
    // ==========================================

    public function settingsIndex()
    {
        return view('admin.settings.index');
    }

    public function settingsUpdate(Request $request)
    {
        // Implement settings update logic
        return back()->with('success', 'Settings updated successfully!');
    }

    // ==========================================
    // PROFILE
    // ==========================================

    public function profileShow()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile.index', compact('admin'));
    }

    public function profileUpdate(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:admins,username,' . $admin->id,
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $admin->update($request->only(['username', 'email', 'phone']));
        return back()->with('success', 'Profile updated successfully!');
    }

    public function profilePasswordUpdate(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->with('error', 'Current password is incorrect!');
        }

        $admin->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password updated successfully!');
    }


    public function serviceProviderPlansStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
            'features' => 'nullable|string', // We accept string from textarea
        ]);

        $data = $request->all();

        // Convert newline separated string to array
        if ($request->filled('features')) {
            $data['features'] = array_filter(array_map('trim', explode("\n", $request->features)));
        } else {
            $data['features'] = [];
        }

        $data['id'] = Str::lower(str_replace(' ', '_', $request->name)) . '_' . Str::random(4); // Manual ID generation as per model
        $data['active'] = $request->has('active');
        $data['most_popular'] = $request->has('most_popular');

        \App\Models\ServiceProviderPlan::create($data);

        return redirect()->route('admin.service-provider-plans.index')->with('success', 'Plan created successfully');
    }

    public function serviceProviderPlansEdit($id)
    {
        $plan = \App\Models\ServiceProviderPlan::findOrFail($id);
        return view('admin.service-provider-plans.edit', compact('plan'));
    }
    /**
     * Update specific user profile image
     */
    public function updateUserImage(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'photo_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('photo_image')) {
            // 1. Delete old image if exists
            if ($user->photo_image && file_exists(public_path($user->photo_image))) {
                @unlink(public_path($user->photo_image));
            }

            // 2. Store new image
            // This stores in storage/app/public/users
            $path = $request->file('photo_image')->store('users', 'public');

            // 3. Update Database (Save as 'storage/users/filename.jpg')
            $user->update([
                'photo_image' => 'storage/' . $path
            ]);
        }

        return back()->with('success', 'Profile image updated successfully!');
    }


    public function agentsEdit($id)
    {
        $agent = Agent::findOrFail($id);

        // Fetch active agent plans to display in the UI (optional reference)
        // or if you want to map them dynamically later.
        $plans = SubscriptionPlan::where('type', 'agent')
            ->where('active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('admin.agents.edit', compact('agent', 'plans'));
    }

    public function subscriptionPlansIndex()
    {
        // Fetch plans sorted by type and sort_order
        $plans = SubscriptionPlan::orderBy('type')->orderBy('sort_order')->paginate(10);

        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function subscriptionPlansCreate()
    {
        return view('admin.subscription-plans.create');
    }

    public function subscriptionPlansStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name.en' => 'required|string|max:255',
            'type' => 'required|in:agent,real_estate_office',
            'final_price_usd' => 'required|numeric|min:0',
            'final_price_iqd' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'property_activation_limit' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['name', 'features']);

        // 1. Handle Name (JSON)
        $data['name'] = [
            'en' => $request->input('name.en'),
            'ar' => $request->input('name.ar'),
            'ku' => $request->input('name.ku'),
        ];

        // 2. Handle Features (Convert new lines/commas to Array)
        if ($request->filled('features')) {
            $features = preg_split("/\\r\\n|\\r|\\n/", $request->input('features'));
            $data['features'] = array_values(array_filter(array_map('trim', $features)));
        } else {
            $data['features'] = [];
        }

        // 3. Booleans
        $data['active'] = $request->has('active');
        $data['is_featured'] = $request->has('is_featured');

        SubscriptionPlan::create($data);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plan created successfully!');
    }

    public function serviceProviderPlansUpdate(Request $request, $id)
    {
        $plan = \App\Models\ServiceProviderPlan::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
        ]);

        $data = $request->all();

        if ($request->filled('features')) {
            $data['features'] = array_filter(array_map('trim', explode("\n", $request->features)));
        } else {
            $data['features'] = [];
        }

        $data['active'] = $request->has('active');
        $data['most_popular'] = $request->has('most_popular');

        $plan->update($data);

        return redirect()->route('admin.service-provider-plans.index')->with('success', 'Plan updated successfully');
    }

    public function subscriptionPlansEdit($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription-plans.edit', compact('plan'));
    }

    public function subscriptionPlansUpdate(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name.en' => 'required|string|max:255',
            'type' => 'required|in:agent,real_estate_office',
            'final_price_usd' => 'required|numeric|min:0',
            'final_price_iqd' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['name', 'features']);

        // 1. Handle Name (JSON)
        $data['name'] = [
            'en' => $request->input('name.en'),
            'ar' => $request->input('name.ar'),
            'ku' => $request->input('name.ku'),
        ];

        // 2. Handle Features
        if ($request->filled('features')) {
            // Split by new line
            $features = preg_split("/\\r\\n|\\r|\\n/", $request->input('features'));
            $data['features'] = array_values(array_filter(array_map('trim', $features)));
        } else {
            $data['features'] = [];
        }

        // 3. Booleans
        $data['active'] = $request->has('active');
        $data['is_featured'] = $request->has('is_featured');

        $plan->update($data);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plan updated successfully!');
    }

    public function subscriptionPlansDelete($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        // Optional: Check if plan has active subscriptions before deleting
        if ($plan->subscriptions()->where('status', 'active')->count() > 0) {
            return back()->with('error', 'Cannot delete plan with active subscriptions. Deactivate it instead.');
        }

        $plan->delete();
        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plan deleted successfully!');
    }

    public function subscriptionPlansToggleActive($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['active' => !$plan->active]);
        return back()->with('success', 'Plan status updated!');
    }

    public function serviceProviderPlansDelete($id)
    {
        $plan = \App\Models\ServiceProviderPlan::findOrFail($id);
        $plan->delete();
        return back()->with('success', 'Plan deleted successfully');
    }



    // ==========================================
    // CATEGORIES (SERVICE PROVIDERS)
    // ==========================================

    public function categoriesIndex(Request $request)
    {
        $query = Category::orderBy('sort_order', 'asc');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function categoriesCreate()
    {
        return view('admin.categories.create');
    }

    public function categoriesStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = asset('storage/' . $path);
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
    }

    public function categoriesEdit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function categoriesUpdate(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            // Optional: Delete old image
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = asset('storage/' . $path);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
    }

    public function categoriesDelete($id)
    {
        $category = Category::findOrFail($id);

        // Prevent deleting if providers are attached
        if ($category->serviceProviders()->count() > 0) {
            return back()->with('error', 'Cannot delete category containing service providers.');
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully');
    }

    public function categoriesToggleActive($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        return back()->with('success', 'Category status updated');
    }


    /**
     * Activate (Verify) a user
     */
    public function usersActivate($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(), // Also mark email as verified
        ]);
        return back()->with('success', 'User verified and activated successfully!');
    }

    /**
     * Suspend (Unverify) a user
     */
    public function usersSuspend($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_verified' => false,
        ]);
        return back()->with('success', 'User suspended successfully!');
    }

    public function propertiesViewers($id)
    {
        $property = Property::with(['owner', 'interactions' => function ($query) {
            $query->where('interaction_type', 'impression')
                ->whereNotNull('user_id')
                ->with('user')
                ->latest();
        }])->findOrFail($id);

        $viewers = $property->interactions()
            ->where('interaction_type', 'impression')
            ->whereNotNull('user_id')
            ->with('user')
            ->latest()
            ->paginate(50);

        return view('admin.properties.viewers', compact('property', 'viewers'));
    }

    // In AdminController@propertiesCreate or wherever you handle property creation
    /**
     * Show property creation form
     */
    public function propertiesCreate(Request $request)
    {
        // Get office_id from query parameter
        $officeId = $request->query('office_id');
        $agentId = $request->query('agent_id');

        $office = null;
        $agent = null;
        $offices = null; // For dropdown
        $agents = null;  // For dropdown

        // Fetch office if ID provided
        if ($officeId) {
            $office = RealEstateOffice::find($officeId);

            // If office not found, redirect back with error
            if (!$office) {
                return redirect()->route('admin.offices.index')
                    ->with('error', 'Office not found.');
            }
        }

        // Fetch agent if ID provided
        if ($agentId) {
            $agent = Agent::find($agentId);

            // If agent not found, redirect back with error
            if (!$agent) {
                return redirect()->route('admin.agents.index')
                    ->with('error', 'Agent not found.');
            }
        }

        // Load all offices and agents for dropdowns (needed for admin create view)
        $offices = RealEstateOffice::where('is_verified', true)->get();
        $agents = Agent::all();

        // Return view with all necessary variables
        return view('admin.properties.create', compact('office', 'agent', 'offices', 'agents'));
    }

    /**
     * Store a newly created property in storage.
     */
    public function propertiesStore(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'name.en' => 'required|string|max:255',
            'description.en' => 'required|string',
            'price_usd' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0', // IQD
            'area' => 'required|numeric|min:1',
            'listing_type' => 'required|in:sale,sell,rent',
            'status' => 'required|in:available,pending,sold,rented,suspended',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',

            // Locations
            'locations.*.lat' => 'required|numeric|between:-90,90',
            'locations.*.lng' => 'required|numeric|between:-180,180',

            // Rooms
            'rooms.bedroom.count' => 'required|integer|min:0',
            'rooms.bathroom.count' => 'required|integer|min:0',
            'rooms.living_room.count' => 'nullable|integer|min:0',

            // Owner (Admin can assign to Agent or Office)
            'owner_type' => 'required|in:Agent,RealEstateOffice,Admin',
            'owner_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // 2. Handle Image Uploads
            $imageUrls = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('properties', 'public');
                    $imageUrls[] = asset('storage/' . $path);
                }
            }

            // 3. Map Listing Type (Database uses 'sell', form might send 'sale')
            $formListingType = $request->input('listing_type');
            $dbListingType = ($formListingType === 'sale') ? 'sell' : $formListingType;

            // 4. Resolve Owner Type Class
            $ownerTypeClass = match ($request->input('owner_type')) {
                'Agent' => 'App\\Models\\Agent',
                'RealEstateOffice' => 'App\\Models\\RealEstateOffice',
                default => 'App\\Models\\Admin', // Fallback or Admin ownership
            };

            // 5. Prepare Data Array
            $propertyData = [
                // Generate Custom ID
                'id' => $this->generateUniquePropertyId(),

                // Ownership
                'owner_type' => $ownerTypeClass,
                'owner_id' => $request->input('owner_id'),

                // JSON: Name & Description
                'name' => [
                    'en' => $request->input('name.en'),
                    'ar' => $request->input('name.ar'),
                    'ku' => $request->input('name.ku'),
                ],
                'description' => [
                    'en' => $request->input('description.en'),
                    'ar' => $request->input('description.ar'),
                    'ku' => $request->input('description.ku'),
                ],

                // JSON: Price (Dual Currency)
                'price' => [
                    'usd' => (float) $request->input('price_usd'),
                    'iqd' => (float) $request->input('price'),
                    'amount' => (float) $request->input('price_usd'), // Standard reference
                    'currency' => 'USD'
                ],

                // Basic Fields
                'listing_type' => $dbListingType,
                'status' => $request->input('status'),
                'area' => (float) $request->input('area'),
                'floor_number' => $request->input('floor_number'),
                'year_built' => $request->input('year_built'),
                'energy_rating' => $request->input('energy_rating'),
                'address' => $request->input('address'),
                'virtual_tour_url' => $request->input('virtual_tour_url'),
                'floor_plan_url' => $request->input('floor_plan_url'),
                'rental_period' => $request->input('rental_period'),

                // Booleans
                'furnished' => $request->has('furnished'),
                'electricity' => $request->has('electricity'),
                'water' => $request->has('water'),
                'internet' => $request->has('internet'),

                // Admin specific flags
                'is_active' => $request->has('is_active'),
                'published' => $request->has('published'),
                'verified' => $request->has('verified'),
                'is_boosted' => $request->has('is_boosted'),
                'boost_start_date' => $request->input('boost_start_date'),
                'boost_end_date' => $request->input('boost_end_date'),

                // JSON: Type
                'type' => [
                    'category' => $request->input('type.category')
                ],

                // JSON: Rooms
                'rooms' => [
                    'bedroom' => [
                        'count' => (int)$request->input('rooms.bedroom.count')
                    ],
                    'bathroom' => [
                        'count' => (int)$request->input('rooms.bathroom.count')
                    ],
                    'living_room' => [
                        'count' => (int)$request->input('rooms.living_room.count')
                    ],
                ],

                // JSON: Locations
                'locations' => [
                    [
                        'lat' => (float) $request->input('locations.0.lat'),
                        'lng' => (float) $request->input('locations.0.lng')
                    ]
                ],

                // JSON: Address Details
                'address_details' => [
                    'city' => ['en' => $request->input('address_details.city.en')],
                    'district' => ['en' => $request->input('address_details.district.en')],
                ],

                // Images array
                'images' => $imageUrls,

                // JSON: Availability
                'availability' => [
                    'from' => $request->input('availability.from'),
                    'to' => $request->input('availability.to'),
                    'status' => 'available'
                ],

                // JSON: Extra Details
                'construction_details' => [
                    'type' => $request->input('construction_details.type'),
                    'quality' => $request->input('construction_details.quality'),
                ],
                'energy_details' => [
                    'certificate' => $request->input('energy_details.certificate'),
                    'consumption' => $request->input('energy_details.consumption'),
                ],
                'furnishing_details' => [
                    'level' => $request->input('furnishing_details.level'),
                    'items' => array_values(array_filter(explode(',', $request->input('furnishing_details.items', '')))),
                ],
                'seo_metadata' => [
                    'title' => $request->input('seo_metadata.title'),
                    'description' => $request->input('seo_metadata.description'),
                    'keywords' => array_values(array_filter(explode(',', $request->input('seo_metadata.keywords', '')))),
                ],

                // Analytics Defaults
                'views' => 0,
                'favorites_count' => 0,
                'rating' => 0,
            ];

            // 6. Create Property
            Property::create($propertyData);

            DB::commit();

            return redirect()->route('admin.properties.index')->with('success', 'Property created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Property Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper to generate unique ID (If not already present in the class)
     */
    private function generateUniquePropertyId(): string
    {
        do {
            $propertyId = 'prop_' . date('Y_m_d') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Property::where('id', $propertyId)->exists());

        return $propertyId;
    }
}
