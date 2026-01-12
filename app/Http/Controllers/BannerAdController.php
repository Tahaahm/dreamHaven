<?php

namespace App\Http\Controllers;

use App\Models\BannerAd;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BannerAdController extends Controller
{
    /**
     * Display a listing of banner ads
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = BannerAd::query();

            // Apply filters
            if ($request->filled('owner_type')) {
                $query->where('owner_type', $request->owner_type);
            }

            if ($request->filled('owner_id')) {
                $query->where('owner_id', $request->owner_id);
            }

            if ($request->filled('banner_type')) {
                $query->byType($request->banner_type);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('position')) {
                $query->byPosition($request->position);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('is_featured')) {
                $query->where('is_featured', $request->boolean('is_featured'));
            }

            if ($request->filled('search')) {
                $query->whereFullText(['title', 'description', 'owner_name'], $request->search);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($sortBy === 'priority') {
                $query->orderByPriority();
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $banners = $query->with(['property', 'approver'])->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $banners,
                'message' => 'Banner ads retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banner ads: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created banner ad
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                // Required fields
                'title' => 'required|string|min:5|max:255',
                'owner_type' => 'required|in:real_estate,agent',
                'owner_id' => 'required|uuid',
                'owner_name' => 'required|string|max:255',
                'banner_type' => 'required|in:property_listing,agent_profile,agency_branding,service_promotion,event_announcement,general_marketing',
                'start_date' => 'required|date|after_or_equal:today',

                // Optional fields
                'description' => 'nullable|string|max:1000',
                'image_url' => 'nullable|url|max:500',
                'image_alt' => 'nullable|string|max:255',
                'link_url' => 'nullable|url|max:500',
                'link_opens_new_tab' => 'boolean',
                'owner_email' => 'nullable|email|max:255',
                'owner_phone' => 'nullable|string|max:20',
                'owner_logo' => 'nullable|url|max:500',
                'property_id' => 'nullable|uuid|exists:properties,id',
                'property_price' => 'nullable|numeric|min:0',
                'property_address' => 'nullable|string|max:500',
                'banner_size' => 'nullable|in:banner,leaderboard,rectangle,sidebar,mobile,custom',
                'custom_dimensions' => 'nullable|json',
                'position' => 'nullable|in:header,sidebar_top,sidebar_bottom,content_top,content_middle,content_bottom,footer,popup,floating',
                'target_locations' => 'nullable|json',
                'target_property_types' => 'nullable|json',
                'target_price_range' => 'nullable|json',
                'target_pages' => 'nullable|json',
                'end_date' => 'nullable|date|after:start_date',
                'is_active' => 'boolean',
                'display_priority' => 'nullable|integer|min:0|max:100',
                'billing_type' => 'nullable|in:free,fixed,per_click,per_impression',
                'budget_total' => 'nullable|numeric|min:0',
                'cost_per_click' => 'nullable|numeric|min:0',
                'cost_per_impression' => 'nullable|numeric|min:0',
                'call_to_action' => 'nullable|string|max:50',
                'additional_images' => 'nullable|json',
                'terms_conditions' => 'nullable|string',
                'show_contact_info' => 'boolean',
                'social_links' => 'nullable|json',
                'metadata' => 'nullable|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Convert title, description, and call_to_action to JSON for multi-language support
            $data = $validated;
            $data['title'] = json_encode([
                'en' => $validated['title'],
                'ar' => $validated['title'],
                'ku' => $validated['title']
            ]);

            if (isset($validated['description']) && $validated['description']) {
                $data['description'] = json_encode([
                    'en' => $validated['description'],
                    'ar' => $validated['description'],
                    'ku' => $validated['description']
                ]);
            }

            if (isset($validated['call_to_action']) && $validated['call_to_action']) {
                $data['call_to_action'] = json_encode([
                    'en' => $validated['call_to_action'],
                    'ar' => $validated['call_to_action'],
                    'ku' => $validated['call_to_action']
                ]);
            }

            // Set additional fields
            $data['created_by_ip'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['status'] = 'draft'; // All new banners start as draft

            $bannerAd = BannerAd::create($data);

            return response()->json([
                'success' => true,
                'data' => $bannerAd->load(['property', 'approver']),
                'message' => 'Banner ad created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating banner ad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified banner ad
     */
    public function show(string $id): JsonResponse
    {
        try {
            $bannerAd = BannerAd::with(['property', 'approver'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $bannerAd,
                'message' => 'Banner ad retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Banner ad not found'
            ], 404);
        }
    }

    /**
     * Update the specified banner ad
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $bannerAd = BannerAd::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|min:5|max:255',
                'owner_type' => 'sometimes|required|in:real_estate,agent',
                'owner_id' => 'sometimes|required|uuid',
                'owner_name' => 'sometimes|required|string|max:255',
                'banner_type' => 'sometimes|required|in:property_listing,agent_profile,agency_branding,service_promotion,event_announcement,general_marketing',
                'start_date' => 'sometimes|required|date',
                'description' => 'nullable|string|max:1000',
                'image_url' => 'nullable|url|max:500',
                'image_alt' => 'nullable|string|max:255',
                'link_url' => 'nullable|url|max:500',
                'link_opens_new_tab' => 'boolean',
                'owner_email' => 'nullable|email|max:255',
                'owner_phone' => 'nullable|string|max:20',
                'owner_logo' => 'nullable|url|max:500',
                'property_id' => 'nullable|uuid|exists:properties,id',
                'property_price' => 'nullable|numeric|min:0',
                'property_address' => 'nullable|string|max:500',
                'banner_size' => 'nullable|in:banner,leaderboard,rectangle,sidebar,mobile,custom',
                'custom_dimensions' => 'nullable|json',
                'position' => 'nullable|in:header,sidebar_top,sidebar_bottom,content_top,content_middle,content_bottom,footer,popup,floating',
                'target_locations' => 'nullable|json',
                'target_property_types' => 'nullable|json',
                'target_price_range' => 'nullable|json',
                'target_pages' => 'nullable|json',
                'end_date' => 'nullable|date|after:start_date',
                'is_active' => 'boolean',
                'status' => 'sometimes|in:draft,active,paused,expired,rejected',
                'display_priority' => 'nullable|integer|min:0|max:100',
                'billing_type' => 'nullable|in:free,fixed,per_click,per_impression',
                'budget_total' => 'nullable|numeric|min:0',
                'cost_per_click' => 'nullable|numeric|min:0',
                'cost_per_impression' => 'nullable|numeric|min:0',
                'call_to_action' => 'nullable|string|max:50',
                'additional_images' => 'nullable|json',
                'terms_conditions' => 'nullable|string',
                'show_contact_info' => 'boolean',
                'social_links' => 'nullable|json',
                'metadata' => 'nullable|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Convert title to JSON if provided
            if (isset($validated['title'])) {
                $validated['title'] = json_encode([
                    'en' => $validated['title'],
                    'ar' => $validated['title'],
                    'ku' => $validated['title']
                ]);
            }

            // Convert description to JSON if provided
            if (isset($validated['description']) && $validated['description']) {
                $validated['description'] = json_encode([
                    'en' => $validated['description'],
                    'ar' => $validated['description'],
                    'ku' => $validated['description']
                ]);
            }

            // Convert call_to_action to JSON if provided
            if (isset($validated['call_to_action']) && $validated['call_to_action']) {
                $validated['call_to_action'] = json_encode([
                    'en' => $validated['call_to_action'],
                    'ar' => $validated['call_to_action'],
                    'ku' => $validated['call_to_action']
                ]);
            }

            $bannerAd->update($validated);

            return response()->json([
                'success' => true,
                'data' => $bannerAd->fresh(['property', 'approver']),
                'message' => 'Banner ad updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating banner ad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified banner ad
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $bannerAd = BannerAd::findOrFail($id);
            $bannerAd->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner ad deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting banner ad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active banners for display
     */
    public function getActiveForDisplay(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'position' => 'nullable|string',
                'location' => 'nullable|string',
                'property_type' => 'nullable|string',
                'price' => 'nullable|numeric',
                'page' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid parameters',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = BannerAd::active();

            // Apply targeting filters
            if ($request->filled('position')) {
                $query->byPosition($request->position);
            }

            if ($request->filled('location')) {
                $query->targetingLocation($request->location);
            }

            if ($request->filled('property_type')) {
                $query->targetingPropertyType($request->property_type);
            }

            if ($request->filled('price')) {
                $query->targetingPriceRange($request->price);
            }

            if ($request->filled('page')) {
                $query->where(function ($q) use ($request) {
                    $q->whereJsonContains('target_pages', $request->page)
                        ->orWhereNull('target_pages');
                });
            }

            // Order by priority and limit results
            $limit = $request->get('limit', 10);
            $banners = $query->orderByPriority()
                ->limit($limit)
                ->get();

            // Record views for displayed banners
            foreach ($banners as $banner) {
                $banner->recordView();
            }

            return response()->json([
                'success' => true,
                'data' => $banners,
                'message' => 'Active banners retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record a click on a banner
     */
    public function recordClick(string $id): JsonResponse
    {
        try {
            $banner = BannerAd::findOrFail($id);

            if (!$banner->canDisplay()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner is not active'
                ], 400);
            }

            $banner->recordClick();

            return response()->json([
                'success' => true,
                'message' => 'Click recorded successfully',
                'redirect_url' => $banner->link_url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error recording click: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banner analytics
     */
    public function analytics(string $id): JsonResponse
    {
        try {
            $banner = BannerAd::findOrFail($id);

            $metrics = $banner->getPerformanceMetrics();
            $targeting = $banner->getTargetingSummary();

            $analytics = [
                'banner_info' => [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'type' => $banner->banner_type,
                    'status' => $banner->status,
                    'created_at' => $banner->created_at,
                ],
                'performance' => $metrics,
                'targeting' => $targeting,
                'dates' => [
                    'start_date' => $banner->start_date,
                    'end_date' => $banner->end_date,
                    'last_viewed_at' => $banner->last_viewed_at,
                    'last_clicked_at' => $banner->last_clicked_at,
                ],
                'boost_info' => [
                    'is_boosted' => $banner->is_boosted_now,
                    'boost_start_date' => $banner->boost_start_date,
                    'boost_end_date' => $banner->boost_end_date,
                    'boost_amount' => $banner->boost_amount,
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Analytics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a banner ad
     */
    public function approve(string $id): JsonResponse
    {
        try {
            $banner = BannerAd::findOrFail($id);

            if ($banner->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft banners can be approved'
                ], 400);
            }

            $banner->approve(Auth::id());

            return response()->json([
                'success' => true,
                'data' => $banner->fresh(),
                'message' => 'Banner approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a banner ad
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rejection reason is required',
                    'errors' => $validator->errors()
                ], 422);
            }

            $banner = BannerAd::findOrFail($id);
            $banner->reject($request->reason);

            return response()->json([
                'success' => true,
                'data' => $banner->fresh(),
                'message' => 'Banner rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pause a banner ad
     */
    public function pause(string $id): JsonResponse
    {
        try {
            $banner = BannerAd::findOrFail($id);

            if ($banner->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active banners can be paused'
                ], 400);
            }

            $banner->pause();

            return response()->json([
                'success' => true,
                'data' => $banner->fresh(),
                'message' => 'Banner paused successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error pausing banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resume a banner ad
     */
    public function resume(string $id): JsonResponse
    {
        try {
            $banner = BannerAd::findOrFail($id);

            if ($banner->status !== 'paused') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only paused banners can be resumed'
                ], 400);
            }

            $banner->resume();

            return response()->json([
                'success' => true,
                'data' => $banner->fresh(),
                'message' => 'Banner resumed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resuming banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Boost a banner ad
     */
    public function boost(Request $request, string $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'boost_start_date' => 'required|date|after_or_equal:today',
                'boost_end_date' => 'required|date|after:boost_start_date',
                'boost_amount' => 'required|numeric|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid boost parameters',
                    'errors' => $validator->errors()
                ], 422);
            }

            $banner = BannerAd::findOrFail($id);

            $banner->update([
                'is_boosted' => true,
                'boost_start_date' => $request->boost_start_date,
                'boost_end_date' => $request->boost_end_date,
                'boost_amount' => $request->boost_amount,
            ]);

            return response()->json([
                'success' => true,
                'data' => $banner->fresh(),
                'message' => 'Banner boosted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error boosting banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get banners pending approval
     */
    public function pendingApproval(): JsonResponse
    {
        try {
            $banners = BannerAd::pendingApproval()
                ->with(['property'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banners,
                'message' => 'Pending banners retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving pending banners: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload banner image
     */
    public function uploadImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file',
                    'errors' => $validator->errors()
                ], 422);
            }

            $image = $request->file('image');
            $path = $image->store('banner_images', 'public');
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'url' => $url,
                    'full_url' => url($url)
                ],
                'message' => 'Image uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }
}