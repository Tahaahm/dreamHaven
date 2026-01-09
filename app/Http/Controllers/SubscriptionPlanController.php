<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans.
     * GET /api/subscription-plans
     */
    public function index(Request $request)
    {
        try {
            $query = SubscriptionPlan::query();

            // Filter by type
            if ($request->has('type')) {
                $query->byType($request->type);
            }

            // Filter by active status
            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            // Filter by featured
            if ($request->has('featured')) {
                $query->featured();
            }

            // Apply ordering
            $query->ordered();

            // Pagination or get all
            if ($request->has('paginate') && $request->paginate == 'true') {
                $plans = $query->paginate($request->input('per_page', 15));
            } else {
                $plans = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $plans,
                'message' => 'Subscription plans retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subscription plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created subscription plan.
     * POST /api/subscription-plans
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'required|in:banner,services,real_estate_office,agent',
                'description' => 'nullable|string',
                'duration_months' => 'required|integer|min:1',
                'duration_label' => 'required|string',
                'final_price_iqd' => 'required|numeric|min:0',
                'final_price_usd' => 'required|numeric|min:0',
                'price_per_month_iqd' => 'required|numeric|min:0',
                'price_per_month_usd' => 'required|numeric|min:0',
                'total_amount_iqd' => 'required|numeric|min:0',
                'total_amount_usd' => 'required|numeric|min:0',
                'original_price_iqd' => 'nullable|numeric|min:0',
                'original_price_usd' => 'nullable|numeric|min:0',
                'discount_iqd' => 'nullable|numeric|min:0',
                'discount_usd' => 'nullable|numeric|min:0',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'savings_percentage' => 'nullable|numeric|min:0|max:100',
                'max_properties' => 'nullable|integer|min:0',
                'price_per_property_iqd' => 'nullable|numeric|min:0',
                'price_per_property_usd' => 'nullable|numeric|min:0',
                'features' => 'nullable|array',
                'conditions' => 'nullable|array',
                'note' => 'nullable|string',
                'active' => 'boolean',
                'is_featured' => 'boolean',
                'sort_order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plan = SubscriptionPlan::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Subscription plan created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating subscription plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified subscription plan.
     * GET /api/subscription-plans/{id}
     */
    public function show($id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Subscription plan retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription plan not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified subscription plan.
     * PUT/PATCH /api/subscription-plans/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|in:banner,services,real_estate_office,agent',
                'description' => 'nullable|string',
                'duration_months' => 'sometimes|required|integer|min:1',
                'duration_label' => 'sometimes|required|string',
                'final_price_iqd' => 'sometimes|required|numeric|min:0',
                'final_price_usd' => 'sometimes|required|numeric|min:0',
                'price_per_month_iqd' => 'sometimes|required|numeric|min:0',
                'price_per_month_usd' => 'sometimes|required|numeric|min:0',
                'total_amount_iqd' => 'sometimes|required|numeric|min:0',
                'total_amount_usd' => 'sometimes|required|numeric|min:0',
                'original_price_iqd' => 'nullable|numeric|min:0',
                'original_price_usd' => 'nullable|numeric|min:0',
                'discount_iqd' => 'nullable|numeric|min:0',
                'discount_usd' => 'nullable|numeric|min:0',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'savings_percentage' => 'nullable|numeric|min:0|max:100',
                'max_properties' => 'nullable|integer|min:0',
                'price_per_property_iqd' => 'nullable|numeric|min:0',
                'price_per_property_usd' => 'nullable|numeric|min:0',
                'features' => 'nullable|array',
                'conditions' => 'nullable|array',
                'note' => 'nullable|string',
                'active' => 'boolean',
                'is_featured' => 'boolean',
                'sort_order' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $plan->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Subscription plan updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating subscription plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified subscription plan.
     * DELETE /api/subscription-plans/{id}
     */
    public function destroy($id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting subscription plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscription plans by type
     * GET /api/subscription-plans/type/{type}
     */
    public function getByType($type)
    {
        try {
            $plans = SubscriptionPlan::active()
                ->byType($type)
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $plans,
                'message' => "Subscription plans for {$type} retrieved successfully"
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subscription plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status
     * PATCH /api/subscription-plans/{id}/toggle-active
     */
    public function toggleActive($id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->active = !$plan->active;
            $plan->save();

            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Subscription plan status updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating subscription plan status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function showSubscriptions(Request $request)
    {
        $office = auth('office')->user();

        $query = SubscriptionPlan::where('active', true);

        // Filter by type if provided
        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        // Get plans ordered by featured first, then by sort_order
        $plans = $query->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('office.subscriptions', compact('plans', 'office'));
    }

    public function showSubscriptionDetails($id)
    {
        $office = auth('office')->user();
        $plan = SubscriptionPlan::findOrFail($id);

        return view('office.subscription-details', compact('plan', 'office'));
    }

    public function subscribeNow($id)
    {
        $office = auth('office')->user();
        $plan = SubscriptionPlan::findOrFail($id);

        // Here you would handle the subscription logic
        // For now, just return a view or redirect

        return view('office.subscription-checkout', compact('plan', 'office'));
    }
}
