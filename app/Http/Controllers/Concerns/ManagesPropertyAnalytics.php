<?php

namespace App\Http\Controllers\Concerns;

use App\Helper\ApiResponse;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Statistics, analytics, trends, and admin-dashboard endpoints. Extracted
 * from PropertyController as-is — no behavior changed, only relocated.
 * See ManagesPropertyEngagement.php for the full explanation of why this
 * is safe (traits are compiled directly into the class that uses them).
 */
trait ManagesPropertyAnalytics
{
    public function getStatistics()
    {
        try {
            $stats = [
                'total_properties' => Property::count(),
                'active_properties' => Property::where('is_active', true)->count(),
                'published_properties' => Property::where('published', true)->count(),
                'verified_properties' => Property::where('verified', true)->count(),
                'boosted_properties' => Property::where('is_boosted', true)->count(),

                // By listing type
                'for_rent' => Property::where('listing_type', 'rent')->count(),
                'for_sale' => Property::where('listing_type', 'sell')->count(),

                // By status
                'available' => Property::where('status', 'available')->count(),
                'sold' => Property::where('status', 'sold')->count(),
                'rented' => Property::where('status', 'rented')->count(),
                'pending' => Property::where('status', 'pending')->count(),

                // Analytics
                'total_views' => Property::sum('views'),
                'total_favorites' => Property::sum('favorites_count'),
                'average_rating' => Property::where('rating', '>', 0)->avg('rating'),

                // Time-based
                'properties_this_month' => Property::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
                'properties_this_week' => Property::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),

                // Pricing
                'average_price_usd' => Property::avg(DB::raw("JSON_EXTRACT(price, '$.usd')")),
                'average_price_iqd' => Property::avg(DB::raw("JSON_EXTRACT(price, '$.iqd')")),

                // By type
                'by_type' => Property::select(
                    DB::raw("JSON_EXTRACT(type, '$.category') as property_type"),
                    DB::raw('COUNT(*) as count')
                )->groupBy('property_type')->get(),

                // Utilities
                'with_electricity' => Property::where('electricity', true)->count(),
                'with_water' => Property::where('water', true)->count(),
                'with_internet' => Property::where('internet', true)->count(),

                // Furnished stats
                'furnished' => Property::where('furnished', true)->count(),
                'unfurnished' => Property::where('furnished', false)->count(),
            ];

            return ApiResponse::success(
                'Property statistics retrieved',
                $stats,
                200
            );
        } catch (\Exception $e) {
            Log::error('Statistics error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return ApiResponse::error(
                'Failed to get statistics',
                $e->getMessage(),
                500
            );
        }
    }

    public function getMyAnalytics(Request $request)
    {
        try {
            $user = Auth::user();

            $properties = Property::where('owner_id', $user->id)
                ->where('owner_type', get_class($user))
                ->get();

            $analytics = [
                'total_properties' => $properties->count(),
                'published_properties' => $properties->where('published', true)->count(),
                'draft_properties' => $properties->where('published', false)->count(),
                'verified_properties' => $properties->where('verified', true)->count(),
                'total_views' => $properties->sum('views'),
                'total_favorites' => $properties->sum('favorites_count'),
                'average_rating' => $properties->where('rating', '>', 0)->avg('rating'),
                'most_viewed' => $properties->sortByDesc('views')->first()?->only(['id', 'name', 'views']),
                'most_favorited' => $properties->sortByDesc('favorites_count')->first()?->only(['id', 'name', 'favorites_count']),
                'status_breakdown' => $properties->groupBy('status')->map->count(),
                'listing_type_breakdown' => $properties->groupBy('listing_type')->map->count(),
            ];

            return ApiResponse::success('Your property analytics', $analytics, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Get analytics overview (Admin/Agent)
     */
    public function getAnalyticsOverview(Request $request)
    {
        try {
            $timeframe = $request->get('timeframe', 'month'); // day, week, month, year

            $startDate = match ($timeframe) {
                'day' => now()->subDay(),
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };

            $overview = [
                'total_properties' => Property::count(),
                'new_properties' => Property::where('created_at', '>=', $startDate)->count(),
                'total_views' => Property::sum('views'),
                'new_views' => Property::where('updated_at', '>=', $startDate)->sum('views'),
                'total_favorites' => Property::sum('favorites_count'),
                'verified_properties' => Property::where('verified', true)->count(),
                'boosted_properties' => Property::where('is_boosted', true)->count(),
                'by_status' => Property::groupBy('status')->selectRaw('status, count(*) as count')->get(),
                'by_listing_type' => Property::groupBy('listing_type')->selectRaw('listing_type, count(*) as count')->get(),
                'top_viewed' => Property::orderBy('views', 'desc')->limit(5)->get(['id', 'name', 'views']),
                'recent_properties' => Property::orderBy('created_at', 'desc')->limit(5)->get(['id', 'name', 'created_at']),
            ];

            return ApiResponse::success('Analytics overview', $overview, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get analytics overview', $e->getMessage(), 500);
        }
    }

    /**
     * Get property trends
     */
    public function getTrends(Request $request)
    {
        try {
            $period = $request->get('period', 30); // days

            $trends = [
                'property_creation_trend' => Property::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subDays($period))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),

                'views_trend' => Property::selectRaw('DATE(updated_at) as date, SUM(views) as total_views')
                    ->where('updated_at', '>=', now()->subDays($period))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),

                'average_price_trend' => Property::selectRaw('DATE(created_at) as date, AVG(JSON_EXTRACT(price, "$.usd")) as avg_price')
                    ->where('created_at', '>=', now()->subDays($period))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ];

            return ApiResponse::success('Property trends', $trends, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get trends', $e->getMessage(), 500);
        }
    }

    /**
     * Get specific property analytics
     */
    public function getPropertyAnalytics($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return ApiResponse::error('Property not found', ['id' => $id], 404);
            }

            $analytics = [
                'basic_stats' => [
                    'views' => $property->views,
                    'favorites_count' => $property->favorites_count,
                    'rating' => $property->rating,
                    'created_at' => $property->created_at,
                ],
                'view_analytics' => $property->view_analytics ?? [],
                'favorites_analytics' => $property->favorites_analytics ?? [],
                'performance_score' => $this->calculatePerformanceScore($property),
                'recommendations' => $this->getPropertyRecommendations($property),
            ];

            return ApiResponse::success('Property analytics', $analytics, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get property analytics', $e->getMessage(), 500);
        }
    }

    public function getAdminDashboard()
    {
        try {
            $dashboard = [
                'overview' => [
                    'total_properties' => Property::count(),
                    'pending_verification' => Property::where('verified', false)->count(),
                    'active_properties' => Property::where('is_active', true)->count(),
                    'boosted_properties' => Property::where('is_boosted', true)->count(),
                ],
                'recent_activity' => [
                    'new_today' => Property::whereDate('created_at', today())->count(),
                    'new_this_week' => Property::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
                    'new_this_month' => Property::whereMonth('created_at', now()->month)->count(),
                ],
                'status_distribution' => Property::groupBy('status')->selectRaw('status, count(*) as count')->get(),
                'recent_properties' => Property::with('owner')->orderBy('created_at', 'desc')->limit(10)->get(),
                'top_performing' => Property::orderBy('views', 'desc')->limit(10)->get(),
                'flagged_properties' => Property::where('rating', '<', 2)->orWhere('views', '<', 5)->count(),
            ];

            return ApiResponse::success('Admin dashboard data', $dashboard, 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get dashboard data', $e->getMessage(), 500);
        }
    }

    /**
     * Get flagged properties
     */
    public function getFlaggedProperties(Request $request)
    {
        try {
            $flagged = Property::where(function ($query) {
                $query->where('rating', '<', 2)
                    ->orWhere('views', '<', 5)
                    ->orWhere('favorites_count', '<', 1);
            })
                ->where('created_at', '<', now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return ApiResponse::success('Flagged properties', [
                'data' => $flagged->items(),
                'total' => $flagged->total(),
            ], 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to get flagged properties', $e->getMessage(), 500);
        }
    }

    /**
     * Calculate property performance score
     */
    private function calculatePerformanceScore($property)
    {
        $score = 0;

        // Views score (0-30 points)
        $viewsScore = min(($property->views / 100) * 30, 30);

        // Favorites score (0-25 points)
        $favoritesScore = min(($property->favorites_count / 50) * 25, 25);

        // Rating score (0-25 points)
        $ratingScore = ($property->rating / 5) * 25;

        // Verification bonus (0-10 points)
        $verificationScore = $property->verified ? 10 : 0;

        // Age penalty (newer properties get higher scores)
        $ageDays = $property->created_at->diffInDays(now());
        $ageScore = max(10 - ($ageDays / 30), 0);

        $totalScore = $viewsScore + $favoritesScore + $ratingScore + $verificationScore + $ageScore;

        return [
            'total_score' => round($totalScore, 1),
            'breakdown' => [
                'views' => round($viewsScore, 1),
                'favorites' => round($favoritesScore, 1),
                'rating' => round($ratingScore, 1),
                'verification' => $verificationScore,
                'age' => round($ageScore, 1),
            ],
            'grade' => $this->getPerformanceGrade($totalScore)
        ];
    }

    /**
     * Get performance grade based on score
     */
    private function getPerformanceGrade($score)
    {
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 50) return 'D';
        return 'F';
    }

    /**
     * Get property recommendations based on analytics
     */
    private function getPropertyRecommendations($property)
    {
        $recommendations = [];

        if ($property->views < 10) {
            $recommendations[] = 'Consider improving your property description and adding more high-quality images to increase views.';
        }

        if ($property->favorites_count < 2) {
            $recommendations[] = 'Your property might benefit from competitive pricing or highlighting unique features.';
        }

        if (!$property->verified) {
            $recommendations[] = 'Get your property verified to increase trust and visibility.';
        }

        if (empty($property->virtual_tour_url)) {
            $recommendations[] = 'Adding a virtual tour can significantly increase user engagement.';
        }

        if ($property->rating < 3 && $property->rating > 0) {
            $recommendations[] = 'Consider reviewing and improving your property listing based on user feedback.';
        }

        return $recommendations;
    }
}
