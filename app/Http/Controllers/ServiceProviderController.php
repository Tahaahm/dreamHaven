<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderGallery;
use App\Models\ServiceProviderOffering;
use App\Models\ServiceProviderReview;
use App\Models\ServiceProviderPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceProviderController extends Controller
{
    // =========================================================================
    // CATEGORIES METHODS
    // =========================================================================

    /**
     * Get all categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = Category::active()
            ->ordered()
            ->withCount('serviceProviders')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Create a new category
     */
    public function createCategory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Delete a category
     */
    public function deleteCategory($id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    // =========================================================================
    // SERVICE PROVIDERS METHODS (API)
    // =========================================================================

    /**
     * Get all service providers with filtering
     */
    public function getServiceProviders(Request $request): JsonResponse
    {
        $query = ServiceProvider::with(['category', 'galleries', 'activeOfferings', 'reviews']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('verified')) {
            $query->verified();
        }
        if ($request->filled('city')) {
            $query->byCity($request->city);
        }
        if ($request->filled('district')) {
            $query->byDistrict($request->district);
        }
        if ($request->filled('min_rating')) {
            $query->withMinRating($request->min_rating);
        }
        if ($request->filled(['latitude', 'longitude', 'radius'])) {
            $query->withinRadius(
                $request->latitude,
                $request->longitude,
                $request->radius
            );
        }
        if ($request->filled('search')) {
            $query->where('company_name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->get('per_page', 15);
        $serviceProviders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $serviceProviders
        ]);
    }

    /**
     * Get a single service provider with all details
     */
    public function getServiceProvider($id): JsonResponse
    {
        $serviceProvider = ServiceProvider::with([
            'category',
            'galleries',
            'activeOfferings',
            'plan',
            'reviews' => function ($query) {
                $query->latest()->take(10);
            }
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $serviceProvider
        ]);
    }

    /**
     * API - Create a new service provider
     */
    public function createServiceProvider(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'company_name' => 'required|string|max:255',
            'email_address' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['business_hours'] = $this->parseBusinessHours($request);

        $serviceProvider = ServiceProvider::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Service provider created successfully',
            'data' => $serviceProvider->load(['category', 'plan'])
        ], 201);
    }

    /**
     * Delete a service provider
     */
    public function deleteServiceProvider($id): JsonResponse
    {
        $serviceProvider = ServiceProvider::findOrFail($id);

        if ($serviceProvider->profile_image && !filter_var($serviceProvider->profile_image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $serviceProvider->profile_image));
        }

        $serviceProvider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service provider deleted successfully'
        ]);
    }

    // =========================================================================
    // ADMIN WEB FORM - STORE METHOD
    // =========================================================================

    /**
     * Handle the creation from Admin Blade form
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'email_address' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $request->except([
                '_token',
                'profile_image',
                'is_verified',
                'hours_open',
                'hours_close',
                'hours_closed',
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
                'reviewer_service_types'
            ]);

            $data['is_verified'] = $request->has('is_verified');

            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('providers/profiles', 'public');
                $data['profile_image'] = '/storage/' . $path;
            }

            // Create initial record
            $provider = ServiceProvider::create($data);

            // Explicitly handle and save business hours JSON
            $provider->business_hours = $this->parseBusinessHours($request);
            $provider->save();

            // Handle Gallery
            if ($request->hasFile('gallery_images')) {
                $this->storeGallery($request, $provider);
            }

            // Handle Offerings & Reviews
            $this->syncOfferings($request, $provider);
            $this->syncReviews($request, $provider);

            // Calculate Rating
            $this->updateAverageRating($provider->id);

            DB::commit();
            return redirect()->route('admin.service-providers.index')->with('success', 'Service Provider created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // =========================================================================
    // ADMIN WEB FORM - UPDATE METHOD
    // =========================================================================

    /**
     * Handle update from Admin Blade form
     */
    public function update(Request $request, $id)
    {
        $provider = ServiceProvider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'email_address' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $data = $request->except([
                '_token',
                '_method',
                'profile_image',
                'is_verified',
                'hours_open',
                'hours_close',
                'hours_closed',
                'gallery_existing_images',
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
                'reviewer_service_types'
            ]);

            $data['is_verified'] = $request->has('is_verified');

            // Handle Profile Image Replacement
            if ($request->hasFile('profile_image')) {
                if ($provider->profile_image && !filter_var($provider->profile_image, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $provider->profile_image));
                }
                $path = $request->file('profile_image')->store('providers/profiles', 'public');
                $data['profile_image'] = '/storage/' . $path;
            }

            // Update main data
            $provider->fill($data);
            $provider->business_hours = $this->parseBusinessHours($request);
            $provider->save();

            // Sync Relationships
            $this->syncGallery($request, $provider);
            $this->syncOfferings($request, $provider);
            $this->syncReviews($request, $provider);

            // Update Rating
            $this->updateAverageRating($provider->id);

            DB::commit();
            return redirect()->route('admin.service-providers.index')->with('success', 'Service Provider updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    // =========================================================================
    // PRIVATE HELPERS (Logic & Sync)
    // =========================================================================

    /**
     * Parse Business Hours with Default Fallback
     */
    private function parseBusinessHours(Request $request): array
    {
        if ($request->has('business_hours') && is_array($request->business_hours)) {
            return $request->business_hours;
        }

        $hoursOpen = $request->input('hours_open', []);
        $hoursClose = $request->input('hours_close', []);
        $hoursClosed = $request->input('hours_closed', []);

        if (!empty($hoursOpen) || !empty($hoursClose) || !empty($hoursClosed)) {
            $businessHours = [];
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

            foreach ($days as $day) {
                if (isset($hoursClosed[$day]) && $hoursClosed[$day] == "1") {
                    $businessHours[$day] = ['closed' => true];
                } else {
                    $businessHours[$day] = [
                        'open' => $hoursOpen[$day] ?? '08:00',
                        'close' => $hoursClose[$day] ?? '17:00',
                    ];
                }
            }
            return $businessHours;
        }

        // Default Working Hours
        return [
            "sunday" => ["open" => "08:00", "close" => "17:00"],
            "monday" => ["open" => "08:00", "close" => "17:00"],
            "tuesday" => ["open" => "08:00", "close" => "17:00"],
            "wednesday" => ["open" => "08:00", "close" => "17:00"],
            "thursday" => ["open" => "08:00", "close" => "17:00"],
            "friday" => ["closed" => true],
            "saturday" => ["open" => "09:00", "close" => "14:00"]
        ];
    }

    /**
     * Store Gallery for new record
     */
    private function storeGallery(Request $request, ServiceProvider $provider)
    {
        $files = $request->file('gallery_images', []);
        $titles = $request->input('gallery_titles', []);
        $descs = $request->input('gallery_descriptions', []);

        foreach ($files as $index => $file) {
            if ($file) {
                $path = $file->store('providers/gallery', 'public');
                ServiceProviderGallery::create([
                    'service_provider_id' => $provider->id,
                    'image_url' => '/storage/' . $path,
                    'project_title' => $titles[$index] ?? null,
                    'description' => $descs[$index] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    /**
     * Sync Gallery for existing record
     */
    private function syncGallery(Request $request, ServiceProvider $provider)
    {
        $existingDbImages = $provider->galleries()->pluck('image_url')->toArray();
        $submittedExistingImages = $request->input('gallery_existing_images', []);

        // Delete removed files
        $imagesToDelete = array_diff($existingDbImages, $submittedExistingImages);
        foreach ($imagesToDelete as $oldImage) {
            if (!filter_var($oldImage, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }
        }

        $provider->galleries()->delete();
        $titles = $request->input('gallery_titles', []);
        $descs = $request->input('gallery_descriptions', []);
        $files = $request->file('gallery_images', []);

        foreach ($titles as $index => $title) {
            $url = $submittedExistingImages[$index] ?? null;
            if (isset($files[$index])) {
                $path = $files[$index]->store('providers/gallery', 'public');
                $url = '/storage/' . $path;
            }
            if ($url) {
                ServiceProviderGallery::create([
                    'service_provider_id' => $provider->id,
                    'image_url' => $url,
                    'project_title' => $title,
                    'description' => $descs[$index] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    /**
     * Sync Offerings
     */
    private function syncOfferings(Request $request, ServiceProvider $provider)
    {
        $provider->offerings()->delete();
        $titles = $request->input('offering_titles', []);
        $descs = $request->input('offering_descriptions', []);
        $prices = $request->input('offering_prices', []);

        foreach ($titles as $index => $title) {
            if (!empty($title)) {
                ServiceProviderOffering::create([
                    'service_provider_id' => $provider->id,
                    'service_title' => $title,
                    'service_description' => $descs[$index] ?? null,
                    'price_range' => $prices[$index] ?? null,
                    'active' => true,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    /**
     * Sync Reviews
     */
    private function syncReviews(Request $request, ServiceProvider $provider)
    {
        $provider->reviews()->delete();
        $names = $request->input('reviewer_names', []);
        $ratings = $request->input('reviewer_ratings', []);
        $contents = $request->input('reviewer_contents', []);
        $types = $request->input('reviewer_service_types', []);

        foreach ($names as $index => $name) {
            if (!empty($name)) {
                ServiceProviderReview::create([
                    'service_provider_id' => $provider->id,
                    'reviewer_name' => $name,
                    'star_rating' => $ratings[$index] ?? 5,
                    'review_content' => $contents[$index] ?? null,
                    'service_type' => $types[$index] ?? null,
                    'review_date' => now()->toDateString(),
                    'is_verified' => true,
                ]);
            }
        }
    }

    /**
     * Update Avg Rating
     */
    private function updateAverageRating($serviceProviderId): void
    {
        $avg = ServiceProviderReview::where('service_provider_id', $serviceProviderId)->avg('star_rating');
        ServiceProvider::where('id', $serviceProviderId)->update(['average_rating' => round((float)$avg, 2)]);
    }

    // =========================================================================
    // ADDITIONAL API ENDPOINTS
    // =========================================================================

    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_categories' => Category::count(),
            'active_categories' => Category::active()->count(),
            'total_service_providers' => ServiceProvider::count(),
            'verified_service_providers' => ServiceProvider::verified()->count(),
            'total_reviews' => ServiceProviderReview::count(),
            'average_rating' => round(ServiceProviderReview::avg('star_rating') ?? 0, 2),
        ];
        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function getProvidersByLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $radius = $request->get('radius', 10);
        $query = ServiceProvider::with(['category', 'activeOfferings'])
            ->withinRadius($request->latitude, $request->longitude, $radius);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return response()->json(['success' => true, 'data' => $query->take(20)->get()]);
    }

    public function getPlanStatus($serviceProviderId): JsonResponse
    {
        $serviceProvider = ServiceProvider::with('plan')->findOrFail($serviceProviderId);
        return response()->json([
            'success' => true,
            'data' => [
                'has_active_plan' => $serviceProvider->hasActivePlan(),
                'remaining_days' => $serviceProvider->remainingPlanDays(),
                'plan' => $serviceProvider->plan,
            ]
        ]);
    }
}
