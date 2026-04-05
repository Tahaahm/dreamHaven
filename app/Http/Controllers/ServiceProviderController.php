<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderGallery;
use App\Models\ServiceProviderOffering;
use App\Models\ServiceProviderReview;
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

    public function createServiceProvider(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'company_name' => 'required|string|max:255',
            'company_bio' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'city' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'business_description' => 'nullable|string',
            'years_in_business' => 'nullable|integer|min:0',
            'phone_number' => 'nullable|string|max:20',
            'email_address' => 'nullable|email|max:255',
            'website_url' => 'nullable|url|max:255',
            'company_overview' => 'nullable|string',
            'plan_id' => 'nullable|exists:service_provider_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // Automatically parse or assign default business hours
        $data['business_hours'] = $this->parseBusinessHours($request);

        $serviceProvider = ServiceProvider::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Service provider created successfully',
            'data' => $serviceProvider->load(['category', 'plan'])
        ], 201);
    }

    public function deleteServiceProvider($id): JsonResponse
    {
        $serviceProvider = ServiceProvider::findOrFail($id);

        // Clean up profile image if it's stored locally
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
    // ADMIN WEB FORM UPDATE METHOD (Handles the complex blade form)
    // =========================================================================

    public function update(Request $request, $id)
    {
        $provider = ServiceProvider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'email_address' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // If it's an API request, return JSON
        if ($request->expectsJson()) {
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
        } else {
            // Web form request
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // 1. Prepare Basic Data (excluding dynamic arrays and files)
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
                'reviewer_service_types',
                'reviewer_verified',
                'reviewer_featured'
            ]);

            // Handle Verified Checkbox
            $data['is_verified'] = $request->has('is_verified');

            // 2. Handle Profile Image Upload
            if ($request->hasFile('profile_image')) {
                if ($provider->profile_image && !filter_var($provider->profile_image, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $provider->profile_image));
                }
                $path = $request->file('profile_image')->store('providers/profiles', 'public');
                $data['profile_image'] = '/storage/' . $path;
            }

            // 3. Parse Business Hours (with Fallback)
            $data['business_hours'] = $this->parseBusinessHours($request);

            // Update Main Provider Record
            $provider->update($data);

            // 4. Synchronize Dynamic Relationships if arrays are present
            if ($request->has('gallery_titles') || $request->has('gallery_existing_images')) {
                $this->syncGallery($request, $provider);
            }
            if ($request->has('offering_titles')) {
                $this->syncOfferings($request, $provider);
            }
            if ($request->has('reviewer_names')) {
                $this->syncReviews($request, $provider);
            }

            // Update Average Rating
            $this->updateAverageRating($provider->id);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Updated successfully', 'data' => $provider->fresh()]);
            }
            return redirect()->route('admin.service-providers.index')->with('success', 'Service Provider updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    // =========================================================================
    // BUSINESS HOURS & SYNC HELPERS
    // =========================================================================

    /**
     * Parses the business hours from the form, falling back to a default
     * schedule if nothing is provided.
     */
    private function parseBusinessHours(Request $request): array
    {
        // 1. If it's a direct API request with the proper JSON array already
        if ($request->has('business_hours') && is_array($request->business_hours)) {
            return $request->business_hours;
        }

        // 2. If it's coming from the Blade Form (combining the separate arrays)
        if ($request->has('hours_open') || $request->has('hours_close') || $request->has('hours_closed')) {
            $businessHours = [];
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

            foreach ($days as $day) {
                if ($request->has("hours_closed.{$day}")) {
                    $businessHours[$day] = ['closed' => true];
                } else {
                    $businessHours[$day] = [
                        'open' => $request->input("hours_open.{$day}", '08:00'),
                        'close' => $request->input("hours_close.{$day}", '17:00'),
                    ];
                }
            }
            return $businessHours;
        }

        // 3. Absolute Default Fallback (if no data was sent at all)
        return [
            "sunday"    => ["open" => "08:00", "close" => "17:00"],
            "monday"    => ["open" => "08:00", "close" => "17:00"],
            "tuesday"   => ["open" => "08:00", "close" => "17:00"],
            "wednesday" => ["open" => "08:00", "close" => "17:00"],
            "thursday"  => ["open" => "08:00", "close" => "17:00"],
            "friday"    => ["closed" => true],
            "saturday"  => ["open" => "09:00", "close" => "14:00"]
        ];
    }

    private function syncGallery(Request $request, ServiceProvider $provider)
    {
        $existingDbImages = $provider->galleries()->pluck('image_url')->toArray();
        $submittedExistingImages = $request->input('gallery_existing_images', []);

        $imagesToDelete = array_diff($existingDbImages, $submittedExistingImages);
        foreach ($imagesToDelete as $oldImage) {
            if (!filter_var($oldImage, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldImage));
            }
        }

        $provider->galleries()->delete();

        $titles = $request->input('gallery_titles', []);
        $descriptions = $request->input('gallery_descriptions', []);
        $files = $request->file('gallery_images', []);

        foreach ($titles as $index => $title) {
            $imageUrl = null;

            if (isset($submittedExistingImages[$index]) && !empty($submittedExistingImages[$index])) {
                $imageUrl = $submittedExistingImages[$index];
            } elseif (isset($files[$index])) {
                $path = $files[$index]->store('providers/gallery', 'public');
                $imageUrl = '/storage/' . $path;
            }

            if ($imageUrl) {
                ServiceProviderGallery::create([
                    'service_provider_id' => $provider->id,
                    'image_url' => $imageUrl,
                    'project_title' => $title,
                    'description' => $descriptions[$index] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }
    }

    private function syncOfferings(Request $request, ServiceProvider $provider)
    {
        $provider->offerings()->delete();

        $titles = $request->input('offering_titles', []);
        $descriptions = $request->input('offering_descriptions', []);
        $prices = $request->input('offering_prices', []);

        foreach ($titles as $index => $title) {
            if (!empty($title)) {
                ServiceProviderOffering::create([
                    'service_provider_id' => $provider->id,
                    'service_title' => $title,
                    'service_description' => $descriptions[$index] ?? null,
                    'price_range' => $prices[$index] ?? null,
                    'active' => true, // Default to true if submitted by admin form
                    'sort_order' => $index,
                ]);
            }
        }
    }

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
                    'is_featured' => false,
                ]);
            }
        }
    }

    // =========================================================================
    // GALLERY, OFFERING, REVIEWS API METHODS
    // =========================================================================

    public function addGalleryImages(Request $request, $serviceProviderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*.image_url' => 'required|string',
            'images.*.description' => 'nullable|string',
            'images.*.project_title' => 'nullable|string',
            'images.*.sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $galleryImages = [];
        foreach ($request->images as $imageData) {
            $imageData['service_provider_id'] = $serviceProviderId;
            $galleryImages[] = ServiceProviderGallery::create($imageData);
        }

        return response()->json(['success' => true, 'data' => $galleryImages], 201);
    }

    public function updateGalleryImage(Request $request, $imageId): JsonResponse
    {
        $galleryImage = ServiceProviderGallery::findOrFail($imageId);
        $galleryImage->update($request->all());
        return response()->json(['success' => true, 'data' => $galleryImage]);
    }

    public function deleteGalleryImage($imageId): JsonResponse
    {
        $galleryImage = ServiceProviderGallery::findOrFail($imageId);
        $galleryImage->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function addOffering(Request $request, $serviceProviderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_title' => 'required|string|max:255',
            'service_description' => 'nullable|string',
            'price_range' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $offeringData = $request->all();
        $offeringData['service_provider_id'] = $serviceProviderId;
        $offeringData['active'] = $offeringData['active'] ?? true;

        $offering = ServiceProviderOffering::create($offeringData);
        return response()->json(['success' => true, 'data' => $offering], 201);
    }

    public function updateOffering(Request $request, $offeringId): JsonResponse
    {
        $offering = ServiceProviderOffering::findOrFail($offeringId);
        $offering->update($request->all());
        return response()->json(['success' => true, 'data' => $offering]);
    }

    public function deleteOffering($offeringId): JsonResponse
    {
        $offering = ServiceProviderOffering::findOrFail($offeringId);
        $offering->delete();
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function addReview(Request $request, $serviceProviderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reviewer_name' => 'required|string|max:255',
            'star_rating' => 'required|integer|between:1,5',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $reviewData = $request->all();
        $reviewData['service_provider_id'] = $serviceProviderId;
        $reviewData['review_date'] = now()->toDateString();

        $review = ServiceProviderReview::create($reviewData);
        $this->updateAverageRating($serviceProviderId);

        return response()->json(['success' => true, 'data' => $review], 201);
    }

    public function getReviews($serviceProviderId): JsonResponse
    {
        $reviews = ServiceProviderReview::where('service_provider_id', $serviceProviderId)
            ->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $reviews]);
    }

    public function updateReviewStatus(Request $request, $reviewId): JsonResponse
    {
        $review = ServiceProviderReview::findOrFail($reviewId);
        $review->update($request->all());
        return response()->json(['success' => true, 'data' => $review]);
    }

    public function deleteReview($reviewId): JsonResponse
    {
        $review = ServiceProviderReview::findOrFail($reviewId);
        $serviceProviderId = $review->service_provider_id;
        $review->delete();
        $this->updateAverageRating($serviceProviderId);
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    // =========================================================================
    // PLAN MANAGEMENT & STATS
    // =========================================================================

    public function assignPlan(Request $request, $serviceProviderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:service_provider_plans,id',
            'duration_months' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $serviceProvider = ServiceProvider::findOrFail($serviceProviderId);
        $startDate = $request->start_immediately ? now() : now()->addDay();
        $expirationDate = $startDate->copy()->addMonths($request->duration_months);

        $serviceProvider->update([
            'plan_id' => $request->plan_id,
            'plan_active' => true,
            'plan_expires_at' => $expirationDate,
        ]);

        return response()->json(['success' => true, 'data' => $serviceProvider->load('plan')]);
    }

    public function cancelPlan($serviceProviderId): JsonResponse
    {
        $serviceProvider = ServiceProvider::findOrFail($serviceProviderId);
        $serviceProvider->update([
            'plan_active' => false,
            'plan_expires_at' => now(),
        ]);
        return response()->json(['success' => true, 'data' => $serviceProvider->load('plan')]);
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
                'plan_expires_at' => $serviceProvider->plan_expires_at,
            ]
        ]);
    }

    private function updateAverageRating($serviceProviderId): void
    {
        $averageRating = ServiceProviderReview::where('service_provider_id', $serviceProviderId)
            ->avg('star_rating');

        ServiceProvider::where('id', $serviceProviderId)
            ->update(['average_rating' => round((float) $averageRating, 2)]);
    }

    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_categories' => Category::count(),
            'active_categories' => Category::active()->count(),
            'total_service_providers' => ServiceProvider::count(),
            'verified_service_providers' => ServiceProvider::verified()->count(),
            'providers_with_active_plans' => ServiceProvider::withActivePlan()->count(),
            'total_reviews' => ServiceProviderReview::count(),
            'average_rating' => ServiceProviderReview::avg('star_rating'),
            'revenue_this_month' => $this->calculateMonthlyRevenue(),
            'new_providers_this_month' => ServiceProvider::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    private function calculateMonthlyRevenue(): float
    {
        $activeProviders = ServiceProvider::withActivePlan()->with('plan')->get();
        $totalRevenue = 0;
        foreach ($activeProviders as $provider) {
            if ($provider->plan) {
                $totalRevenue += $provider->plan->monthly_price;
            }
        }
        return round($totalRevenue, 2);
    }

    public function getProvidersByLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
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

    // =========================================================================
    // ADMIN WEB FORM STORE METHOD (Handles creating a new provider)
    // =========================================================================

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

        if ($request->expectsJson()) {
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
        } else {
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // 1. Prepare Basic Data (excluding dynamic arrays and files)
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
                'reviewer_service_types',
                'reviewer_verified',
                'reviewer_featured'
            ]);

            // Handle Verified Checkbox
            $data['is_verified'] = $request->has('is_verified');

            // 2. Handle Profile Image Upload
            if ($request->hasFile('profile_image')) {
                $path = $request->file('profile_image')->store('providers/profiles', 'public');
                $data['profile_image'] = '/storage/' . $path;
            }

            // 3. Parse Business Hours (with Fallback)
            $data['business_hours'] = $this->parseBusinessHours($request);

            // Create Main Provider Record
            $provider = ServiceProvider::create($data);

            // 4. Insert Dynamic Relationships if arrays are present
            if ($request->hasFile('gallery_images')) {
                $this->storeGallery($request, $provider);
            }
            if ($request->has('offering_titles')) {
                $this->syncOfferings($request, $provider); // Reusing sync method as it deletes and inserts
            }
            if ($request->has('reviewer_names')) {
                $this->syncReviews($request, $provider); // Reusing sync method as it deletes and inserts
            }

            // Update Average Rating
            $this->updateAverageRating($provider->id);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Created successfully', 'data' => $provider->fresh()]);
            }
            return redirect()->route('admin.service-providers.index')->with('success', 'Service Provider created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Specialized method for Storing Gallery on Creation (Doesn't need to check existing images)
     */
    private function storeGallery(Request $request, ServiceProvider $provider)
    {
        $titles = $request->input('gallery_titles', []);
        $descriptions = $request->input('gallery_descriptions', []);
        $files = $request->file('gallery_images', []);

        foreach ($files as $index => $file) {
            $path = $file->store('providers/gallery', 'public');
            $imageUrl = '/storage/' . $path;

            ServiceProviderGallery::create([
                'service_provider_id' => $provider->id,
                'image_url' => $imageUrl,
                'project_title' => $titles[$index] ?? null,
                'description' => $descriptions[$index] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}
