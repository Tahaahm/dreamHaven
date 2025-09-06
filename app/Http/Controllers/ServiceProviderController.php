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
use Illuminate\Support\Str;

class ServiceProviderController extends Controller
{
    // CATEGORIES METHODS

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

    // SERVICE PROVIDERS METHODS

    /**
     * Get all service providers with filtering
     */
    public function getServiceProviders(Request $request): JsonResponse
    {
        $query = ServiceProvider::with(['category', 'galleries', 'activeOfferings', 'reviews']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by verified status
        if ($request->filled('verified')) {
            $query->verified();
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        // Filter by district
        if ($request->filled('district')) {
            $query->byDistrict($request->district);
        }

        // Filter by minimum rating
        if ($request->filled('min_rating')) {
            $query->withMinRating($request->min_rating);
        }

        // Location-based search
        if ($request->filled(['latitude', 'longitude', 'radius'])) {
            $query->withinRadius(
                $request->latitude,
                $request->longitude,
                $request->radius
            );
        }

        // Search by company name
        if ($request->filled('search')) {
            $query->where('company_name', 'like', '%' . $request->search . '%');
        }

        // Pagination
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
     * Create a new service provider
     */
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
            'business_hours' => 'nullable|array',
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

        $serviceProvider = ServiceProvider::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Service provider created successfully',
            'data' => $serviceProvider->load(['category', 'plan'])
        ], 201);
    }

    /**
     * Update a service provider
     */
    public function updateServiceProvider(Request $request, $id): JsonResponse
    {
        $serviceProvider = ServiceProvider::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'company_name' => 'sometimes|string|max:255',
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
            'business_hours' => 'nullable|array',
            'company_overview' => 'nullable|string',
            'is_verified' => 'nullable|boolean',
            'plan_id' => 'nullable|exists:service_provider_plans,id',
            'plan_active' => 'nullable|boolean',
            'plan_expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceProvider->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Service provider updated successfully',
            'data' => $serviceProvider->load(['category', 'plan'])
        ]);
    }

    /**
     * Delete a service provider
     */
    public function deleteServiceProvider($id): JsonResponse
    {
        $serviceProvider = ServiceProvider::findOrFail($id);
        $serviceProvider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service provider deleted successfully'
        ]);
    }

    // GALLERY METHODS

    /**
     * Add gallery images to service provider
     */
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
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceProvider = ServiceProvider::findOrFail($serviceProviderId);
        $galleryImages = [];

        foreach ($request->images as $imageData) {
            $imageData['service_provider_id'] = $serviceProviderId;
            $galleryImages[] = ServiceProviderGallery::create($imageData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Gallery images added successfully',
            'data' => $galleryImages
        ], 201);
    }

    /**
     * Update gallery image
     */
    public function updateGalleryImage(Request $request, $imageId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image_url' => 'sometimes|string',
            'description' => 'nullable|string',
            'project_title' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $galleryImage = ServiceProviderGallery::findOrFail($imageId);
        $galleryImage->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Gallery image updated successfully',
            'data' => $galleryImage
        ]);
    }

    /**
     * Delete gallery image
     */
    public function deleteGalleryImage($imageId): JsonResponse
    {
        $galleryImage = ServiceProviderGallery::findOrFail($imageId);
        $galleryImage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery image deleted successfully'
        ]);
    }

    // OFFERINGS METHODS

    /**
     * Add service offering
     */
    public function addOffering(Request $request, $serviceProviderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_title' => 'required|string|max:255',
            'service_description' => 'nullable|string',
            'price_range' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceProvider = ServiceProvider::findOrFail($serviceProviderId);

        $offeringData = $request->all();
        $offeringData['service_provider_id'] = $serviceProviderId;
        $offeringData['active'] = $offeringData['active'] ?? true;

        $offering = ServiceProviderOffering::create($offeringData);

        return response()->json([
            'success' => true,
            'message' => 'Service offering added successfully',
            'data' => $offering
        ], 201);
    }

    /**
     * Update service offering
     */
    public function updateOffering(Request $request, $offeringId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_title' => 'sometimes|string|max:255',
            'service_description' => 'nullable|string',
            'price_range' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $offering = ServiceProviderOffering::findOrFail($offeringId);
        $offering->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Service offering updated successfully',
            'data' => $offering
        ]);
    }

    /**
     * Delete service offering
     */
    public function deleteOffering($offeringId): JsonResponse
    {
        $offering = ServiceProviderOffering::findOrFail($offeringId);
        $offering->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service offering deleted successfully'
        ]);
    }

    // REVIEWS METHODS

    /**
     * Add review for service provider
     */
    public function addReview(Request $request, $serviceProviderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reviewer_name' => 'required|string|max:255',
            'reviewer_avatar' => 'nullable|string',
            'star_rating' => 'required|integer|between:1,5',
            'review_content' => 'nullable|string',
            'service_type' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceProvider = ServiceProvider::findOrFail($serviceProviderId);

        $reviewData = $request->all();
        $reviewData['service_provider_id'] = $serviceProviderId;
        $reviewData['review_date'] = now()->toDateString();

        $review = ServiceProviderReview::create($reviewData);

        // Update average rating
        $this->updateAverageRating($serviceProviderId);

        return response()->json([
            'success' => true,
            'message' => 'Review added successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Get reviews for service provider
     */
    public function getReviews($serviceProviderId): JsonResponse
    {
        $reviews = ServiceProviderReview::where('service_provider_id', $serviceProviderId)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Update review verification status
     */
    public function updateReviewStatus(Request $request, $reviewId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_verified' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = ServiceProviderReview::findOrFail($reviewId);
        $review->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Review status updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Delete review
     */
    public function deleteReview($reviewId): JsonResponse
    {
        $review = ServiceProviderReview::findOrFail($reviewId);
        $serviceProviderId = $review->service_provider_id;
        $review->delete();

        // Update average rating after deleting review
        $this->updateAverageRating($serviceProviderId);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    // PLAN MANAGEMENT METHODS

    /**
     * Assign plan to service provider
     */
    public function assignPlan(Request $request, $serviceProviderId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:service_provider_plans,id',
            'duration_months' => 'required|integer|min:1|max:12',
            'start_immediately' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $serviceProvider = ServiceProvider::findOrFail($serviceProviderId);

        $startDate = $request->start_immediately ? now() : now()->addDay();
        $expirationDate = $startDate->copy()->addMonths($request->duration_months);

        $serviceProvider->update([
            'plan_id' => $request->plan_id,
            'plan_active' => true,
            'plan_expires_at' => $expirationDate,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan assigned successfully',
            'data' => $serviceProvider->load('plan')
        ]);
    }

    /**
     * Cancel service provider plan
     */
    public function cancelPlan($serviceProviderId): JsonResponse
    {
        $serviceProvider = ServiceProvider::findOrFail($serviceProviderId);

        $serviceProvider->update([
            'plan_active' => false,
            'plan_expires_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan cancelled successfully',
            'data' => $serviceProvider->load('plan')
        ]);
    }

    /**
     * Get service provider plan status
     */
    public function getPlanStatus($serviceProviderId): JsonResponse
    {
        $serviceProvider = ServiceProvider::with('plan')->findOrFail($serviceProviderId);

        $planStatus = [
            'has_active_plan' => $serviceProvider->hasActivePlan(),
            'remaining_days' => $serviceProvider->remainingPlanDays(),
            'plan' => $serviceProvider->plan,
            'plan_expires_at' => $serviceProvider->plan_expires_at,
            'advertisement_slots' => $serviceProvider->getAdvertisementSlots(),
            'banner_allowance' => $serviceProvider->getBannerAllowance(),
        ];

        return response()->json([
            'success' => true,
            'data' => $planStatus
        ]);
    }

    // HELPER METHODS

    /**
     * Update average rating for service provider
     */
    private function updateAverageRating($serviceProviderId): void
    {
        $averageRating = ServiceProviderReview::where('service_provider_id', $serviceProviderId)
            ->avg('star_rating');

        ServiceProvider::where('id', $serviceProviderId)
            ->update(['average_rating' => round($averageRating, 2)]);
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_categories' => Category::count(),
            'active_categories' => Category::active()->count(),
            'total_service_providers' => ServiceProvider::count(),
            'verified_service_providers' => ServiceProvider::verified()->count(),
            'providers_with_active_plans' => ServiceProvider::withActivePlan()->count(),
            'total_plans' => DB::table('service_provider_plans')->count(),
            'active_plans' => DB::table('service_provider_plans')->where('active', true)->count(),
            'total_reviews' => ServiceProviderReview::count(),
            'average_rating' => ServiceProviderReview::avg('star_rating'),
            'plan_distribution' => ServiceProvider::select('plan_id', DB::raw('count(*) as count'))
                ->whereNotNull('plan_id')
                ->groupBy('plan_id')
                ->with('plan:id,name')
                ->get(),
            'revenue_this_month' => $this->calculateMonthlyRevenue(),
            'new_providers_this_month' => ServiceProvider::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Calculate monthly revenue from active plans
     */
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

    /**
     * Get service providers by location
     */
    public function getProvidersByLocation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100', // max 100km radius
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $radius = $request->get('radius', 10); // default 10km

        $query = ServiceProvider::with(['category', 'activeOfferings'])
            ->withinRadius($request->latitude, $request->longitude, $radius);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $providers = $query->take(20)->get();

        return response()->json([
            'success' => true,
            'data' => $providers
        ]);
    }
}